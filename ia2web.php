#!/usr/bin/env php
<?php
require_once __DIR__. '/vendor/autoload.php';

main();

function main() {
	$args = get_args();

	handle_theme($args);
	$args['files'] = handle_files($args);
	handle_index($args);
}

function get_args() {
	return [
		'Parsedown' => new Parsedown(),
		'layout'    => file_get_contents('template/_layout.html'),
		'dir'       => 'html',
	];
}

function handle_files($args) {
	$files = [];
	foreach (glob("../*.md") as $source) {
		$subject = file_get_contents($source);
		$content = $args['Parsedown']->text($subject); 
		$_layout = $args['layout'];
		$data    = str_replace('{content}', $content, $_layout);

		$dest     = __DIR__ . '/html/' . $args['dir'] . '/' . $source;
		$filename = str_replace ( '.md', '.html', $dest);
	   	file_put_contents ( $filename, $data );

		$files[] = $source;

		echo "+ ${filename}"  . PHP_EOL;
	}
	return $files;
}

function handle_index($args) {
	$content = '<ul>';
	foreach ($args['files'] as $created => $source) {
		$link = str_replace ( '.md', '.html', $source);
		$link = str_replace ( '../', '', $link);
		$title = str_replace ( '.html', '', $link);
		$content .= "<li><a href='${link}'>${title}</a></li>";
	}
	$content .= '</ul>';

	$_layout = $args['layout'];
	$data    = str_replace('{content}', $content, $_layout);

	$filename = __DIR__ .'/'. $args['dir'] . '/index.html';
   	file_put_contents ( $filename, $data );

	echo "${filename}"  . PHP_EOL;
}

function handle_theme($args) {
	@mkdir($args['dir']);
	copy('template/style.css', 'html/style.css');
}
