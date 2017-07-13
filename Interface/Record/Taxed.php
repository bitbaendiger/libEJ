<?PHP

  interface libEJ_Interface_Record_Taxed {
    // {{{ getTaxID
    /**
     * Retrive the ID of the tax-value referenced/used by this entry
     * 
     * @access public
     * @return int
     **/
    public function getTaxID ();
    // }}}
    
    // {{{ setTaxID
    /**
     * Set the ID of the referenced tax
     * 
     * @param int $ID
     * 
     * @access public
     * @return void
     **/
    public function setTaxID ($ID);
    // }}}
  }

?>