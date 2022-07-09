#!/usr/bin/env php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$config = [
	'parser' => new Parsedown(),
	'dir' => 'html',
];

$cache = [];

main();

function main() : void {
    global $config;
	if ( ! create_directory( $config['dir'] ) ) {
		throw new RuntimeException( sprintf( 'Directory "%s" was not created', $config['dir'] ) );
	}
	while ( true ) {
		$changed = [];
		$changed['theme'] = handle_theme_files( glob( 'template/*.css' ) );
		$files = array_merge( glob( '../*.md' ), glob( '../*.txt' ) );
		$changed['posts'] = handle_files( $files );

		if ( count( $changed['posts'] ) > 0 ) {
			handle_index();
		}
		if ( sleep( 1 ) !== 0 ) {
			break;
		}
	}
}

function display( string $md5, string $dest ) : void {
	$md5 = substr( $md5, 0, 8 );
	$dest = (string) pathinfo( $dest, PATHINFO_BASENAME );
	echo "$md5 $dest" . PHP_EOL;
}

function handle_files( array $files ) : array {
	$handled = [];
	foreach ( $files as $source ) {
		$handled[] = handle_file( $source );
	}

	return array_filter( $handled );
}

function handle_theme_files( array $files ) : array {
	$handled = [];
	foreach ( $files as $source ) {
		$handled[] = handle_theme( $source );
	}

	return array_filter( $handled );
}

function handle_file( string $filename ) : ?string {
	global $config;

	$slug_fn = slug_fn( $filename );
	if ( is_cached( 'files', $slug_fn, $filename ) ) {
		return null;
	}
	$contents = file_get_contents( $filename );
	$html = $config['parser']->text( $contents );

	// relative .. files workaround
	$dest = __DIR__ . '/html/' . $config['dir'] . '/' . $slug_fn;
	$partial = render( 'template/single.php', [ 'content' => $html ] );
	file_put_contents( $dest, $partial );

    $md5 = md5( $contents );
	display( $md5, $dest );

	update_cache( 'files', $slug_fn, $contents );

	return $slug_fn;
}

function update_cache( string $group, string $key, string $value ) {
    global $cache;
	$cache[$group][ $key ] = md5( $value );
}

/**
 * @param string $group
 * @param string $key
 * @param string $value
 *
 * @return bool
 */
function is_cached( string $group, string $key, string $value ) : bool {
	global $cache;

	return isset( $cache[ $group ][ $key ] ) && md5_file( $value ) === $cache[ $group ][ $key ];
}
/**
 * @param string $filename
 *
 * @return array|false|string|string[]|null
 */
function slug_fn( string $filename ) {
	$ext = pathinfo( $filename, PATHINFO_EXTENSION );
	$fn = mb_ereg_replace( "([^a-zA-Z0-9\.\/\\_])", '-', $filename );

	return str_replace( [ ".$ext", '--' ], [ '.html', '-' ], $fn );
}

function handle_index() : void {
	global $config;
	$html = html_nav();
	$html = render( 'template/index.php', [ 'content' => $html ] );

	$dest = __DIR__ . '/' . $config['dir'] . '/index.html';
	file_put_contents( $dest, $html );

	$md5 = md5( $dest );
	display( $md5, $dest );
}

function handle_theme( string $filename ) : ?string {
	global $config;

	if ( is_cached('theme', $filename, $filename) ) {
		return null;
	}

	$dest = str_replace( 'template', $config['dir'], $filename );
	copy( $filename, $dest );

	$contents = file_get_contents( $filename );
    $md5 = md5( $contents );

	display( $md5, $dest );

	update_cache( 'theme', $filename, $contents );

	return $filename;
}

/**
 * @param string $dir
 *
 * @return bool
 */
function create_directory( string $dir ) : bool {
	return is_dir( $dir ) || mkdir( $concurrentDirectory = $dir ) || is_dir( $concurrentDirectory );
}

function html_nav() : string {
	global $cache;
	$html = '<ul>';
	foreach ( array_keys( $cache ) as $dest ) {
		$ext = pathinfo( $dest, PATHINFO_EXTENSION );
		$link = str_replace( [ ".$ext", '../' ], [ '.html', '' ], $dest );

		$title = str_replace( '.html', '', $link );
		$html .= "<li><a href='$link'>$title</a></li>";
	}
	$html .= '</ul>';

	return $html;
}

function render( string $layout, array $shared ) : string {
	ob_start();
	( new Template( $layout, $shared ) )->render();

	return ob_get_clean();
}
