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
 * Copyright (c) 2007, 2009 Ivan ENDERLIN. All rights reserved.
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
 * @subpackage  Hoa_File_Socket
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
 * Hoa_File_ReadWrite
 */
import('File.ReadWrite');

/**
 * Hoa_Socket_Connection_Client
 */
import('Socket.Connection.Client');

/**
 * Hoa_Socket_Unix
 */
import('Socket.Unix');

/**
 * Class Hoa_File_Socket.
 *
 * Socket handler.
 *
 * @author      Ivan ENDERLIN <ivan.enderlin@hoa-project.net>
 * @copyright   Copyright (c) 2007, 2009 Ivan ENDERLIN.
 * @license     http://gnu.org/licenses/gpl.txt GNU GPL
 * @since       PHP 5
 * @version     0.1
 * @package     Hoa_File
 * @subpackage  Hoa_File_Socket
 */

class Hoa_File_Socket extends Hoa_File_ReadWrite {

    /**
     * Wrapped socket.
     *
     * @var Hoa_Socket_Connection_Client object
     */
    private $_socket = null;



    /**
     * Open a Unix socket.
     *
     * @access  public
     * @param   string  $streamName    Stream name.
     * @param   int     $timeout       Timeout.
     * @param   int     $flag          Flag, see the
     *                                 Hoa_Socket_Connection_Client::*
     *                                 constants.
     * @param   string  $context       Context ID (please, see the
     *                                 Hoa_Stream_Context class).
     * @return  void
     * @throw   Hoa_File_Exception
     * @throw   Hoa_Stream_Exception
     */
    public function __construct ( $streamName,
                                  $timeout = 30,
                                  $flag    = Hoa_Socket_Connection_Client::CONNECT,
                                  $context = null ) {

        $this->_socket = new Hoa_Socket_Connection_Client(
            new Hoa_Socket_Unix($streamName, 'unix'),
            $timeout,
            $flag,
            $context
        );

        $this->_socket->connect();
    }

    /**
     * Get the wrapped socket.
     *
     * @access  private
     * @return  Hoa_Socket_Connection_Client
     */
    private function getSocket ( ) {

        return $this->_socket;
    }

    /**
     * Overload the getStreamName() method.
     * PHP does not have a multiple inheritance. So, we wrap the
     * Hoa_Socket_Connection_Client and redirect all calls to the
     * getStreamName() method to the wrapped socket. In this way, we benefit
     * from all Hoa_File methods and Hoa_Socket_Connection_Client methods.
     *
     * @access  protected
     * @return  string
     */
    public function getStreamName ( ) {

        return $this->getSocket()->getStreamName();
    }

    /**
     * Overload the getStream() method.
     * PHP does not have a multiple inheritance. So, we wrap the
     * Hoa_Socket_Connection_Client and redirect all calls to the
     * getStreamName() method to the wrapped socket. In this way, we benefit
     * from all Hoa_File methods and Hoa_Socket_Connection_Client methods.
     *
     * @access  protected
     * @return  resource
     */
    public function getStream ( ) {

        return $this->getSocket()->getStream();
    }

    /**
     * Overload the getStreamContext() method.
     * PHP does not have a multiple inheritance. So, we wrap the
     * Hoa_Socket_Connection_Client and redirect all calls to the
     * getStreamName() method to the wrapped socket. In this way, we benefit
     * from all Hoa_File methods and Hoa_Socket_Connection_Client methods.
     *
     * @access  protected
     * @return  Hoa_Stream_Context
     */
    public function getStreamContext ( ) {

        return $this->getSocket()->getStreamContext();
    }
}
