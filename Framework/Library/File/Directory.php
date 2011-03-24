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
 * \Hoa\File\Exception\FileDoesNotExist
 */
-> import('File.Exception.FileDoesNotExist')

/**
 * \Hoa\File\Generic
 */
-> import('File.Generic')

/**
 * \Hoa\File\Finder
 */
-> import('File.Finder')

/**
 * \Hoa\Stream\Exception
 */
-> import('Stream.Exception')

/**
 * \Hoa\Stream\Context
 */
-> import('Stream.Context');

}

namespace Hoa\File {

/**
 * Class \Hoa\File\Directory.
 *
 * Directory handler.
 *
 * @author     Ivan ENDERLIN <ivan.enderlin@hoa-project.net>
 * @copyright  Copyright (c) 2007, 2011 Ivan ENDERLIN.
 * @license    New BSD License
 */

class Directory extends Generic {

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
     *                                 \Hoa\Stream\Context class).
     * @return  void
     * @throw   \Hoa\Stream\Exception
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
     * @param   string               $streamName    Stream name (e.g. path or URL).
     * @param   \Hoa\Stream\Context  $context       Context.
     * @return  resource
     * @throw   \Hoa\File\Exception\FileDoesNotExist
     * @throw   \Hoa\File\Exception
     */
    protected function &_open ( $streamName, \Hoa\Stream\Context $context = null ) {

        if(false === is_dir($streamName))
            if($this->getMode() == self::MODE_READ)
                throw new Exception\FileDoesNotExist(
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
                throw new Exception(
                    'Failed to open stream %s.', 1, $streamName);

            return $out;
        }

        if(false === $out = @fopen($streamName, 'r', $context->getContext()))
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

        return fclose($this->getStream());
    }

    /**
     * Copy file.
     * Return the destination directory path if succeed, false otherwise.
     *
     * @access  public
     * @param   string  $to       Destination path.
     * @param   bool    $force    Force to copy if the file $to already exists.
     *                            Use the \Hoa\Stream\IStream\Touchable::*OVERWRITE
     *                            constants.
     * @return  bool
     * @throw   \Hoa\File\Exception
     */
    public function copy ( $to, $force = \Hoa\Stream\IStream\Touchable::DO_NOT_OVERWRITE ) {

        $from   = $this->getStreamName();
        $finder = new Finder(
            $from,
            Finder::LIST_ALL |
            Finder::LIST_NO_DOT
        );

        self::create($to, self::MODE_CREATE_RECURSIVE);

        foreach($finder as $key => $file) {

            if(   $force === \Hoa\Stream\IStream\Touchable::DO_NOT_OVERWRITE
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
        $finder = new Finder(
            $from,
            Finder::LIST_ALL |
            Finder::LIST_NO_DOT
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
     *                              \Hoa\Stream\Context class).
     * @return  bool
     * @throw   \Hoa\Stream\Exception
     */
    public static function create ( $name, $mode = self::MODE_CREATE_RECURSIVE,
                                    $context = null ) {

        if(true === is_dir($name))
            return true;

        if(empty($name))
            return false;

        if(null !== $context)
            if(false === \Hoa\Stream\Context::contextExists($context))
                throw new \Hoa\Stream\Exception(
                    'Context %s was not previously declared, cannot retrieve ' .
                    'this context.', 3, $context);
            else
                $context = \Hoa\Stream\Context::getInstance($context);

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

}
