<?php 

/*
https://phpunit.de/getting-started/phpunit-5.html

Manual for *.phar installations
Using *.bat
Using CMD
https://stackoverflow.com/questions/22297546/how-to-run-phar-from-anywhere-on-windows

PHP 5.6

All tests
phpunit --bootstrap tests/bootstrap_autoload.php tests --debug

One file
phpunit --bootstrap tests/bootstrap_autoload.php tests/%file%Test.php --debug

One test
phpunit --bootstrap tests/bootstrap_autoload.php --filter %method% tests/%file%Test.php --debug
*/

require __DIR__ . '/../vendor/autoload.php';