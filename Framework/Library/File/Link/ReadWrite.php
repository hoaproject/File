<?php

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright (c) 2007-2011, Ivan Enderlin. All rights reserved.
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
 * \Hoa\File\Link
 */
-> import('File.Link.~')

/**
 * \Hoa\Stream\IStream\In
 */
-> import('Stream.I~.In')

/**
 * \Hoa\Stream\IStream\Out
 */
-> import('Stream.I~.Out');

}

namespace Hoa\File\Link {

/**
 * Class \Hoa\File\Link\ReadWrite.
 *
 * File handler.
 *
 * @author     Ivan ENDERLIN <ivan.enderlin@hoa-project.net>
 * @copyright  Copyright (c) 2007-2011 Ivan ENDERLIN.
 * @license    New BSD License
 */

class          ReadWrite
    extends    Link
    implements \Hoa\Stream\IStream\In,
               \Hoa\Stream\IStream\Out {

    /**
     * Open a file.
     *
     * @access  public
     * @param   string  $streamName    Stream name.
     * @param   string  $mode          Open mode, see the parent::MODE_* constants.
     * @param   string  $context       Context ID (please, see the
     *                                 \Hoa\Stream\Context class).
     * @return  void
     * @throw   \Hoa\Stream\Exception
     */
    public function __construct ( $streamName, $mode = parent::MODE_APPEND_READ_WRITE,
                                  $context = null ) {

        parent::__construct($streamName, $mode, $context);

        return;
    }

    /**
     * Open the stream and return the associated resource.
     *
     * @access  protected
     * @param   string               $streamName    Stream name (e.g. path or URL).
     * @param   \Hoa\Stream\Context  $context       Context.
     * @return  resource
     * @throw   \Hoa\File\Exception\FileDoesNotExist
     * @throw   \Hoa\File\Exception
     */
    protected function &_open ( $streamName, \Hoa\Stream\Context $context = null ) {

        static $createModes = array(
            parent::MODE_READ_WRITE,
            parent::MODE_TRUNCATE_READ_WRITE,
            parent::MODE_APPEND_READ_WRITE,
            parent::MODE_CREATE_READ_WRITE
        );

        if(!in_array($this->getMode(), $createModes))
            throw new \Hoa\File\Exception(
                'Open mode are not supported; given %d. Only %s are supported.',
                0, array($this->getMode(), implode(',', $createModes)));

        preg_match('#^(\w+)://#', $streamName, $match);

        if((   (isset($match[1]) && $match[1] == 'file') || !isset($match[1]))
            && !file_exists($streamName)
            && parent::MODE_READ_WRITE == $this->getMode())
            throw new \Hoa\File\Exception\FileDoesNotExist(
                'File %s does not exist.', 0, $streamName);

        $out = parent::_open($streamName, $context);

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
     * @throw   \Hoa\File\Exception
     */
    public function read ( $length ) {

        if($length <= 0)
            throw new \Hoa\File\Exception(
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
    public function readArray ( $format = null ) {

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

            $this->seek(0, \Hoa\Stream\IStream\Pointable::SEEK_END);
            $end     = $this->tell();

            $this->seek(0, \Hoa\Stream\IStream\Pointable::SEEK_SET);
            $handle  = $this->read($end);

            $this->seek($current, \Hoa\Stream\IStream\Pointable::SEEK_SET);

            return $handle;
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
            0
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
     * @throw   \Hoa\File\Exception
     */
    public function write ( $string, $length ) {

        if($length < 0)
            throw new \Hoa\File\Exception(
                'Length must be greather than or equal to 0, given %d.',
                0, $length);

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
     * Write a boolean.
     *
     * @access  public
     * @param   bool    $boolean    Boolean.
     * @return  mixed
     */
    public function writeBoolean ( $boolean ) {

        return $this->write((string) (bool) $boolean, 1);
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

        $array = var_export($array, true);

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
            return $this->write($line . "\n", strlen($line) + 1);

        ++$n;

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
}

}
