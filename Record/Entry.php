<?PHP

  require_once ('libEJ/Record.php');
  require_once ('libEJ/Interface/Record/Price.php');
  require_once ('libEJ/Interface/Record/Count.php');
  require_once ('libEJ/Interface/Record/Taxed.php');
  require_once ('libEJ/Interface/Record/Titled.php');
  
  class libEJ_Record_Entry extends libEJ_Record implements libEJ_Interface_Record_Count, libEJ_Interface_Record_Price, libEJ_Interface_Record_Taxed, libEJ_Interface_Record_Titled {
    /* Number of items referenced by this entry */
    private $Count = 0;
    
    /* Total price of this entry */
    private $Price = 0;
    
    /* ID of referenced tax */
    private $TaxID = null;
    
    /* Title for this entry */
    private $Title = '';
    
    // {{{ __toString
    /**
     * Convert this record into a simple string
     * 
     * @access friendly
     * @return string
     **/
    function __toString () {
      return $this->Count . 'x ' . $this->Title . ' / ' . $this->Price . ' '. $this->TaxID;
    }
    // }}}
    
    // {{{ getCount
    /**
     * Retrive the number of items referenced
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
     * Store the number for referenced items
     * 
     * @param int $Counter
     * 
     * @access public
     * @return void
     **/
    public function setCount ($Counter) {
      $this->Count = intval ($Counter);
    }
    // }}}
    
    // {{{ getPrice
    /**
     * Retrive the total price of this entry
     * 
     * @access public
     * @return float
     **/
    public function getPrice () {
      return $this->Price;
    }
    // }}}
    
    // {{{ setPrice
    /**
     * Store the total price for this entry
     * 
     * @param float $Price
     * 
     * @access public
     * @return void
     **/
    public function setPrice ($Price) {
      $this->Price = floatval ($Price);
    }
    // }}}
    
    // {{{ getTaxID
    /**
     * Retrive the ID of the tax-value referenced/used by this entry
     * 
     * @access public
     * @return int
     **/
    public function getTaxID () {
      return $this->TaxID;
    }
    // }}}
    
    // {{{ setTaxID
    /**
     * Set the ID of the referenced tax
     * 
     * @param mixed $ID Number or letter describing the Tax-ID
     * 
     * @access public
     * @return void
     **/
    public function setTaxID ($ID) {
      if ($ID !== null) {
        if ((strlen ($ID) == 1) && (ord ($ID) > 64) && (ord ($ID) < 91))
          $ID = ord ($ID) - 64;
        else
          $ID = intval ($ID);
      }
      
      $this->TaxID = $ID;
    }
    // }}}
    
    // {{{ getTitle
    /**
     * Retrive the title of this entry
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
     * Store a title for this entry
     * 
     * @param string $Title
     * 
     * @access public
     * @return void
     **/
    public function setTitle ($Title) {
      $this->Title = strval ($Title);
    }
    // }}}
  }

?>