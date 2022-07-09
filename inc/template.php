<?php

function render( string $layout, array $shared ) : string {
	ob_start();
	( new Template( $layout, $shared ) )->render();

	return ob_get_clean();
}
