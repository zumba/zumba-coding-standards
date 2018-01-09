<?php

/**
 * This is a bug in the current void detector: we only check the first return
 *
 * @return void
 */
function foobar() {
	if (strlen("hello")) {
		return;
	}
	return "hello";
}
?>
--EXPECT--

--------------------------------------------------------------------------------
FOUND 1 ERROR(S) AFFECTING 1 LINE(S)
--------------------------------------------------------------------------------
 12 | ERROR | Function return type is void, but function contains return
    |       | statement
--------------------------------------------------------------------------------
