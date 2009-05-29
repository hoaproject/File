<?php

/**
 * Hoa Framework
 *
 *
 * @license
 *
 * GNU General Public License
 *
 * This file is part of Hoa Open Accessibility.
 * Copyright (c) 2007, 2008 Ivan ENDERLIN. All rights reserved.
 *
 * HOA Open Accessibility is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * HOA Open Accessibility is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with HOA Open Accessibility; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 *
 * @category    Framework
 * @package     Hoa_File
 * @subpackage  Hoa_File_Temporary
 *
 */

/**
 * Hoa_Framework
 */
require_once 'Framework.php';

/**
 * Hoa_File_Exception
 */
import('File.Exception');

/**
 * Hoa_File
 */
import('File.~');

/**
 * Class Hoa_File_Temporary.
 *
 * Temporary file handler.
 *
 * @author      Ivan ENDERLIN <ivan.enderlin@hoa-project.net>
 * @copyright   Copyright (c) 2007, 2008 Ivan ENDERLIN.
 * @license     http://gnu.org/licenses/gpl.txt GNU GPL
 * @since       PHP 5
 * @version     0.1
 * @package     Hoa_File
 * @subpackage  Hoa_File_Temporary
 */

class Hoa_File_Temporary extends Hoa_File {

    /**
     * Open a temporary file.
     *
     * @access  public
     * @return  void
     * @throw   Hoa_Stream_Exception
     */
    public function __construct ( ) {

        parent::__construct(null, null);
    }

    /**
     * Open the stream and return the associated resource.
     *
     * @access  protected
     * @param   string              $streamName    Stream name (here, it is
     *                                             null).
     * @param   Hoa_Stream_Context  $context       Context.
     * @return  resource
     * @throw   Hoa_File_Exception
     */
    protected function &open ( $streamName, Hoa_Stream_Context $context = null ) {

        if(false === $out = @tmpfile())
            throw new Hoa_File_Exception(
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

        if(version_compare(phpversion(), '5.2.1', '>='))
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
