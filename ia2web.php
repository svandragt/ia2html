#!/usr/bin/env php
<?php
require_once __DIR__. '/vendor/autoload.php';

main();

function main() {
	$args = get_args();

	handle_theme($args);
	handle_files($args);
}

function get_args() {
	return [
		'Parsedown' => new Parsedown(),
		'layout'    => file_get_contents('template/_layout.html'),
		'dir'       => 'html',
	];
}

function handle_files($args) {
	foreach (glob("../*.md") as $source) {
		$subject = file_get_contents($source);
		$content = $args['Parsedown']->text($subject); 
		$_layout = $args['layout'];
		$data    = str_replace('{content}', $content, $_layout);

		$dest     = __DIR__ . '/html/' . $args['dir'] . '/' . $source;
		$filename = str_replace ( '.md', '.html', $dest);
	   	file_put_contents ( $filename, $data );

		echo $filename  . PHP_EOL;
	}
}

function handle_theme($args) {
	@mkdir($args['dir']);
	copy('template/style.css', 'html/style.css');
}