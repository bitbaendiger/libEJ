<?PHP

  require_once ('libEJ/Record/Text.php');
  
  class libEJ_Record_Version extends libEJ_Record_Text {
    // {{{ getModel
    /**
     * Retrive the model from this version
     * 
     * @access public
     * @return string
     **/
    public function getModel () {
      // Retrive the text from the model
      $Text = $this->getText ();
      
      // Find the first space
      if (($p = strpos ($Text, ' ')) === false)
        return $Text;
      
      // Return everything before that space
      return substr ($Text, 0, $p);
    }
    // }}}
    
    // {{{ getVersion
    /**
     * Retrive the version-string of this record
     * 
     * @access public
     * @return string
     **/
    public function getVersion () {
      // Retrive the text from the model
      $Text = $this->getText ();
      
      // Find the first space
      if (($p = strpos ($Text, ' ')) === false)
        return $Text;
      
      // Return everything after that space
      $Version = trim (substr ($Text, $p + 1));
      
      if (substr ($Version, 0, 3) == 'Ver')
        $Version = substr ($Version, 3);
      
      return $Version;
    }
    // }}}
  }

?>