<?php
/**
 * Zumba_Sniffs_NamingConventions_ValidNamespaceNameSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 */

/**
 * Zumba_Sniffs_NamingConventions_ValidNamespaceNameSniff.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 */
class Zumba_Sniffs_NamingConventions_ValidNamespaceNameSniff implements PHP_CodeSniffer_Sniff
{

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_NAMESPACE);

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
        $tokens    = $phpcsFile->getTokens();
        $i = 2; // Ignore namespace word and whitespace
        $filename = $phpcsFile->getFilename();
        $ns = '';

        while ($tokens[$stackPtr+$i]['code'] !== T_SEMICOLON) {
            $ns .= $tokens[$stackPtr+$i]['content'];
            $i++;
        }

        $ns = '\\' . ltrim($ns, '\\');
        $path = dirname($filename);

        if (substr(str_replace('/', '\\', $path), -1 * strlen($ns)) !== $ns) {
            $error = 'Namespace do not match with the folder name';
            $phpcsFile->addError($error, $stackPtr, 'InvalidNamespace', array());
        }
    }


}
?>