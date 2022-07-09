<?php
function html_nav(array $cache) {
	foreach ( array_keys( $cache ) as $dest ) {
		$ext = pathinfo( $dest, PATHINFO_EXTENSION );
		$link = str_replace( [ ".$ext", '../' ], [ '.html', '' ], $dest );

		$title = str_replace( '.html', '', $link );
		echo "<li><a href='$link'>$title</a></li>" . PHP_EOL;
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>IA2Web</title>
	<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
	<nav>IA2Web</nav>
	<main>
        <ul>
            <?php html_nav($this->cache);?>
        </ul>
	</main>
	<footer><p>Powered by <a href="https://github.com/svandragt/ia2web">IA2web</a></p></footer>
</body>
</html>
