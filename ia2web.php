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
		$changes = [];
		foreach ($paths as $path => $callback) {
			$changes[$callback] = 0;
			foreach (glob($path) as $source) {
				$changes[$callback] += (int)$callback($args, $source);
			}
		}
		if ($changes['handle_file'] > 0) {
			handle_index($args);
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
		'dir'       => 'html',
	];
}

function handle_file(&$args, $source) {
	$fn = mb_ereg_replace("([^a-zA-Z0-9\.\/\\_])", '-', $source);
	$fn = str_replace ( ['.md', '--'], ['.html','-'], $fn);

	if (isset($args['files'][$fn]) && md5_file($source) === $args['files'][$fn]) {
		return false;
	}

	$markdown = file_get_contents($source);
	$html = $args['Parsedown']->text($markdown); 
	$html = render( 'template/single.php', ['content' => $html ]);

	$dest = __DIR__ . '/html/' . $args['dir'] . '/' . $fn;   
   	file_put_contents ( $dest, $html );

	$args['files'][$fn] = $md5 = md5($markdown);
	display($md5, $dest);
	return true;
}

function handle_index(array $args) : void {	
	$html = html_nav($args);
	$html = render( 'template/index.php', ['content' => $html ]);

	$dest = __DIR__ .'/'. $args['dir'] . '/index.html';
   	file_put_contents ( $dest, $html );

   	$md5 = md5($out);
	display($md5, $dest);
}

function handle_theme(array &$args, $source) : void {
	@mkdir($args['dir']);

	if (isset($args['theme'][$source]) && md5_file($source) === $args['theme'][$source]) {
		return;
	}

	$dest = str_replace('template', $args['dir'], $source);
	copy($source, $dest);

	$data = file_get_contents($source);
	$args['theme'][$source] = $md5 = md5($data);
	display($md5, $dest);
}

function html_nav(array $args) : string {
	$html ='<ul>';
	foreach (array_keys($args['files']) as $dest) {
		$link = str_replace ( '.md', '.html', $dest);
		$link = str_replace('../', '', $link);

		$title = str_replace ( '.html', '', $link);
		$html .= "<li><a href='${link}'>${title}</a></li>";
	}
	$html .= '</ul>';
	return $html;
}

function render(string $layout, array $shared) : string {
        ob_start();
        (new Template( $layout, $shared))->render();
        $out = ob_get_contents ();
        ob_end_clean();
        return $out;
}