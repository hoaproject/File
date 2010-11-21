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
 * @subpackage  Hoa_File_Upload
 *
 */

/**
 * Hoa_File
 */
import('File.~');

/**
 * Class Hoa_File_Upload.
 *
 * Manage file uploading from web forms.
 *
 * @author      Ivan ENDERLIN <ivan.enderlin@hoa-project.net>
 * @copyright   Copyright (c) 2007, 2010 Ivan ENDERLIN.
 * @license     http://gnu.org/licenses/gpl.txt GNU GPL
 * @since       PHP 5
 * @version     0.3
 * @package     Hoa_File
 * @subpackage  Hoa_File_Upload
 */

class Hoa_File_Upload /*extends Hoa_File*/ {

    /**
     * Upload parameters.
     *
     * @var Hoa_File_Upload type
     */
    private static $UPLOAD_MAX_FILESIZE = null;

    /**
     * Upload error code.
     *
     * @const int
     */
    const ERR_OK         = 0;
    const ERR_INI_SIZE   = 1;
    const ERR_FORM_SIZE  = 2;
    const ERR_PARTIAL    = 3;
    const ERR_NO_FILE    = 4;
    const ERR_NO_TMP_DIR = 6;
    const ERR_CANT_WRITE = 7;
    const ERR_EXTENSION  = 8;

    /**
     * Upload error message.
     *
     * @const string
     */
    const UPLOAD_0       = 'There is no error, the file uploaded with success.';
    const UPLOAD_1       = 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
    const UPLOAD_2       = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
    const UPLOAD_3       = 'The uploaded file was only partially uploaded.';
    const UPLOAD_4       = 'No file was uploaded.';
    const UPLOAD_6       = 'Missing a temporary folder.';
    const UPLOAD_7       = 'Failed to write file to disk.';
    const UPLOAD_8       = 'File upload stopped by extension.';
    const UPLOAD_UNKNONW = 'Failed to upload file. Unknown error.';

    /**
     * Overwrite or not.
     *
     * @const bool
     */
    const OVERWRITE         = true;
    const DONOT_OVERWRITE   = false;

    /**
     * Extension filter.
     *
     * @var Hoa_File_Upload array
     */
    protected static $extensionFilter = array();



    /**
     * Upload a file.
     * For more documentation, see RFC 1867.
     *
     * @access  public
     * @param   string  $id           Input name.
     * @param   string  $uDir         Upload directory.
     * @param   mixed   $uFile        Upload filename.
     * @param   bool    $overwrite    Overwrite a file or not.
     * @param   bool    $createdir    Force to create directory or not.
     * @param   bool    $extfilter    Apply extension filter or not.
     * @return  array
     * @throw   Hoa_File_Exception
     */
    public static function move ( $id = '', $uDir = '', $uFile = '',
                                  $overwrite = self::DONOT_OVERWRITE,
                                  $createdir = false, $extfilter = true ) {

        if(null === self::$UPLOAD_MAX_FILESIZE)
            self::$UPLOAD_MAX_FILESIZE = ini_get('upload_max_filesize');

        if(empty($_FILES))
            throw new Hoa_File_Exception('Global variable _FILES is empty.', 0);

        if(empty($id))
            throw new Hoa_File_Exception('Id could not be empty.', 1);

        if(empty($uDir))
            throw new Hoa_File_Exception('Upload directory name could not be empty.', 2);

        if(!is_dir($uDir) && !$createdir)
            throw new Hoa_File_Exception('Directory %s does not exist.', 3, $uDir);
        else
            Hoa_File_Dir::create($uDir);

        if(!isset($_FILES[$id]))
            throw new Hoa_File_Exception('Index %s does not exist.', 4, $id);

        $uFile = (array) $uFile;
        $files =  array();
        if(is_array($_FILES[$id]['name']))
            foreach($_FILES[$id]['name'] as $i => $value)
                $files[$i] = array(
                    'file'     => isset($uFile[$i]) ? $uFile[$i] : '',
                    'name'     => $_FILES[$id]['name'][$i],
                    'type'     => $_FILES[$id]['type'][$i],
                    'tmp_name' => $_FILES[$id]['tmp_name'][$i],
                    'error'    => $_FILES[$id]['error'][$i],
                    'size'     => $_FILES[$id]['size'][$i]);
        else
            $files[0] = array(
                'file'     => isset($uFile[0]) ? $uFile[0] : '',
                'name'     => $_FILES[$id]['name'],
                'type'     => $_FILES[$id]['type'],
                'tmp_name' => $_FILES[$id]['tmp_name'],
                'error'    => $_FILES[$id]['error'],
                'size'     => $_FILES[$id]['size']);

        return self::upload($files, $uDir, $overwrite, $extfilter);
    }

    /**
     * Upload a list of files.
     *
     * @access  protected
     * @param   array      $files        List of files.
     * @param   string     $uDir         Upload directory.
     * @param   bool       $overwrite    Overwrite a file or not.
     * @param   bool       $extfilter    Apply extension filter or not.
     * @return  array
     * @throw   Hoa_File_Exception
     * @throw   Hoa_File_Upload_Extension_Exception
     */
    protected static function upload ( $files, $uDir, $overwrite, $extfilter ) {

        $out = array();

        foreach($files as $e => $file) {

            if(empty($file['name']))
                continue;

            $uFile = $file['file'];
            if(empty($uFile[$e]))
                $uFile = $file['name'];

            $uFile = Hoa_File_Util::makeSecure(
                         Hoa_File_Util::makeSafe(basename($uFile))
                     );

            $uPath     = $uDir.$uFile;
            $uFileNExt = Hoa_File_Util::skipExt($uFile);
            $uFileExt  = Hoa_File_Util::getExt($uFile);

            if($extfilter) {

                $extStatus  = false;
                foreach(self::$extensionFilter as $i => $extension)
                    $extStatus |= !(bool) preg_match('#' . $extension . '#i', $uFileExt);

                if(!$extStatus)
                    throw new Hoa_File_Upload_Extension_Exception(
                        'Extension %s is rejected.', 5, $uFileExt);
            }

            if(!$overwrite && file_exists($uPath)) {

                $i = 1;
                while(file_exists($uDir . $uFileNExt . '_copy' . $i . '.' . $uFileExt))
                    $i++;

                $uPath = $uDir.$uFileNExt . '_copy' . $i . '.' . $uFileExt;
            }


            $muf = false;
            if(isset($file['error'])) {

                if($file['error'] == self::ERR_OK)
                    $muf = true;
            }
            else
                $muf = true;

            if($muf) {

                if(false === @move_uploaded_file($file['tmp_name'], $uPath))
                    throw new Hoa_File_Exception(
                        'Could not upload %s file.', 6, $file['tmp_name']);
                else
                    $out[$e] = $uPath;
            }
            else {

                if(isset($file['error']))
                    throw new Hoa_File_Exception(constant('UPLOAD_' . $file['error']), 7);
                else
                    throw new Hoa_File_Exception(UPLOAD_UNKNOWN, 8);
            }
        }

        return $out;
    }

    /**
     * Enable some extensions, make a filter.
     *
     * @access  public
     * @param   array   $extensions    List of extensions.
     *                                 Extensions support regulars expressions
     *                                 (except [, ^ and ]).
     * @param   bool    $enable        Enable or disable extensions.
     * @return  array
     * @throw   Hoa_File_Exception
     */
    public static function extensionFilter ( $extensions = array(), $enable = true ) {

        if(empty($extensions))
            throw new Hoa_File_Exception('Extension could not be empty.', 9);

        $extensions = (array) $extensions;

        foreach($extensions as $i => $extension) {

            $handle = trim($extension);
            $handle = preg_quote($handle);
            $handle = preg_replace('#[\.\t\n\r\0\x0B\/\\\]|[^a-z0-9\+\*\?\$\(\)\{\}\=\!\<\>\|\:]?#Si', '', $handle);
            $handle = strtolower($handle);
            self::$extensionFilter[] = $enable ? '[^'.$handle.']' : $handle;		
        }

        return self::$extensionFilter;
    }

    /**
     * Clean extension filter.
     *
     * @access  public
     */
    public static function cleanExtensionFilter ( ) {

        self::$extensionFilter = array();
    }

    /**
     * If version > 4.1.0, may use $_FILES variable, else $HTTP_POST_FILES.
     *
     * @access  public
     * @return  string
     */
    public static function globalVariable ( ) {

        return PHP_VERSION_ID > 40100 ? '_FILES' : 'HTTP_POST_FILES';
    }

    /**
     * Get name of a specific file.
     *
     * @access  public
     * @param   string  $id     File id.
     * @return  string
     * @throw   Hoa_File_Exception
     */
    public static function getName ( $id = '' ) {

        if(empty($id))
            throw new Hoa_File_Exception('Id could not be empty.', 10);

        return $_FILES[$id]['name'];
    }


    /**
     * Get type of a specific file.
     *
     * @access  public
     * @param   string  $id     File id.
     * @return  string
     * @throw   Hoa_File_Exception
     */
    public static function getType ( $id = '' ) {

        if(empty($id))
            throw new Hoa_File_Exception('Id could not be empty.', 11);

        return $_FILES[$id]['type'];
    }


    /**
     * Get size of a specific file.
     *
     * @access  public
     * @param   string  $id     File id.
     * @return  string
     * @throw   Hoa_File_Exception
     */
    public static function getSize ( $id = '' ) {

        if(empty($id))
            throw new Hoa_File_Exception('Id could not be empty.', 12);

        return $_FILES[$id]['size'];
    }


    /**
     * Get tempory name of a specific file.
     *
     * @access  public
     * @param   string  $id     File id.
     * @return  string
     * @throw   Hoa_File_Exception
     */
    public static function getTmpName ( $id = '' ) {

        if(empty($id))
            throw new Hoa_File_Exception('Id could not be empty.', 13);

        return $_FILES[$id]['tmp_name'];
    }


    /**
     * Get error of a specific file.
     * We could get error only if php version is upper to 4.2.0.
     *
     * @access  public
     * @param   string  $id     File id.
     * @return  mixed
     * @throw   Hoa_File_Exception
     */
    public static function getError ( $id = '' ) {

        if(empty($id))
            throw new Hoa_File_Exception('Id could not be empty.', 14);

        if(PHP_VERSION_ID > 40200)
            return $_FILES[$id]['error'];

        return false;
    }
}
