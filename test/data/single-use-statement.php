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

--------------------------------------------------------------------------------
FOUND 1 ERROR(S) AFFECTING 1 LINE(S)
--------------------------------------------------------------------------------
 6 | ERROR | The "use" has been used before on line 5
--------------------------------------------------------------------------------
