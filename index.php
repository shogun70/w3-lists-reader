<?php
ini_set('display_errors', '0');

$root_dir = ".";

require 'vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use GuzzleHttp\Psr7\Uri as Uri;
use GuzzleHttp\Client;
use Monolog\ErrorHandler as ErrorHandler;

$log_dir = "$root_dir/logs";
$template_dir = "$root_dir/templates";
$content_dir = "$root_dir/content";
$remote_dir = "https://lists.w3.org/";

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$app = new \Slim\App([ 'settings' => $config ]);
$container = $app->getContainer();
$container['view'] = new \Slim\Views\PhpRenderer("$template_dir/");
$container['cache'] = new \Slim\HttpCache\CacheProvider();
$container['logger'] = function($c) {
	global $log_dir;
	$logger = new \Monolog\Logger('my_logger');
	$file_handler = new \Monolog\Handler\StreamHandler("$log_dir/app.log");
	$logger->pushHandler($file_handler);
	return $logger;
};
ErrorHandler::register($container['logger']);

$app->add(new \Slim\HttpCache\Cache('public', 86400)); # TODO should this be global or per resource??

$app->get('{path:.*}', function (Request $request, Response $response, $args) {
	$contents = 'FILE NOT FOUND';
	$path = $args['path'];
	$contents = get_file($path);
	$response = $this->view->render($response, "default.php", ["contents" => $contents ]);
	return $response;
});

$app->run();

function get_file_async($path) {
	return (new FulfilledPromise())
	->then(function() { return get_file($path); });
}

function get_file($path) {
	global $content_dir;
	$local_path = "$content_dir$path";
	if (file_exists($local_path)) return file_get_contents($local_path);
	return generate_file($path);
}

function generate_file_async($path) {
	return (new FulfilledPromise())
	->then(function() { return generate_file($path); });
}

function generate_file($path) {
	$dom = generate_document($path);
	return $dom->saveHTML();
}

function generate_document($path) {
	$rr = fetch($path);
	$contents = $rr->getBody();
	return localize_content($contents, 'message');
}

function localize_url($url) {
	$uri = new Uri($url);
	$host = $uri->getHost();

	if (!$host) return $url;

	if ($host != 'www.w3.org') return $url;

	$path = $uri->getPath();
	if (preg_match('/^\/StyleSheets\//', $path)) {
		if (!preg_match('/\.css$/', $path)) $path = $path . '.css';
		return $path;
	}

	$scheme = $uri->getScheme();
	if ($uri->getScheme() == 'http') $uri = $uri->withScheme('https');

	if (preg_match('/^\/mid\//', $path)) {
		$resolve_mid = fetch("$uri");
		$status_code = $resolve_mid->getStatusCode();
		if ($status_code < 300 || $status_code > 399) return '';
		// FIXME status should be 301 or other redirect
		$resolved_url = $resolve_mid->getHeader('Location')[0];
		return localize_url($resolved_url);
	}

	# if not StyleSheet or mid lookup
	return "$uri";
}

function fetch($url) {
	global $remote_dir;

	$uri = new Uri($url);

	$host = $uri->getHost();
	if ($host) {
		$actual_remote_dir = $uri->getScheme() . '://' . $uri->getHost();
		$path = $uri->getPath();
	}
	else {
		$actual_remote_dir = $remote_dir;
		$path = $url;
	}

	$client = new Client([
		'base_uri' => $actual_remote_dir,
		'timeout'  => 5.0,
		'allow_redirects' => false
	]);
		
	$rr = $client->request('GET', $path);
	return $rr;
}

function localize_content($content, $type) {
	$dom = new DOMDocument;
	$dom->loadHTML($content);

	$xpath = new DOMXPath($dom);
	$elts = $xpath->query("//link[contains(@rel, 'stylesheet')]");
	foreach ($elts as $elt) {
		$href = $elt->getAttribute('href');
		$url = localize_url($href);
		$elt->setAttribute('href', $url);
	}

	$mid_msg = 'message archived in another';
	$elts = $xpath->query("//a[contains(@title, '$mid_msg')]");
	foreach ($elts as $elt) {
		$href = $elt->getAttribute('href');
echo $href;
		$url = localize_url($href);
		if (!$url) { // TODO can we indicate a dead In-Reply-To??
echo $url;
			$elt->parentNode->removeChild($elt);
			continue;
		}
		$linkedDom = generate_document($url);
		$meta = get_meta($linkedDom);
		$author = $meta['author'];
		$subject = $meta['subject'];
		$elt->setAttribute('title', "$author: '$subject'");
		$elt->setAttribute('href', $url);
		if (strpos($elt->textContent, $mid_msg) !== false) $elt->textContent = "$author: '$subject'"; 
	}

	global $template_dir;
	$template_path = "$template_dir/$type.xsl";
	
	return xslt_proc($template_path, $dom);
}

function get_meta($dom) {
	$xpath = new DOMXPath($dom);
	$authorElt = $xpath->query("//meta[starts-with(@name,'Author')]")[0];
	$author = preg_split('/\s*\(/', $authorElt->getAttribute('content'))[0];
	$subjectElt = $xpath->query("//meta[@name='Subject']")[0];
	$subject = $subjectElt->getAttribute('content');
	return (['author' => $author, 'subject' => $subject]);
}

function xslt_proc($template, $dom, $args) {
	$xsl = new DOMDocument;
	$xsl->load($template);

	$proc = new XSLTProcessor;
	$proc->importStyleSheet($xsl);

	if ($args) $proc->setParameter('', $args);
	return $proc->transformToDoc($dom);
}

?>

