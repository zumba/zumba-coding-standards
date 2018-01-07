<?php

namespace Zumba;

use \Foobar;
use \Foobaz;

trait Foo {
	use Foo;
}
?>
--EXPECT--

--------------------------------------------------------------------------------
FOUND 2 ERROR(S) AFFECTING 2 LINE(S)
--------------------------------------------------------------------------------
 6 | ERROR | The "use" has been used before on line 5
 9 | ERROR | The "use" has been used before on line 5
--------------------------------------------------------------------------------
