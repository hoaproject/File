<?php

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2007-2015, Ivan Enderlin. All rights reserved.
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

namespace Hoa\File;

// use Hoa\Iterator\SplFileInfo;
use Hoa\File\Exception;
use Hoa\File\ReadWrite as FileReadWrite;
use Hoa\File\Directory;
use Hoa\File\Link\ReadWrite as LinkReadWrite;

/**
 * Class \Hoa\File\SplFileInfo.
 *
 * Link between \Hoa\Iterator\SplFileInfo and \Hoa\File.
 *
 * @author     Ivan Enderlin <ivan.enderlin@hoa-project.net>
 * @copyright  Copyright © 2007-2015 Ivan Enderlin.
 * @license    New BSD License
 */

class SplFileInfo extends \Hoa\Iterator\SplFileInfo {

    /**
     * Current stream.
     *
     * @var \Hoa\File\Generic object
     */
    protected $_stream = null;



    /**
     * Open the SplFileInfo as a Hoa\File stream.
     *
     * @access  public
     * @return  \Hoa\File\Generic
     * @throw   \Hoa\File\Exception
     */
    public function open ( ) {

        if(true === $this->isFile())
            return $this->_stream = new FileReadWrite($this->getPathname());

        elseif(true === $this->isDir())
            return $this->_stream = new Directory($this->getPathname());

        elseif(true === $this->isLink())
            return $this->_stream = new LinkReadWrite($this->getPathname());

        throw new Exception('%s has an unknown type.', 0, $this->getPathname());
    }

    /**
     * Close the opened stream.
     *
     * @access  public
     * @return  mixed
     */
    public function close ( ) {

        if(null === $this->_stream)
            return;

        return $this->_stream->close();
    }

    /**
     * Destruct.
     *
     * @access  public
     * @return  void
     */
    public function __destruct ( ) {

        $this->close();

        return;
    }
}
