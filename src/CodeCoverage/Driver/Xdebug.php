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
 * @since      File available since Release 1.0.0
 */

/**
 * Driver for Xdebug's code coverage functionality.
 *
 * @category   PHP
 * @package    CodeCoverage
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2009-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://github.com/sebastianbergmann/php-code-coverage
 * @since      Class available since Release 1.0.0
 * @codeCoverageIgnore
 */
class PHP_CodeCoverage_Driver_Xdebug extends PHP_CodeCoverage_Driver
{
    /**
     * @var integer
     */
    private $flags = false;

    /**
     * Constructor.
     */
    protected function ensureDriverCanWork()
    {
        if (!extension_loaded('xdebug')) {
            throw new PHP_CodeCoverage_Exception('This driver requires Xdebug');
        }

        if (version_compare(phpversion('xdebug'), '2.2.0-dev', '>=') &&
            !ini_get('xdebug.coverage_enable')) {
            throw new PHP_CodeCoverage_Exception(
                'xdebug.coverage_enable=On has to be set in php.ini'
            );
        }

        $this->flags = XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE;

        if (defined('XDEBUG_CC_BRANCH_CHECK')) {
            $this->flags |= XDEBUG_CC_BRANCH_CHECK;
        }
    }

    /**
     * Start collection of code coverage information.
     */
    protected function doStart()
    {
        xdebug_start_code_coverage($this->flags);
    }

    /**
     * Stop collection of code coverage information.
     *
     * @return array
     */
    protected function doStop()
    {
        $data = xdebug_get_code_coverage();

        xdebug_stop_code_coverage();

        return $data;
    }

    /**
     * @param array $data
     */
    protected function cleanup(array &$data)
    {
        foreach (array_keys($data) as $file) {
            if (!isset($data[$file]['lines'])) {
                $data[$file] = array('lines' => $data[$file]);
            }

            if (isset($data[$file]['lines'][0])) {
                unset($data[$file]['lines'][0]);
            }

            if (file_exists($file)) {
                $numLines = PHP_CodeCoverage_Util::numberOfLinesInFile($file);

                foreach (array_keys($data[$file]['lines']) as $line) {
                    if (isset($data[$file]['lines'][$line]) && $line > $numLines) {
                        unset($data[$file]['lines'][$line]);
                    }
                }
            }
        }
    }
}

