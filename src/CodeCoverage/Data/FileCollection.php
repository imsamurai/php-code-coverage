<?php
/**
 * PHP_CodeCoverage
 *
 * Copyright (c) 2009-2014, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   PHP
 * @package    CodeCoverage
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2009-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://github.com/sebastianbergmann/php-code-coverage
 * @since      File available since Release 2.1.0
 */

/**
 *
 *
 * @category   PHP
 * @package    CodeCoverage
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2009-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://github.com/sebastianbergmann/php-code-coverage
 * @since      Class available since Release 2.1.0
 */
class PHP_CodeCoverage_Data_FileCollection implements Countable, IteratorAggregate
{
    /**
     * @var PHP_CodeCoverage_Data_File[]
     */
    private $files = array();

    /**
     * @param string $id
     * @param array  $data
     */
    public function processData($id, array $data)
    {
        foreach ($data as $file => $_data) {
            if (!isset($this->files[$file])) {
                $this->createFile($file, $_data);
            }

            $_lines = array();

            foreach ($_data['lines'] as $lineNumber => $flag) {
                if ($flag == 1) {
                    $_lines[] = $lineNumber;
                }
            }

            $this->files[$file]->addCoveringTest($id, $_lines);
        }
    }

    /**
     * @param PHP_CodeCoverage_Data_FileCollection $other
     */
    public function merge(PHP_CodeCoverage_Data_FileCollection $other)
    {
    }

    /**
     * @param string $path
     * @param array  $data
     */
    private function createFile($path, array $data)
    {
        $_lines    = new PHP_CodeCoverage_Data_LineCollection;
        $functions = new PHP_CodeCoverage_Data_FunctionCollection;
        // @todo Populate $functions

        foreach ($data['lines'] as $lineNumber => $flag) {
            $opcodes = new PHP_CodeCoverage_Data_OpcodeCollection;
            // @todo Populate $opcodes

            $_lines->addLine(
                $lineNumber,
                new PHP_CodeCoverage_Data_Line(
                    $opcodes,
                    $flag != -2,
                    $flag == -2
                )
            );
        }

        for ($lineNumber = 1; $lineNumber <= PHP_CodeCoverage_Util::numberOfLinesInFile($path); $lineNumber++) {
            if (!isset($lines[$lineNumber])) {
                $_lines->addLine(
                    $lineNumber,
                    new PHP_CodeCoverage_Data_Line(
                        new PHP_CodeCoverage_Data_OpcodeCollection,
                        false,
                        false
                    )
                );
            }
        }

        $this->files[$path] = new PHP_CodeCoverage_Data_File($path, $functions, $_lines);
    }

    /**
     * @return integer
     */
    public function count()
    {
        return count($this->files);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->files);
    }
}
