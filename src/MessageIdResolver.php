<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use GuzzleHttp\Psr7\Uri as Uri;
use GuzzleHttp\Client;

class MessageIdResolver {

	private static $mid_base = 'https://www.w3.org/mid/';
	// WARN sometimes https://www.w3.org/mid/<mid> 
	// can redirect to the same URL leading to infinite recursion.
	// This is why we only accept the first redirect.

	private static $db_definition =<<<"END"

			CREATE TABLE record(
				id INTEGER PRIMARY KEY,
				mid TEXT UNIQUE,
				success INTEGER,
				http_status INTEGER,
				path TEXT,
				referrer TEXT
			);
			CREATE INDEX mid_index ON record(mid);

END;

	private $db_handle;
	private $http_client;

	public function __construct($db_file, $http_client) {
		$this->http_client = $http_client;
		mkdir_for_file($db_file);
		if (!file_exists($db_file)) $this->create_db($db_file);
		else $this->open_db($db_file);
		return $this;
	}	

	private function open_db($db_file) {
		$this->db_handle = $dbh = new PDO("sqlite:$db_file");
	}

	private function create_db($db_file) { 
		$this->db_handle = $dbh = new PDO("sqlite:$db_file");

		$dbh->exec(self::$db_definition);
	}

	private function put_record($mid, $success, $http_status, $path, $referrer) {
		$dbh = $this->db_handle;
		$dbh->exec(<<<"END"
			INSERT INTO record (mid, success, http_status, path, referrer) 
					VALUES ("$mid", "$success", "$http_status", "$path", "$referrer");
END
		);
	}

	private function get_record($mid) {
		$dbh = $this->db_handle;
		$records = $dbh->query(<<<"END"
			SELECT * FROM record WHERE mid="$mid";
END
		);
		$records = iterator_to_array($records);
		return count($records) ? $records[0] : false; // FIXME what if more than one record
	}

	private function add_referrer($mid, $referrer) {
		$dbh = $this->db_handle;
		$dbh->exec(<<<"END"
			UPDATE record SET referrer=(coalesce(referrer, "") || " $referrer")
			WHERE mid="$mid" AND (referrer IS NULL OR referrer NOT LIKE "%$referrer%");
END
		);
		
	}

	public function resolve($mid, $referrer) {
		$record = $this->get_record($mid);
		if ($record && array_key_exists('id', $record)) {
			$this->add_referrer($mid, $referrer);
			if (array_key_exists('success', $record) && $record['success']) return $record['path'];
			else return null;
		}

		$mid_response = $this->fetch($mid);

		$status_code = $mid_response->getStatusCode();
		$success = 0;
		$local_url = null;
		switch ($status_code) {
		case 301: case 302: case 303: case 307: case 308:
			$resolved_url = $mid_response->getHeader('Location')[0];
			$local_url = $this->localize_url($resolved_url);
			if ($local_url) $success = 1;
			break;
		default:
		}
		$this->put_record($mid, $success, $status_code, $local_url, $referrer);
		return $local_url;
	}
	
	private function localize_url($url) {
		$uri = new Uri($url);
		$host = $uri->getHost();

		if (!$host) return $url;

		$path = $uri->getPath();

		if ($host == 'lists.w3.org') return $path;
		return null;
	}

	private function fetch($mid) {
		return $this->http_client->request('GET', 
			self::$mid_base . $mid, // FIXME use full URL because $mid could contain ':'
			[
			'base_uri' => self::$mid_base, // WARN currently redundant
			'http_errors' => false, // prevent throw on 404, 500, etc
			'allow_redirects' => false
			]);
	}


	
}
?>
