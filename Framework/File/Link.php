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
 * Copyright (c) 2007, 2008 Ivan ENDERLIN. All rights reserved.
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
 * @subpackage  Hoa_File_Link
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
 * Hoa_File
 */
import('File.~');

/**
 * Hoa_File_Directory
 */
import('File.Directory');

/**
 * Hoa_File_Link
 */
import('File.Link');

/**
 * Class Hoa_File_Link.
 *
 * Link handler.
 *
 * @author      Ivan ENDERLIN <ivan.enderlin@hoa-project.net>
 * @copyright   Copyright (c) 2007, 2008 Ivan ENDERLIN.
 * @license     http://gnu.org/licenses/gpl.txt GNU GPL
 * @since       PHP 5
 * @version     0.1
 * @package     Hoa_File
 * @subpackage  Hoa_File_Link
 */

class Hoa_File_Link extends Hoa_File {

    /**
     * Open a link.
     *
     * @access  public
     * @param   string  $streamName    Stream name.
     * @param   string  $mode          Open mode, see the parent::MODE_*
     *                                 constants.
     * @param   string  $context       Context ID (please, see the
     *                                 Hoa_Stream_Context class).
     * @return  void
     * @throw   Hoa_File_Exception
     * @throw   Hoa_Stream_Exception
     */
    public function __construct ( $streamName, $mode, $context = null ) {

        if(!is_link($streamName))
            throw new Hoa_File_Exception(
                'File %s is not a link.', 0, $streamName);

        parent::__construct($streamName, $mode, $context);
    }

    /**
     * Get informations about a link.
     *
     * @access  public
     * @return  array
     */
    public function getStatistic ( ) {

        return lstat($this->getStreamName());
    }

    /**
     * Change file group.
     *
     * @access  public
     * @param   mixed   $group    Group name or number.
     * @return  bool
     */
    public function changeGroup ( $group ) {

        return lchgrp($this->getStreamName(), $group);
    }

    /**
     * Change file owner.
     *
     * @access  public
     * @param   mixed   $user   User.
     * @return  bool
     */
    public function changeOwner ( $user ) {

        return lchown($this->getStreamName(), $user);
    }

    /**
     * Get file permissions.
     *
     * @access  public
     * @return  int
     */
    public function getPermissions ( ) {

        return 41453; // i.e. lrwxr-xr-x
    }

    /**
     * Get the target of a symbolic link.
     *
     * @access  public
     * @return  Hoa_File_Abstract
     * @throw   Hoa_File_Exception
     */
    public function getTarget ( ) {

        $target  = dirname($this->getStreamName()) . DS .
                   $this->getTargetName();
        $context = null !== $this->getStreamContext()
                       ? $this->getStreamContext()->getCurrentId()
                       : null;

        switch(filetype($target)) {

            case 'link':
                return new Hoa_File_Link($target, Hoa_File::MODE_READ, $context);
              break;

            case 'file':
                return new Hoa_File($target, Hoa_File::MODE_READ, $context);
              break;

            case 'dir':
                return new Hoa_File_Directory($target, Hoa_File::MODE_READ, $context);
              break;

            default:
                throw new Hoa_File_Exception(
                    'Cannot find an appropriated object that matches with ' .
                    'the symbolic link target %s.', 1, $target);
        }
    }

    /**
     * Get the target name of a symbolic link.
     *
     * @access  public
     * @return  string
     */
    public function getTargetName ( ) {

        return readlink($this->getStreamName());
    }

    /**
     * Create a link.
     *
     * @access  public
     * @param   string  $name      Link name.
     * @param   string  $target    Target name.
     * @return  bool
     */
    public static function create ( $name, $target ) {

        if(false != linkinfo($name))
            return true;

        return symlink($target, $name);
    }
}
