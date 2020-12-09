#!/usr/bin/env php
<?php
require_once __DIR__. '/vendor/autoload.php';

main();

function main() : void {
	$args = get_args();

	watch($args, [
		'template/*.css' => 'handle_theme',
		'../*.md' => 'handle_file',

	]);
}

function watch(array $args, $paths) : void {
	while(true) {

	foreach ($paths as $path => $callback) {
		// Watch __FILE__ for metadata changes (e.g. mtime)
		foreach (glob($path) as $source) {
			$callback($args, $source);
		}
	}
			$abort = sleep(1);
		if ($abort) {
			break;
		}
	}

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

	$subject = file_get_contents($source);
	$content = $args['Parsedown']->text($subject); 
	$_layout = $args['layout'];
	$data    = str_replace('{content}', $content, $_layout);

   	file_put_contents ( $dest, $data );

	$args['files'][$source] = $md5 = md5($subject);

	handle_index($args);

	$md5 = substr($md5, 0, 8);
	echo "${md5} ${dest}"  . PHP_EOL;
}

function handle_index(array $args) : void {	
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

   	$md5 = md5($data);
	$md5 = substr($md5, 0, 8);
	echo "${md5} ${filename}"  . PHP_EOL;
}

function handle_theme(array &$args, $source) : void {
	@mkdir($args['dir']);
	$dest = str_replace('template', $args['dir'], $source);

	$data = file_get_contents($source);

	if (isset($args['files'][$source]) && md5_file($source) === $args['files'][$source]) {
		return;
	}
	copy($source, $dest);
	$args['files'][$source] = $md5 = md5($data);

	$md5 = substr($md5, 0, 8);
	echo "${md5} ${dest}"  . PHP_EOL;
}
