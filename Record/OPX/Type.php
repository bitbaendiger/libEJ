<?PHP

  require_once ('libEJ/Record/Integer.php');
  
  class libEJ_Record_OPX_Type extends libEJ_Record_Integer {
    const TYPE_USER = 0x01;
    const TYPE_USERS = 0x02;
    const TYPE_PRODUCTGROUPS = 0x03;
    const TYPE_TRANSACTION = 0x04;
    const TYPE_JOURNAL = 0x05;
    
    const TYPE_PRODUCTS = 0x06;
    const TYPE_BY_PRODUCTGROUPS = 0x07;
    const TYPE_HOURS = 0x1b;
    const TYPE_TABLES = 0x20;
    const TYPE_BY_USER = 0x21;
    
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
        case self::TYPE_PRODUCTS:
          return 'Artikel';
        case self::TRYP_BY_PRODUCTGROUPS:
          return 'Nach Warengruppen';
        case self::TYPE_TRANSACTION:
          return 'Transaktion';
        case self::TYPE_JOURNAL:
          return 'Journal';
        case self::TYPE_HOURS:
          return 'Stundenbericht';
        case self::TYPE_TABLES:
          return 'Tische';
        case self::TYPE_BY_USER:
          return 'Nach Bediener';
        
        default:
          return 'Unknown OPX-Type';
      }
    }
    // }}}
  }

?>