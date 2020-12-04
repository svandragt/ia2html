#!/usr/bin/env php
<?php
require_once __DIR__. '/vendor/autoload.php';

$dir = 'html';
@mkdir($dir);

$Parsedown = new Parsedown();

copy('template/style.css', 'html/style.css');
$layout = file_get_contents('template/_layout.html');


foreach (glob("../*.md") as $source) {
	$subject = file_get_contents($source);

	$_layout = $layout;
	$content = $Parsedown->text($subject); 
	$data = str_replace('{content}', $content, $_layout);

	$dest = __DIR__ . '/html/' . $dir . '/' . $source;
	$filename = str_replace ( '.md', '.html', $dest);
	echo $filename  . PHP_EOL;
   	file_put_contents ( $filename, $data );
}