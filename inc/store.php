<?php
$store = [];

function load( string $group, ?string $key = null ) {
	global $store;
	if (is_null($key)) {
		return $store[$group];
	}
	return $store[ $group ][ $key ];
}

function save( string $group, string $key, array $new ) {
	global $store;
	$old = isset($store[ $group ][ $key ]) ? $store[ $group ][ $key ] : [];
	$store[ $group ][ $key ] =  array_merge($old, $new);
}

/**
 * @param string $group
 * @param string $key
 * @param string $value
 *
 * @return bool
 */
function is_stored_file( string $group, string $key, string $value ) : bool {
	global $store;

	return isset( $store[ $group ][ $key ] ) && md5_file( $value ) === $store[ $group ][ $key ]['md5'];
}
