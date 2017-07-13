<?PHP

  require_once ('libEJ/Record.php');
  
  class libEJ_Record_Text extends libEJ_Record {
    private $Text = '';
    
    // {{{ __toString
    /**
     * Convert this object into a human readable string
     * 
     * @access friendly
     * @return string
     **/
    function __toString () {
      return $this->getText ();
    }
    // }}}
    
    // {{{ getText
    /**
     * Retrive the text on this object
     * 
     * @access public
     * @return string
     **/
    public function getText () {
      return $this->Text;
    }
    // }}}
    
    // {{{ setText
    /**
     * Set the text of this record
     * 
     * @param string $Text
     * 
     * @access public
     * @return void
     **/
    public function setText ($Text) {
      $this->Text = strval ($Text);
    }
    // }}}
  }

?>