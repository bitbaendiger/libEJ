<?PHP

  interface libEJ_Interface_Record_Count {
    // {{{ getCount
    /**
     * Retrive the counter-value of this record
     * 
     * @access public
     * @return int
     **/
    public function getCount ();
    // }}}
    
    // {{{ setCount
    /**
     * Set the counter of this record
     * 
     * @param int $Count
     * 
     * @access public
     * @return void
     **/
    public function setCount ($Count);
    // }}}
  }

?>