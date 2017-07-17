<?PHP

  require_once ('libEJ/Interface/Parser.php');
  
  require_once ('libEJ/Transaction/Normal.php');
  require_once ('libEJ/Transaction/OPX.php');
  require_once ('libEJ/Transaction/OPZ.php');
  require_once ('libEJ/Transaction/X1.php');
  require_once ('libEJ/Transaction/Z1.php');
  require_once ('libEJ/Transaction/None.php');
  require_once ('libEJ/Transaction/Unknown.php');
  
  require_once ('libEJ/Record/Entry.php');
  require_once ('libEJ/Record/Text.php');
  
  class libEJ_Parser_Plain implements libEJ_Interface_Parser {
    /* Input-Buffer */
    private $Buffer = '';
    
    /* Input-Resource */
    private $fp = null;
    
    /* Name of input */
    private $fn = '<stdin>';
    
    // {{{ __construct
    /**
     * Create a new EJ-Parser for EJ-Files only containing plain-text
     * 
     * @param mixed $Filename
     * 
     * @access friendly
     * @return void
     **/
    function __construct ($Filename) {
      // Setup input
      if (is_resource ($Filename)) {
        $this->fp = $Filename;
        $this->fn = '<stdin>';
      } elseif (is_resource ($this->fp = @fopen ($Filename, 'r')))
        $this->fn = $Filename;
      else
        throw new Exception ('Could not open file');
      
      // Find first transaction
      do {
        $Record = $this->getRecord (false);
      } while (strlen (trim ($Record)) == 0);
      
      if (strlen (trim ($Record)) == 0) {
        trigger_error ('No transactions on file');
        $this->getRecord ();
      } else
        $this->Buffer = $Record . "\r\n" . $this->Buffer;
    }
    // }}}
    
    // {{{ getRecord
    /**
     * Read a single raw record from buffer/input
     * 
     * @access private
     * @return string
     **/
    private function getRecord ($Cleanup = true) {
      // Check our buffer
      if (($p = strpos ($this->Buffer, "\r\n")) !== false) {
        $rc = substr ($this->Buffer, 0, $p);
        $this->Buffer = substr ($this->Buffer, $p + 2);
        
        return $rc;
      }
      
      // Make sure input is valid
      if (!$this->fp)
        return false;
      
      // Try to fill the buffer
      while (!feof ($this->fp)) {
        if (($data = fread ($this->fp, 4096)) === false)
          break;
        
        $l = max (1, strlen ($this->Buffer));
        $this->Buffer .= $data;
        
        if (($p = strpos ($this->Buffer, "\r\n", $l - 1)) !== false) {
          $rc = substr ($this->Buffer, 0, $p);
          $this->Buffer = substr ($this->Buffer, $p + 2);
          
          return $rc;
        }
      }
      
      // Forward the whole buffer
      $rc = $this->Buffer;
      
      if ($Cleanup) {
        fclose ($this->fp);
        $this->Buffer = false;
        $this->fp = false;
      }
      
      return $rc;
    }
    // }}}
    
    // {{{ getTransaction
    /**
     * Retrive all records from an entire transaction
     * 
     * @access public
     * @return object
     **/
    public function getTransaction () {
      // Try to read the header of the next transaction
      if ((($Record1 = $this->getRecord ()) === false) ||
          (($Record2 = $this->getRecord ()) === false))
        return false;
      
      // Try to read the whole transaction
      static $TransactionClassMap = array (
        '* O P X *'     => 'libEJ_Transaction_OPX',
        '* O P Z *'     => 'libEJ_Transaction_OPZ',
        '* P G M *'     => 'libEJ_Transaction_Unknown',
        '* S D *'       => 'libEJ_Transaction_Unknown',
        '* X 1 *'       => 'libEJ_Transaction_X1',
        '* X 2 *'       => 'libEJ_Transaction_Unknown',
        '* Z 1 *'       => 'libEJ_Transaction_Z1',
        'KEIN VERKAUF'  => 'libEJ_Transaction_None',
        'HILFE-AUSWAHL' => 'libEJ_Transaction_Unknown',
      );
      
      $TransactionClass = 'libEJ_Transaction_Normal';
      $Records = array ();
      
      while ($Record = $this->getRecord ()) {
        // Remove white-space from record
        $Record = trim ($Record);
        
        if (strlen ($Record) == 0)
          break;
        
        // Try to detect type of transaction
        if (count ($Records) == 2) {
          /**
           * Known but unhandled:
           * 13 LOGOTEXT PROGRAMMIEREN
           * # NUMMER0000000000XXXXXX
           **/
          
          // Check for a direct mapping
          if (isset ($TransactionClassMap [$Record])) {
            $TransactionClass = $TransactionClassMap [$Record];
            
            continue;
          }
        }
        
        // Treat this as comment/unhandled
        if ($Record [0] == '#')
          continue;
        
        $Records [] = $Record;
      }
      
      // Create a new transaction
      $Transaction = new $TransactionClass;
      
      if (sscanf ($Record1, '#%d %d/%d/%d %d:%d', $ID, $Day, $Month, $Year, $Hour, $Minute) != 6)
        return false;
      
      $Transaction->setID ($ID);
      $Transaction->setTimestamp (mktime ($Hour, $Minute, 0, $Month, $Day, $Year));
      $Transaction->setUserID (intval ($Record2)); // TODO: Record2 contains also username and an unknown number at end
      
      // Post-Process Records
      foreach ($Records as $Text) {
        if (sscanf ($Text, '%dx %s *%f %s', $Count, $Text, $Price, $Tax) == 4) {
          $Record = new libEJ_Record_Entry ($Transaction);
          $Record->setCount ($Count);
          $Record->setTitle ($Text);
          $Record->setPrice ($Price);
          $Record->setTaxID ($Tax);
        // TODO: Parse more formats here
        } else {
          $Record = new libEJ_Record_Text ($Transaction);
          $Record->setText ($Text);
        }
        
        $Transaction [] = $Record;
      }
      
      return $Transaction;
    }
    // }}}
  }

?>