<?php

function get_title(string $contents) : string {
	// get the first non-empty line
	do {
		$line = trim(strtok( $contents, "\n" ));
	}while(empty($line));

	// strip markdown
	$markdown = Parsedown::instance()->text(  $line );
	$plain = strip_tags( $markdown);

	return $plain;
}

function render( string $layout, array $shared ) : string {
	ob_start();
	( new Template( $layout, $shared ) )->render();

	return ob_get_clean();
}
