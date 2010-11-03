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
 * @subpackage  Hoa_File_Undefined
 *
 */

/**
 * Hoa_Core
 */
require_once 'Core.php';

/**
 * Hoa_File_Abstract
 */
import('File.Abstract');

/**
 * Hoa_File
 */
import('File.~');

/**
 * Hoa_File_ReadWrite
 */
import('File.ReadWrite');

/**
 * Hoa_File_Link_ReadWrite
 */
import('File.Link.ReadWrite');

/**
 * Hoa_File_Directory
 */
import('File.Directory');

/**
 * Hoa_File_Socket
 */
import('File.Socket');

/**
 * Class Hoa_File_Undefined.
 *
 * Undefined file handler, i.e. accede to all abstract (super) file method even
 * if the file type is unknown.
 *
 * @author      Ivan ENDERLIN <ivan.enderlin@hoa-project.net>
 * @copyright   Copyright (c) 2007, 2010 Ivan ENDERLIN.
 * @license     http://gnu.org/licenses/gpl.txt GNU GPL
 * @since       PHP 5
 * @version     0.1
 * @package     Hoa_File
 * @subpackage  Hoa_File_Undefined
 */

class Hoa_File_Undefined extends Hoa_File_Abstract {

    /**
     * Open a file.
     *
     * @access  public
     * @param   string  $streamName    Stream name.
     * @param   string  $context       Context ID (please, see the
     *                                 Hoa_Stream_Context class).
     * @return  void
     * @throw   Hoa_Stream_Exception
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
     * @param   string              $streamName    Stream name (e.g. path or URL).
     * @param   Hoa_Stream_Context  $context       Context.
     * @return  resource
     * @throw   Hoa_File_Exception_FileNotExists
     */
    protected function &_open ( $streamName, Hoa_Stream_Context $context = null ) {

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
     * path is a file, return a Hoa_File.
     *
     * @access  public
     * @param   string  $path       Defining with another path.
     * @param   string  $context    Context ID (please, see the  
     *                              Hoa_Stream_Context class).
     * @return  Hoa_File_Abstract
     * @throw   Hoa_File_Exception
     */
    public function define ( $path = null, $context = null ) {

        if(null === $path)
            $path = $this->getStreamName();

        if(   null === $context
           && null !== $this->getStreamContext())
            $context = $this->getStreamContext()->getCurrentId();

        if(true === $this->isLink())
            return new Hoa_File_Link_ReadWrite(
                $path,
                Hoa_File::MODE_APPEND_READ_WRITE,
                $context
            );

        elseif(true === $this->isFile())
            return new Hoa_File_ReadWrite(
                $path,
                Hoa_File::MODE_APPEND_READ_WRITE,
                $context
            );

        elseif(true === $this->isDirectory())
            return new Hoa_File_Directory($path, Hoa_File::MODE_READ, $context);

        elseif(true === $this->isSocket())
            return new Hoa_File_Socket(
                $path,
                30,
                Hoa_Socket_Connection_Client::CONNECT,
                $context
            );

        throw new Hoa_File_Exception(
            'Cannot find an appropriated object that matches with ' .
            'path %s when defining it.', 0, $path);
    }
}
