<?php

require '../vendor/autoload.php';

use function aphp\Foundation\validate;

if (php_sapi_name() !== 'cli') {
	header("Content-Type: text/plain");
}

$z = validate(function(){ return false; }, 'z_value');