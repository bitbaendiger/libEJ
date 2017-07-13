<?PHP

  require_once ('libEJ/Record/Integer.php');
  
  class libEJ_Record_OPX_User extends libEJ_Record_Integer {
    private $Username = null;
    
    // {{{ __toString
    /**
     * Create a string from this object
     * 
     * @access public
     * @return string
     **/
    function __toString () {
      return $this->getValue () . ' / ' . $this->getUsername ();
    }
    // }}}
    
    // {{{ setUsername
    /**
     * Store a username on this record
     * 
     * @param string $Username
     * 
     * @access public
     * @return void
     **/
    public function setUsername ($Username) {
      $this->Username = $Username;
    }
    // }}}
    
    // {{{ getUsername
    /**
     * Retrive the username from this record
     * 
     * @access public
     * @return string
     **/
    public function getUsername () {
      return $this->Username;
    }
    // }}}
  }

?>