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

use Hoa\Iterator\Aggregate;
use Hoa\Iterator\Append;
use Hoa\Iterator\IteratorIterator;
use Hoa\Iterator\Recursive\Iterator;
use Hoa\Iterator\Recursive\Directory;
use Hoa\Iterator\FileSystem;
use Hoa\Iterator\CallbackFilter;
use Hoa\Iterator\Map;
use Hoa\File\SplFileInfo;

/**
 * Class \Hoa\File\Finder.
 *
 * This class allows to find files easily by using filters and flags.
 *
 * @author     Ivan Enderlin <ivan.enderlin@hoa-project.net>
 * @copyright  Copyright © 2007-2015 Ivan Enderlin.
 * @license    New BSD License
 */

class Finder implements \Hoa\Iterator\Aggregate {

    /**
     * SplFileInfo classname.
     *
     * @var \Hoa\File\Finder string
     */
    protected $_splFileInfo = 'Hoa\File\SplFileInfo';

    /**
     * Paths where to look for.
     *
     * @var \Hoa\File\Finder array
     */
    protected $_paths       = array();

    /**
     * Max depth in recursion.
     *
     * @var \Hoa\File\Finder int
     */
    protected $_maxDepth    = -1;

    /**
     * Filters.
     *
     * @var \Hoa\File\Finder array
     */
    protected $_filters     = array();

    /**
     * Flags.
     *
     * @var \Hoa\File\Finder int
     */
    protected $_flags       = -1;

    /**
     * Types of files to handle.
     *
     * @var \Hoa\File\Finder array
     */
    protected $_types       = array();

    /**
     * What comes first: parent or child?
     *
     * @var \Hoa\File\Finder int
     */
    protected $_first       = -1;

    /**
     * Sorts.
     *
     * @var \Hoa\File\Finder array
     */
    protected $_sorts       = array();



    /**
     * Initialize.
     *
     * @access  public
     * @return  void
     */
    public function __construct ( ) {

        $this->_flags =   \Hoa\Iterator\FileSystem::KEY_AS_PATHNAME
                        | \Hoa\Iterator\FileSystem::CURRENT_AS_FILEINFO
                        | \Hoa\Iterator\FileSystem::SKIP_DOTS;
        $this->_first = \Hoa\Iterator\Recursive\Iterator::SELF_FIRST;

        return;
    }

    /**
     * Select a directory to scan.
     *
     * @access  public
     * @param   string  $path    Path.
     * @return  \Hoa\File\Finder
     */
    public function in ( $path ) {

        if(!is_array($path))
            $path = array($path);

        foreach($path as $p)
            $this->_paths[] = $p;

        return $this;
    }

    /**
     * Set max depth for recursion.
     *
     * @access  public
     * @param   int  $depth    Depth.
     * @return  \Hoa\File\Finder
     */
    public function maxDepth ( $depth ) {

        $this->_maxDepth = $depth;

        return $this;
    }

    /**
     * Include files in the result.
     *
     * @access  public
     * @return  \Hoa\File\Finder
     */
    public function files ( ) {

        $this->_types[] = 'file';

        return $this;
    }

    /**
     * Include directories in the result.
     *
     * @access  public
     * @return  \Hoa\File\Finder
     */
    public function directories ( ) {

        $this->_types[] = 'dir';

        return $this;
    }

    /**
     * Include links in the result.
     *
     * @access  public
     * @return  \Hoa\File\Finder
     */
    public function links ( ) {

        $this->_types[] = 'link';

        return $this;
    }

    /**
     * Follow symbolink links.
     *
     * @access  public
     * @param   bool  $flag    Whether we follow or not.
     * @return  \Hoa\File\Finder
     */
    public function followSymlinks ( $flag = true ) {

        if(true === $flag)
            $this->_flags ^= \Hoa\Iterator\FileSystem::FOLLOW_SYMLINKS;
        else
            $this->_flags |= \Hoa\Iterator\FileSystem::FOLLOW_SYMLINKS;

        return $this;
    }

    /**
     * Include files that match a regex.
     * Example:
     *     $this->name('#\.php$#');
     *
     * @access  public
     * @return  \Hoa\File\Finder
     */
    public function name ( $regex ) {

        $this->_filters[] = function ( $current ) use ( $regex ) {

            return 0 !== preg_match($regex, $current->getBasename());
        };

        return $this;
    }

    /**
     * Exclude directories that match a regex.
     * Example:
     *      $this->notIn('#^\.(git|hg)$#');
     *
     * @access  public
     * @return  \Hoa\File\Finder
     */
    public function notIn ( $regex ) {

        $this->_filters[] = function ( $current ) use ( $regex ) {

            foreach(explode(DS, $current->getPathname()) as $part)
                if(0 !== preg_match($regex, $part))
                    return false;

            return true;
        };

        return $this;
    }

    /**
     * Include files that respect a certain size.
     * The size is a string of the form:
     *     operator number unit
     * where
     *     • operator could be: <, <=, >, >= or =;
     *     • number is a positive integer;
     *     • unit could be: b (default), Kb, Mb, Gb, Tb, Pb, Eb, Zb, Yb.
     * Example:
     *     $this->size('>= 12Kb');
     *
     * @access  public
     * @param   string  $size    Size.
     * @return  \Hoa\File\Finder
     */
    public function size ( $size ) {

        if(0 === preg_match('#^(<|<=|>|>=|=)\s*(\d+)\s*((?:[KMGTPEZY])b)?$#', $size, $matches))
            return $this;

        $number   = floatval($matches[2]);
        $unit     = isset($matches[3]) ? $matches[3] : 'b';
        $operator = $matches[1];

        switch($unit) {

            case 'b':
              break;

            // kilo
            case 'Kb':
                $number <<= 10;
              break;

            // mega.
            case 'Mb':
                $number <<= 20;
              break;

            // giga.
            case 'Gb':
                $number <<= 30;
              break;

            // tera.
            case 'Tb':
                $number *= 1099511627776;
              break;

            // peta.
            case 'Pb':
                $number *= pow(1024, 5);
              break;

            // exa.
            case 'Eb':
                $number *= pow(1024, 6);
              break;

            // zetta.
            case 'Zb':
                $number *= pow(1024, 7);
              break;

            // yota.
            case 'Yb':
                $number *= pow(1024, 8);
              break;
        }

        $filter = null;

        switch($operator) {

            case '<':
                $filter = function ( $current, $key, $iterator ) use ( $number ) {

                    return $current->getSize() < $number;
                };
              break;

            case '<=':
                $filter = function ( $current, $key, $iterator ) use ( $number ) {

                    return $current->getSize() <= $number;
                };
              break;

            case '>':
                $filter = function ( $current, $key, $iterator ) use ( $number ) {

                    return $current->getSize() > $number;
                };
              break;

            case '>=':
                $filter = function ( $current, $key, $iterator ) use ( $number ) {

                    return $current->getSize() >= $number;
                };
              break;

            case '=':
                $filter = function ( $current, $key, $iterator ) use ( $number ) {

                    return $current->getSize() === $number;
                };
              break;
        }

        $this->_filters[] = $filter;

        return $this;
    }

    /**
     * Whether we should include dots or not (respectively . and ..).
     *
     * @access  public
     * @param   bool  $flag    Include or not.
     * @return  \Hoa\File\Finder
     */
    public function dots ( $flag = true ) {

        if(true === $flag)
            $this->_flags ^= \Hoa\Iterator\FileSystem::SKIP_DOTS;
        else
            $this->_flags |= \Hoa\Iterator\FileSystem::SKIP_DOTS;

        return $this;
    }

    /**
     * Include files that are owned by a certain owner.
     *
     * @access  public
     * @param   int  $owner    Owner.
     * @return  \Hoa\File\Finder
     */
    public function owner ( $owner ) {

        $this->_filters[] = function ( $current ) use ( $owner ) {

            return $current->getOwner() === $owner;
        };

        return $this;
    }

    /**
     * Format date.
     * Date can have the following syntax:
     *     date
     *     since date
     *     until date
     * If the date does not have the “ago” keyword, it will be added.
     * Example: “42 hours” is equivalent to “since 42 hours” which is equivalent
     * to “since 42 hours ago”.
     *
     * @access  protected
     * @param   string  $date         Date.
     * @param   int     &$operator    Operator (-1 for since, 1 for until).
     * @return  int
     */
    protected function formatDate ( $date, &$operator ) {

        $time     =  0;
        $operator = -1;

        if(0 === preg_match('#\bago\b#', $date))
            $date .= ' ago';

        if(0 !== preg_match('#^(since|until)\b(.+)$#', $date, $matches)) {

            $time = strtotime($matches[2]);

            if('until' === $matches[1])
                $operator = 1;
        }
        else
            $time = strtotime($date);

        return $time;
    }

    /**
     * Include files that have been changed from a certain date.
     * Example:
     *     $this->changed('since 13 days');
     *
     * @access  public
     * @param   string  $date    Date.
     * @return  \Hoa\File\Finder
     */
    public function changed ( $date ) {

        $time = $this->formatDate($date, $operator);

        if(-1 === $operator)
            $this->_filters[] = function ( $current ) use ( $time ) {

                return $current->getCTime() >= $time;
            };
        else
            $this->_filters[] = function ( $current ) use ( $time ) {

                return $current->getCTime() < $time;
            };

        return $this;
    }

    /**
     * Include files that have been modified from a certain date.
     * Example:
     *     $this->modified('since 13 days');
     *
     * @access  public
     * @param   string  $date    Date.
     * @return  \Hoa\File\Finder
     */
    public function modified ( $date ) {

        $time = $this->formatDate($date, $operator);

        if(-1 === $operator)
            $this->_filters[] = function ( $current ) use ( $time ) {

                return $current->getMTime() >= $time;
            };
        else
            $this->_filters[] = function ( $current ) use ( $time ) {

                return $current->getMTime() < $time;
            };

        return $this;
    }

    /**
     * Add your own filter.
     * The callback will receive 3 arguments: $current, $key and $iterator. It
     * must return a boolean: true to include the file, false to exclude it.
     * Example:
     *     // Include files that are readable
     *     $this->filter(function ( $current ) {
     *
     *         return $current->isReadable();
     *     });
     *
     * @access  public
     * @param   callable  $callback    Callback
     * @return  \Hoa\File\Finder
     */
    public function filter ( $callback ) {

        $this->_filters[] = $callback;

        return $this;
    }

    /**
     * Sort result by name.
     * If \Collator exists (from ext/intl), the $locale argument will be used
     * for its constructor. Else, strcmp() will be used.
     * Example:
     *     $this->sortByName('fr_FR');
     *
     * @access  public
     * @param   string  $locale   Locale.
     * @return  \Hoa\File\Finder
     */
    public function sortByName ( $locale = 'root' ) {

        if(true === class_exists('Collator', false)) {

            $collator = new \Collator($locale);

            $this->_sorts[] = function ( $a, $b ) use ( $collator ) {

                return $collator->compare($a->getPathname(), $b->getPathname());
            };
        }
        else
            $this->_sorts[] = function ( $a, $b ) {

                return strcmp($a->getPathname(), $b->getPathname());
            };

        return $this;
    }

    /**
     * Sort result by size.
     * Example:
     *     $this->sortBySize();
     *
     * @access  public
     * @return  \Hoa\File\Finder
     */
    public function sortBySize ( ) {

        $this->_sorts[] = function ( $a, $b ) {

            return $a->getSize() < $b->getSize();
        };

        return $this;
    }

    /**
     * Add your own sort.
     * The callback will receive 2 arguments: $a and $b. Please see the uasort()
     * function.
     * Example:
     *     // Sort files by their modified time.
     *     $this->sort(function ( $a, $b ) {
     *
     *         return $a->getMTime() < $b->getMTime();
     *     });
     *
     * @access  public
     * @param   callable  $callback    Callback
     * @return  \Hoa\File\Finder
     */
    public function sort ( $callable ) {

        $this->_sorts[] = $callable;

        return $this;
    }

    /**
     * Child comes first when iterating.
     *
     * @access  public
     * @return  \Hoa\File\Finder
     */
    public function childFirst ( ) {

        $this->_first = \Hoa\Iterator\Recursive\Iterator::CHILD_FIRST;

        return $this;
    }

    /**
     * Get the iterator.
     *
     * @access  public
     * @return  \Traversable
     */
    public function getIterator ( ) {

        $_iterator = new \Hoa\Iterator\Append();
        $types     = $this->getTypes();

        if(!empty($types))
            $this->_filters[] = function ( $current ) use ( $types ) {

                return in_array($current->getType(), $types);
            };

        $maxDepth    = $this->getMaxDepth();
        $splFileInfo = $this->getSplFileInfo();

        foreach($this->getPaths() as $path) {

            if(1 == $maxDepth)
                $iterator = new \Hoa\Iterator\IteratorIterator(
                    new \Hoa\Iterator\Recursive\Directory(
                        $path,
                        $this->getFlags(),
                        $splFileInfo
                    ),
                    $this->getFirst()
                );
            else {

                $iterator = new \Hoa\Iterator\Recursive\Iterator(
                    new \Hoa\Iterator\Recursive\Directory(
                        $path,
                        $this->getFlags(),
                        $splFileInfo
                    ),
                    $this->getFirst()
                );

                if(1 < $maxDepth)
                    $iterator->setMaxDepth($maxDepth - 1);
            }

            $_iterator->append($iterator);
        }

        foreach($this->getFilters() as $filter)
            $_iterator = new \Hoa\Iterator\CallbackFilter(
                $_iterator,
                $filter
            );

        $sorts = $this->getSorts();

        if(empty($sorts))
            return $_iterator;

        $array = iterator_to_array($_iterator);

        foreach($sorts as $sort)
            uasort($array, $sort);

        return new \Hoa\Iterator\Map($array);
    }

    /**
     * Set SplFileInfo classname.
     *
     * @access  public
     * @param   string  $splFileInfo    SplFileInfo classname.
     * @return  string
     */
    public function setSplFileInfo ( $splFileInfo ) {

        $old                = $this->_splFileInfo;
        $this->_splFileInfo = $splFileInfo;

        return $old;
    }

    /**
     * Get SplFileInfo classname.
     *
     * @access  public
     * @return  string
     */
    public function getSplFileInfo ( ) {

        return $this->_splFileInfo;
    }

    /**
     * Get all paths.
     *
     * @access  protected
     * @return  array
     */
    protected function getPaths ( ) {

        return $this->_paths;
    }

    /**
     * Get max depth.
     *
     * @access  public
     * @return  int
     */
    public function getMaxDepth ( ) {

        return $this->_maxDepth;
    }

    /**
     * Get types.
     *
     * @access  public
     * @return  array
     */
    public function getTypes ( ) {

        return $this->_types;
    }

    /**
     * Get name.
     *
     * @access  public
     * @return  string
     */
    public function getName ( ) {

        return $this->_name;
    }

    /**
     * Get filters.
     *
     * @access  protected
     * @return  array
     */
    protected function getFilters ( ) {

        return $this->_filters;
    }

    /**
     * Get sorts.
     *
     * @access  protected
     * @return  array
     */
    protected function getSorts ( ) {

        return $this->_sorts;
    }

    /**
     * Get flags.
     *
     * @access  public
     * @return  int
     */
    public function getFlags ( ) {

        return $this->_flags;
    }

    /**
     * Get first.
     *
     * @access  public
     * @return  int
     */
    public function getFirst ( ) {

        return $this->_first;
    }
}
