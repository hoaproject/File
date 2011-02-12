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
 * Copyright (c) 2007, 2011 Ivan ENDERLIN. All rights reserved.
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
 */

namespace {

from('Hoa')

/**
 * \Hoa\File\Exception
 */
-> import('File.Exception.~')

/**
 * \Hoa\File\Exception\FileDoesNotExist
 */
-> import('File.Exception.FileDoesNotExist')

/**
 * \Hoa\File\Generic
 */
-> import('File.Generic')

/**
 * \Hoa\Stream\IStream\Bufferable
 */
-> import('Stream.I~.Bufferable')

/**
 * \Hoa\Stream\IStream\Lockable
 */
-> import('Stream.I~.Lockable')

/**
 * \Hoa\Stream\IStream\Pointable
 */
-> import('Stream.I~.Pointable');

}

namespace Hoa\File {

/**
 * Class \Hoa\File.
 *
 * File handler.
 *
 * @author     Ivan ENDERLIN <ivan.enderlin@hoa-project.net>
 * @copyright  Copyright (c) 2007, 2011 Ivan ENDERLIN.
 * @license    http://gnu.org/licenses/gpl.txt GNU GPL
 */

abstract class File
    extends    Generic
    implements \Hoa\Stream\IStream\Bufferable,
               \Hoa\Stream\IStream\Lockable,
               \Hoa\Stream\IStream\Pointable {

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
     * Open for reading and writing; place the file pointer at the end of the
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
     *                                 \Hoa\Stream\Context class).
     * @return  void
     * @throw   \Hoa\Stream\Exception
     */
    public function __construct ( $streamName, $mode, $context = null ) {

        $this->setMode($mode);
        parent::__construct($streamName, $context);

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

        if(   substr($streamName, 0, 4) == 'file'
           && false === is_dir(dirname($streamName)))
            throw new Exception(
                'Directory %s does not exist. Could not open file %s.',
                0, array(dirname($streamName), basename($streamName)));

        if(null === $context) {

            if(false === $out = @fopen($streamName, $this->getMode()))
                throw new Exception(
                    'Failed to open stream %s.', 1, $streamName);

            return $out;
        }

        $out = @fopen(
            $streamName,
            $this->getMode(),
            true,
            $context->getContext()
        );

        if(false === $out)
            throw new Exception(
                'Failed to open stream %s.', 2, $streamName);

        return $out;
    }

    /**
     * Close the current stream.
     *
     * @access  protected
     * @return  bool
     */
    protected function _close ( ) {

        return @fclose($this->getStream());
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
     *                                \Hoa\Stream\IStream\Lockable::LOCK_* constants.
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
     *                             \Hoa\Stream\IStream\Pointable::SEEK_* constants.
     * @return  int
     */
    public function seek ( $offset, $whence = \Hoa\Stream\IStream\Pointable::SEEK_SET ) {

        return fseek($this->getStream(), $offset, $whence);
    }

    /**
     * Get the current position of the stream pointer.
     *
     * @access  public
     * @return  int
     */
    public function tell ( ) {

        $stream = $this->getStream();

        if(null === $stream)
            return 0;

        return ftell($stream);
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

}
