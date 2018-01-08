<?php

/**
 * Short function description
 *
 * @return \Generator
 */
function generatorByItself() {
	yield 'foo';
}

/**
 * Short function description
 *
 * @return \Generator|boolean
 */
function generatorWithBoolean() {
	yield true;
}

?>
--EXPECT--

--------------------------------------------------------------------------------
FOUND 2 ERROR(S) AFFECTING 2 LINE(S)
--------------------------------------------------------------------------------
  6 | ERROR | Function return type is not void, but function has no return
    |       | statement
 15 | ERROR | Function return type is not void, but function has no return
    |       | statement
--------------------------------------------------------------------------------
