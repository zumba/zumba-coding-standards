<?php
/**
 * Zumba_Sniffs_PHP_DisallowMultipleUseSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 */

/**
 * Zumba_Sniffs_PHP_DisallowMultipleUseSniff.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 */
class Zumba_Sniffs_PHP_DisallowMultipleUseSniff implements PHP_CodeSniffer_Sniff
{

    /**
     * Token information about the first declared use token
     *
     * @var array
     */
    protected $firstUse = array();

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_USE);

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $filename = $phpcsFile->getFilename();

        if (!isset($this->firstUse[$filename])) {
            $this->firstUse[$filename] = $tokens[$stackPtr];
            return;
        }

        $error = 'The "use" has been used before on line %s';
        $data  = array($this->firstUse[$filename]['line']);
        $phpcsFile->addError($error, $stackPtr, 'InvalidUse', $data);
    }


}
?>