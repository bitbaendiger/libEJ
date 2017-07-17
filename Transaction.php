<?PHP

  abstract class libEJ_Transaction implements ArrayAccess, IteratorAggregate {
    // Meta-Data
    private $Timestamp = 0x00;
    private $ID = 0x000000;
    private $User = 0x00;
    
    // Bits
    private $isCopy = false;
    private $isTraining = false;
    
    // All records
    private $Records = array ();
    
    // {{{ __toString
    /**
     * Convert this transaction into a string
     * 
     * @access friendly
     * @return string
     **/
    function __toString () {
      $Result = sprintf ('#%06d   %s' . "\n" . '%02d                     000000' . "\n", $this->ID, date ('d/m/Y   H:i', $this->Timestamp), $this->User);
      
      foreach ($this->Records as $Record)
        $Result .= strval ($Record) . "\n";
      
      return $Result;
    }
    // }}}
    
    // {{{ getTimestamp
    /**
     * Retrive the timestamp from this transaction
     * 
     * @access public
     * @return int
     **/
    public function getTimestamp () {
      return $this->Timestamp;
    }
    // }}}
    
    // {{{ setTimestamp
    /**
     * Set the timestamp of this transaction
     * 
     * @param int $Timestamp
     * 
     * @access public
     * @return void
     **/
    public function setTimestamp ($Timestamp) {
      if (is_numeric ($Timestamp))
        $this->Timestamp = intval ($Timestamp);
      else
        $this->Timestamp = strtotime ($Timestamp);
    }
    // }}}
    
    // {{{ getID
    /**
     * Retrive the number of this transaction
     * 
     * @access public
     * @return int
     **/
    public function getID () {
      return $this->ID;
    }
    // }}}
    
    // {{{ setID
    /**
     * Set the number of this transaction
     * 
     * @param int $ID
     * 
     * @access public
     * @return void
     **/
    public function setID ($ID) {
      $this->ID = intval ($ID);
    }
    // }}}
    
    // {{{ getUserID
    /**
     * Retrive the ID of the user who made this transaction
     * 
     * @access public
     * @return int
     **/
    public function getUserID () {
      return $this->User;
    }
    // }}}
    
    // {{{ setUserID
    /**
     * Set the ID of the user who made this transaction
     * 
     * @param int $User
     * 
     * @access public
     * @return void
     **/
    public function setUserID ($User) {
      $this->User = intval ($User);
    }
    // }}}
    
    // {{{ isCopy
    /**
     * Check if this transaction is the copy of another one
     * 
     * @access public
     * @return bool
     **/
    public function isCopy () {
      return $this->isCopy;
    }
    // }}}
    
    // {{{ setCopy
    /**
     * Set the copy-bit of this transaction
     * 
     * @param bool $Toggle
     * 
     * @access public
     * @return void
     **/
    public function setCopy ($Toggle) {
      $this->isCopy = ($Toggle == true);
    }
    // }}}
    
    // {{{ isTraining
    /**
     * Check if this transaction was made for training purposes
     * 
     * @access public
     * @return bool
     **/
    public function isTraining () {
      return $this->isTraining;
    }
    // }}}
    
    // {{{ setTraining
    /**
     * Set the training-bit of this transaction
     * 
     * @param bool $Toggle
     * 
     * @access public
     * @return void
     **/
    public function setTraining ($Toggle) {
      $this->isTraining = ($Toggle == true);
    }
    // }}}
    
    // {{{ offsetExists
    /**
     * Check if a record exists on our collection
     * 
     * @param mixed $Offset
     * 
     * @access public
     * @return bool
     **/
    public function offsetExists ($Offset) {
      return isset ($this->Records [$Offset]);
    }
    // }}}
    
    // {{{ offsetGet
    /**
     * Retrive a record from our collection
     * 
     * @param mixed $Offset
     * 
     * @access public
     * @return object
     **/
    public function offsetGet ($Offset) {
      if (!isset ($this->Records [$Offset]))
        return null;
      
      return $this->Records [$Offset];
    }
    // }}}
    
    // {{{ offsetSet
    /**
     * Append/Insert a record into our collection
     * 
     * @param mixed $Offset
     * @param object $Value
     * 
     * @access public
     * @return void
     **/
    public function offsetSet ($Offset, $Value) {
      // Check the type
      if (!($Value instanceof libEJ_Record)) {
        trigger_error ('This collection only carries records of type libEJ_Record');
        
        return null;
      }
      
      // Append to our collection
      if ($Offset === null)
        $this->Records [] = $Value;
      else
        $this->Records [$Offset] = $Value;
    }
    // }}}
    
    // {{{ offsetUnset
    /**
     * Remove a record from our collection
     * 
     * @param mixed $Offset
     * 
     * @access public
     * @return void
     **/
    public function offsetUnset ($Offset) {
      unset ($this->Records [$Offset]);
    }
    // }}}
    
    // {{{ getIterator
    /**
     * Retrive an object to iterate through our records
     * 
     * @access public
     * @return object
     **/
    public function getIterator () {
      return new ArrayIterator ($this->Records);
    }
    // }}}
  }

?>