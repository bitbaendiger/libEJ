<?PHP

  require_once ('libEJ/Record/Float.php');
  require_once ('libEJ/Interface/Record/Amount.php');
  
  class libEJ_Record_Subtotal extends libEJ_Record_Float implements libEJ_Interface_Record_Amount {
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