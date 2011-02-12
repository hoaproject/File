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
 * @copyright  Copyright (c) 2007, 2011 Ivan ENDERLIN.
 * @license    http://gnu.org/licenses/gpl.txt GNU GPL
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
