<?php

use GuzzleHttp\Psr7\Uri as Uri;

class ListHandler {

	private static $base = 'https://lists.w3.org/';
	private static $base_path = '/Archives/Public/';
	private static $page_size = 20;

	private static $db_definition =<<<"END"

	CREATE TABLE period(
		id INTEGER PRIMARY KEY,
		path TEXT UNIQUE,
		label TEXT,
		start_date TEXT,
		end_date TEXT,
		count INTEGER,
		new_thread_count INTEGER
	);
	CREATE INDEX period_path ON period(path);

	CREATE TABLE message(
		id INTEGER PRIMARY KEY,
		path TEXT UNIQUE,
		subject TEXT,
		author TEXT,
		date TEXT,
		date_time TEXT,
		in_reply_to TEXT,
		replies TEXT,
		contemporary_root TEXT,
		root TEXT
	);
	CREATE INDEX message_path ON message(path);

END;

	private $list_name;
	private $list_path;
	private $cache_dir;
	private $template_dir;
	private $mid_resolver;
	private $db_handle;
	private $http_proxy;

	public function __construct($list_name, $cache_dir, $template_dir, $http_proxy, $mid_resolver) {
		$this->list_name = $list_name;
		$this->cache_dir = $cache_dir;
		$this->template_dir = $template_dir;
		$this->http_proxy = $http_proxy;
		$this->mid_resolver = $mid_resolver;
	
		$base_path = self::$base_path;
		$list_path = "$base_path$list_name/";
		$this->list_path = $list_path;
		$db_file = $cache_dir . $list_path . 'index.sq3';
		mkdir_for_file($db_file);
		if (!file_exists($db_file)) {
			$this->create_db($db_file);
			$this->init();
		}
		else $this->open_db($db_file);
		return $this;
	}

	public function get_index() {
		$path = $this->list_path;
		$local_path = $this->cache_dir . $path;
		if (preg_match('/\/$/', $local_path)) $local_path .= 'index.html';
		if (file_exists($local_path)) return get_file_contents($local_path);

		$contents = $this->render_index();
		put_file_contents($local_path, $contents);
		return $contents;
	}

	public function get_period($period) {
		$path = $this->list_path . $period . '/';
		$local_path = $this->cache_dir . $path;
		if (preg_match('/\/$/', $local_path)) $local_path .= 'index.html';
		if (file_exists($local_path)) return get_file_contents($local_path);

		$contents = $this->render_period($path);
		put_file_contents($local_path, $contents);
		return $contents;
	}

	public function get_period_by_new_threads($period) {
		$path = $this->list_path . $period . '/new-threads.html';
		$local_path = $this->cache_dir . $path;
		if (file_exists($local_path)) return get_file_contents($local_path);

		$contents = $this->render_period_by_new_threads($period);
		put_file_contents($local_path, $contents);
		return $contents;
	}

	public function get_message($period, $item) {
		$path = $this->list_path . $period . '/' . $item . '.html';
		$local_path = $this->cache_dir . $path;
		if (file_exists($local_path)) return get_file_contents($local_path);

		$contents = $this->render_message($path);
		put_file_contents($local_path, $contents);
		return $contents;
	}

	private function render_index() {
		$type = 'list-index';
		$template_dir = $this->template_dir;

		$dbh = $this->db_handle;
		$periods = $dbh->query(<<<"END"

			SELECT * FROM period ORDER BY start_date DESC;
END
		);
		$periods = iterator_to_array($periods);

		$years = [];
		foreach ($periods as $i => $period) {
			$start_date = date_create_from_format('Y-m-d', $period['start_date']);
			$y = date_format($start_date, 'Y');
			$m = date_format($start_date, 'n');
			$data = [
				'messages' => [
					'path' => $period['path'],
					'count' => $period['count']
				],
				'threads' => [
					'path' => $period['path'] . 'new-threads.html',
					'count' => $period['new_thread_count']
				]
			];
			if ($i === 0) $data['position'] = 'newest';
			if ($i === count($periods) - 1) $data['position'] = 'oldest';
			if (!$years["$y"]) $years["$y"] = self::create_empty_year();
			$years["$y"]["$m"] = $data;
		}

		$template_path = "$template_dir/$type.php";		
		$params = [ 'years' => $years ];
		$contents = php_render($template_path, $params);

		$template_path = "$template_dir/$type.xsl";
		if (!file_exists($template_path)) return $contents;

		$dom = parse_html($contents);
		$dom = xslt_proc($template_path, $dom);
		return $dom->saveHTML();
	}

	private static function create_empty_year() {
		$year = [];
		for ($i = 1; $i <= 12; $i++) {
			$year["$i"] = [];
		}
		return $year;
	}

	private function render_period($path) {
		$type = 'message-period';
		$dom = $this->fetch_document($path);
		$dom = $this->localize_period($dom);

		$template_dir = $this->template_dir;
		$template_path = "$template_dir/$type.xsl";
		
		if (file_exists($template_path)) $dom = xslt_proc($template_path, $dom);
		return $dom->saveHTML();
	}

	private function localize_period($dom) {
		return $dom;
	}

	private function render_period_by_new_threads($period) {
		$type = 'threads';
		$template_dir = $this->template_dir;
		
		$dbh = $this->db_handle;
		$path = $this->list_path . $period . '/';
		$threads = $dbh->query(<<<"END"

		SELECT * FROM message WHERE path LIKE "$path%" AND path=contemporary_root AND in_reply_to IS NULL ORDER BY date DESC;

END
		);

		$threads = iterator_to_array($threads);
		foreach ($threads as &$thread) { // WARN updating original objects
			$date = date_create_from_format('Y-m-d', $thread['date']);
			$thread['date'] = $date;
/* FIXME datetime isn't stored in database
			$datetime = date_create_from_format('Y-m-d H:i:s', $thread['datetime']);
			$thread['datetime'] = $datetime;
*/
		}
		unset($thread);

		$periods = $dbh->query(<<<"END"

			SELECT * FROM period ORDER BY start_date DESC;
END
		);
		$periods = iterator_to_array($periods);
		$period_index;
		foreach ($periods as $i => $per) {
			if ($per['path'] === $path) $period_index = $i;
		}
		$older_period = $periods[$period_index + 1];
		$newer_period = $periods[$period_index - 1];
		$older_path = $older_period ? $older_period['path'] . 'new-threads.html' : false;
		$newer_path = $newer_period ? $newer_period['path'] . 'new-threads.html' : false;


		$template_path = "$template_dir/$type.php";
		$params = [ messages => $threads ];
		if ($older_path) $params['older'] = [ 'path' => $older_path ];
		if ($newer_path) $params['newer'] = [ 'path' => $newer_path ];
		$contents = php_render($template_path, $params);

		$template_path = "$template_dir/$type.xsl";
		if (!file_exists($template_path)) return $contents;

		$dom = xslt_proc($template_path, $dom);
		$dom = parse_html($contents);
		return $dom->saveHTML();
	}

	private function render_message($path) {
		$type = 'message';
		$dom = $this->fetch_document($path);
		$dom = $this->localize_message($dom);

		$template_dir = $this->template_dir;
		$template_path = "$template_dir/$type.xsl";
		
		if (file_exists($template_path)) $dom = xslt_proc($template_path, $dom);
		return $dom->saveHTML();
	}
	
	private function localize_message($dom) {
		$xpath = new DOMXPath($dom);
		$mid_msg = 'message archived in another';
		$elts = $xpath->query("//a[contains(@title, '$mid_msg')]");
		foreach ($elts as $elt) {
			if (strcasecmp($elt->parentNode->tagName, 'del') === 0) { // TODO how to best indicate broken In-Reply-To??
				continue;
			}
			$url = $elt->getAttribute('href');
			if (strpos($url, $this->list_path) !== 0) {
				$elt->setAttribute('title', 'Message archived in another list');
				$elt->setAttribute('href', self::$base . $url);
				continue;
			}
			$elt->setAttribute('href', $url);
			$linkedDom = $this->fetch_document($url);
			$meta = $this->get_message_meta($linkedDom);
			// TODO date-time
			$author = $meta['author'];
			$subject = $meta['subject'];
			$elt->setAttribute('title', "$author: '$subject'");
			if (strpos($elt->textContent, $mid_msg) !== false) $elt->textContent = "$author: '$subject'"; 
		}
		return $dom;
	}


	private function open_db($db_file) {
		$this->db_handle = $dbh = new PDO("sqlite:$db_file");
	}

	private function create_db($db_file) { 
		$this->db_handle = $dbh = new PDO("sqlite:$db_file");

		$dbh->exec(self::$db_definition);
	}

	private function init() {
		$this->init_list();
	}

	private function init_list() {

		$contents = $this->http_proxy->get($this->list_path);
		$dom = parse_html($contents);

		$xpath = new DOMXPath($dom);
		$rows = $xpath->query("//table/tbody/tr");
		$rows = array_reverse(iterator_to_array($rows)); // $rows are now oldest first

		foreach ($rows as $row) $this->init_period($row, $xpath);

		$dbh = $this->db_handle;
		$rows = $dbh->query(<<<"END"

			SELECT * FROM period ORDER BY start_date ASC;
END
		);

		foreach ($rows as $row) {
			$path = $row['path'];
			$thread_path = $path . 'thread.html';
			$start_date = date_create_from_format('Y-m-d', $row['start_date']);
			$this->init_period_sorted_by_thread($thread_path, $path, $start_date);
		}

		$this->crosslink_periods();
		$this->count_new_threads();
	}

	private function init_period($row, $xpath) {
		$cells = $xpath->query("td", $row);
		$date_cell = $cells[0];
		$label = $xpath->evaluate("string(a)", $date_cell);
		$rel_href = $xpath->evaluate("string(a/@href)", $date_cell);
		$href = $this->list_path . $rel_href;
		$datestring = basename($rel_href) . '01';
		$date = date_create_from_format('YMd', $datestring);
		$start_date = date_format($date, 'Y-m-d');

		$count_cell = $cells[4];
		$count = $xpath->evaluate("string(.)", $count_cell);

		$dbh = $this->db_handle;
		$dbh->exec(<<<"END"

		INSERT INTO period (path, label, start_date, count) VALUES ("$href", "$label", "$start_date", $count);

END
		);
	}

	private function init_period_sorted_by_thread($thread_path, $path, $start_date) {
		$contents = $this->http_proxy->get($thread_path);
		$dom = parse_html($contents);
		$contents = null;

		$xpath = new DOMXPath($dom);
		$root_items = $xpath->query("//div[@class='messages-list']/ul/li");
		$root_items = array_reverse(iterator_to_array($root_items));
		foreach ($root_items as $root_item) $this->init_thread($root_item, $path, $xpath, $start_date);
	}

	private function init_thread($root_item, $path, $xpath, $start_date) {
		$root_rel_href = $xpath->evaluate("string(a[1]/@href)", $root_item);
		$root_href = "$path$root_rel_href";
		$items = $xpath->query("descendant-or-self::li", $root_item);

		foreach ($items as $item) $this->init_item($item, $root_href, $path, $xpath, $start_date);
	}

	private function init_item($item, $root_href, $path, $xpath, $start_date) {

		$rel_href = $xpath->evaluate("string(a[1]/@href)", $item);
		$href = "$path$rel_href";
		$subject = $xpath->evaluate("string(a[1])", $item);
		$author = $xpath->evaluate("string(a[2]/em)", $item);
		$datestring = $xpath->evaluate("string(em)", $item);
		$datestring .= date_format($start_date, 'Y');
		$date = date_create_from_format('\(l, d F\)Y', $datestring);
		$datestring = date_format($date, 'Y-m-d');

		$dbh = $this->db_handle;
		$dbh->exec(<<<"END"

		INSERT INTO message (path, subject, author, date, contemporary_root) VALUES ("$href", "$subject", "$author", "$datestring", "$root_href");

END
		);
	}

	private function crosslink_periods() {
		$dbh = $this->db_handle;
		$lost_items = $dbh->query(<<<"END"

			SELECT * FROM message WHERE path=contemporary_root AND subject LIKE "Re:%"; 
END
		);
		foreach ($lost_items as $item) $this->crosslink_item($item);
	}

	private function crosslink_item($item) {
		$path = $item['path'];
		$dom = $this->fetch_document($path);
		$xpath = new DOMXPath($dom);
		$elts = $xpath->query("//a[contains(.,'In reply to')]");
		if ($elts->length <= 0) return; // FIXME Found an orphan. What to do??
		$elt = $elts[0];
		if (strcasecmp($elt->parentNode->tagName, 'del') === 0) return; // FIXME in_reply_to broken-link

		$href = $elt->getAttribute('href');

		if (strpos($href, $this->list_path) !== 0) { // FIXME in_reply_to another list. What to do??
			return;
		}
		$dbh = $this->db_handle;
		$dbh->exec(<<<"END"

		UPDATE message SET in_reply_to="$href" WHERE path="$path";

END
		);

		$dbh->exec(<<<"END"

		UPDATE message SET replies=(coalesce(replies, "") || " $path") WHERE path="$href" AND (replies IS NULL OR replies NOT LIKE "%$path%");

END
		);
	}

	private function count_new_threads() {
		$dbh = $this->db_handle;
		$thread_starters = $dbh->query(<<<"END"

			SELECT * FROM message WHERE path=contemporary_root AND in_reply_to IS NULL; 
END
		);

		$thread_starters = iterator_to_array($thread_starters);
		$period_new_threads = [];
		foreach ($thread_starters as $item) {
			$path = $item['path'];
			$period_path = dirname($path) . '/';			
			if (!$period_new_threads[$period_path]) {
				$period_new_threads[$period_path] = 1;
			}
			else {
				$period_new_threads[$period_path]++;
			}
		}
		foreach ($period_new_threads as $path => $count) {
			$dbh->exec(<<<"END"
	
			UPDATE period SET new_thread_count=$count WHERE path="$path";

END
			);

		}
	}

	private function fetch_document($path) {
		$contents = $this->http_proxy->get($path);
		$dom = parse_html($contents);
		return $this->localize_document($dom, $path);
	}

	private function localize_document($dom, $path) {
		$xpath = new DOMXPath($dom);
		$elts = $xpath->query("//link[contains(@rel, 'stylesheet')]");
		foreach ($elts as $elt) {
			$href = $elt->getAttribute('href');
			$url = $this->localize_url($href, $path);
			$elt->setAttribute('href', $url);
		}
		$elts = $xpath->query("//a[@href]");
		foreach ($elts as $elt) {
			$href = $elt->getAttribute('href');
			$url = $this->localize_url($href, $path);
			if (!$url) { // TODO how to best indicate broken links??
				$del = $dom->createElement('del');
				$elt->parentNode->replaceChild($del, $elt);
				$del->appendChild($elt);
				$elt->setAttribute('title', 'Broken link');
				$elt->setAttribute('href', 'javascript:void(0);');
				continue;
			}
			$elt->setAttribute('href', $url);
		}

		return $dom;
	}

	private function localize_url($url, $referrer) {
		$uri = new Uri($url);
		$host = $uri->getHost();

		if (!$host) return $url;

		$path = $uri->getPath();

		if ($host == 'lists.w3.org') return $path;
		if ($host != 'www.w3.org') return $url;

		if (preg_match('/^\/StyleSheets\//', $path)) {
			if (!preg_match('/\.css$/', $path)) $path = $path . '.css';
			return $path;
		}

		$scheme = $uri->getScheme();
		if ($uri->getScheme() == 'http') $uri = $uri->withScheme('https');

		if (preg_match('/^\/mid\//i', $path)) {
			$mid = preg_replace('/^\/mid\//i', '', $path);
			$mid_resolver = $this->mid_resolver;
			return $mid_resolver->resolve($mid, $referrer);
		}

		// if not StyleSheet or mid lookup
		return "$uri";
	}

	private function get_message_meta($dom) {
		$xpath = new DOMXPath($dom);
		$authorElt = $xpath->query("//meta[starts-with(@name,'Author')]")[0];
		$author = preg_split('/\s*\(/', $authorElt->getAttribute('content'))[0];
		$subjectElt = $xpath->query("//meta[@name='Subject']")[0];
		$subject = $subjectElt->getAttribute('content');
		return (['author' => $author, 'subject' => $subject]);
	}


}

?>

