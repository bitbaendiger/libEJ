<?PHP

  abstract class libEJ_Record {
    /* Instance of the transaction we are assigned to */
    private $Transaction = null;
    
    // {{{ __construct
    /**
     * Create a new transaction-record
     * 
     * @param libEJ_Transaction $Transaction
     * 
     * @access freindly
     * @return void
     **/
    function __construct (libEJ_Transaction $Transaction) {
      $this->Transaction = $Transaction;
    }
    // }}}
  }

?>