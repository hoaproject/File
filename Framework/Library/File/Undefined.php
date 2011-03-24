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
 * \Hoa\File\Generic
 */
-> import('File.Generic')

/**
 * \Hoa\File
 */
-> import('File.~')

/**
 * \Hoa\File\ReadWrite
 */
-> import('File.ReadWrite')

/**
 * \Hoa\File\Link\ReadWrite
 */
-> import('File.Link.ReadWrite')

/**
 * \Hoa\File\Directory
 */
-> import('File.Directory')

/**
 * \Hoa\File\Socket
 */
-> import('File.Socket');

}

namespace Hoa\File {

/**
 * Class \Hoa\File\Undefined.
 *
 * Undefined file handler, i.e. accede to all abstract (super) file method even
 * if the file type is unknown.
 *
 * @author     Ivan ENDERLIN <ivan.enderlin@hoa-project.net>
 * @copyright  Copyright (c) 2007-2011 Ivan ENDERLIN.
 * @license    New BSD License
 */

class Undefined extends Generic {

    /**
     * Open a file.
     *
     * @access  public
     * @param   string  $streamName    Stream name.
     * @param   string  $context       Context ID (please, see the
     *                                 \Hoa\Stream\Context class).
     * @return  void
     * @throw   \Hoa\Stream\Exception
     */
    public function __construct ( $streamName, $context = null ) {

        parent::__construct($streamName, $context);

        return;
    }

    /**
     * Open the stream and return the associated resource.
     * It's a fake implementation to be conform with the parent abstract class,
     * but this class just allows us to instance parent class.
     *
     * @access  protected
     * @param   string               $streamName    Stream name (e.g. path or URL).
     * @param   \Hoa\Stream\Context  $context       Context.
     * @return  void
     */
    protected function &_open ( $streamName, \Hoa\Stream\Context $context = null ) {

        $dummy = null;

        return $dummy;
    }

    /**
     * Close the current stream.
     *
     * @access  protected
     * @return  bool
     */
    protected function _close ( ) {

        return null;
    }

    /**
     * Find an appropriated object that matches with a specific path, e.g. if the
     * path is a file, return a \Hoa\File.
     *
     * @access  public
     * @param   string  $path       Defining with another path.
     * @param   string  $context    Context ID (please, see the  
     *                              \Hoa\Stream\Context class).
     * @return  \Hoa\File\Generic
     * @throw   \Hoa\File\Exception
     */
    public function define ( $path = null, $context = null ) {

        if(null === $path)
            $path = $this->getStreamName();

        if(   null === $context
           && null !== $this->getStreamContext())
            $context = $this->getStreamContext()->getCurrentId();

        if(true === $this->isLink())
            return new Link\ReadWrite(
                $path,
                File::MODE_APPEND_READ_WRITE,
                $context
            );

        elseif(true === $this->isFile())
            return new ReadWrite(
                $path,
                File::MODE_APPEND_READ_WRITE,
                $context
            );

        elseif(true === $this->isDirectory())
            return new Directory($path, File::MODE_READ, $context);

        elseif(true === $this->isSocket())
            return new Socket(
                $path,
                30,
                \Hoa\Socket\Connection\Client::CONNECT,
                $context
            );

        throw new Exception(
            'Cannot find an appropriated object that matches with ' .
            'path %s when defining it.', 0, $path);
    }
}

}
