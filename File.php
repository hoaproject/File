<?php

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2007-2014, Ivan Enderlin. All rights reserved.
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
 * @author     Ivan Enderlin <ivan.enderlin@hoa-project.net>
 * @copyright  Copyright © 2007-2014 Ivan Enderlin.
 * @license    New BSD License
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
     * @param   string  $streamName    Stream name (or file descriptor).
     * @param   string  $mode          Open mode, see the self::MODE_*
     *                                 constants.
     * @param   string  $context       Context ID (please, see the
     *                                 \Hoa\Stream\Context class).
     * @param   bool    $wait          Differ opening or not.
     * @return  void
     * @throw   \Hoa\File\Exception
     */
    public function __construct ( $streamName, $mode, $context = null,
                                  $wait = false ) {

        $this->setMode($mode);

        switch($streamName) {

            case '0':
                $streamName = 'php://stdin';
              break;

            case '1':
                $streamName = 'php://stdout';
              break;

            case '2':
                $streamName = 'php://stderr';
              break;

            default:
                if(true === ctype_digit($streamName))
                    if(PHP_VERSION_ID >= 50306)
                        $streamName = 'php://fd/' . $streamName;
                    else
                        throw new Exception(
                            'You need PHP5.3.6 to use a file descriptor ' .
                            'other than 0, 1 or 2 (tried %d with PHP%s).',
                            0, array($streamName, PHP_VERSION));
        }

        parent::__construct($streamName, $context, $wait);

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

            if(false === $out = @fopen($streamName, $this->getMode(), true))
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
     * Start a new buffer.
     * The callable acts like a light filter.
     *
     * @access  public
     * @param   mixed   $callable    Callable.
     * @param   int     $size        Size.
     * @return  int
     */
    public function newBuffer ( $callable = null, $size = null ) {

        $this->setStreamBuffer($size);

        //@TODO manage $callable as a filter?

        return 1;
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
     * Delete buffer.
     *
     * @access  public
     * @return  bool
     */
    public function deleteBuffer ( ) {

        return $this->disableStreamBuffer();
    }

    /**
     * Get bufffer level.
     *
     * @access  public
     * @return  int
     */
    public function getBufferLevel ( ) {

        return 1;
    }

    /**
     * Get buffer size.
     *
     * @access  public
     * @return  int
     */
    public function getBufferSize ( ) {

        return $this->getStreamBufferSize();
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

namespace {

/**
 * Flex entity.
 */
Hoa\Core\Consistency::flexEntity('Hoa\File\File');

}
