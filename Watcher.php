<?php

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2007-2014, Ivan Enderlin. All rights reserved.
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

/**
 * Class \Hoa\File\Watcher.
 *
 * A naive file system watcher that fires three events: new, move and modify.
 *
 * @author     Ivan Enderlin <ivan.enderlin@hoa-project.net>
 * @copyright  Copyright © 2007-2014 Ivan Enderlin.
 * @license    New BSD License
 */

class Watcher extends Finder implements \Hoa\Core\Event\Listenable {

    /**
     * Listeners.
     *
     * @var \Hoa\Core\Event\Listener object
     */
    protected $_on      = null;

    /**
     * Latency.
     *
     * @var \Hoa\File\Watcher int
     */
    protected $_latency = 1;



    /**
     * Constructor.
     *
     * @access  public
     * @param   int  $latency    Latency (in seconds).
     * @return  void
     */
    public function __construct ( $latency = null ) {

        parent::__construct();

        $this->_on = new \Hoa\Core\Event\Listener($this,[
            'new',
            'modify',
            'move'
        ]);

        if(null !== $latency)
            $this->setLatency($latency);

        return;
    }

    /**
     * Attach a callable to this listenable object.
     *
     * @access  public
     * @param   string  $listenerId    Listener ID.
     * @param   mixed   $callable      Callable.
     * @return  \Hoa\Stream
     * @return  \Hoa\Core\Exception
     */
    public function on ( $listenerId, $callable ) {

        $this->_on->attach($listenerId, $callable);

        return $this;
    }

    /**
     * Run the watcher.
     *
     * Listenable events:
     *     • new, when a file is new, i.e. found by the finder;
     *     • modify, when a file has been modified;
     *     • move, when a file has moved, i.e. no longer found by the finder.
     *
     * @access  public
     * @return  void
     */
    public function run ( ) {

        $iterator = $this->getIterator();
        $previous = iterator_to_array($iterator);
        $current  = $previous;

        while(true) {

            foreach($current as $name => $c) {

                if(!isset($previous[$name])) {

                    $this->_on->fire(
                        'new',
                        new \Hoa\Core\Event\Bucket([
                            'file' => $c
                        ])
                    );

                    continue;
                }

                if(null === $c->getHash()) {

                    unset($current[$name]);

                    continue;
                }

                if($previous[$name]->getHash() != $c->getHash())
                    $this->_on->fire(
                        'modify',
                        new \Hoa\Core\Event\Bucket([
                            'file' => $c
                        ])
                    );

                unset($previous[$name]);
            }

            foreach($previous as $p)
                $this->_on->fire(
                    'move',
                    new \Hoa\Core\Event\Bucket([
                        'file' => $p
                    ])
                );

            usleep($this->getLatency() * 1000000);

            $previous = $current;
            $current  = iterator_to_array($iterator);
        }

        return;
    }

    /**
     * Set latency.
     *
     * @access  public
     * @param   int  $latency    Latency (in seconds).
     * @return  int
     */
    public function setLatency ( $latency ) {

        $old            = $this->_latency;
        $this->_latency = $latency;

        return $old;
    }

    /**
     * Get latency.
     *
     * @access  public
     * @return  int
     */
    public function getLatency ( ) {

        return $this->_latency;
    }
}
