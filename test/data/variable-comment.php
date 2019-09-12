<?php declare(strict_types = 1);

namespace Zumba\CodingStandards\Test;

class VariableCommentTest {

	/** @var string */
	public $shortProperty;

	/** @var \Zumba\CodingStandards\Test\VariableCommentTest[] Set the property here to something. */
	protected $shortPropertyWithDescription;

	/** @var array<string, \Zumba\CodingStandards\Test\VariableCommentTest[]> Set the property here to something. */
	protected $complexType;

	/** @var ?array<string, \Zumba\CodingStandards\Test\VariableCommentTest[]> Set the property here to something. */
	protected $nullableType;

	/**
	 * @var string
	 */
	private $longPropertyWithoutComment;

	/**
	 * With a comment
	 *
	 * @var string
	 */
	public $longPropertyWithComment;

	protected $propertyWithoutComment;

	/**
	 * Long doc comments are supported.
	 */
	public function longCommentsOnMethodsAreSupported() : void {

	}

	/** @return foo Not supported */
	public function shortCommentsOnMethodsNotSupported() {

	}

}
?>
--EXPECT--
--------------------------------------------------------------------------------
FOUND 5 ERROR(S) AFFECTING 2 LINE(S)
--------------------------------------------------------------------------------
 31 | ERROR | Missing variable doc comment
 40 | ERROR | The open comment tag must be the only content on the line
 40 | ERROR | Missing @return tag in function comment
 40 | ERROR | Additional blank lines found at end of function comment
 40 | ERROR | Function comment short description must start with a capital
    |       | letter
--------------------------------------------------------------------------------
