<?PHP

  interface libEJ_Interface_Record_Amount {
    // {{{ getAmount
    /**
     * Retrive the total amount of money of this record
     * 
     * @access public
     * @return float
     **/
    public function getAmount ();
    // }}}
    
    // {{{ setAmount
    /**
     * Store the total amount of money for this record
     * 
     * @param float $Amount
     * 
     * @access public
     * @return void
     **/
    public function setAmount ($Amount);
    // }}}
  }

?>