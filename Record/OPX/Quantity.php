<?PHP

  require_once ('libEJ/Record/Integer.php');
  require_once ('libEJ/Interface/Record/Count.php');
  require_once ('libEJ/Interface/Record/Titled.php');
  
  class libEJ_Record_OPX_Quantity extends libEJ_Record_Integer implements libEJ_Interface_Record_Count, libEJ_Interface_Record_Titled {
    private $Count = 0;
    private $Title = '';
    
    // {{{ __toString
    /**
     * Create a string from this object
     * 
     * @access public
     * @return string
     **/
    function __toString () {
      return $this->getTitle () . ' / ' . $this->getCount ();
    }
    // }}}
    
    // {{{ getCount
    /**
     * Retrive the counter-value of this record
     * 
     * @access public
     * @return int
     **/
    public function getCount () {
      return $this->getValue ();
    }
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
    public function setCount ($Count) {
      return $this->setValue ($Count);
    }
    // }}}
    
    // {{{ getTitle
    /**
     * Retrive the title of this record
     *  
     * @access public
     * @return string
     **/
    public function getTitle () {
      return $this->Title;
    }
    // }}}
    
    // {{{ setTitle
    /**
     * Store the title of this record
     * 
     * @param string $Title
     *  
     * @access public
     * @return void
     **/
    public function setTitle ($Title) {
      $this->Title = $Title;
    }
    // }}}
  }

?>