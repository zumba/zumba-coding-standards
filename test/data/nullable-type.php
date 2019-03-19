<?php

namespace Zumba\CodingStandards\Test;

class NullableTypeTest {
	/**
	 * Test that the the nullable ternary space doesn't break.
     *
     * @return static
	 */
	public function local(?NullableTypeTest $nullableType) : self {
        $foo = $bar?'cheese':'baz';
	}

	public function relative(?Relative\Test $nullableType) : self {
	}

	public function absolute(?\Zumba\Test $nullableType) : self {
	}


}
?>
--EXPECT--
--------------------------------------------------------------------------------
FOUND 4 ERROR(S) AFFECTING 1 LINE(S)
--------------------------------------------------------------------------------
12 | ERROR | Expected 1 space before "?"; 0 found
12 | ERROR | Expected 1 space after "?"; 0 found
12 | ERROR | Expected 1 space before ":"; 0 found
12 | ERROR | Expected 1 space after ":"; 0 found
--------------------------------------------------------------------------------

