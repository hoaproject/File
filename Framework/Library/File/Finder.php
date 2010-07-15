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
 * @subpackage  Hoa_File_Finder
 *
 */

/**
 * Hoa_Core
 */
require_once 'Core.php';

/**
 * Hoa_File_Exception
 */
import('File.Exception');

/**
 * Hoa_File_Exception_FileDoesNotExist
 */
import('File.Exception.FileDoesNotExist');

/**
 * Hoa_File_Undefined
 */
import('File.Undefined');

/**
 * Hoa_Iterator_Basic
 */
import('Iterator.Basic');

/**
 * Hoa_Iterator_Aggregate
 */
import('Iterator.Aggregate');

/**
 * Class Hoa_File_Finder.
 *
 * Propose a finder to scan directory. It returns a neutral (undefined) file
 * that should be infered to a real file object.
 *
 * @author      Ivan ENDERLIN <ivan.enderlin@hoa-project.net>
 * @copyright   Copyright (c) 2007, 2010 Ivan ENDERLIN.
 * @license     http://gnu.org/licenses/gpl.txt GNU GPL
 * @since       PHP 5
 * @version     0.1
 * @package     Hoa_File
 * @subpackage  Hoa_File_Finder
 */

class Hoa_File_Finder implements Hoa_Iterator_Aggregate,
                                 Countable {

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
     * @var Hoa_File_Finder resource
     */
    protected $_directory = null;

    /**
     * Current path.
     *
     * @var Hoa_File_Finder string
     */
    protected $_path      = null;

    /**
     * Iterator.
     *
     * @var Hoa_Iterator_Basic object
     */
    protected $_iterator  = null;

    /**
     * Type of list.
     *
     * @var Hoa_File_Finder int
     */
    protected $_list      = self::LIST_NATURAL;

    /**
     * Type of sort.
     *
     * @var Hoa_File_Finder int
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
     * @throw   Hoa_File_Exception
     */
    protected function &setDirectory ( $path ) {

        if(false === file_exists($path))
            throw new Hoa_File_Exception_FileDoesNotExist(
                'Path %s does not exist.', 0, $path);

        if(false === is_dir($path))
            throw new Hoa_File_Exception(
                'Path %s is not a directory.', 1, $path);

        if(false === $foo = @opendir($path))
            throw new Hoa_File_Exception(
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
     * @return  Hoa_Iterator_Basic
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
                $out[] = new Hoa_File_Undefined($this->getPath() . DS . $handle);
        }

        rewinddir($this->getDirectory());

        $out             = $this->sort($out);
        $old             = $this->_iterator;
        $this->_iterator = new Hoa_Iterator_Basic($out);

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
            throw new Hoa_File_Exception(
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
     * @return  Hoa_File_Finder
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
     * @return  Hoa_Iterator_Basic
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
