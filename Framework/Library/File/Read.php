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
 * @subpackage  Hoa_File_Read
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
 * Hoa_Stream_Io_In
 */
import('Stream.Io.In');

/**
 * Class Hoa_File_Read.
 *
 * File handler.
 *
 * @author      Ivan ENDERLIN <ivan.enderlin@hoa-project.net>
 * @copyright   Copyright (c) 2007, 2010 Ivan ENDERLIN.
 * @license     http://gnu.org/licenses/gpl.txt GNU GPL
 * @since       PHP 5
 * @version     0.3
 * @package     Hoa_File
 * @subpackage  Hoa_File_Read
 */

class          Hoa_File_Read
    extends    Hoa_File
    implements Hoa_Stream_Io_In {

    /**
     * Open a file.
     *
     * @access  public
     * @param   string  $streamName    Stream name.
     * @param   string  $mode          Open mode, see the self::MODE_* constants.
     * @param   string  $context       Context ID (please, see the
     *                                 Hoa_Stream_Context class).
     * @return  void
     * @throw   Hoa_Stream_Exception
     */
    public function __construct ( $streamName, $mode = parent::MODE_READ,
                                  $context = null ) {

        parent::__construct($streamName, $mode, $context);
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

        static $createModes = array(
            parent::MODE_READ
        );

        if(!in_array($this->getMode(), $createModes))
            throw new Hoa_File_Exception(
                'Open mode are not supported; given %d. Only %s are supported.',
                0, array($this->getMode(), implode(',', $createModes)));

        preg_match('#^(\w+)://#', $streamName, $match);

        if((   (isset($match[1]) && $match[1] == 'file') || !isset($match[1]))
            && !file_exists($streamName))
            throw new Hoa_File_Exception_FileDoesNotExist(
                'File %s does not exist.', 0, $streamName);

        $out = parent::open($streamName, $context);

        return $out;
    }

    /**
     * Test for end-of-file.
     *
     * @access  public
     * @return  bool
     */
    public function eof ( ) {

        return feof($this->getStream());
    }

    /**
     * Read n characters.
     *
     * @access  public
     * @param   int     $length    Length.
     * @return  string
     * @throw   Hoa_File_Exception
     */
    public function read ( $length ) {

        if($length <= 0)
            throw new Hoa_File_Exception(
                'Length must be greather than 0, given %d.', 3, $length);

        return fread($this->getStream(), $length);
    }

    /**
     * Alias of $this->read().
     *
     * @access  public
     * @param   int     $length    Length.
     * @return  string
     */
    public function readString ( $length ) {

        return $this->read($length);
    }

    /**
     * Read a character.
     *
     * @access  public
     * @return  string
     */
    public function readCharacter ( ) {

        return fgetc($this->getStream());
    }

    /**
     * Read a boolean.
     *
     * @access  public
     * @return  bool
     */
    public function readBoolean ( ) {

        return (bool) $this->read(1);
    }

    /**
     * Read an integer.
     *
     * @access  public
     * @param   int     $length    Length.
     * @return  int
     */
    public function readInteger ( $length = 1 ) {

        return (int) $this->read($length);
    }

    /**
     * Read a float.
     *
     * @access  public
     * @param   int     $length    Length.
     * @return  float
     */
    public function readFloat ( $length = 1 ) {

        return (float) $this->read($length);
    }

    /**
     * Read an array.
     * Alias of the $this->scanf() method.
     *
     * @access  public
     * @param   string  $format    Format (see printf's formats).
     * @return  array
     */
    public function readArray ( $format ) {

        return $this->scanf($format);
    }

    /**
     * Read a line.
     *
     * @access  public
     * @return  string
     */
    public function readLine ( ) {

        return fgets($this->getStream());
    }

    /**
     * Read all, i.e. read as much as possible.
     *
     * @access  public
     * @return  string
     */
    public function readAll ( ) {

        if(true === $this->isStreamResourceMustBeUsed()) {

            $current = $this->tell();
            $this->seek(0, Hoa_Stream_Io_Pointable::SEEK_END);
            $end     = $this->tell();
            $this->seek($current, Hoa_Stream_Io_Pointable::SEEK_SET);

            return $this->read($end - $current);
        }

        if(PHP_VERSION_ID < 60000)
            $second = true;
        else
            $second = 0;

        if(null === $this->getStreamContext())
            $third  = null;
        else
            $third  = $this->getStreamContext()->getContext();

        return file_get_contents(
            $this->getStreamName(),
            $second,
            $third,
            $this->tell()
        );
    }

    /**
     * Parse input from a stream according to a format.
     *
     * @access  public
     * @param   string  $format    Format (see printf's formats).
     * @return  array
     */
    public function scanf ( $format ) {

        return fscanf($this->getStream(), $format);
    }
}
