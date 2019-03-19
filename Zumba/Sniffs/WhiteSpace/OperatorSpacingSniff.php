<?php
/**
 * Zumba_Sniffs_ControlStructures_MultiLineConditionSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 */

if (class_exists('Squiz_Sniffs_WhiteSpace_OperatorSpacingSniff', true) === false) {
    $error = 'Class Squiz_Sniffs_WhiteSpace_OperatorSpacingSniff not found';
    throw new PHP_CodeSniffer_Exception($error);
}

/**
 * Zumba_Sniffs_WhiteSpace_OperatorSpacingSniff
 *
 * Handle spacing around operators.
 *
 * Overridden to handle PHP 7 nullable types.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 */
class Zumba_Sniffs_WhiteSpace_OperatorSpacingSniff
    extends Squiz_Sniffs_WhiteSpace_OperatorSpacingSniff
    implements PHP_CodeSniffer_Sniff {

    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
        $tokens = $phpcsFile->getTokens();
        if ($this->isProbablyNullableType($phpcsFile, $tokens, $stackPtr)) {
            return;
        }
        return parent::process($phpcsFile, $stackPtr);
    }

    private function isProbablyNullableType(PHP_CodeSniffer_File $phpcsFile, $tokens, $stackPtr) {
        $token = $tokens[$stackPtr];
        if ($token['code'] !== T_INLINE_THEN) {
            return false;
        }

        $namespaceTokenOrIdentifier = $phpcsFile->findNext(
            [T_NS_SEPARATOR, T_STRING],
            $stackPtr + 1,
            null,
            false,
            null,
            true
        );
        if (!$namespaceTokenOrIdentifier) {
            return false;
        }
        $maybeVariableOrOpenBrace = $phpcsFile->findNext(
            [T_WHITESPACE, T_NS_SEPARATOR, T_STRING],
            $stackPtr + 1,
            null,
            true,
            null,
            true
        );
        if (!$maybeVariableOrOpenBrace) {
            return false;
        }
        // if it's a variable, then assume it's a parameter with a nullable type:
        if ($tokens[$maybeVariableOrOpenBrace]['code'] === T_VARIABLE) {
            return true;
        }
        // if it's an opening block, assume it's a return type with a nullable type:
        if ($tokens[$maybeVariableOrOpenBrace]['code'] === T_OPEN_CURLY_BRACKET) {
            return true;
        }
        return false;
    }

}