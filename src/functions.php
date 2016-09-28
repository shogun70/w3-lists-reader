<?php

function batch_array($array, $size) {
	$matrix = array();
	$n = count($array);
	for ($i=0; $i<$n; $i+=$size) {
		$matrix []= array_slice($array, $i, $size);
	}
	return $matrix;
}


function mkdir_for_file($file_path) {
	if (file_exists($file_path)) return;
	$dir = dirname($file_path);
	if (file_exists($dir)) {
		if (is_dir($dir)) return;
		throw new Exception("$dir is not a directory");
	}
	mkdir($dir, 0777, $dir);
}

function get_file_contents($path) {
	$fh = fopen($path, 'r');
	flock($fh, LOCK_SH);
	$contents = file_get_contents($path);
	flock($fh, LOCK_UN);
	return $contents;
}

function put_file_contents($path, $contents) {
	mkdir_for_file($path);
	file_put_contents($path, $contents, LOCK_EX);
}

libxml_use_internal_errors(true);

function parse_html($markup) {
	$doc = new DOMDocument;
	$success = $doc->loadHTML($markup);
	if (!success) {
		foreach (libxml_get_errors() as $error) {
			// TODO
		}
		
		libxml_clear_errors();
	}
	return $doc;
}

function inner_html($el) {
	$doc = $el->ownerDocument;
	$text = '';
	foreach ($el->childNodes as $node) $text .= $doc->saveHTML($node);
	return $text;
}
	
function xslt_proc($template, $dom, $args = null) {
	$xsl = new DOMDocument;
	$xsl->load($template);

	$proc = new XSLTProcessor;
	$proc->importStyleSheet($xsl);

	if ($args) $proc->setParameter('', $args);
	return $proc->transformToDoc($dom);
}

function php_render($template, array $params = []) {
        if (!is_file($template)) {
            throw new \RuntimeException("Template file not found: $template");
        }

        ob_start();
        extract($params);
        include $template;
        $output = ob_get_clean();

        return $output;
}
