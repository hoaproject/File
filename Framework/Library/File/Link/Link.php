<?php

/**
 * Hoa
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
 * \Hoa\File
 */
-> import('File.~')

/**
 * \Hoa\File\Undefined
 */
-> import('File.Undefined');

}

namespace Hoa\File\Link {

/**
 * Class \Hoa\File\Link.
 *
 * Link handler.
 *
 * @author     Ivan ENDERLIN <ivan.enderlin@hoa-project.net>
 * @copyright  Copyright (c) 2007, 2011 Ivan ENDERLIN.
 * @license    http://gnu.org/licenses/gpl.txt GNU GPL
 */

class Link extends \Hoa\File {

    /**
     * Open a link.
     *
     * @access  public
     * @param   string  $streamName    Stream name.
     * @param   string  $mode          Open mode, see the parent::MODE_*
     *                                 constants.
     * @param   string  $context       Context ID (please, see the
     *                                 \Hoa\Stream\Context class).
     * @return  void
     * @throw   \Hoa\File\Exception
     * @throw   \Hoa\Stream\Exception
     */
    public function __construct ( $streamName, $mode, $context = null ) {

        if(!is_link($streamName))
            throw new \Hoa\File\Exception(
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
     * @return  \Hoa\File\Abstract
     * @throw   \Hoa\File\Exception
     */
    public function getTarget ( ) {

        $target    = dirname($this->getStreamName()) . DS .
                     $this->getTargetName();
        $context   = null !== $this->getStreamContext()
                         ? $this->getStreamContext()->getCurrentId()
                         : null;
        $undefined = new \Hoa\File\Undefined($target, $context);
        $defined   = $undefined->define();

        unset($undefined);

        return $defined;
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

}
