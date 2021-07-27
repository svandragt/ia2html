#!/usr/bin/env php
<?php
require_once __DIR__. '/vendor/autoload.php';

main();

function main() : void {
	$args  = get_args();
	$paths = [
		'template/*.css' => 'handle_theme',
		'../*.md'        => 'handle_file',
		'../*.txt'       => 'handle_file',
	];

	while ( true ) {
		$changes = 0;
		foreach ( $paths as $path => $callback ) {
			foreach ( glob( $path ) as $source ) {
				$changes += (int) $callback( $args, $source );
			}
		}
		if ( $changes > 0 ) {
			handle_index( $args );
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

function get_args() : array {
	return [
		'Parsedown' => new Parsedown(),
		'dir'       => 'html',
	];
}

function handle_file( array &$args, string $source ) : bool {
	$ext = pathinfo( $source, PATHINFO_EXTENSION );
	$fn  = mb_ereg_replace( "([^a-zA-Z0-9\.\/\\_])", '-', $source );
	$fn  = str_replace( [ ".${ext}", '--' ], [ '.html', '-' ], $fn );

	if ( isset( $args['files'][ $fn ] ) && md5_file( $source ) === $args['files'][ $fn ] ) {
		return false;
	}
	echo "file: $source; mtime: " . date( "F d Y H:i:s.",filemtime( $source));

	$markdown = file_get_contents( $source );
	$html     = $args['Parsedown']->text( $markdown );
	$html     = render( 'template/single.php', [ 'content' => $html ] );

	$dest = __DIR__ . '/html/' . $args['dir'] . '/' . $fn;
	file_put_contents( $dest, $html );

	$args['files'][ $fn ] = $md5 = md5( $markdown );
	display( $md5, $dest );

	return true;
}

function handle_index( array $args ) : void {
	$html = html_nav( $args );
	$html = render( 'template/index.php', [ 'content' => $html ] );

	$dest = __DIR__ . '/' . $args['dir'] . '/index.html';
	file_put_contents( $dest, $html );

	$md5 = md5( $dest );
	display( $md5, $dest );
}

function handle_theme( array &$args, $source ) : bool {
	if ( ! is_dir( $args['dir'] ) && ! mkdir( $concurrentDirectory = $args['dir'] ) && ! is_dir( $concurrentDirectory ) ) {
		throw new \RuntimeException( sprintf( 'Directory "%s" was not created', $concurrentDirectory ) );
	}

	if ( isset( $args['theme'][ $source ] ) && md5_file( $source ) === $args['theme'][ $source ] ) {
		return false;
	}

	$dest = str_replace( 'template', $args['dir'], $source );
	copy( $source, $dest );

	$data                     = file_get_contents( $source );
	$args['theme'][ $source ] = $md5 = md5( $data );
	display( $md5, $dest );

	return true;
}

function html_nav(array $args) : string {
	$html ='<ul>';
	foreach (array_keys($args['files']) as $dest) {
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
