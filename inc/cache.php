<?php
function cache( string $group, string $key = '' ) {
	global $cache;
	if (empty($key)) {
		return $cache[$group];
	}
	return $cache[ $group ][ $key ];
}

function update_cache( string $group, string $key, string $value ) {
	global $cache;
	$cache[ $group ][ $key ] = md5( $value );
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
