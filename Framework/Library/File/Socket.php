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
 * \Hoa\File\ReadWrite
 */
-> import('File.ReadWrite')

/**
 * \Hoa\Socket\Connection\Client
 */
-> import('Socket.Connection.Client')

/**
 * \Hoa\Socket\Unix
 */
-> import('Socket.Unix');

}

namespace Hoa\File {

/**
 * Class \Hoa\File\Socket.
 *
 * Socket handler.
 *
 * @author     Ivan Enderlin <ivan.enderlin@hoa-project.net>
 * @copyright  Copyright © 2007-2011 Ivan Enderlin.
 * @license    New BSD License
 */

class Socket extends ReadWrite {

    /**
     * Wrapped socket.
     *
     * @var \Hoa\Socket\Connection\Client object
     */
    private $_socket = null;



    /**
     * Open a Unix socket.
     *
     * @access  public
     * @param   string  $streamName    Stream name.
     * @param   int     $timeout       Timeout.
     * @param   int     $flag          Flag, see the
     *                                 \Hoa\Socket\Connection\Client::*
     *                                 constants.
     * @param   string  $context       Context ID (please, see the
     *                                 \Hoa\Stream\Context class).
     * @return  void
     * @throw   \Hoa\File\Exception
     * @throw   \Hoa\Stream\Exception
     */
    public function __construct ( $streamName,
                                  $timeout = 30,
                                  $flag    = \Hoa\Socket\Connection\Client::CONNECT,
                                  $context = null ) {

        $this->_socket = new \Hoa\Socket\Connection\Client(
            new \Hoa\Socket\Unix($streamName, 'unix'),
            $timeout,
            $flag,
            $context
        );
        $this->_socket->connect();

        return;
    }

    /**
     * Get the wrapped socket.
     *
     * @access  private
     * @return  \Hoa\Socket\Connection\Client
     */
    private function getSocket ( ) {

        return $this->_socket;
    }

    /**
     * Override the getStreamName() method.
     * PHP does not have a multiple inheritance. So, we wrap the
     * \Hoa\Socket\Connection\Client and redirect all calls to the
     * getStreamName() method to the wrapped socket. In this way, we benefit
     * from all \Hoa\File methods and \Hoa\Socket\Connection\Client methods.
     *
     * @access  protected
     * @return  string
     */
    public function getStreamName ( ) {

        return $this->getSocket()->getStreamName();
    }

    /**
     * Override the getStream() method.
     * PHP does not have a multiple inheritance. So, we wrap the
     * \Hoa\Socket\Connection\Client and redirect all calls to the
     * getStreamName() method to the wrapped socket. In this way, we benefit
     * from all \Hoa\File methods and \Hoa\Socket\Connection\Client methods.
     *
     * @access  protected
     * @return  resource
     */
    public function getStream ( ) {

        return $this->getSocket()->getStream();
    }

    /**
     * Override the getStreamContext() method.
     * PHP does not have a multiple inheritance. So, we wrap the
     * \Hoa\Socket\Connection\Client and redirect all calls to the
     * getStreamName() method to the wrapped socket. In this way, we benefit
     * from all \Hoa\File methods and \Hoa\Socket\Connection\Client methods.
     *
     * @access  protected
     * @return  \Hoa\Stream\Context
     */
    public function getStreamContext ( ) {

        return $this->getSocket()->getStreamContext();
    }
}

}
