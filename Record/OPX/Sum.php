<?PHP

  require_once ('libEJ/Record/Float.php');
  require_once ('libEJ/Interface/Record/Amount.php');
  require_once ('libEJ/Interface/Record/Titled.php');
  
  class libEJ_Record_OPX_Sum extends libEJ_Record_Float implements libEJ_Interface_Record_Amount, libEJ_Interface_Record_Titled {
    private $Title = '';
    
    // {{{ __toString
    /**
     * Create a string from this object
     * 
     * @access public
     * @return string
     **/
    function __toString () {
      return $this->getTitle () . ' / ' . $this->getValue ();
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
    
    // {{{ getAmount
    /**
     * Retrive the total amount of money of this record
     * 
     * @access public
     * @return float
     **/
    public function getAmount () {
      return parent::getValue ();
    }
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
    public function setAmount ($Amount) {
      return parent::setValue ($Amount);
    }
    // }}}
  }

?>