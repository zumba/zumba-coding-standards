<?php
/**
 * Zumba_Sniffs_Formatting_SQLSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 */

/**
 * Zumba_Sniffs_Formatting_SQLSniff.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 */
class Zumba_Sniffs_Formatting_SQLSniff implements PHP_CodeSniffer_Sniff
{

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
                T_CONSTANT_ENCAPSED_STRING,
                T_DOUBLE_QUOTED_STRING,
               );


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

        // We are only interested in the first token in a multi-line string.
        if ($tokens[$stackPtr]['code'] === $tokens[($stackPtr - 1)]['code']) {
            return;
        }

        $content = $tokens[$stackPtr]['content'];
        $i = ($stackPtr + 1);
        while ($tokens[$i]['code'] === $tokens[$stackPtr]['code']) {
            $content .= $tokens[$i]['content'];
            $i++;
        }

        // We are only interested in multi-lines
        if (strpos($content, "\n") === false) {
            return;
        }

        // We are only interested in SQL.
        if (!$this->isSql($content)) {
            return;
        }

        $lines = preg_split('/\r\n|\n/', $content);
        $firstLine = array_shift($lines);
        if ($firstLine !== '"' && $firstLine !== "'") {
            $error = 'The first line in multi-line SQL must be in a single line';
            $phpcsFile->addError($error, $stackPtr, 'FirstSQLLine');
        }

        $lastLine = trim(end($lines));
        $lastLineKey = key($lines);
        if ($lastLine !== '"' && $lastLine !== "'") {
            $error = 'The last line in multi-line SQL must be in a single line';
            $phpcsFile->addError($error, $stackPtr, 'LastSQLLine');
        }

        $currentLine = $tokens[$stackPtr]['line'];
        $lineColumn = $tokens[$stackPtr]['column'];
        $i = $stackPtr-1;
        while ($tokens[$i]['line'] === $currentLine) {
            if ($tokens[$i]['code'] === T_WHITESPACE) {
                $i--;
                continue;
            }
            $lineColumn = $tokens[$i]['column'];
            $i--;
        }

        foreach ($lines as $pos => $line) {
            $foundIndent = 0;
            // Assuming the spaces will be tab
            while (isset($line[$foundIndent]) && $line[$foundIndent] === "\t") {
                $foundIndent++;
            }
            $expectedIndent = $pos === $lastLineKey ? $lineColumn-1 : $lineColumn;
            if ($foundIndent !== $expectedIndent) {
                // Ok, we give a chance for 1 space, except in the first line
                if ($pos !== 0 && $foundIndent-1 === $expectedIndent) {
                    continue;
                }
                $error = 'Multi-line SQL not indented correctly; expected %s spaces but found %s';
                $data  = array(
                          $expectedIndent,
                          $foundIndent,
                         );
                $phpcsFile->addError($error, $stackPtr+$pos+1, 'AlignSQLLine', $data);
            }
        }
    }


    /**
     * Check if the string is SQL code
     *
     * @param string $content
     * @return boolean
     */
    protected function isSql($content) {
        return (bool)preg_match('/^[\'"]?\s*(SELECT|INSERT|DELETE|UPDATE|DROP|CREATE|TRUNCATE) /i', $content);
    }

}
