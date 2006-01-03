<?php
// +----------------------------------------------------------------------+
// | PHP version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1998-2004 Manuel Lemos, Tomas V.V.Cox,                 |
// | Stig. S. Bakken, Lukas Smith                                         |
// | All rights reserved.                                                 |
// +----------------------------------------------------------------------+
// | MDB2 is a merge of PEAR DB and Metabases that provides a unified DB  |
// | API as well as database abstraction for PHP applications.            |
// | This LICENSE is in the BSD license style.                            |
// |                                                                      |
// | Redistribution and use in source and binary forms, with or without   |
// | modification, are permitted provided that the following conditions   |
// | are met:                                                             |
// |                                                                      |
// | Redistributions of source code must retain the above copyright       |
// | notice, this list of conditions and the following disclaimer.        |
// |                                                                      |
// | Redistributions in binary form must reproduce the above copyright    |
// | notice, this list of conditions and the following disclaimer in the  |
// | documentation and/or other materials provided with the distribution. |
// |                                                                      |
// | Neither the name of Manuel Lemos, Tomas V.V.Cox, Stig. S. Bakken,    |
// | Lukas Smith nor the names of his contributors may be used to endorse |
// | or promote products derived from this software without specific prior|
// | written permission.                                                  |
// |                                                                      |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS  |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT    |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS    |
// | FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL THE      |
// | REGENTS OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,          |
// | INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, |
// | BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS|
// |  OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED  |
// | AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT          |
// | LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY|
// | WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE          |
// | POSSIBILITY OF SUCH DAMAGE.                                          |
// +----------------------------------------------------------------------+
// | Author: Lukas Smith <smith@backendmedia.com>                         |
// +----------------------------------------------------------------------+
//
// $Id$

/**
 * @package  MDB2
 * @category Database
 * @author   Lukas Smith <smith@backendmedia.com>
 */

class MDB2_Iterator extends MDB2_Result implements Iterator
{
    private $result;
    private $row;
    private $buffer;

    // {{{ constructor

    /**
     * Constructor
     */
    function __construct(&$result)
    {
        $this->result =& $result;
    }

    // }}}
    // {{{ seek()

    /**
    * seek forward to a specific row in a result set
    *
    * @param int    $rownum    number of the row where the data can be found
    * @return mixed MDB2_OK on success, a MDB2 error on failure
    * @access public
    */
    function seek($rownum = 0)
    {
        if ($this->result->rownum == $rownum) {
            return true;
        }
        if ($this->result->rownum < $rownum) {
            return false;
        }
        $this->row = null;
        $this->buffer = null;
        return $this->result->seek($rownum);
    }

    // }}}
    // {{{ next()

    /**
    * Fetch next row of data
    *
    * @return mixed data array on success, a MDB2 error on failure
    * @access public
    */
    function next()
    {
        if ($this->buffer) {
            $this->row = $this->buffer;
            $this->buffer = null;
            return true;
        }
        $row = $this->result->fetchRow();
        if (MDB2::isError($row)) {
            $this->row = null;
            return false;
        }
        $this->row = $row;
        return true;
    }

    // }}}
    // {{{ current()

    /**
     * return a row of data
     *
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
    */
    function current()
    {
        return $this->row;
    }

    // }}}
    // {{{ valid()

    /**
    * check if the end of the result set has been reached
    *
    * @return mixed true or false on sucess, a MDB2 error on failure
    * @access public
    */
    function valid()
    {
        return $this->result->valid();
    }

    // }}}
    // {{{ free()

    /**
     * Free the internal resources associated with result.
     *
     * @return boolean true on success, false if result is invalid
     * @access public
     */
    function free()
    {
        return $this->result->free();
    }

    // }}}
    // {{{ destructor

    /**
     * Destructor
     */
    function __destruct()
    {
        $this->free();
    }

    // }}}
    // {{{ key()

    /**
    * nothing, but Iterator wants to implement this.
    *
    * @return void
    * @access public
    */
    function key()
    {
        $this->result->getRowCount();
    }

    // }}}
    // {{{ rewind()

    /**
    * seek to the first row in a result set
    *
    * @return mixed MDB2_OK on success, a MDB2 error on failure
    * @access public
    */
    function rewind()
    {
    }
}

class MDB2_BufferedIterator extends MDB2_Iterator implements Iterator
{
    // {{{ seek()

    /**
    * seek to a specific row in a result set
    *
    * @param int    $rownum    number of the row where the data can be found
    * @return mixed MDB2_OK on success, a MDB2 error on failure
    * @access public
    */
    function seek($rownum = 0)
    {
        $this->row = null;
        return $this->result->seek($rownum);
    }

    // }}}
    // {{{ valid()

    /**
    * check if the end of the result set has been reached
    *
    * @return mixed true or false on sucess, a MDB2 error on failure
    * @access public
    */
    function valid()
    {
        return $this->result->valid();
    }

    // }}}
    // {{{ next()

    /**
    * Fetch next row of data
    *
    * @return mixed data array on success, a MDB2 error on failure
    * @access public
    */
    function next()
    {
        $row = $this->result->fetchRow();
        if (MDB2::isError($row)) {
            $this->row = null;
            return false;
        }
        $this->row = $row;
        return true;
    }

    // }}}
    // {{{ size()

    /**
     * returns the number of rows in a result object
     *
     * @return mixed MDB2 Error Object or the number of rows
     * @access public
     */
    function size()
    {
        return $this->result->numRows();
    }

    // }}}
    // {{{ hasPrev()

    /**
    * check if there is a previous row
    *
    * @return mixed true or false on sucess, a MDB2 error on failure
    * @access public
    */
    function hasPrev()
    {
        return $this->result->rownum > - 1;
    }

    // }}}
    // {{{ rewind()

    /**
    * seek to the first row in a result set
    *
    * @return mixed MDB2_OK on success, a MDB2 error on failure
    * @access public
    */
    function rewind()
    {
        return $this->seek(0);
    }

    // }}}
    // {{{ prev()

    /**
    * move internal row point to the previous row
    * Fetch and return a row of data
    *
    * @return mixed data array on success, a MDB2 error on failure
    * @access public
    */
    function prev()
    {
        if ($this->hasPrev()) {
            $this->seek($this->result->rownum - 1);
        } else {
            return false;
        }
        return $this->next();
    }
}

?>