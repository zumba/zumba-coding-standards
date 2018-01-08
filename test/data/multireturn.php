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
