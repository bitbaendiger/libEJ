<?PHP

  require_once ('qcREST/Interface/Resource.php');
  require_once ('qcREST/Representation.php');
  
  class libEJ_REST_Record implements qcREST_Interface_Resource {
    /* ID of the record on transaction */
    private $ID = null;
    
    /* The Record we are referenced to */
    private $Record = null;
    
    // {{{ __construct
    /**
     * Create a new REST-Representation of a libEJ-Record
     * 
     * @param int $ID ID of the record on the transaction
     * @param libEJ_Record $Record
     * 
     * @access friendly
     * @return void
     **/
    function __construct ($ID, libEJ_Record $Record) {
      $this->ID = $ID;
      $this->Record = $Record;
    }
    // }}}
    
    // {{{ isReadable
    /**
     * Checks if this resource's attributes might be forwarded to the client
     * 
     * @param qcVCard_Entity $User (optional)
     * 
     * @access public
     * @return bool
     **/
    public function isReadable (qcVCard_Entity $User = null) {
      return true;
    }
    // }}}
    
    // {{{ isWritable
    /**
     * Checks if this resource is writable and may be modified by the client
     * 
     * @param qcVCard_Entity $User (optional)
     * 
     * @access public
     * @return bool
     **/
    public function isWritable (qcVCard_Entity $User = null) {
      return false;
    }
    // }}}
    
    // {{{ isRemovable
    /**
     * Checks if this resource may be removed by the client
     * 
     * @param qcVCard_Entity $User (optional)
     * 
     * @access public
     * @return bool
     **/
    public function isRemovable (qcVCard_Entity $User = null) {
      return false;
    }
    // }}}
    
    
    // {{{ getName
    /**
     * Retrive the name of this resource
     * 
     * @access public
     * @return string
     **/
    public function getName () {
      return $this->ID;
    }
    // }}}
    
    // {{{ hasChildCollection
    /**
     * Determine if this resource as a child-collection available
     * 
     * @access public
     * @return bool
     **/
    public function hasChildCollection () {
      return false;
    }
    // }}}
    
    // {{{ getChildCollection
    /**
     * Retrive a child-collection for this node
     * 
     * @param callable $Callback
     * @param mixed $Private (optional)
     * 
     * The callback will be raised in the form of
     * 
     *   function (qcREST_Interface_Resource $Self, qcREST_Interface_Collection $Collection = null, mixed $Private = null) { }
     * 
     * @access public
     * @return bool
     **/
    public function getChildCollection (callable $Callback, $Private = null) {
      call_user_func ($Callback, $this, null, $Private);
      
      return false;
    }
    // }}}
    
    // {{{ getAttributes
    /**
     * Retrive all REST-Attributes for the assigned record
     * 
     * @access public
     * @return array
     **/
    public function getAttributes () {
      $Attributes = array (
        'Class' => get_class ($this->Record),
        'Value' => strval ($this->Record),
      );
      
      if ($this->Record instanceof libEJ_Record_Version) {
        $Attributes ['Model'] = $this->Record->getModel ();
        $Attributes ['Version'] = $this->Record->getVersion ();
      } elseif ($this->Record instanceof libEJ_Record_Tax) {
        $Attributes ['Group'] = $this->Record->getGroup ();
        $Attributes ['Rate'] = $this->Record->getRate ();
        $Attributes ['Amount'] = $this->Record->getAmount ();
        $Attributes ['ValueTotal'] = $this->Record->getValue ();
      } else {
        if ($this->Record instanceof libEJ_Interface_Record_Titled)
          $Attributes ['Title'] = $this->Record->getTitle ();
        
        if ($this->Record instanceof libEJ_Interface_Record_Amount)
          $Attributes ['Amount'] = $this->Record->getAmount ();
        
        if ($this->Record instanceof libEJ_Interface_Record_Count)
          $Attributes ['Count'] = $this->Record->getCount ();
        
        if ($this->Record instanceof libEJ_Interface_Record_Price)
          $Attributes ['Price'] = $this->Record->getPrice ();
        
        if ($this->Record instanceof libEJ_Interface_Record_Taxed)
          $Attributes ['TaxID'] = $this->Record->getTaxID ();
      }
      
      return $Attributes;
    }
    // }}}
    
    // {{{ getRepresentation
    /**
     * Retrive a representation of this resource
     * 
     * @param callable $Callback A callback to fire once the operation was completed
     * @param mixed $Private (optional) Some private data to pass to the callback
     * 
     * The callback will be raised once the operation was completed in the form of:
     * 
     *   function (qcREST_Interface_Resource $Self, qcREST_Interface_Representation $Representation = null, mixed $Private) { }
     * 
     * @access public
     * @return bool
     **/
    public function getRepresentation (callable $Callback, $Private = null) {
      // Create representation
      $Representation = new qcREST_Representation ($this->getAttributes ());
      
      // Return the representation to our callee
      call_user_func ($Callback, $this, $Representation, $Private);
      
      return true;
    }
    // }}}
    
    // {{{ setRepresentation
    /**
     * Update this resource from a given representation
     * 
     * @param qcREST_Interface_Representation $Representation Representation to update this resource from
     * @param callable $Callback (optional) A callback to fire once the operation was completed
     * @param mixed $Private (optional) Some private data to pass to the callback
     * 
     * The callback will be raised once the operation was completed in the form of:
     * 
     *   function (qcREST_Interface_Resource $Self, qcREST_Interface_Representation $Representation, bool $Status, mixed $Private) { }
     * 
     * @access public
     * @return bool
     **/
    public function setRepresentation (qcREST_Interface_Representation $Representation, callable $Callback = null, $Private = null) {
      if ($Callback)
        call_user_func ($Callback, $this, $Representation, false, $Private);
      
      return false;
    }
    // }}}
    
    // {{{ remove
    /**
     * Remove this resource from the server
     * 
     * @param callable $Callback (optional) A callback to fire once the operation was completed
     * @param mixed $Private (optional) Some private data to pass to the callback
     * 
     * The callback will be raised once the operation was completed in the form of:
     * 
     *   function (qcREST_Interface_Resource $Self, bool $Status, mixed $Private) { }
     * 
     * @access public
     * @return bool
     **/
    public function remove (callable $Callback = null, $Private = null) {
      if ($Callback)
        call_user_func ($Callback, $this, false, $Private);
      
      return false;
    }
    // }}}
  }

?>