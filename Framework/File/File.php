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
 * Hoa_File_Exception_FileDoesNotExist
 */
import('File.Exception.FileDoesNotExist');

/**
 * Hoa_File_Abstract
 */
import('File.Abstract');

/**
 * Hoa_Stream_Io
 */
import('Stream.Io');

/**
 * Hoa_Stream_Io_Bufferable
 */
import('Stream.Io.Bufferable');

/**
 * Hoa_Stream_Io_Lockable
 */
import('Stream.Io.Lockable');

/**
 * Hoa_Stream_Io_Pointable
 */
import('Stream.Io.Pointable');

/**
 * Class Hoa_File.
 *
 * File handler.
 *
 * @author      Ivan ENDERLIN <ivan.enderlin@hoa-project.net>
 * @copyright   Copyright (c) 2007, 2008 Ivan ENDERLIN.
 * @license     http://gnu.org/licenses/gpl.txt GNU GPL
 * @since       PHP 5
 * @version     0.3
 * @package     Hoa_File
 */

class          Hoa_File
    extends    Hoa_File_Abstract
    implements Hoa_Stream_Io,
               Hoa_Stream_Io_Bufferable,
               Hoa_Stream_Io_Lockable,
               Hoa_Stream_Io_Pointable {

    /**
     * Open for reading only; place the file pointer at the beginning of the
     * file.
     *
     * @const string
     */
    const MODE_READ                = 'rb';

    /**
     * Open for reading and writing; place the file pointer at the beginning of
     * the file.
     *
     * @const string
     */
    const MODE_READ_WRITE          = 'r+b';

    /**
     * Open for writing only; place the file pointer at the beginning of the
     * file and truncate the file to zero length. If the file does not exist,
     * attempt to create it.
     *
     * @const string
     */
    const MODE_TRUNCATE_WRITE      = 'wb';

    /**
     * Open for reading and writing; place the file pointer at the beginning of
     * the file and truncate the file to zero length. If the file does not
     * exist, attempt to create it.
     *
     * @const string
     */
    const MODE_TRUNCATE_READ_WRITE = 'w+b';

    /**
     * Open for writing only; place the file pointer at the end of the file. If
     * the file does not exist, attempt to create it.
     *
     * @const string
     */
    const MODE_APPEND_WRITE        = 'ab';

    /**
     * Open the reading and writing; place the file pointer at the end of the
     * file. If the file does not exist, attempt to create it.
     *
     * @const string
     */
    const MODE_APPEND_READ_WRITE   = 'a+b';

    /**
     * Create and open for writing only; place the file pointer at the beginning
     * of the file. If the file already exits, the fopen() call with fail by
     * returning false and generating an error of level E_WARNING. If the file
     * does not exist, attempt to create it. This is equivalent to specifying
     * O_EXCL | O_CREAT flags for the underlying open(2) system call.
     *
     * @const string
     */
    const MODE_CREATE_WRITE        = 'xb';

    /**
     * Create and open for reading and writing; place the file pointer at the
     * beginning of the file. If the file already exists, the fopen() call with
     * fail by returning false and generating an error of level E_WARNING. If
     * the file does not exist, attempt to create it. This is equivalent to
     * specifying O_EXCL | O_CREAT flags for the underlying open(2) system call.
     *
     * @const string
     */
    const MODE_CREATE_READ_WRITE   = 'x+b';



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

        static $createModes = array(
            self::MODE_TRUNCATE_WRITE,
            self::MODE_TRUNCATE_READ_WRITE,
            self::MODE_APPEND_WRITE,
            self::MODE_APPEND_READ_WRITE,
            self::MODE_CREATE_WRITE,
            self::MODE_CREATE_READ_WRITE,
        );

        if(   !in_array($this->getMode(), $createModes)
           && !file_exists($streamName))
            throw new Hoa_File_Exception_FileDoesNotExist(
                'File %s does not exist.', 0, $streamName);

        if(null === $context) {

            if(false === $out = @fopen($streamName, $this->getMode()))
                throw new Hoa_File_Exception(
                    'Failed to open stream %s.', 1, $streamName);

            return $out;
        }

        if(false === $out = @fopen($streamName, $this->getMode(), $context->getContext()))
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

        return @fclose($this->getStream());
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
     * Get filename component of path.
     *
     * @access  public
     * @return  string
     */
    public function getBasename ( ) {

        return basename($this->getStreamName());
    }

    /**
     * Get directory name component of path.
     *
     * @access  public
     * @return  string
     */
    public function getDirname ( ) {

        return dirname($this->getStreamName());
    }

    /**
     * Get size.
     *
     * @access  public
     * @return  int
     */
    public function getSize ( ) {

        return filesize($this->getStreamName());
    }

    /**
     * Read n characters.
     *
     * @access  public
     * @param   int     $length    Length.
     * @return  string
     */
    public function read ( $length ) {

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

    /**
     * Write n characters.
     *
     * @access  public
     * @param   string  $string    String.
     * @param   int     $length    Length.
     * @return  mixed
     */
    public function write ( $string, $length ) {

        return fwrite($this->getStream(), $string, $length);
    }

    /**
     * Write a string.
     *
     * @access  public
     * @param   string  $string    String.
     * @return  mixed
     */
    public function writeString ( $string ) {

        $string = (string) $string;

        return $this->write($string, strlen($string));
    }

    /**
     * Write a character.
     *
     * @access  public
     * @param   string  $char    Character.
     * @return  mixed
     */
    public function writeCharacter ( $char ) {

        return $this->write((string) $char[0], 1);
    }

    /**
     * Write an integer.
     *
     * @access  public
     * @param   int     $integer    Integer.
     * @return  mixed
     */
    public function writeInteger ( $integer ) {

        $integer = (string) (int) $integer;

        return $this->write($integer, strlen($integer));
    }

    /**
     * Write a float.
     *
     * @access  public
     * @param   float   $float    Float.
     * @return  mixed
     */
    public function writeFloat ( $float ) {

        $float = (string) (float) $float;

        return $this->write($float, strlen($float));
    }

    /**
     * Write an array.
     *
     * @access  public
     * @param   array   $array    Array.
     * @return  mixed
     */
    public function writeArray ( Array $array ) {

        $array = serialize($array);

        return $this->write($array, strlen($array));
    }

    /**
     * Write a line.
     *
     * @access  public
     * @param   string  $line    Line.
     * @return  mixed
     */
    public function writeLine ( $line ) {

        if(false === $n = strpos($line, "\n"))
            return $this->write($line, strlen($line));

        return $this->write(substr($line, 0, $n), $n);
    }

    /**
     * Write all, i.e. as much as possible.
     *
     * @access  public
     * @param   string  $string    String.
     * @return  mixed
     */
    public function writeAll ( $string ) {

        return $this->write($string, strlen($string));
    }

    /**
     * Truncate a file to a given length.
     *
     * @access  public
     * @param   int     $size    Size.
     * @return  bool
     */
    public function truncate ( $size ) {

        return ftruncate($this->getStream(), $size);
    }

    /**
     * Flush the output to a stream.
     *
     * @access  public
     * @return  bool
     */
    public function flush ( ) {

        return fflush($this->getStream());
    }

    /**
     * Portable advisory locking.
     *
     * @access  public
     * @param   int     $operation    Operation, use the
     *                                Hoa_Stream_Io_Lockable::LOCK_* constants.
     * @return  bool
     */
    public function lock ( $operation ) {

        return flock($this->getStream(), $operation);
    }

    /**
     * Rewind the position of a stream pointer.
     *
     * @access  public
     * @return  bool
     */
    public function rewind ( ) {

        return rewind($this->getStream());
    }

    /**
     * Seek on a stream pointer.
     *
     * @access  public
     * @param   int     $offset    Offset (negative value should be supported).
     * @param   int     $whence    Whence, use the
     *                             Hoa_Stream_Io_Pointable::SEEK_* constants.
     * @return  int
     */
    public function seek ( $offset, $whence = Hoa_Stream_Io_Pointable::SEEK_SET ) {

        return fseek($this->getStream(), $offset, $whence);
    }

    /**
     * Get the current position of the stream pointer.
     *
     * @access  public
     * @return  int
     */
    public function tell ( ) {

        return ftell($this->getStream());
    }

    /**
     * Create a file.
     *
     * @access  public
     * @param   string  $name     File name.
     * @param   mixed   $dummy    To be compatible with childs.
     * @return  bool
     */
    public static function create ( $name, $dummy ) {

        if(file_exists($name))
            return true;

        return touch($name);
    }
}
