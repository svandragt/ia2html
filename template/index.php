<?php
function html_nav(array $store) {
	foreach ( $store as $slug_fn => $data ) {
		$ext = pathinfo( $slug_fn, PATHINFO_EXTENSION );
		$link = str_replace( [ ".$ext", '../' ], [ '.html', '' ], $slug_fn );

		$title = $data['title'];
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
            <?php html_nav($this->store);?>
        </ul>
	</main>
	<footer><p>Powered by <a href="https://github.com/svandragt/ia2web">IA2web</a></p></footer>
</body>
</html>
