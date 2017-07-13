<?PHP

  require_once ('libEJ/Transaction.php');
  require_once ('libEJ/Record/Entry.php');
  
  class libEJ_Transaction_Normal extends libEJ_Transaction {
    // {{{ getEntries
    /**
     * Retrive all entries from this transaction
     * 
     * @access public
     * @return array
     **/
    public function getEntries () {
      $Entries = array ();
      
      foreach ($this as $Record)
        if ($Record instanceof libEJ_Record_Entry)
          $Entries [] = $Record;
      
      return $Entries;
    }
    // }}}
    
    // {{{ getEntriesTotal
    /**
     * Retrive the total price of all entries
     * 
     * @param bool $Force (optional) Force a result even if there is no subtotal-record
     * 
     * @access public
     * @return float
     **/
    public function getEntriesTotal ($Force = false) {
      // Find the subtotal-record and calculate checksum
      $Check = 0.00;
      $Sum = null;
      
      foreach ($this as $Record)
        if ($Record instanceof libEJ_Record_Entry)
          $Check += $Record->getCount () * $Record->getPrice ();
        elseif ($Record instanceof libEJ_Record_Subtotal)
          $Sum = $Record->getAmount ();
      
      // Make sure the sum is correct
      if (($Sum !== null) && (abs ($Check - $Sum) > 0.01))
        trigger_error ('Checksum does not match subtotal: check ' . $Check . ' given ' . $Sum);
      
      // Check if a return-value is forced
      elseif (($Sum === null) && $Force)
        $Sum = $Check;
      
      return $Sum;
    }
    // }}}
    
    // {{{ getTotal
    /**
     * Retrive the total amount of this transaction
     * 
     * @param bool $Force (optional) Force a result even if there is no total-record
     * 
     * @access public
     * @return float
     **/
    public function getTotal ($Force = false) {
      // Find total and generate checksum
      $Check = 0.00;
      $Sum = null;
      
      foreach ($this as $Record)
        if ($Record instanceof libEJ_Record_Entry)
          $Check += $Record->getCount () * $Record->getPrice ();
        elseif (($Record instanceof libEJ_Record_Total) && !is_subclass_of ($Record, 'libEJ_Record_Total'))
          $Sum = $Record->getPrice ();
      
      // Make sure the sum is correct
      if (($Sum !== null) && (abs ($Check - $Sum) > 0.01))
        trigger_error ('Checksum does not match total: check ' . $Check . ' given ' . $Sum);
      
      // Check if a return-value is forced
      elseif (($Sum === null) && $Force)  
        $Sum = $Check;
      
      return $Sum;
    }
    // }}}
    
    // {{{ getTotalGiven
    /**
     * Retrive the amount of money given by the customer
     * 
     * @access public
     * @return float
     **/
    public function getTotalGiven () {
      foreach ($this as $Record) 
        if (($Record instanceof libEJ_Record_Total) && is_subclass_of ($Record, 'libEJ_Record_Total'))
          return $Record->getPrice ();
    }
    // }}}
    
    // {{{ getTotalMethod
    /**
     * Retrive the method by which this transaction was paid
     * 
     * @access public
     * @return string
     **/
    public function getTotalMethod () {
      foreach ($this as $Record) 
        if (($Record instanceof libEJ_Record_Total) && is_subclass_of ($Record, 'libEJ_Record_Total'))
          return substr (get_class ($Record), 19);
    }
    // }}}
    
    // {{{ getChange
    /**
     * Retrive the amount of money returned to the customer
     * 
     * @access public
     * @return float
     **/
    public function getChange () {
      foreach ($this as $Record)
        if ($Record instanceof libEJ_Record_Change)
          return $Record->getValue ();
    }
    // }}}
        
    // {{{ getTaxes
    /**
     * Retrive all tax-records from this transaction
     * 
     * @access public
     * @return array
     **/
    public function getTaxes () {
      $Taxes = array ();
      
      foreach ($this as $Record)
        if ($Record instanceof libEJ_Record_Tax)
          $Taxes [$Record->getGroup ()] = $Record;
      
      return $Taxes;
    }
    // }}}
  }

?>