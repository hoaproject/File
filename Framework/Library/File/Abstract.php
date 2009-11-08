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
 * @subpackage  Hoa_File_Abstract
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
 * Hoa_Stream
 */
import('Stream.~');

/**
 * Hoa_Stream_Io_Pathable
 */
import('Stream.Io.Pathable');

/**
 * Hoa_Stream_Io_Statable
 */
import('Stream.Io.Statable');

/**
 * Hoa_Stream_Io_Touchable
 */
import('Stream.Io.Touchable');

/**
 * Hoa_File_Abstract
 */
import('File.Abstract');

/**
 * Hoa_File_Directory
 */
import('File.Directory');

/**
 * Class Hoa_File_Abstract.
 *
 * Describe a super-file.
 *
 * @author      Ivan ENDERLIN <ivan.enderlin@hoa-project.net>
 * @copyright   Copyright (c) 2007, 2008 Ivan ENDERLIN.
 * @license     http://gnu.org/licenses/gpl.txt GNU GPL
 * @since       PHP 5
 * @version     0.3
 * @package     Hoa_File
 * @subpackage  Hoa_File_Abstract
 */

abstract class Hoa_File_Abstract
    extends    Hoa_Stream
    implements Hoa_Stream_Io_Pathable,
               Hoa_Stream_Io_Statable,
               Hoa_Stream_Io_Touchable {

    /**
     * Mode.
     *
     * @var Hoa_File string
     */
    protected $_mode = null;



    /**
     * Get filename component of path.
     *
     * @access  public
     * @return  string
     */
    public function getBasename ( ) {

        return basename($this->getStreamName());
    }

    /**
     * Get directory name component of path.
     *
     * @access  public
     * @return  string
     */
    public function getDirname ( ) {

        return dirname($this->getStreamName());
    }

    /**
     * Get size.
     *
     * @access  public
     * @return  int
     */
    public function getSize ( ) {

        return filesize($this->getStreamName());
    }

    /**
     * Get informations about a file.
     *
     * @access  public
     * @return  array
     */
    public function getStatistic ( ) {

        return stat($this->getStreamName());
    }

    /**
     * Get last access time of file.
     *
     * @access  public
     * @return  int
     */
    public function getATime ( ) {

        return fileatime($this->getStreamName());
    }

    /**
     * Get inode change time of file.
     *
     * @access  public
     * @return  int
     */
    public function getCTime ( ) {

        return filectime($this->getStreamName());
    }

    /**
     * Get file modification time.
     *
     * @access  public
     * @return  int
     */
    public function getMTime ( ) {

        return filemtime($this->getStreamName());
    }

    /**
     * Get file group.
     *
     * @access  public
     * @return  int
     */
    public function getGroup ( ) {

        return filegroup($this->getStreamName());
    }

    /**
     * Get file owner.
     *
     * @access  public
     * @return  int
     */
    public function getOwner ( ) {

        return fileowner($this->getStreamName());
    }

    /**
     * Get file permissions.
     *
     * @access  public
     * @return  int
     */
    public function getPermissions ( ) {

        return fileperms($this->getStreamName());
    }

    /**
     * Get file permissions as a string.
     * Result sould be interpreted like this:
     *     * s: socket;
     *     * l: symbolic link;
     *     * -: regular;
     *     * b: block special;
     *     * d: directory;
     *     * c: character special;
     *     * p: FIFO pipe;
     *     * u: unknown.
     *
     * @access  public
     * @return  string
     */
    public function getReadablePermissions ( ) {

        $p = $this->getPermissions();

        if(($p & 0xC000) == 0xC000)
            $out = 's';
        elseif(($p & 0xA000) == 0xA000)
            $out = 'l';
        elseif(($p & 0x8000) == 0x8000)
            $out = '-';
        elseif(($p & 0x6000) == 0x6000)
            $out = 'b';
        elseif(($p & 0x4000) == 0x4000)
            $out = 'd';
        elseif(($p & 0x2000) == 0x2000)
            $out = 'c';
        elseif(($p & 0x1000) == 0x1000)
            $out = 'p';
        else
            $out = 'u';

        $out .= (($p & 0x0100) ? 'r' : '-')  .
                (($p & 0x0080) ? 'w' : '-')  .
                (($p & 0x0040) ?
                (($p & 0x0800) ? 's' : 'x')  :
                (($p & 0x0800) ? 'S' : '-')) .
                (($p & 0x0020) ? 'r' : '-')  .
                (($p & 0x0010) ? 'w' : '-')  .
                (($p & 0x0008) ?
                (($p & 0x0400) ? 's' : 'x')  :
                (($p & 0x0400) ? 'S' : '-')) .
                (($p & 0x0004) ? 'r' : '-')  .
                (($p & 0x0002) ? 'w' : '-')  .
                (($p & 0x0001) ?
                (($p & 0x0200) ? 't' : 'x')  :
                (($p & 0x0200) ? 'T' : '-'));

        return $out;
    }

    /**
     * Check if the file is readable.
     *
     * @access  public
     * @return  bool
     */
    public function isReadable ( ) {

        return is_readable($this->getStreamName());
    }

    /**
     * Check if the file is writable.
     *
     * @access  public
     * @return  bool
     */
    public function isWritable ( ) {

        return is_writable($this->getStreamName());
    }

    /**
     * Check if the file is executable.
     *
     * @access  public
     * @return  bool
     */
    public function isExecutable ( ) {

        return is_executable($this->getStreamName());
    }

    /**
     * Clear file status cache.
     *
     * @access  public
     * @return  void
     */
    public function clearStatisticCache ( ) {

        if(PHP_VERSION_ID >= 50300)
            clearstatcache(true, $this->getStreamName());
        else
            clearstatcache();

        return;
    }

    /**
     * Clear all files status cache.
     *
     * @access  public
     * @return  void
     */
    public static function clearAllStatisticCaches ( ) {

        clearstatcache();

        return;
    }

    /**
     * Set access and modification time of file.
     *
     * @access  public
     * @param   int     $time     Time. If equals to -1, time() should be used.
     * @param   int     $atime    Access time. If equals to -1, $time should be
     *                            used.
     * @return  bool
     */
    public function touch ( $time = -1, $atime = -1 ) {

        if($time == -1)
            $time  = time();

        if($atime == -1)
            $atime = $time;

        return touch($this->getStreamName(), $time, $atime);
    }

    /**
     * Copy file.
     * Return the destination file path if succeed, false otherwise.
     *
     * @access  public
     * @param   string  $to       Destination path.
     * @param   bool    $force    Force to copy if the file $to already exists.
     *                            Use the Hoa_Stream_Io_Touchable::*OVERWRITE
     *                            constants.
     * @return  bool
     */
    public function copy ( $to, $force = Hoa_Stream_Io_Touchable::DO_NOT_OVERWRITE ) {

        $from = $this->getStreamName();

        if(   $force === Hoa_Stream_Io_Touchable::DO_NOT_OVERWRITE
           && true   === file_exists($to))
            return true;

        if(null === $this->getStreamContext())
            return @copy($from, $to);

        return @copy($from, $to, $this->getStreamContext()->getContext());
    }

    /**
     * Move a file.
     *
     * @access  public
     * @param   string  $name     New name.
     * @param   bool    $force    Force to move if the file $name already
     *                            exists.
     *                            Use the Hoa_Stream_Io_Touchable::*OVERWRITE
     *                            constants.
     * @param   bool    $mkdir    Force to make directory if does not exist.
     *                            Use the Hoa_Stream_Io_Touchable::*DIRECTORY
     *                            constants.
     * @return  bool
     */
    public function move ( $name, $force = Hoa_Stream_Io_Touchable::DO_NOT_OVERWRITE,
                           $mkdir = Hoa_Stream_Io_Touchable::DO_NOT_MAKE_DIRECTORY ) {

        $from = $this->getStreamName();

        if(   $force === Hoa_Stream_Io_Touchable::DO_NOT_OVERWRITE
           && true   === file_exists($name))
            return false;

        if(Hoa_Stream_Io_Touchable::MAKE_DIRECTORY === $mkdir)
            Hoa_File_Directory::create(
                dirname($name),
                Hoa_File_Directory::MODE_CREATE_RECURSIVE
            );

        if(null === $this->getStreamContext())
            return @rename($from, $name);

        return @rename($from, $name, $this->getStreamContext()->getContext());
    }

    /**
     * Delete a file.
     *
     * @access  public
     * @return  bool
     */
    public function delete ( ) {

        if(null === $this->getStreamContext())
            return @unlink($this->getStreamName());

        return @unlink(
            $this->getStreamName(),
            $this->getStreamContext()->getContext()
        );
    }

    /**
     * Change file group.
     *
     * @access  public
     * @param   mixed   $group    Group name or number.
     * @return  bool
     */
    public function changeGroup ( $group ) {

        return chgrp($this->getStreamName(), $group);
    }

    /**
     * Change file mode.
     *
     * @access  public
     * @param   int     $mode    Mode (in octal!).
     * @return  bool
     */
    public function changeMode ( $mode ) {

        return chmod($this->getStreamName(), $mode);
    }

    /**
     * Change file owner.
     *
     * @access  public
     * @param   mixed   $user    User.
     * @return  bool
     */
    public function changeOwner ( $user ) {

        return chown($this->getStreamName(), $user);
    }

    /**
     * Change the current umask.
     *
     * @access  public
     * @param   int     $umask    Umask (in octal!). If null, given the current
     *                            umask value.
     * @return  int
     */
    public static function umask ( $umask = null ) {

        if(null === $umask)
            return umask();

        return umask($umask);
    }

    /**
     * Check if it is a file.
     *
     * @access  public
     * @return  bool
     */
    public function isFile ( ) {

        return is_file($this->getStreamName());
    }

    /**
     * Check if it is a link.
     *
     * @access  public
     * @return  bool
     */
    public function isLink ( ) {

        return is_link($this->getStreamName());
    }

    /**
     * Check if it is a directory.
     *
     * @access  public
     * @return  bool
     */
    public function isDirectory ( ) {

        return is_dir($this->getStreamName());
    }

    /**
     * Check if it is a socket.
     *
     * @access  public
     * @return  bool
     */
    public function isSocket ( ) {

        return filetype($this->getStreamName()) == 'socket';
    }

    /**
     * Check if it is a FIFO pipe.
     *
     * @access  public
     * @return  bool
     */
    public function isFIFOPipe ( ) {

        return filetype($this->getStreamName()) == 'fifo';
    }

    /**
     * Check if it is character special file.
     *
     * @access  public
     * @return  bool
     */
    public function isCharacterSpecial ( ) {

        return filetype($this->getStreamName()) == 'char';
    }

    /**
     * Check if it is block special.
     *
     * @access  public
     * @return  bool
     */
    public function isBlockSpecial ( ) {

        return filetype($this->getStreamName()) == 'block';
    }

    /**
     * Check if it is an unknown type.
     *
     * @access  public
     * @return  bool
     */
    public function isUnknown ( ) {

        return filetype($this->getStreamName()) == 'unknown';
    }

    /**
     * Set the open mode.
     *
     * @access  protected
     * @param   string     $mode    Open mode. Please, see the child::MODE_*
     *                              constants.
     * @return  string
     */
    protected function setMode ( $mode ) {

        $old         = $this->_mode;
        $this->_mode = $mode;

        return $old;
    }

    /**
     * Get the open mode.
     *
     * @access  public
     * @return  string
     */
    public function getMode ( ) {

        return $this->_mode;
    }

    /**
     * Get inode.
     *
     * @access  public
     * @return  int
     */
    public function getINode ( ) {

        return fileinode($this->getStreamName());
    }

    /**
     * Check if the system is case sensitive or not.
     *
     * @access  public
     * @return  bool
     */
    public static function isCaseSensitive ( ) {

        return !(
               file_exists(strtolower(__FILE__))
            && file_exists(strtoupper(__FILE__))
        );
    }

    /**
     * Get a canonicalized absolute pathname.
     *
     * @access  public
     * @return  string
     */
    public function getRealPath ( ) {

        return realpath($this->getStreamName());
    }

    /**
     * Get file extension (if exists).
     *
     * @access  public
     * @return  string
     */
    public function getExtension ( ) {

        return pathinfo(
            $this->getStreamName(),
            PATHINFO_EXTENSION
        );
    }

    /**
     * Get filename without extension.
     *
     * @access  public
     * @return  string
     */
    public function getFilename ( ) {

        $file = basename($this->getStreamName());

        if(defined('PATHINFO_FILENAME'))
            return pathinfo($file, PATHINFO_FILENAME);

        if(strstr($file, '.'))
            return substr($file, 0, strrpos($file, '.'));

        return $file;
    }
}
