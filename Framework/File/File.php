<?php

/**
 * Hoa Framework
 *
 *
 * @license
 *
 * GNU General Public License
 *
 * This file is part of HOA Open Accessibility.
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
 * Hoa_File_Util
 */
import('File.Util');

/**
 * Hoa_File_Dir
 */
import('File.Dir');

/**
 * Hoa_File_Upload
 */
import('File.Upload');

/**
 * Class Hoa_File.
 *
 * Manage files (read, write etc.).
 *
 * @author      Ivan ENDERLIN <ivan.enderlin@hoa-project.net>
 * @copyright   Copyright (c) 2007, 2008 Ivan ENDERLIN.
 * @license     http://gnu.org/licenses/gpl.txt GNU GPL
 * @since       PHP 5
 * @version     0.2
 * @package     Hoa_File
 */

class Hoa_File {

    /**
     * File parameters.
     *
     * @const int
     */
    const DEFAULT_READSIZE  =  1024;
    const MAX_LINE_READSIZE = 40960;

    /**
     * File open mode.
     *
     * @const string
     */
    const MODE_CREATE       = 'xb';
    const MODE_READ         = 'rb';
    const MODE_WRITE        = 'wb';
    const MODE_APPEND       = 'ab';

    /**
     * Lock or unlock file.
     *
     * @const bool
     */
    const LOCK              = true;
    const DONOT_LOCK        = false;

    /**
     * File lock mode.
     *
     * @const mixed
     */
    const LOCK_BLOCK        = true;
    const LOCK_SHARED       = LOCK_SH; // | (LOCK_BLOCK ? 0 : LOCK_NB);
    const LOCK_EXCLUSIVE    = LOCK_EX; // | (LOCK_BLOCK ? 0 : LOCK_NB);

    /**
     * File seek position.
     *
     * @const int
     */
    const SEEK_SET          = SEEK_SET;
    const SEEK_CUR          = SEEK_CUR;
    const SEEK_END          = SEEK_END;

    /**
     * Overwrite or not.
     *
     * @const bool
     */
    const OVERWRITE         = true;
    const DONOT_OVERWRITE   = false;

    /**
     * List of files pointers.
     *
     * @var Hoa_File array
     */
    private static $filePointer = array();



    /**
     * Handle file pointer.
     *
     * @access  private
     * @param   string   $filename    File to read from.
     * @param   string   $mode        File open mode.
     * @param   mixed    $lock        Lock type to use.
     * @return  resource
     * @throw   Hoa_File_Exception
     */
    final private static function &_getPointer ( $filename = '',
                                                 $mode = self::MODE_READ,
                                                 $lock = self::DONOT_LOCK ) {

        if(empty($filename))
            throw new Hoa_File_Exception('Filename could not be empty.', 0);

        if(!isset(self::$filePointer[$filename][$mode])
           || !is_resource(self::$filePointer[$filename][$mode])) {

            switch($mode) {

                case self::MODE_READ:
                    if(!file_exists($filename)
                       && !preg_match('#^.+(?<!file):\/\/#i', $filename))
                        throw new Hoa_File_Exception(
                            'File does not exist : %s.', 1, $filename);
                  break;

                case self::MODE_APPEND:
                case self::MODE_WRITE :
                    if(file_exists($filename)) {

                        if(!is_writable($filename))
                            throw new Hoa_File_Exception(
                                'File is not writable : %s.', 2, $filename);
                    }
                    elseif(!is_writable($foo = dirname($filename)))
                        throw new Hoa_File_Exception(
                            'Cannot create file in directory : %s.',
                            3, $foo);
                  break;

                default:
                    throw new Hoa_File_Exception(
                        'Invalid access mode : %s in %s.', 4, array($mode, $filename));
            }

            self::$filePointer[$filename][$mode] = @fopen($filename, $mode);

            if(!is_resource(self::$filePointer[$filename][$mode]))
                throw new Hoa_File_Exception('Failed to open file %s, in mode %s',
                    5, array($filename, $mode));
        }

        if($lock === self::LOCK) {

            $lock = $mode == self::MODE_READ
                        ? self::LOCK_SHARED
                        : self::LOCK_EXCLUSIVE;

            if (!@flock(self::$filePointer[$filename][$mode], $lock))
                throw new Hoa_File_Exception('Could not lock file %s : %s',
                    6, array($filename, $lock));
        }

        return self::$filePointer[$filename][$mode];
    }

    /**
     * Read n bytes from a file.
     *
     * @access  public
     * @param   string  $filename    File to read from.
     * @param   int     $size        Number of bytes to read.
     * @param   mixed   $lock        Lock type to use.
     * @return  mixed
     * @throw   Hoa_File_Exception
     */
    public static function read ( $filename = '',
                                  $size = self::DEFAULT_READSIZE,
                                  $lock = self::DONOT_LOCK ) {

        $filePointer = &self::_getPointer($filename, self::MODE_READ, $lock);

        if(empty($filename))
            throw new Hoa_File_Exception('Filename could not be empty.', 7);

        if($size <= 0)
            return self::readAll($filename, $lock);

        if(   !isset($filePointer[$filename])
           || !is_resource($filePointer[$filename]))
            $fp = &self::_getPointer($filename, self::MODE_READ, $lock);
        else
            $fp = &$filePointer[$filename][self::MODE_READ];

        return !feof($fp) ? fread($fp, $size) : false;
    }

    /**
     * Read only first char from a file.
     *
     * @access  public
     * @param   string  $filename    File to read from.
     * @param   mixed   $lock        Lock type to use.
     * @return  string
     */
    public static function readChar ( $filename = '', $lock = self::DONOT_LOCK ) {

        return self::read($filename, 1, $lock);
    }

    /**
     * Read all bytes from a file.
     *
     * @access  public
     * @param   string  $filename    File to read from.
     * @param   mixed   $lock        Lock type to use.
     * @param   bool    $func        Force to use file_get_contents.
     * @return  string
     * @throw   Hoa_File_Exception
     */
    public static function readAll ( $filename = '', $lock = self::DONOT_LOCK, $func = true ) {

        if(empty($filename))
            throw new Hoa_File_Exception('Filename could not be empty.', 8);

        if(true === $func && function_exists('file_get_contents')) {

            if(false === $file = @file_get_contents($filename))
                throw new Hoa_File_Exception('Cannot read file : %s',
                    9, $filename);
            return $file;
        }

        $file = '';
        while(false !== $handle = self::read($filename, self::DEFAULT_READSIZE, $lock))
            $file .= $handle;

        self::close($filename, self::MODE_READ);

        return $file;
    }

    /**
     * Write data into a file.
     *
     * @access  public
     * @param   string  $filename    File to write in.
     * @param   string  $data        Data to write.
     * @param   string  $mode        File open mode.
     * @param   mixed   $lock        Lock type to use.
     * @param   int     $length      Data length.
     * @return  int
     * @throw   Hoa_File_Exception
     */
    public static function write ( $filename = '', $data = '',
                                   $mode = self::MODE_APPEND,
                                   $lock = self::DONOT_LOCK, $length = 0 ) {

        if(empty($filename))
            throw new Hoa_File_Exception('Filename could not be empty.', 10);

        if(empty($data))
            throw new Hoa_File_Exception('Data could not be empty.', 11);

        if($mode === self::MODE_READ)
            throw new Hoa_File_Exception(
                'Cannot write in a file if its mode is MODE_READ.', 12);

        if($length <= 0)
            $length = strlen($data);

        $fp = &self::_getPointer($filename, $mode, $lock);

        if(false === $out = @fwrite($fp, $data, $length))
            throw new Hoa_File_Exception('Cannot write data : %s, into file : %s',
                12, array($data, $filename));

        return $out;
    }

    /**
     * Write only one char into a file.
     *
     * @access  public
     * @param   string  $filename    File to write in.
     * @param   char    $data        Char to write.
     * @param   string  $mode        File open mode.
     * @param   mixed   $lock        Lock type to use.
     * @return  int
     */
    public static function writeChar ( $filename = '', $data = '',
                                       $mode = self::MODE_APPEND,
                                       $lock = self::DONOT_LOCK ) {

        return self::write($filename, $data, $mode, $lock, 1);
    }

    /**
     * Seek on a file pointer.
     *
     * @access  public
     * @param   string  $filename    File to seek.
     * @param   int     $offset      Offset.
     * @param   int     $whence      The new position, measured in bytes from
     *                               the beginning of the file, is obtained by
     *                               adding offset to the position specified by
     *                               whence.
     * @param   string  $mode        File open mode.
     * @param   mixed   $lock        Lock type to use.
     * @return  int
     * @throw   Hoa_File_Exception
     */
    public static function seek ( $filename = '',           $offset = 0,
                                  $whence = self::SEEK_SET, $mode = self::MODE_READ,
                                  $lock = self::DONOT_LOCK ) {

        if(empty($filename))
            throw new Hoa_File_Exception('Filename could not be empty.', 13);

        if(   $whence != self::SEEK_SET
           && $whence != self::SEEK_CUR
           && $whence != self::SEEK_END)
            throw new Hoa_File_Exception('Whence option must be equal to ' .
                'SEEK_SET, SEEK_CUR or SEEK_END.', 14);

        $fp = &self::_getPointer($filename, $mode, $lock);

        if(-1 === $out = fseek($fp, $offset, $whence))
            throw new Hoa_File_Exception('Cannot seek pointer to %d offset in %s file.',
                15, array($offset, $filename));

        return $out;
    }

    /**
     * Rewind the position of a file pointer.
     *
     * @access  public
     * @param   string  $filename    File to rewind.
     * @param   string  $mode        File open mode.
     * @param   bool    $lock        Lock type to use.
     * @return  int
     * @throw   Hoa_File_Exception
     */
    public static function rewind ( $filename = '',
                                    $mode     = self::MODE_READ,
                                    $lock     = self::DONOT_LOCK ) {

        if(empty($filename))
            throw new Hoa_File_Exception('Filename could not be empty.', 14);

        $fp = &self::_getPointer($filename, $mode);

        if(is_resource($fp))
            return self::seek($filename, 0, SEEK_SET, $mode, $lock);
        else
            throw new Hoa_File_Exception(
                'Could not rewind %s pointer in mode %s',
                16, array($filename, $mode));
    }

    /**
     * Close an opened file pointer.
     *
     * @access  public
     * @param   string  $filename    File to close.
     * @param   string  $mode        File open mode.
     * @param   mixed   $lock        Lock type to use.
     * @return  bool
     * @throw   Hoa_File_Exception
     */
    public static function close ( $filename = '',
                                   $mode     = self::MODE_READ,
                                   $lock     = self::DONOT_LOCK ) {

        if(empty($filename))
            throw new Hoa_File_Exception('Filename could not be empty.', 17);

        $filePointer = &self::_getPointer($filename, $mode, $lock);

        if(!isset($filePointer[$filename][$mode]))
            return true;

        $fp = $filePointer[$filename][$mode];
        unset($filePointer[$filename][$mode]);

        if(is_resource($fp)) {

            @flock($fp, LOCK_UN);

            if(!@fclose($fp))
                throw new Hoa_File_Exception('Cannot close file pointer : %s.',
                    18, $filename);
        }

        return true;
    }

    /**
     * Close all opened files pointers.
     *
     * @access  public
     * @return  bool
     */
    public static function closeAll ( ) {

        if(!isset(self::$filePointer))
            return false;

        foreach(self::$filePointer as $filename => $mode) {

            foreach(array_keys($mode) as $mod) {

                $fp = self::$filePointer[$filename][$mod];
                unset(self::$filePointer[$filename][$mod]);

                if(is_resource($fp)) {

                    @flock ($fp, LOCK_UN);
                    @fclose($fp);
                }
            }
        }

        self::$filePointer = array();

        return true;
    }

    /**
     * Make a copy of a file.
     *
     * @access  public
     * @param   string  $source       File source.
     * @param   string  $dest         File destination.
     * @param   bool    $overwrite    Overwrite file or not.
     * @return  string
     * @throw   Hoa_File_Exception
     */
    public static function copy ( $source    = '', $dest = '',
                                  $overwrite = self::DONOT_OVERWRITE ) {

        if(empty($source))
            throw new Hoa_File_Exception('Source could not be empty.', 19);

        if(empty($dest))
            throw new Hoa_File_Exception('Destination could not be empty.', 20);

        $source   = Hoa_File_Util::realPath($source, DS, false);
        $dest     = Hoa_File_Util::realPath($dest  , DS, false);

        if(!file_exists($source))
            throw new Hoa_File_Exception('Source file does not exist (%s).',
                21, $source);

        $destFile = Hoa_File_Util::skipExt($dest  , false);
        $destExt  = Hoa_File_Util::getExt ($dest         );

        if(!$overwrite && file_exists($dest)) {

            $i = 1;
            while(file_exists($destFile . '_copy' . $i . '.' . $destExt))
                $i++;

            $dest = $destFile . '_copy' . $i . '.' . $destExt;
        }

        if(false === copy($source, $dest))
            throw new Hoa_File_Exception('File copy failed (%s to %s)',
                22, array($source, $dest));

        return $dest;
    }

    /**
     * Delete file(s).
     *
     * @access  public
     * @param   mixed  $files     List of files to delete.
     *                            Use dir/* for cleaning a directory.
     * @param   bool   $silent    Silent unlink error.
     * @return  bool
     * @throw   Hoa_File_Exception
     */
    public static function delete ( $files = array(), $silent = true ) {

        if(empty($files))
            return true;

        $fileStack = array();

        if(is_string($files)) {

            if(basename($files) == '*') {

                $dir = substr($files, 0, -1);
                $dirStack = Hoa_File_Dir::scan($dir, Hoa_File_Dir::LIST_FILE);

                foreach($dirStack as $i => $file)
                    $fileStack[] = Hoa_File_Util::realPath($dir . DS . $file['name'],
                                                           DS, false);
            }
            else
                $fileStack[0] = $files;
        }

        if(empty($fileStack))
            foreach($files as $i => $file)
                $fileStack[] = Hoa_File_Util::realPath($file, DS, false);

        foreach($fileStack as $i => $file) {

            if(is_file($file)) {

                if(false === @unlink($file)) {

                    if(!$silent)
                        throw new Hoa_File_Exception('Could not delete %s.',
                            23, $file);
                }
                else
                    unset($fileStack[$i]);
            }
        }

        return empty($fileStack) ? true : $fileStack;
    }

   /**
     * Move a file to.
     *
     * @access  public
     * @param   string  $source    File source.
     * @param   string  $dest      File destination.
     * @param   bool    $mkdir     Force to make directory if does not exist.
     * @return  bool
     * @throw   Hoa_File_Exception
     */
    public static function move ( $source = '', $dest = '', $mkdir = true ) {

        if(empty($source))
            throw new Hoa_File_Exception('Source could not be empty.', 24);

        if(empty($dest))
            throw new Hoa_File_Exception('Destination could not be empty.', 25);

        Hoa_File_Dir::create(substr($dest, 0, strrpos($dest, '/') + 1), $mkdir);

        if(false === @rename($source, $dest))
            throw new Hoa_File_Exception('Could not move %s to %s.',
                26, array($source, $dest));
        else
            return true;
    }

    /**
     * Close all opened files.
     *
     * @access  public
     */
    public static function _Hoa_File ( ) {

        self::closeAll();
    }
}

Hoa_Framework::registerShutDownFunction('Hoa_File', '_Hoa_File');
