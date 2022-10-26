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
 * @return \Generator|boolean[]
 */
function generatorWithBoolean() {
	yield true;
}

/**
 * Short function description
 *
 * @return mixed
 */
function generatorWithMixedReturn() {
    yield 'something';
}
?>
--EXPECT--
