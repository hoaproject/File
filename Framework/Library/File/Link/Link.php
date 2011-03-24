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
 * @copyright  Copyright © 2007-2011 Ivan ENDERLIN.
 * @license    New BSD License
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
