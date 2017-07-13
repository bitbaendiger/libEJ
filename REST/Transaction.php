<?PHP

  require_once ('qcREST/Interface/Resource.php');
  require_once ('qcREST/Resource/Collection.php');
  require_once ('qcREST/Representation.php');
  require_once ('libEJ/REST/Record.php');
  
  class libEJ_REST_Transaction implements qcREST_Interface_Resource {
    /* The Transaction we are referenced to */
    private $Transaction = null;
    
    /* The Rest-Collection for recrods */
    private $Collection = null;
    
    /* Include Records in Representation */
    private $RepresentationIncludeRecords = true;
    
    // {{{ __construct
    /**
     * Create a new REST-Representation of a libEJ-Transaction
     * 
     * @param libEJ_Transaction $Transaction
     * 
     * @access friendly
     * @return void
     **/
    function __construct (libEJ_Transaction $Transaction) {
      $this->Transaction = $Transaction;
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
      return $this->Transaction->getID ();
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
      return true;
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
      // Make sure we have a collection
      if ($this->Collection === null) {
        $this->Collection = new qcREST_Resource_Collection (null, true, false, false, true);
        
        foreach ($this->Transaction as $ID=>$Record)
          $this->Collection->addChild (new libEJ_REST_Record ($ID, $Record));
      }
      
      // Return the collection to our callee
      call_user_func ($Callback, $this, $this->Collection, $Private);
      
      return true;
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
      // Prepare flags for representation
      $Flags = new stdClass;
      $Flags->isCopy = $this->Transaction->isCopy ();
      $Flags->isTraining = $this->Transaction->isTraining ();
      
      // Create representation
      $Representation = new qcREST_Representation (array (
        'ID' => $this->Transaction->getID (),
        'Class' => get_class ($this->Transaction),
        'Date' => date ('Y-m-d H:i:s', $this->Transaction->getTimestamp ()),
        'UserID' => $this->Transaction->getUserID (),
        'Flags' => $Flags,
      ));
      
      // Extend the representation with class-specific
      if ($this->Transaction instanceof libEJ_Transaction_Model) {
        $Representation ['Model'] = $this->Transaction->getModel ();
        $Representation ['Version'] = $this->Transaction->getVersion ();
      } elseif ($this->Transaction instanceof libEJ_Transaction_Normal) {
        $Entries = array ();
        $Taxes = array ();
        
        foreach ($this->Transaction->getEntries () as $Entry)
          $Entries [] = array (
            'Title' => $Entry->getTitle (),
            'Count' => $Entry->getCount (),
            'Price' => $Entry->getPrice (),
            'TaxID' => $Entry->getTaxID (),
          );
        
        foreach ($this->Transaction->getTaxes () as $ID=>$Tax)
          $Taxes [$ID] = array (
            'Group' => $Tax->getGroup (),
            'Rate' => $Tax->getRate (),
            'Amount' => $Tax->getAmount (),
            'Value' => $Tax->getValue (),
          );
        
        $Representation ['Entries'] = $Entries;
        $Representation ['Subtotal'] = $this->Transaction->getEntriesTotal ();
        $Representation ['Taxes'] = $Taxes;
        $Representation ['Total'] = $this->Transaction->getTotal ();
        $Representation ['TotalGiven'] = $this->Transaction->getTotalGiven ();
        $Representation ['TotalMethod'] = $this->Transaction->getTotalMethod ();
        $Representation ['Change'] = $this->Transaction->getChange ();
      }
      
      // Check wheter to include records on collection
      if ($this->RepresentationIncludeRecords)
        return $this->getChildCollection (function (qcREST_Interface_Resource $Self, qcREST_Interface_Collection $Collection = null) use ($Callback, $Representation, $Private) {
          // Check if the collection could be retrived
          if ($Collection === null) {
            call_user_func ($Callback, $this, $Representation, $Private);
            
            return true;
          }
          
          // Retrive all children from the collection
          return $Collection->getChildren (function (qcREST_Interface_Collection $Collection, array $Children = null) use ($Callback, $Representation, $Private) {
            // Append children to result
            if (is_array ($Children)) {
              $Items = array ();
              
              foreach ($Children as $Child)
                $Items [] = $Child->getAttributes ();
              
              $Representation ['Records'] = $Items;
            }
            
            // Return to the callee
            call_user_func ($Callback, $this, $Representation, $Private);
            
            return true;
          });
        });
      
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