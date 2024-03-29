<?php
/**
 * Zumba_Sniffs_ControlStructures_MultiLineConditionSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 */

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Zumba_Sniffs_ControlStructures_MultiLineConditionSniff.
 *
 * Ensure multi-line IF conditions are defined correctly.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 */
class Zumba_Sniffs_ControlStructures_MultiLineConditionSniff implements Sniff
{


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_IF);

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // We need to work out how far indented the if statement
        // itself is, so we can work out how far to indent conditions.
        $statementIndent = 0;
        for ($i = ($stackPtr - 1); $i >= 0; $i--) {
            if ($tokens[$i]['line'] !== $tokens[$stackPtr]['line']) {
                $i++;
                break;
            }
        }

        if ($i >= 0 && $tokens[$i]['code'] === T_WHITESPACE) {
            $statementIndent = strlen($tokens[$i]['content']);
        }

        // Each line between the parenthesis should be indented 1 spaces
        // and can not start with an operator, unless the line is inside a
        // function call, in which case it is ignored.
        $openBracket  = $tokens[$stackPtr]['parenthesis_opener'];
        $closeBracket = $tokens[$stackPtr]['parenthesis_closer'];
        $lastLine     = $tokens[$openBracket]['line'];
        for ($i = ($openBracket + 1); $i < $closeBracket; $i++) {
            if ($tokens[$i]['line'] !== $lastLine) {
                if ($tokens[$i]['line'] === $tokens[$closeBracket]['line']) {
                    $next = $phpcsFile->findNext(T_WHITESPACE, $i, null, true);
                    if ($next !== $closeBracket) {
                        // Closing bracket is on the same line as a condition.
                        $error = 'Closing parenthesis of a multi-line IF statement must be on a new line';
                        $phpcsFile->addError($error, $i, 'CloseBracketNewLine');
                        $expectedIndent = ($statementIndent + 1);
                    } else {
                        // Closing brace needs to be indented to the same level
                        // as the function.
                        $expectedIndent = $statementIndent;
                    }
                } else {
                    $expectedIndent = ($statementIndent + 1);
                }

                // We changed lines, so this should be a whitespace indent token.
                if ($tokens[$i]['code'] !== T_WHITESPACE) {
                    $foundIndent = 0;
                } else {
                    $foundIndent = strlen($tokens[$i]['content']);
                }

                if ($expectedIndent !== $foundIndent) {
                    $error = 'Multi-line IF statement not indented correctly; expected %s spaces but found %s';
                    $data  = array(
                              $expectedIndent,
                              $foundIndent,
                             );
                    $phpcsFile->addError($error, $i, 'Alignment', $data);
                }

                if ($tokens[$i]['line'] !== $tokens[$closeBracket]['line']) {
                    $next = $phpcsFile->findNext(T_WHITESPACE, $i, null, true);
                    if (in_array($tokens[$next]['code'], Tokens::$booleanOperators) === true) {
                        $error = 'Each line in a multi-line IF statement must not begin with a boolean operator';
                        $phpcsFile->addError($error, $i, 'StartWithBoolean');
                    }
                }

                $lastLine = $tokens[$i]['line'];
            }//end if

            if ($tokens[$i]['code'] === T_STRING) {
                $next = $phpcsFile->findNext(T_WHITESPACE, ($i + 1), null, true);
                if ($tokens[$next]['code'] === T_OPEN_PARENTHESIS) {
                    // This is a function call, so skip to the end as they
                    // have their own indentation rules.
                    $i        = $tokens[$next]['parenthesis_closer'];
                    $lastLine = $tokens[$i]['line'];
                    continue;
                }
            }
        }//end for

        // From here on, we are checking the spacing of the opening and closing
        // braces. If this IF statement does not use braces, we end here.
        if (isset($tokens[$stackPtr]['scope_opener']) === false) {
            return;
        }

        // The opening brace needs to be one space away from the closing parenthesis.
        if ($tokens[($closeBracket + 1)]['code'] !== T_WHITESPACE) {
            $length = 0;
        } else if ($tokens[($closeBracket + 1)]['content'] === $phpcsFile->eolChar) {
            $length = -1;
        } else {
            $length = strlen($tokens[($closeBracket + 1)]['content']);
        }

        if ($length !== 1) {
            $data = array($length);
            $code = 'SpaceBeforeOpenBrace';

            $error = 'There must be a single space between the closing parenthesis and the opening brace of a multi-line IF statement; found ';
            if ($length === -1) {
                $error .= 'newline';
                $code   = 'NewlineBeforeOpenBrace';
            } else {
                $error .= '%s spaces';
            }

            $phpcsFile->addError($error, ($closeBracket + 1), $code, $data);
        }

        // And just in case they do something funny before the brace...
        $next = $phpcsFile->findNext(T_WHITESPACE, ($closeBracket + 1), null, true);
        if ($next !== false && $tokens[$next]['code'] !== T_OPEN_CURLY_BRACKET) {
            $error = 'There must be a single space between the closing parenthesis and the opening brace of a multi-line IF statement';
            $phpcsFile->addError($error, $next, 'NoSpaceBeforeOpenBrace');
        }

    }//end process()


}//end class

?>
