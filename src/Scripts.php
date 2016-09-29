<?php

//ini_set('display_errors', '0');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use GuzzleHttp\Psr7\Uri as Uri;
use GuzzleHttp\Client;
use Monolog\ErrorHandler as ErrorHandler;

require 'vendor/autoload.php';

class Scripts {

public static function postInstall() {

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


$lists_string = file_get_contents($index_file);
$lists = preg_split("/\s+/", trim($lists_string));


foreach ($lists as $list_name) {
	$list_path = "/Archives/Public/$list_name/";
	$list_content_dir = "$content_dir$list_path";
	if (file_exists($list_content_dir)) exec("rm -r $list_content_dir");
	$list_handler = new ListHandler($list_name, $content_dir, $template_dir, $lists_proxy, $mid_resolver);

}

} // end postInstall

} // end class

?>


