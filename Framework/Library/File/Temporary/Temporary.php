<?php

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2007-2011, Ivan Enderlin. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the Hoa nor the names of its contributors may be
 *       used to endorse or promote products derived from this software without
 *       specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS AND CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace {

from('Hoa')

/**
 * \Hoa\File\Exception
 */
-> import('File.Exception.~')

/**
 * \Hoa\File
 */
-> import('File.~');

}

namespace Hoa\File\Temporary {

/**
 * Class \Hoa\File\Temporary.
 *
 * Temporary file handler.
 *
 * @author     Ivan ENDERLIN <ivan.enderlin@hoa-project.net>
 * @copyright  Copyright © 2007-2011 Ivan ENDERLIN.
 * @license    New BSD License
 */

class Temporary extends \Hoa\File {

    /**
     * Temporary file index.
     *
     * @var \Hoa\File\Temporary int
     */
    private static $_i = 0;



    /**
     * Open a temporary file.
     *
     * @access  public
     * @return  void
     * @throw   \Hoa\Stream\Exception
     */
    public function __construct ( $streamName = null ) {

        if(null === $streamName)
            $streamName = 'hoa://Library/File/Temporary.php#' .
                          self::$_i++;

        parent::__construct($streamName, null);

        return;
    }

    /**
     * Open the stream and return the associated resource.
     *
     * @access  protected
     * @param   string              $streamName    Stream name (here, it is
     *                                             null).
     * @param   \Hoa\Stream\Context  $context       Context.
     * @return  resource
     * @throw   \Hoa\File\Exception
     */
    protected function &_open ( $streamName, \Hoa\Stream\Context $context = null ) {

        if(false === $out = @tmpfile())
            throw new \Hoa\File\Exception(
                'Failed to open a temporary stream.', 0);

        return $out;
    }

    /**
     * Create a unique temporary file, i.e. a file with a unique filename. It is
     * different of calling $this->__construct() that will create a temporary
     * file that will be destroy when calling the $this->close() method.
     *
     * @access  public
     * @param   string  $directory    Directory where the temporary filename
     *                                will be created. If the directory does not
     *                                exist, it may generate a file in the
     *                                system's temporary directory.
     * @param   string  $prefix       Prefix of the generated temporary
     *                                filename.
     * @return  bool
     */
    public static function create ( $directory = '/tmp', $prefix = '__hoa_' ) {

        if(file_exists($name))
            return true;

        return tempnam($directory, $prefix);
    }

    /**
     * Get the directory path used for temporary files.
     *
     * @access  public
     * @return  string
     */
    public static function getTemporaryDirectory ( ) {

        if(PHP_VERSION_ID >= 50201)
            return sys_get_temp_dir();

        if(OS_WIN) {

            if(isset($_ENV['TEMP']))          return $_ENV['TEMP'];
            if(isset($_ENV['TMP']))           return $_ENV['TMP'] . DS;
            if(isset($_ENV['windir']))        return $_ENV['windir'] . DS . 'temp' . DS;
            if(isset($_ENV['SystemRoot']))    return $_ENV['SystemRoot'] . DS . 'temp' . DS;
            if(isset($_SERVER['TEMP']))       return $_SERVER['TEMP'] . DS;
            if(isset($_SERVER['TMP']))        return $_SERVER['TMP'] . DS;
            if(isset($_SERVER['windir']))     return $_SERVER['windir'] . DS . 'temp' . DS;
            if(isset($_SERVER['SystemRoot'])) return $_SERVER['SystemRoot'] . DS . 'temp' . DS;
        }

        if(isset($_ENV['TMPDIR']))            return $_ENV['TMPDIR'] . DS;
        if(isset($_SERVER['TMPDIR']))         return $_SERVER['TMPDIR'] . DS;

        return DS . 'tmp' . DS;
    }
}

}
