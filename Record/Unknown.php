<?PHP

  require_once ('libEJ/Record/Text.php');
  
  class libEJ_Record_Unknown extends libEJ_Record_Text {
    private $Debug = array ();
    
    // {{{ setDebug
    /**
     * Store some debugging-info on this unknown record
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
     * Retrive all debug-information from this record
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