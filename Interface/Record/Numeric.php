<?PHP

  interface libEJ_Interface_Record_Numeric {
    // {{{ setValue
    /**
     * Store the value of this record
     * 
     * @param number $Value
     * 
     * @access public
     * @return void
     **/
    public function setValue ($Value);
    // }}}
    
    // {{{ getValue
    /**
     * Retrive the value of this record
     * 
     * @access public
     * @return number
     **/
    public function getValue ();
    // }}}
  }

?>