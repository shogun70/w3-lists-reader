<?php

use GuzzleHttp\Psr7\Uri as Uri;

class IndexHandler {

	private static $base = 'https://lists.w3.org/';
	private static $base_path = '/Archives/Public/';

	private $index_file;
	private $cache_dir;
	private $template_dir;
	private $http_proxy;
	private $lists;

	public function __construct($index_file, $cache_dir, $template_dir, $http_proxy) {
		$this->index_file = $index_file;
		$this->cache_dir = $cache_dir;
		$this->template_dir = $template_dir;
		$this->http_proxy = $http_proxy;
	
		$base_path = self::$base_path;
		$this->path = $base_path;

		$lists_string = file_get_contents($this->index_file);
		$this->lists = preg_split("/\s+/", $lists_string);

		return $this;
	}

	public function get_index() {
		$path = $this->path;
		$local_path = $this->cache_dir . $path;
		if (preg_match('/\/$/', $local_path)) $local_path .= 'index.html';

		if (file_exists($local_path)) return get_file_contents($local_path);

		$contents = $this->render_index2($path);
		put_file_contents($local_path, $contents);
		return $contents;
	}

	private function render_index2($path) {
		$type = 'public-index';
		$template_dir = $this->template_dir;
		$dom = $this->fetch_document($path);

		$info = $this->extract_list_info($dom);

		$info = $this->filter_list_info($info);

		$template_path = "$template_dir/$type.php";
		
		$contents = php_render($template_path, [ 'lists' => $info ]);
		return $contents;
	}

	private function extract_list_info($dom) {
		$xpath = new DOMXPath($dom);
		$inactive_header = $xpath->query("//*[@id='inactive']")[0];
		$inactive_list = $xpath->query("following-sibling::*", $inactive_header)[0];
		$inactive_header->parentNode->removeChild($inactive_header);
		$inactive_list->parentNode->removeChild($inactive_list);

		$info = [];
		$items = $xpath->query("//dl/dt");
		foreach ($items as $dt) {
			$name = $dt->getAttribute('id');
			$link = $xpath->query("a[@href][1]", $dt)[0];
			$path = $link->getAttribute('href');
			$dd = $xpath->query("following-sibling::dd[1]", $dt)[0];
			$details = '';
			foreach ($dd->childNodes as $node) {
				$details .= $dom->saveHTML($node);
			}
			
			array_push($info, [
				'name' => $name,
				'path' => $path,
				'details' => $details
			]);
		}

		return $info;
	}


	private function filter_list_info($items) {
		return array_filter($items, function($item) {
			return in_array($item['name'], $this->lists);
		});
	}

	private function render_index($path) {
		$type = 'public-index';
		$dom = $this->fetch_document($path);

		$dom = $this->filter_index($dom);

		$dom = $this->localize_index($dom);

		$template_dir = $this->template_dir;
		$template_path = "$template_dir/$type.xsl";
		
		if (file_exists($template_path)) $dom = xslt_proc($template_path, $dom);
		return $dom->saveHTML();
	}

	private function localize_index($dom) {
		return $dom;
	}

	private function filter_index($dom) {
		$lists_string = file_get_contents($this->index_file);
		$lists = preg_split("/\s+/", $lists_string);

		$xpath = new DOMXPath($dom);
		$inactive_header = $xpath->query("//*[@id='inactive']")[0];
		$inactive_list = $xpath->query("following-sibling::*", $inactive_header)[0];
		$inactive_header->parentNode->removeChild($inactive_header);
		$inactive_list->parentNode->removeChild($inactive_list);
		$items = $xpath->query("//dl/dt");
		//$items = iterator_to_array($items);
		foreach ($items as $item) {
			$name = $item->getAttribute('id');
			if (in_array($name, $lists)) continue;
			for ($node = $item->nextSibling; $node && strcasecmp($node->nodeName, 'dt') !== 0; $node = $item->nextSibling) {
				$node->parentNode->removeChild($node);
			}
			$item->parentNode->removeChild($item);
		}

		return $dom;
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

}

?>

