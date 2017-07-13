<?PHP

  require_once ('libEJ/Record/Float.php');
  require_once ('libEJ/Interface/Record/Count.php');
  require_once ('libEJ/Interface/Record/Amount.php');
  require_once ('libEJ/Interface/Record/Titled.php');
  
  class libEJ_Record_OPX_SumQuantity extends libEJ_Record_Float implements libEJ_Interface_Record_Count, libEJ_Interface_Record_Amount, libEJ_Interface_Record_Titled {
    private $Count = 0;
    private $Title = null;
    
    // {{{ __toString
    /**
     * Create a string from this object
     * 
     * @access public
     * @return string
     **/
    function __toString () {
      return $this->getTitle () . ' / ' . $this->getCount () . ' / ' . $this->getValue ();
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
      return $this->Count;
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
      $this->Count = intval ($Count);
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