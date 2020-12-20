<?php
namespace aphp\Foundation;

function validate($validation, $value = null, $exceptionText = null)
{
	return Validator::getInstance()->validate( $validation, $value, $exceptionText, 1 );
}
