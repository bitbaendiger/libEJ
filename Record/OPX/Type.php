<?PHP

  require_once ('libEJ/Record/Integer.php');
  
  class libEJ_Record_OPX_Type extends libEJ_Record_Integer {
    const TYPE_USER = 0x01;
    const TYPE_USERS = 0x02;
    const TYPE_PRODUCTGROUPS = 0x03;
    const TYPE_TRANSACTION = 0x04;
    const TYPE_JOURNAL = 0x05;
    
    // {{{ __toString
    /**
     * Create a string from this object
     * 
     * @access public
     * @return string
     **/
    function __toString () {
      switch ($this->getValue ()) {
        case self::TYPE_USER:
          return 'Bediener';
        case self::TYPE_USERS:
          return 'Alle Bediener';
        case self::TYPE_PRODUCTGROUPS:
          return 'Warengruppen';
        case self::TYPE_TRANSACTION:
          return 'Transaktion';
        case self::TYPE_JOURNAL:
          return 'Journal';
        
        default:
          return 'Unknown OPX-Type';
      }
    }
    // }}}
  }

?>