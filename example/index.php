<?php
require __DIR__ . '/../vendor/autoload.php';

$path    = __DIR__;
$files = array_diff(scandir($path), array('.', '..', 'index.php', 'startServer.bat', 'startServer.sh'));

sort($files);

echo '<h2>aphp/Foundation - example</h2>';

foreach ($files as $f) {
	echo '<a href="/'. $f . '">' . $f . '</a><br>';
}
