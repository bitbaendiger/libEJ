<?PHP

  interface libEJ_Interface_Record_Titled {
    // {{{ getTitle
    /**
     * Retrive the title of this record
     * 
     * @access public
     * @return string
     **/
    public function getTitle ();
    // }}}
    
    // {{{ setTitle
    /**
     * Store the title for this record
     * 
     * @param string $Title
     * 
     * @access public
     * @return void
     **/
    public function setTitle ($Title);
    // }}}
  }

?>