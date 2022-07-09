#!/usr/bin/env php
<?php
require_once __DIR__. '/vendor/autoload.php';

$config = [
	'Parsedown' => new Parsedown(),
	'dir' => 'html',
];

$cache = [];

main();

function main() : void {


	while ( true ) {
		$changed = [];
		$changed['theme'] = handle_theme_files( glob( 'template/*.css' ) );
        $files = array_merge( glob( '../*.md' ), glob( '../*.txt' ));
		$changed['posts'] = handle_files( $files );

		if ( count($changed['posts']) > 0 ) {
			handle_index();
		}
		if ( sleep( 1 ) !== 0 ) {
			break;
		}
	}
}

function display(string $md5, string $dest ) : void {
	$md5  = substr( $md5, 0, 8 );
	$dest = (string) pathinfo( $dest, PATHINFO_BASENAME );
	echo "${md5} ${dest}" . PHP_EOL;
}

function handle_files($files) {
    $handled = [];
    foreach ($files as $source) {
       $handled[] = handle_file($source);
    }
    return array_filter($handled);
}

function handle_theme_files( $files ) {
	$handled = [];
	foreach ( $files as $source ) {
		$handled[] = handle_theme( $source );
	}

	return array_filter($handled);
}

function handle_file( string $source ) : ?string {
    global $cache;
    global $config;

	$ext = pathinfo( $source, PATHINFO_EXTENSION );
	$fn  = mb_ereg_replace( "([^a-zA-Z0-9\.\/\\_])", '-', $source );
	$fn  = str_replace( [ ".${ext}", '--' ], [ '.html', '-' ], $fn );

	if ( isset( $cache['files'][ $fn ] ) && md5_file( $source ) === $cache['files'][ $fn ] ) {
		return null;
	}
	echo "file: $source; mtime: " . date( "F d Y H:i:s.",filemtime( $source));

	$markdown = file_get_contents( $source );
	$html     = $config['Parsedown']->text( $markdown );
	$html     = render( 'template/single.php', [ 'content' => $html ] );

    // relative .. files workaround
	$dest = __DIR__ . '/html/' . $config['dir'] . '/' . $fn;
	file_put_contents( $dest, $html );

	$cache['files'][ $fn ] = $md5 = md5( $markdown );
	display( $md5, $dest );

	return $fn;
}

function handle_index( ) : void {
    global $config;
	$html = html_nav();
	$html = render( 'template/index.php', [ 'content' => $html ] );

	$dest = __DIR__ . '/' . $config['dir'] . '/index.html';
	file_put_contents( $dest, $html );

	$md5 = md5( $dest );
	display( $md5, $dest );
}

function handle_theme( $source ) : bool {
    global $config;
    global $cache;
	if ( ! is_dir( $config['dir'] ) && ! mkdir( $concurrentDirectory = $config['dir'] ) && ! is_dir( $concurrentDirectory ) ) {
		throw new \RuntimeException( sprintf( 'Directory "%s" was not created', $concurrentDirectory ) );
	}

	if ( isset( $cache['theme'][ $source ] ) && md5_file( $source ) === $cache['theme'][ $source ] ) {
		return false;
	}

	$dest = str_replace( 'template', $config['dir'], $source );
	copy( $source, $dest );

	$data                     = file_get_contents( $source );
	$cache['theme'][ $source ] = $md5 = md5( $data );
	display( $md5, $dest );

	return true;
}

function html_nav() : string {
    global $cache;
	$html ='<ul>';
	foreach (array_keys($cache) as $dest) {
		$ext  = pathinfo( $dest, PATHINFO_EXTENSION );
		$link = str_replace( [ ".${ext}", '../' ], [ '.html', '' ], $dest );

		$title = str_replace( '.html', '', $link );
		$html  .= "<li><a href='${link}'>${title}</a></li>";
	}
	$html .= '</ul>';
	return $html;
}

function render(string $layout, array $shared) : string {
	ob_start();
	( new Template( $layout, $shared ) )->render();

	return ob_get_clean();
}
