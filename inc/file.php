<?php
function create_directory( string $dir ) : bool {
	return is_dir( $dir ) || mkdir( $concurrentDirectory = $dir ) || is_dir( $concurrentDirectory );
}

function slug_fn( string $filename ) : string {
	$ext = pathinfo( $filename, PATHINFO_EXTENSION );
	$fn = mb_ereg_replace( "([^a-zA-Z0-9\.\/\\_])", '-', $filename );

	return (string)str_replace( [ ".$ext", '--' ], [ '.html', '-' ], $fn );
}
