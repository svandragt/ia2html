#!/usr/bin/env php
<?php
require_once __DIR__. '/vendor/autoload.php';

main();

function main() : void {
	$args = get_args();
	$paths = [
		'template/*.css' => 'handle_theme',
		'../*.md' => 'handle_file',

	];

	while(true) {
		foreach ($paths as $path => $callback) {
			foreach (glob($path) as $source) {
				$callback($args, $source);
			}
		}
		if (sleep(1) !== 0) {
			break;
		}
	}
}

function display(string $md5, string $dest ) : void {
	$md5 = substr($md5, 0, 8);
	echo "${md5} ${dest}"  . PHP_EOL;
}

function get_args() : array {
	return [
		'Parsedown' => new Parsedown(),
		'layout'    => file_get_contents('template/_layout.html'),
		'dir'       => 'html',
	];
}

function handle_file(&$args, $source) {
	$dest = str_replace ( '.md', '.html', __DIR__ . '/html/' . $args['dir'] . '/' . $source);

	if (isset($args['files'][$source]) && md5_file($source) === $args['files'][$source]) {
		return;
	}

	$markdown = file_get_contents($source);
	$html = $args['Parsedown']->text($markdown); 
	$data    = str_replace('{content}', $html, $args['layout']);

   	file_put_contents ( $dest, $data );

	$args['files'][$source] = $md5 = md5($markdown);
	handle_index($args);
	display($md5, $dest);
}

function handle_index(array $args) : void {	
	$html = html_nav($args);

	$data    = str_replace('{content}', $html, $args['layout']);
	$dest = __DIR__ .'/'. $args['dir'] . '/index.html';
   	file_put_contents ( $dest, $data );

   	$md5 = md5($data);
	display($md5, $dest);
}

function handle_theme(array &$args, $source) : void {
	@mkdir($args['dir']);

	if (isset($args['files'][$source]) && md5_file($source) === $args['files'][$source]) {
		return;
	}

	$dest = str_replace('template', $args['dir'], $source);
	copy($source, $dest);

	$data = file_get_contents($source);
	$args['files'][$source] = $md5 = md5($data);
	display($md5, $dest);
}

function html_nav(array $args) : string {
	$html ='<ul>';
	foreach ($args['files'] as $created => $source) {
		$link = str_replace ( '.md', '.html', $source);
		$link = str_replace ( '../', '', $link);
		$title = str_replace ( '.html', '', $link);
		$html .= "<li><a href='${link}'>${title}</a></li>";
	}
	$html .= '</ul>';
	return $html;
}