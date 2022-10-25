<?php
/**
 * Zumba_Sniffs_NamingConventions_ValidVariableNameSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 */

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Standards\PEAR\Sniffs\NamingConventions\ValidVariableNameSniff;

/**
 * Zumba_Sniffs_NamingConventions_ValidVariableNameSniff.
 *
 * Checks the naming of member variables.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 */
class Zumba_Sniffs_NamingConventions_ValidVariableNameSniff extends ValidVariableNameSniff
{

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    protected function processVariable(File $phpcsFile, $stackPtr)
    {
        if (strpos($phpcsFile->getFilename(), DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR) !== false) {
            return;
        }
        parent::processVariable($phpcsFile, $stackPtr);
    }

    /**
     * Processes class member variables.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    protected function processMemberVar(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $memberProps = $phpcsFile->getMemberProperties($stackPtr);
        if (empty($memberProps) === true) {
            return;
        }

        $memberName     = ltrim($tokens[$stackPtr]['content'], '$');
        $scope          = $memberProps['scope'];
        $scopeSpecified = $memberProps['scope_specified'];

        // Variable must not have an underscore on the front.
        if ($scopeSpecified === true && $memberName[0] === '_') {
            $error = '%s member variable "%s" must not be prefixed with an underscore';
            $data  = array(
                      ucfirst($scope),
                      $memberName,
                     );
            $phpcsFile->addError($error, $stackPtr, 'VariableUnderscore', $data);
            return;
        }

    }//end processMemberVar()


    /**
     * Processes the variable found within a double quoted string.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the double quoted
     *                                        string.
     *
     * @return void
     */
    protected function processVariableInString(File $phpcsFile, $stackPtr)
    {
        if (strpos($phpcsFile->getFilename(), DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR) !== false) {
            return;
        }
        parent::processVariableInString($phpcsFile, $stackPtr);
    }

}//end class

?>
