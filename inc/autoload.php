<?php
function _autoload_files() {
	$files = glob( __DIR__ . '/*.php' );
	if ( $files === false ) {
		throw new RuntimeException( "Failed to glob for function files" );
	}
	foreach ( $files as $file ) {
		require_once $file;
	}
}

_autoload_files();
