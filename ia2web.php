#!/usr/bin/env php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$config = [
	'dir' => 'html',
];

main();

function main() : void {
	global $config;
	if ( ! create_directory( $config['dir'] ) ) {
		throw new RuntimeException( sprintf( 'Directory "%s" was not created', $config['dir'] ) );
	}
	while ( true ) {
		update_assets( glob( 'template/*.css' ) );

		$files = array_merge( glob( '../*.md' ), glob( '../*.txt' ) );
		$changed = update_posts( $files );

		if ( count( $changed ) > 0 ) {
			update_post_index();
		}
		if ( sleep( 1 ) !== 0 ) {
			break;
		}
	}
}

function cli_line( string $md5, string $dest ) : void {
	$md5 = substr( $md5, 0, 8 );
	$dest = (string) pathinfo( $dest, PATHINFO_BASENAME );
	echo "$md5 $dest" . PHP_EOL;
}

function update_posts( array $files ) : array {
	$handled = [];
	foreach ( $files as $source ) {
		$handled[] = update_post( $source );
	}

	return array_filter( $handled );
}

function update_assets( array $files ) : array {
	$handled = [];
	foreach ( $files as $source ) {
		$handled[] = update_asset( $source );
	}

	return array_filter( $handled );
}

function update_post( string $filename ) : ?string {
	global $config;

	$slug_fn = slug_fn( $filename );
	if ( is_stored_file( 'files', $slug_fn, $filename ) ) {
		return null;
	}
	$contents = file_get_contents( $filename );

	// relative .. files workaround
	$dest = __DIR__ . '/html/' . $config['dir'] . '/' . $slug_fn;
	$data = [
		'content' => Parsedown::instance()->text( $contents ),
        'md5' => md5( $contents ),
		'title' => get_title($contents),
	];

	$partial = render( 'template/single.php', $data );
	file_put_contents( $dest, $partial );

	cli_line( $data['md5'], $dest );

	save( 'files', $slug_fn, $data );

	return $slug_fn;
}

function update_post_index() : void {
	global $config;
	$html = render( 'template/index.php', [ 'store' => load( 'files' ) ] );

	$dest = __DIR__ . '/' . $config['dir'] . '/index.html';
	file_put_contents( $dest, $html );

	$md5 = md5( $dest );
	cli_line( $md5, $dest );
}

function update_asset( string $filename ) : ?string {
	global $config;

	if ( is_stored_file( 'theme', $filename, $filename ) ) {
		return null;
	}

	$dest = str_replace( 'template', $config['dir'], $filename );
	copy( $filename, $dest );

	$contents = file_get_contents( $filename );
	$md5 = md5( $contents );

	cli_line( $md5, $dest );

	save( 'theme', $filename, [ 'md5' => $md5 ] );

	return $filename;
}
