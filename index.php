<?php
#ini_set('display_errors', '0');

$http_timeout = 10;

$lists_host = 'lists.w3.org';
$mid_host = 'www.w3.org';


$root_dir = getcwd();
$index_file = "$root_dir/index";
$log_dir = "$root_dir/logs";
$template_dir = "$root_dir/templates";
$content_dir = "$root_dir/content";
$remote_dir = "https://$lists_host/";
$cacert_dir = "$root_dir/cacert";
$cacert_bundle = "$cacert_dir/_w3_org.pem";

$list_path = "/Archives/Public/$list_name/";

require 'vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use GuzzleHttp\Psr7\Uri as Uri;
use GuzzleHttp\Client;
use Monolog\ErrorHandler as ErrorHandler;

$config = [
	'settings' => [
		'displayErrorDetails' => true,
		'addContentLengthHeader' => false
	],
	'log_file' => "$log_dir/app.log"
];

$app = new \Slim\App($config);
$container = $app->getContainer();

$container['logger'] = function($c) {
	$logger = new \Monolog\Logger('my_logger');
	$file_handler = new \Monolog\Handler\StreamHandler($c['log_file']);
	$logger->pushHandler($file_handler);
	return $logger;
};
ErrorHandler::register($container['logger']);

$container['cache'] = function($c) {
	return new \Slim\HttpCache\CacheProvider();
};
$app->add(new \Slim\HttpCache\Cache('public', 86400)); # TODO should this be global or per resource??

$lists_cache_dir = "$root_dir/$lists_host";
$lists_client = new Client([
		'headers' => [ 'Accept-Encoding' => 'gzip,deflate' ],
		'decode_content' => true,
		'timeout'  => $http_timeout,
		'verify' => $cacert_bundle,
		'http_errors' => false, // don't throw on 404, 500, etc
		'allow_redirects' => false
	]);
$lists_proxy = new ListsProxy($lists_cache_dir, $lists_client);

$mid_cache_dir = "$root_dir/$mid_host/mid";
$mid_db_file = "$mid_cache_dir/index.sq3";
$mid_client = new Client([
		'headers' => [ 'Accept-Encoding' => 'gzip,deflate' ],
		'decode_content' => true,
		'timeout'  => $http_timeout,
		'verify' => $cacert_bundle,
		'http_errors' => false, // don't throw on 404, 500, etc
		'allow_redirects' => false
	]);
$mid_resolver = new MessageIdResolver($mid_db_file, $mid_client);

$app->get('/Archives/Public/{list:[-A-Za-z0-9]+}/[{period:\d{4}[A-Za-z]{3}}/[{item:\d{4}|new-threads}.html]]', function (Request $request, Response $response, $args) {
	$contents = 'FILE NOT FOUND';
	$list = $args['list'];
	$period = $args['period'];
	$item = $args['item'];
	$threads = ($item === 'new-threads');
	if ($threads) $item = false;
	$path = $request->getUri()->getPath();
	
	global $content_dir;
	global $template_dir;
	global $lists_proxy;
	global $mid_resolver;

	$list_handler = new ListHandler($list, $content_dir, $template_dir, $lists_proxy, $mid_resolver);

	if ($period) {
		if ($threads) $contents = $list_handler->get_period_by_new_threads($period);
		elseif ($item) $contents = $list_handler->get_message($period, $item); 
		else $contents = $list_handler->get_period($period);
	}
	else $contents = $list_handler->get_index();

	if ($period && $item || !$period) {
		$contents = php_render($template_dir . "/default.php", ["contents" => $contents ]);
	}

	$response->write($contents);
	return $response;
});

$app->get('/Archives/Public/', function (Request $request, Response $response) {
	$contents = 'FILE NOT FOUND';
	$path = $request->getUri()->getPath();
	
	global $index_file;
	global $content_dir;
	global $template_dir;
	global $lists_proxy;

	$index_handler = new IndexHandler($index_file, $content_dir, $template_dir, $lists_proxy);

	$contents = $index_handler->get_index();

	$contents = php_render($template_dir . "/default.php", ["contents" => $contents ]);
	$response->write($contents);
	return $response;
});

$app->get('/', function (Request $request, Response $response) {
	$contents = 'FILE NOT FOUND';
	$path = $request->getUri()->getPath();
	
	global $content_dir;
	$contents = get_file_contents("$content_dir/index.html");

	$response->write($contents);
	return $response;
});


$app->run();

?>
