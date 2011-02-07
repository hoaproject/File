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
 * @author     Ivan ENDERLIN <ivan.enderlin@hoa-project.net>
 * @copyright  Copyright (c) 2007, 2010 Ivan ENDERLIN.
 * @license    http://gnu.org/licenses/gpl.txt GNU GPL
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
