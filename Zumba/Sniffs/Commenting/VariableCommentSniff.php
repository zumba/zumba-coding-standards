<?php
/**
 * Parses and verifies the variable doc comment.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 */

if (class_exists('Squiz_Sniffs_Commenting_VariableCommentSniff', true) === false) {
    throw new PHP_CodeSniffer_Exception('Class Squiz_Sniffs_Commenting_VariableCommentSniff not found');
}

/**
 * Parses and verifies the variable doc comment.
 *
 * Verifies that :
 * <ul>
 *  <li>A variable doc comment exists.</li>
 *  <li>There is a blank line after the short description.</li>
 *  <li>There is a blank line between the description and the tags.</li>
 *  <li>Check the order, indentation and content of each tag.</li>
 * </ul>
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 */

class Zumba_Sniffs_Commenting_VariableCommentSniff extends Squiz_Sniffs_Commenting_VariableCommentSniff
{

    /**
     * Called to process class member vars.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function processMemberVar(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $this->currentFile = $phpcsFile;
        $tokens            = $phpcsFile->getTokens();
        $commentToken      = array(
                              T_COMMENT,
                              T_DOC_COMMENT,
                             );

        // Extract the var comment docblock.
        $commentEnd = $phpcsFile->findPrevious($commentToken, ($stackPtr - 3));
        if ($commentEnd !== false && $tokens[$commentEnd]['code'] === T_COMMENT) {
            $phpcsFile->addError('You must use "/**" style comments for a variable comment', $stackPtr, 'WrongStyle');
            return;
        } else if ($commentEnd === false || $tokens[$commentEnd]['code'] !== T_DOC_COMMENT) {
            $phpcsFile->addError('Missing variable doc comment', $stackPtr, 'Missing');
            return;
        } else {
            // Make sure the comment we have found belongs to us.
            $commentFor = $phpcsFile->findNext(array(T_VARIABLE, T_CLASS, T_INTERFACE), ($commentEnd + 1));
            if ($commentFor !== $stackPtr) {
                $phpcsFile->addError('Missing variable doc comment', $stackPtr, 'Missing');
                return;
            }
        }

        $commentStart  = ($phpcsFile->findPrevious(T_DOC_COMMENT, ($commentEnd - 1), null, true) + 1);
        $commentString = $phpcsFile->getTokensAsString($commentStart, ($commentEnd - $commentStart + 1));

        // Parse the header comment docblock.
        try {
            $this->commentParser = new PHP_CodeSniffer_CommentParser_MemberCommentParser($commentString, $phpcsFile);
            $this->commentParser->parse();
        } catch (PHP_CodeSniffer_CommentParser_ParserException $e) {
            $line = ($e->getLineWithinComment() + $commentStart);
            $phpcsFile->addError($e->getMessage(), $line, 'ErrorParsing');
            return;
        }

        $comment = $this->commentParser->getComment();
        if (is_null($comment) === true) {
            $error = 'Variable doc comment is empty';
            $phpcsFile->addError($error, $commentStart, 'Empty');
            return;
        }

        // The first line of the comment should just be the /** code.
        $eolPos    = strpos($commentString, $phpcsFile->eolChar);
        $firstLine = substr($commentString, 0, $eolPos);
        if ($firstLine !== '/**') {
            $error = 'The open comment tag must be the only content on the line';
            $phpcsFile->addError($error, $commentStart, 'ContentAfterOpen');
        }

        // Check for a comment description.
        $short = $comment->getShortComment();
        $long  = '';
        if (trim($short) === '') {
            $newlineCount = 1;
        } else {
            // No extra newline before short description.
            $newlineCount = 0;
            $newlineSpan  = strspn($short, $phpcsFile->eolChar);
            if ($short !== '' && $newlineSpan > 0) {
                $error = 'Extra newline(s) found before variable comment short description';
                $phpcsFile->addError($error, ($commentStart + 1), 'SpacingBeforeShort');
            }

            $newlineCount = (substr_count($short, $phpcsFile->eolChar) + 1);

            // Exactly one blank line between short and long description.
            $long = $comment->getLongComment();
            if (empty($long) === false) {
                $between        = $comment->getWhiteSpaceBetween();
                $newlineBetween = substr_count($between, $phpcsFile->eolChar);
                if ($newlineBetween !== 2) {
                    $error = 'There must be exactly one blank line between descriptions in variable comment';
                    $phpcsFile->addError($error, ($commentStart + $newlineCount + 1), 'SpacingBetween');
                }

                $newlineCount += $newlineBetween;

                $testLong = trim($long);
                if (preg_match('|[A-Z]|', $testLong[0]) === 0) {
                    $error = 'Variable comment long description must start with a capital letter';
                    $phpcsFile->addError($error, ($commentStart + $newlineCount), 'LongNotCapital');
                }
            }//end if

            // Short description must be single line.
            $testShort = trim($short);
            $lastChar  = $testShort[(strlen($testShort) - 1)];
            if (substr_count($testShort, $phpcsFile->eolChar) !== 0) {
                $error = 'Variable comment short description must be on a single line';
                $phpcsFile->addError($error, ($commentStart + 1), 'ShortSingleLine');
            }

            if (preg_match('|[A-Z]|', $testShort[0]) === 0) {
                $error = 'Variable comment short description must start with a capital letter';
                $phpcsFile->addError($error, ($commentStart + 1), 'ShortNotCapital');
            }
        }//end if

        // Exactly one blank line before tags.
        $tags = $this->commentParser->getTagOrders();
        if (count($tags) > 1 && trim($short) !== '') {
            $newlineSpan = $comment->getNewlineAfter();
            if ($newlineSpan !== 2) {
                $error = 'There must be exactly one blank line before the tags in variable comment';
                if ($long !== '') {
                    $newlineCount += (substr_count($long, $phpcsFile->eolChar) - $newlineSpan + 1);
                }

                $phpcsFile->addError($error, ($commentStart + $newlineCount), 'SpacingBeforeTags');
                $short = rtrim($short, $phpcsFile->eolChar.' ');
            }
        }

        // Check for unknown/deprecated tags.
        $unknownTags = $this->commentParser->getUnknown();
        foreach ($unknownTags as $errorTag) {
            // Unknown tags are not parsed, do not process further.
            $error = '@%s tag is not allowed in variable comment';
            $data  = array($errorTag['tag']);
            $phpcsFile->addWarning($error, ($commentStart + $errorTag['line']), 'TagNotAllowed', $data);
        }

        // Check each tag.
        $this->processVar($commentStart, $commentEnd);
        $this->processSees($commentStart);

        // The last content should be a newline and the content before
        // that should not be blank. If there is more blank space
        // then they have additional blank lines at the end of the comment.
        $words   = $this->commentParser->getWords();
        $lastPos = (count($words) - 1);
        if (trim($words[($lastPos - 1)]) !== ''
            || strpos($words[($lastPos - 1)], $this->currentFile->eolChar) === false
            || trim($words[($lastPos - 2)]) === ''
        ) {
            $error = 'Additional blank lines found at end of variable comment';
            $this->currentFile->addError($error, $commentEnd, 'SpacingAfter');
        }

    }//end processMemberVar()


    /**
     * Process the var tag.
     *
     * @param int $commentStart The position in the stack where the comment started.
     * @param int $commentEnd   The position in the stack where the comment ended.
     *
     * @return void
     */
    protected function processVar($commentStart, $commentEnd)
    {
        $var = $this->commentParser->getVar();

        if ($var !== null) {
            $errorPos = ($commentStart + $var->getLine());
            $index    = array_keys($this->commentParser->getTagOrders(), 'var');

            if (count($index) > 1) {
                $error = 'Only 1 @var tag is allowed in variable comment';
                $this->currentFile->addError($error, $errorPos, 'DuplicateVar');
                return;
            }

            if ($index[0] !== 1) {
                $error = 'The @var tag must be the first tag in a variable comment';
                $this->currentFile->addError($error, $errorPos, 'VarOrder');
            }

            $content = $var->getContent();
            if (empty($content) === true) {
                $error = 'Var type missing for @var tag in variable comment';
                $this->currentFile->addError($error, $errorPos, 'MissingVarType');
                return;
            } else {
				$content = $content == 'bool' ? 'boolean' : ($content == 'int' ? 'integer' : $content);
                $suggestedType = PHP_CodeSniffer::suggestType($content);
                if ($content !== $suggestedType) {
                    $error = 'Expected "%s"; found "%s" for @var tag in variable comment';
                    $data  = array(
                              $suggestedType,
                              $content,
                             );
                    $this->currentFile->addError($error, $errorPos, 'IncorrectVarType', $data);
                }
            }

            $spacing = substr_count($var->getWhitespaceBeforeContent(), ' ');
            if ($spacing !== 1) {
                $error = '@var tag indented incorrectly; expected 1 space but found %s';
                $data  = array($spacing);
                $this->currentFile->addError($error, $errorPos, 'VarIndent', $data);
            }
        } else {
            $error = 'Missing @var tag in variable comment';
            $this->currentFile->addError($error, $commentEnd, 'MissingVar');
        }//end if

    }//end processVar()


}//end class
