<?PHP

  interface libEJ_Interface_Record_Price {
    // {{{ getPrice
    /**
     * Retrive the total price of this record
     * 
     * @access public
     * @return float
     **/
    public function getPrice ();
    // }}}
    
    // {{{ setPrice
    /**
     * Store the total price for this record
     * 
     * @param float $Price
     * 
     * @access public
     * @return void
     **/
    public function setPrice ($Price);
    // }}}
  }

?>