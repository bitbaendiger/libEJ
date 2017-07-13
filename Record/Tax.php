<?PHP

  require_once ('libEJ/Record.php');
  
  class libEJ_Record_Tax extends libEJ_Record {
    private $Group = 0;
    private $Rate = 0.00;
    private $Amount = 0.00;
    private $Value = 0.00;
    
    // {{{ __toString
    /**
     * Create a simple string from this record
     * 
     * @access friendly
     * @return string
     **/
    function __toString () {
      return $this->Group . ' ' . $this->Rate . '% ' . $this->Value . ' / ' . $this->Amount;
    }
    // }}}
    
    // {{{ getGroup
    /**
     * Retrive the assigned tax-group-number
     * 
     * @access public
     * @return int
     **/
    public function getGroup () {
      return $this->Group;
    }
    // }}}
    
    // {{{ setGroup
    /**
     * Assign a new tax-group-number
     * 
     * @param int $Group
     * 
     * @access public
     * @return void
     **/
    public function setGroup ($Group) {
      $this->Group = intval ($Group);
    }
    // }}}
    
    // {{{ getRate
    /**
     * Retrive the actual tax-rate of this record
     * 
     * @access public
     * @return float
     **/
    public function getRate () {
      return $this->Rate;
    }
    // }}}
    
    // {{{ setRate
    /**
     * Store a new tax-rate for this record
     * 
     * @param float $Rate
     * 
     * @access public
     * @return void
     **/
    public function setRate ($Rate) {
      $this->Rate = floatval ($Rate);
    }
    // }}}
    
    // {{{ getAmount
    /**
     * Retrive the amount of tax payed
     * 
     * @access public
     * @return float
     **/
    public function getAmount () {
      return $this->Amount;
    }
    // }}}
    
    // {{{ setAmount
    /**
     * Store a new amount of tax payed
     * 
     * @param float $Amount
     * 
     * @access public
     * @return void
     **/
    public function setAmount ($Amount) {
      $this->Amount = floatval ($Amount);
    }
    // }}}
    
    // {{{ getValue
    /**
     * Retrive the total value referenced by this tax-rate
     * 
     * @access public
     * @return float
     **/
    public function getValue () {
      return $this->Value;
    }
    // }}}
    
    // {{{ setValue
    /**
     * Store a new total value referenced by this tax-rate
     * 
     * @param float $Value
     * 
     * @access public
     * @return void
     **/
    public function setValue ($Value) {
      $this->Value = floatval ($Value);
    }
    // }}}
  }

?>