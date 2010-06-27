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
 * Copyright (c) 2007, 2010 Ivan ENDERLIN. All rights reserved.
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
 * @subpackage  Hoa_File_Directory
 *
 */

/**
 * Hoa_Core
 */
require_once 'Core.php';

/**
 * Hoa_File_Exception
 */
import('File.Exception');

/**
 * Hoa_File_Exception_FileDoesNotExist
 */
import('File.Exception.FileDoesNotExist');

/**
 * Hoa_File_Abstract
 */
import('File.Abstract');

/**
 * Hoa_File_Finder
 */
import('File.Finder');

/**
 * Hoa_Stream_Exception
 */
import('Stream.Exception');

/**
 * Hoa_Stream_Context
 */
import('Stream.Context');

/**
 * Class Hoa_File_Directory.
 *
 * Directory handler.
 *
 * @author      Ivan ENDERLIN <ivan.enderlin@hoa-project.net>
 * @copyright   Copyright (c) 2007, 2010 Ivan ENDERLIN.
 * @license     http://gnu.org/licenses/gpl.txt GNU GPL
 * @since       PHP 5
 * @version     0.1
 * @package     Hoa_File
 * @subpackage  Hoa_File_Directory
 */

class Hoa_File_Directory extends Hoa_File_Abstract {

    /**
     * Open for reading.
     *
     * @const string
     */
    const MODE_READ             = 'rb';

    /**
     * Open for reading and writing. If the directory does not exist, attempt to
     * create it.
     *
     * @const string
     */
    const MODE_CREATE           = 'xb';

    /**
     * Open for reading and writing. If the directory does not exist, attempt to
     * create it recursively.
     *
     * @const string
     */
    const MODE_CREATE_RECURSIVE = 'xrb';



    /**
     * Open a directory.
     *
     * @access  public
     * @param   string  $streamName    Stream name.
     * @param   string  $mode          Open mode, see the self::MODE* constants.
     * @param   string  $context       Context ID (please, see the
     *                                 Hoa_Stream_Context class).
     * @return  void
     * @throw   Hoa_Stream_Exception
     */
    public function __construct ( $streamName, $mode = self::MODE_READ,
                                  $context = null ) {

        $this->setMode($mode);
        parent::__construct($streamName, $context);
    }

    /**
     * Open the stream and return the associated resource.
     *
     * @access  protected
     * @param   string              $streamName    Stream name (e.g. path or URL).
     * @param   Hoa_Stream_Context  $context       Context.
     * @return  resource
     * @throw   Hoa_File_Exception_FileDoesNotExist
     * @throw   Hoa_File_Exception
     */
    protected function &open ( $streamName, Hoa_Stream_Context $context = null ) {

        if(false === is_dir($streamName))
            if($this->getMode() == self::MODE_READ)
                throw new Hoa_File_Exception_FileDoesNotExist(
                    'Directory %s does not exist.', 0, $streamName);
            else
                self::create(
                    $streamName,
                    $this->getMode(),
                    null !== $context
                        ? $context->getContext()
                        : null
                );

        if(null === $context) {

            if(false === $out = @fopen($streamName, 'r'))
                throw new Hoa_File_Exception(
                    'Failed to open stream %s.', 1, $streamName);

            return $out;
        }

        if(false === $out = @fopen($streamName, 'r', $context->getContext()))
            throw new Hoa_File_Exception(
                'Failed to open stream %s.', 2, $streamName);

        return $out;
    }

    /**
     * Close the current stream.
     *
     * @access  public
     * @return  bool
     */
    public function close ( ) {

        return fclose($this->getStream());
    }

    /**
     * Copy file.
     * Return the destination directory path if succeed, false otherwise.
     *
     * @access  public
     * @param   string  $to       Destination path.
     * @param   bool    $force    Force to copy if the file $to already exists.
     *                            Use the Hoa_Stream_Io_Touchable::*OVERWRITE
     *                            constants.
     * @return  bool
     * @throw   Hoa_File_Exception
     */
    public function copy ( $to, $force = Hoa_Stream_Io_Touchable::DO_NOT_OVERWRITE ) {

        $from   = $this->getStreamName();
        $finder = new Hoa_File_Finder(
            $from,
            Hoa_File_Finder::LIST_ALL |
            Hoa_File_Finder::LIST_NO_DOT
        );

        self::create($to, self::MODE_CREATE_RECURSIVE);

        foreach($finder as $key => $file) {

            if(   $force === Hoa_Stream_Io_Touchable::DO_NOT_OVERWRITE
               && file_exists($to . DS . $file))
                continue;

            $file->define()->copy(
                $to . DS . substr($file->getStreamName(), strlen($from) + 1),
                $force
            );

            $file->close();
        }

        $finder->close();

        return true;
    }

    /**
     * Delete a directory.
     *
     * @access  public
     * @return  bool
     */
    public function delete ( ) {

        $from   = $this->getStreamName();
        $finder = new Hoa_File_Finder(
            $from,
            Hoa_File_Finder::LIST_ALL |
            Hoa_File_Finder::LIST_NO_DOT
        );

        foreach($finder as $key => $file)
            $file->define()->delete();

        if(null === $this->getStreamContext())
            return @rmdir($from);

        return @rmdir($from, $this->getStreamContext()->getContext());
    }

    /**
     * Create a directory.
     *
     * @access  public
     * @param   string  $name       Directory name.
     * @param   string  $mode       Create mode. Please, see the self::MODE_CREATE*
     *                              constants.
     * @param   string  $context    Context ID (please, see the
     *                              Hoa_Stream_Context class).
     * @return  bool
     * @throw   Hoa_Stream_Exception
     */
    public static function create ( $name, $mode = self::MODE_CREATE_RECURSIVE,
                                    $context = null ) {

        if(true === is_dir($name))
            return true;

        if(empty($name))
            return false;

        if(null !== $context)
            if(false === Hoa_Stream_Context::contextExists($context))
                throw new Hoa_Stream_Exception(
                    'Context %s was not previously declared, cannot retrieve ' .
                    'this context.', 3, $context);
            else
                $context = Hoa_Stream_Context::getInstance($context);

        if(null === $context)
            return @mkdir(
                $name,
                0755,
                $mode == self::MODE_CREATE_RECURSIVE
            );

        return @mkdir(
            $name,
            0755,
            $mode == self::MODE_CREATE_RECURSIVE,
            $context->getContext()
        );
    }
}
