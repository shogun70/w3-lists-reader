<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use GuzzleHttp\Psr7\Uri as Uri;
use GuzzleHttp\Client;

class ListsProxy {

	private static $base = 'https://lists.w3.org/';

	private $cache_dir;
	private $http_client;

	public function __construct($cache_dir, $http_client) {
		$this->cache_dir = $cache_dir;
		$this->http_client = $http_client;
		return $this;
	}	

	public function get($path) {
		return $this->get_file($path);	
	}

	private function get_file($path) {
		$cache_dir = $this->cache_dir;
		$local_path = "$cache_dir/$path";
		if (preg_match('/\/$/', $local_path)) $local_path .= 'index.html';
		if (file_exists($local_path)) return get_file_contents($local_path);

		$response = $this->fetch($path);
		$status_code = $response->getStatusCode();
		switch ($status_code) {
		case 200:
			$contents = $response->getBody();
			put_file_contents($local_path, $contents);
			return $contents;
		default:
			throw new Exception("Failed to fetch $path");
		}
	}

	private function fetch($path) {
		return $this->http_client->request('GET', $path, [
			'base_uri' => self::$base,
			'http_errors' => false, // don't throw on 404, 500, etc
			'allow_redirects' => false
		]);
	}


}

?>
