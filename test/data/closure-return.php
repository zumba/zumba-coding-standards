<?php

/**
 * This documents existing behavior that a closure inside function returning void prints an error.
 *
 * @return void
 */
function foobar() {
	$hello = function() {
		return 'hello';
	};
	return "string";
}
?>
--EXPECT--

--------------------------------------------------------------------------------
FOUND 1 ERROR(S) AFFECTING 1 LINE(S)
--------------------------------------------------------------------------------
 12 | ERROR | Function return type is void, but function contains return
    |       | statement
--------------------------------------------------------------------------------
