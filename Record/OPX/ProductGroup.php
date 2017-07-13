<?PHP

  require_once ('libEJ/Record/OPX/SumQuantity.php');
  
  class libEJ_Record_OPX_ProductGroup extends libEJ_Record_OPX_SumQuantity {
    /* Group ID of this product-group */
    private $Group = null;
    
    // {{{ getGroupID
    /**
     * Retrive the group-ID of this product-group
     * 
     * @access public
     * @return int
     **/
    public function getGroupID () {
      return $this->Group;
    }
    // }}}
    
    // {{{ setGroupID
    /**
     * Store the ID of this group
     * 
     * @param int $Group
     * 
     * @access public
     * @return void
     **/
    public function setGroupID ($Group) {
      $this->Group = intval ($Group);
    }
    // }}}
  }

?>