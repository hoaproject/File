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
 * \Hoa\File\Undefined
 */
-> import('File.Undefined')

/**
 * \Hoa\Iterator\Basic
 */
-> import('Iterator.Basic')

/**
 * \Hoa\Iterator\Aggregate
 */
-> import('Iterator.Aggregate');

}

namespace Hoa\File {

/**
 * Class \Hoa\File\Finder.
 *
 * Propose a finder to scan directory. It returns a neutral (undefined) file
 * that should be infered to a real file object.
 *
 * @author      Ivan ENDERLIN <ivan.enderlin@hoa-project.net>
 * @copyright   Copyright (c) 2007, 2011 Ivan ENDERLIN.
 * @license     New BSD License
 */

class Finder implements \Hoa\Iterator\Aggregate, \Countable {

    /**
     * List visibles files.
     *
     * @const int
     */
    const LIST_VISIBLE     =    1;

    /**
     * List hidden files.
     *
     * @const int
     */
    const LIST_HIDDEN      =    2;

    /**
     * List file.
     *
     * @const int
     */
    const LIST_FILE        =    4;

    /**
     * List directory.
     *
     * @const int
     */
    const LIST_DIRECTORY   =    8;

    /**
     * List link.
     *
     * @const int
     */
    const LIST_LINK        =   16;

    /**
     * List with no dots.
     *
     * @const int
     */
    const LIST_NO_DOT      =   32;

    /**
     * List dots (self and parent).
     *
     * @const int
     */
    const LIST_DOT         =   64;

    /**
     * List all.
     *
     * @const int
     */
    const LIST_ALL         =    3; // LIST_VISIBLE | LIST_HIDDEN.

    /**
     * List natural.
     *
     * @const int
     */
    const LIST_NATURAL     =   33;

    /**
     * Sort by last access time.
     *
     * @const int
     */
    const SORT_ATIME       =    1;

    /**
     * Sort by inode change time.
     *
     * @const int
     */
    const SORT_CTIME       =    2;

    /**
     * Sort by modification time.
     *
     * @const int
     */
    const SORT_MTIME       =    4;

    /**
     * Sort by group.
     *
     * @const int
     */
    const SORT_GROUP       =    8;

    /**
     * Sort by owner.
     *
     * @const int
     */
    const SORT_OWNER       =   16;

    /**
     * Sort by permisions.
     *
     * @const int
     */
    const SORT_PERMISSIONS =   32;

    /**
     * Sort by name.
     *
     * @const int
     */
    const SORT_NAME        =   64;

    /**
     * Sort by name with an insensitive case.
     *
     * @const int
     */
    const SORT_INAME       =  128;

    /**
     * Reverse the sort.
     *
     * @const int
     */
    const SORT_REVERSE     =  256;

    /**
     * Random sort.
     *
     * @const int
     */
    const SORT_RANDOM      =  512;

    /**
     * No sort.
     *
     * @const int
     */
    const SORT_NONE        = 1024;

    /**
     * Resource to directory.
     *
     * @var \Hoa\File\Finder resource
     */
    protected $_directory = null;

    /**
     * Current path.
     *
     * @var \Hoa\File\Finder string
     */
    protected $_path      = null;

    /**
     * Iterator.
     *
     * @var \Hoa\Iterator\Basic object
     */
    protected $_iterator  = null;

    /**
     * Type of list.
     *
     * @var \Hoa\File\Finder int
     */
    protected $_list      = self::LIST_NATURAL;

    /**
     * Type of sort.
     *
     * @var \Hoa\File\Finder int
     */
    protected $_sort      = self::SORT_INAME;



    /**
     * Open a directory and initialize the iterator.
     *
     * @access  public
     * @param   string  $path    Path to directory.
     * @param   int     $list    Type of files to list, use the self::LIST_*
     *                           constants.
     * @param   int     $sort    Type of sort, use the self::SORT_* constants.
     * @return  void
     */
    public function __construct ( $path, $list = self::LIST_NATURAL,
                                  $sort = self::SORT_INAME ) {

        $this->setList($list);
        $this->setSort($sort);
        $this->setDirectory($path);
        $this->setIterator();

        return;
    }

    /**
     * Set the current directory resource.
     *
     * @access  protected
     * @param   string     $path    Path to directory.
     * @return  resource
     * @throw   \Hoa\File\Exception
     */
    protected function &setDirectory ( $path ) {

        if(false === file_exists($path))
            throw new Exception\FileDoesNotExist(
                'Path %s does not exist.', 0, $path);

        if(false === is_dir($path))
            throw new Exception(
                'Path %s is not a directory.', 1, $path);

        if(false === $foo = @opendir($path))
            throw new Exception(
                'Directory %s cannot be opened.', 2, $path);

        if(substr($path, -1) == DS)
            $path = substr($path, 0, -1);

        $old              = $this->_directory;
        $this->_path      = $path;
        $this->_directory = &$foo;

        return $old;
    }

    /**
     * Set the directory iterator.
     *
     * @access  protected
     * @return  \Hoa\Iterator\Basic
     */
    protected function setIterator ( ) {

        $out      = array();
        $list     = $this->getList();
        $complete = null;
        $dot      = null;

        while(false !== $handle = readdir($this->getDirectory())) {

            $dot      = $handle == '.' || $handle == '..';
            $visible  = $handle[0] != '.';
            $complete = $this->getPath() . DS . $handle;

            if($list & self::LIST_NO_DOT && $dot)
                continue;

            if(   false
               || ($list & self::LIST_FILE      &&  is_file($complete)
                                                && !is_link($complete)
                                                &&  $visible)
               || ($list & self::LIST_DIRECTORY &&  is_dir($complete)
                                                && !is_link($complete)
                                                &&  $visible)
               || ($list & self::LIST_LINK      &&  is_link($complete)
                                                &&  $visible)
               || ($list & self::LIST_DOT       &&  $dot)
               || ($list & self::LIST_VISIBLE   &&  $visible)
               || ($list & self::LIST_HIDDEN    && !$visible))
                $out[] = new Undefined($this->getPath() . DS . $handle);
        }

        rewinddir($this->getDirectory());

        $out             = $this->sort($out);
        $old             = $this->_iterator;
        $this->_iterator = new \Hoa\Iterator\Basic($out);

        return $old;
    }

    /**
     * Sort the result of the scan.
     *
     * @access  protected
     * @param   array      $data    Data to sort.
     * @return  array
     */
    protected function sort ( Array $data ) {

        static $sortFlags = array(
            self::SORT_ATIME       => SORT_NUMERIC,
            self::SORT_CTIME       => SORT_NUMERIC,
            self::SORT_MTIME       => SORT_NUMERIC,
            self::SORT_GROUP       => SORT_STRING,
            self::SORT_OWNER       => SORT_NUMERIC,
            self::SORT_PERMISSIONS => SORT_NUMERIC,
            self::SORT_NAME        => SORT_STRING,
            self::SORT_INAME       => SORT_STRING
        );

        $sort = $this->getSort();
        $r    = array();

        if($sort == self::SORT_NONE)
            return $data;

        if($sort & self::SORT_RANDOM) {

            shuffle($data);
            return $data;
        }

        if($sort & self::SORT_ATIME)
            foreach($data as $i => $entry)
                $r[] = $entry->getATime();

        elseif($sort & self::SORT_CTIME)
            foreach($data as $i => $entry)
                $r[] = $entry->getCTime();

        elseif($sort & self::SORT_MTIME)
            foreach($data as $i => $entry)
                $r[] = $entry->getMTime();

        elseif($sort & self::SORT_GROUP)
            foreach($data as $i => $entry)
                $r[] = $entry->getGroup();

        elseif($sort & self::SORT_OWNER)
            foreach($data as $i => $entry)
                $r[] = $entry->getOwner();

        elseif($sort & self::SORT_PERMISSIONS)
            foreach($data as $i => $entry)
                $r[] = $entry->getPermissions();

        elseif($sort & self::SORT_NAME)
            foreach($data as $i => $entry)
                $r[] = $entry->__toString();

        elseif($sort & self::SORT_INAME)
            foreach($data as $i => $entry)
                $r[] = strtolower($entry->__toString());

        if(!isset($sortFlags[$sort & ~self::SORT_REVERSE]))
            throw new Exception(
                'Constants sort combination is not supported, ' .
                'excepted for reversing the sort. ' .
                'Please, look the %s::SORT_* constants.', 0, __CLASS__);
        else
            asort($r, $sortFlags[$sort & ~self::SORT_REVERSE]);

        $result = array();

        foreach($r as $i => $foo)
            $result[] = $data[$i];

        if($sort & self::SORT_REVERSE)
            return array_reverse($result, false);

        return $result;
    }

    /**
     * Set the type of list.
     *
     * @access  protected
     * @param   int        $list    Type of list.
     * @return  int
     */
    protected function setList ( $list ) {

        $old         = $this->_list;
        $this->_list = $list;

        return $old;
    }

    /**
     * Set the type of sort.
     *
     * @access  protected
     * @param   int        $sort    Type of sort.
     * @return  int
     */
    protected function setSort ( $sort ) {

        $old         = $this->_sort;
        $this->_sort = $sort;

        return $old;
    }

    /**
     * Change directory, i.e. return a new finder iterator.
     *
     * @access  public
     * @param   string  $path    Path. If null is given, use the current path of
     *                           the iterator.
     * @return  \Hoa\File\Finder
     */
    public function changeDirectory ( $path = null ) {

        if(null === $path)
            $path = $this->getIterator()->current();

        return new self($path, $this->getList(), $this->getSort());
    }

    /**
     * Count number of elements in collection.
     *
     * @access  public
     * @return  int
     */
    public function count ( ) {

        return count($this->getIterator());
    }

    /**
     * Get the current directory resource.
     *
     * @access  protected
     * @return  resource
     */
    protected function &getDirectory ( ) {

        return $this->_directory;
    }

    /**
     * Get the directory iterator.
     *
     * @access  public
     * @return  \Hoa\Iterator\Basic
     */
    public function getIterator ( ) {

        return $this->_iterator;
    }

    /**
     * Get the type of file.
     *
     * @æccess  public
     * @return  int
     */
    public function getList ( ) {

        return $this->_list;
    }

    /**
     * Get the type of sort.
     *
     * @æccess  public
     * @return  int
     */
    public function getSort ( ) {

        return $this->_sort;
    }

    /**
     * Get the current path.
     *
     * @access  public
     * @return  string
     */
    public function getPath ( ) {

        return $this->_path;
    }

    /**
     * Close the current directory resource.
     *
     * @access  public
     * @return  void
     */
    public function close ( ) {

        @closedir($this->getDirectory());

        return;
    }

    /**
     * Close the current directory resource when destroy the object.
     *
     * @access  public
     * @return  void
     */
    public function __destruct ( ) {

        $this->close();

        return;
    }
}

}
