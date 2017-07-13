<?PHP

  require_once ('libEJ/Transaction.php');
  
  class libEJ_Transaction_Model extends libEJ_Transaction {
    // {{{ getModel
    /**
     * Retrive the model referenced by this transaction-record
     * 
     * @access public
     * @return string
     **/
    public function getModel () {
      foreach ($this as $Record)
        if ($Record instanceof libEJ_Record_Version)
          return $Record->getModel ();
    }
    // }}}
    
    // {{{ getVersion
    /**
     * Retrive the version of the model referenced by this transaction-record
     * 
     * @access public
     * @return string
     **/
    public function getVersion () {
      foreach ($this as $Record)
        if ($Record instanceof libEJ_Record_Version)
          return $Record->getVersion ();
    }
    // }}}
  }

?>