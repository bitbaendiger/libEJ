<?PHP

  require_once ('libEJ/Transaction.php');
  
  class libEJ_Transaction_Unknown extends libEJ_Transaction {
    private $Debug = array ();
   
    // {{{ setDebug
    /**
     * Store some debugging-info on this unknown transaction-type
     * 
     * @param string $Key
     * @param mixed $Value
     * 
     * @access public
     * @return void
     **/
    public function setDebug ($Key, $Value) {
      $this->Debug [$Key] = $Value;
    }
    // }}}
    
    // {{{ getDebugInfo
    /**
     * Retrive all debug-information from this transaction
     * 
     * @access public
     * @return array
     **/
    public function getDebugInfo () {
      return $this->Debug;
    }
    // }}}
  }

?>