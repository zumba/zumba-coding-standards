<?php

namespace Zumba;

use Foobar;
use Foobaz;

class Test {
	//
	// Test that using a trait doesn't print a warning:
	//
	use SomeTrait;
}
?>
--EXPECT--

