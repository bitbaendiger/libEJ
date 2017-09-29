<?PHP

  require_once ('libEJ/Interface/Parser.php');
  
  require_once ('libEJ/Transaction.php');
  require_once ('libEJ/Transaction/None.php');
  require_once ('libEJ/Transaction/Model.php');
  require_once ('libEJ/Transaction/Reset.php');
  require_once ('libEJ/Transaction/Normal.php');
  require_once ('libEJ/Transaction/Storno.php');
  require_once ('libEJ/Transaction/OPX.php');
  require_once ('libEJ/Transaction/OPZ.php');
  require_once ('libEJ/Transaction/X1.php');
  require_once ('libEJ/Transaction/Z1.php');
  require_once ('libEJ/Transaction/Unknown.php');
  
  require_once ('libEJ/Record/Text.php');
  require_once ('libEJ/Record/Unknown.php');
  require_once ('libEJ/Record/Version.php');
  require_once ('libEJ/Record/Entry.php');
  require_once ('libEJ/Record/NoSale.php');
  require_once ('libEJ/Record/Subtotal.php');
  require_once ('libEJ/Record/Subtotal/Storno.php');
  require_once ('libEJ/Record/Tax.php');
  require_once ('libEJ/Record/Total.php');
  require_once ('libEJ/Record/Total/Cash.php');
  require_once ('libEJ/Record/Total/Credit.php');
  require_once ('libEJ/Record/Total/Cheque.php');
  require_once ('libEJ/Record/Total/Netto.php');
  require_once ('libEJ/Record/Change.php');
  require_once ('libEJ/Record/OPX/Type.php');
  require_once ('libEJ/Record/OPX/User.php');
  require_once ('libEJ/Record/OPX/Sum.php');
  require_once ('libEJ/Record/OPX/Quantity.php');
  require_once ('libEJ/Record/OPX/SumQuantity.php');
  require_once ('libEJ/Record/OPX/ProductGroup.php');
  
  if (!extension_loaded ('mbstring') && !dl ('mbstring.so'))
    return;
  
  class libEJ_Parser_Sharp implements libEJ_Interface_Parser {
    private $fn = '';
    private $fp = null;
    private $buffer = '';
    private $line = 0;
    
    public $Status = array ();
    
    // {{{ __construct
    /**
     * Create a new EJ-Parser for Sharp Cash-Registers with a well-formed binary header
     * 
     * @param mixed $Filename
     * 
     * @access friendly
     * @return void
     **/
    function __construct ($Filename) {
      if (is_resource ($Filename)) {
        $this->fp = $Filename;
        $this->fn = '<stdin>';
      } elseif (!is_resource ($this->fp = @fopen ($Filename, 'r')))
        throw new Exception ('Could not open file');
      else
        $this->fn = $Filename;
      
      $this->line = 0;
    }
    // }}}
    
    // {{{ getRecord
    /**
     * Read a single raw record from buffer/input
     * 
     * @access private
     * @return string
     **/
    private function getRecord () {
      if (!$this->fp)
        return false;
      
      if (($p = strpos ($this->buffer, "\r\n")) !== false) {
        $rc = substr ($this->buffer, 0, $p);
        $this->buffer = substr ($this->buffer, $p + 2);
        $this->line++;
        
        return $rc;
      }
      
      while (!feof ($this->fp)) {
        if (($data = fread ($this->fp, 4096)) === false)
          break;
        
        $l = max (1, strlen ($this->buffer));
        $this->buffer .= $data;
        
        if (($p = strpos ($this->buffer, "\r\n", $l - 1)) !== false) {
          $rc = substr ($this->buffer, 0, $p);
          $this->buffer = substr ($this->buffer, $p + 2);
          $this->line++;
          
          return $rc;
        }
      }
      
      fclose ($this->fp); 
      $rc = $this->buffer;
      $this->buffer = false;
      $this->fp = false;
      $this->line++;
      
      if (strlen ($rc) == 0)
        $rc = false;
      
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
      if (($header = $this->getRecord ()) === false)
        return false;
      
      // Parse known fields on the header
      $Type = ord ($header [0]);
      $Subtype = (ord ($header [22]) << 8) + ord ($header [23]);
      $Timestamp = mktime (intval (bin2hex ($header [5])), intval (bin2hex ($header [6])), 0, intval (bin2hex ($header [2])), intval (bin2hex ($header [3])), 2000 + intval (bin2hex ($header [1])));
      $ID = bin2hex (substr ($header, 9, 4));
      $User = bin2hex ($header [13]);
      $Length = (ord ($header [28]) << 8) + ord ($header [29]); 
      
      if (strlen ($header) >= 32)
        $Length2 = (ord ($header [30]) << 8) + ord ($header [31]);
      else
        $Length2 = 0;
      
      // Handle bits
      $isAction = (($Subtype & 0x0001) == 0x0001);
      $isCopy = (($Subtype & 0x1000) == 0x1000);
      $isTraining = (($Subtype & 0x4000) == 0x4000);
      
      // Store unknown bits      
      $Byte5 = $header [4];
      $Zeros8 = substr ($header, 7, 2);
      $Unknown21 = substr ($header, 20, 2);
      $Unknown25 = substr ($header, 24, 4);
      
      // Retrive all remaining records
      $Records = array ();
      
      for ($i = 1; $i < $Length; $i++)
        if (($Record = $this->getRecord ()) === false) {
          trigger_error ('Record-Read failed in ' . $this->fn);
          
          return false;
        } else
          $Records [] = $Record;
      
      // Process/Remove pressed keys  
      $keyBlock = '';
      $keys = array ();
      
      for ($i = count ($Records) - 1; ($i > 0) && ($Length2 > 0); $i--)
        // Ignore text-records that might appear at the end.
        if (substr ($Records [$i], -2, 2) != "\xFF\XFF") {
          $keyBlock = $Records [$i] . $keyBlock;
          $Length2--;
          unset ($Records [$i]);
        }
      
      // Skip the first two records
      $TransTime = array_shift ($Records);
      $UnknownHead = array_shift ($Records);
      # TODO: We might extract the username from $UnknownHead
      
      // Determine the type of transaction and create a handle
      static $Types = array (
        0x00 => 'libEJ_Transaction_Normal',
        # 0x01 => 'libEJ_Transaction_Setup',
        0x03 => 'libEJ_Transaction_OPX',
        0x06 => 'libEJ_Transaction_Reset',
        0x09 => 'libEJ_Transaction_Model',
        # 0x12 => 'libEJ_Transaction_PGM',
        0x13 => 'libEJ_Transaction_X1',
        # 0x23 => ???
        0x80 => 'libEJ_Transaction_Storno',
        0x83 => 'libEJ_Transaction_OPZ',
        # 0x92 => 'libEJ_Transaction_PGM',
        0x93 => 'libEJ_Transaction_Z1',
      );
      
      if (!isset ($Types [$Type])) {
        $Handle = new libEJ_Transaction_Unknown;
        $Handle->setDebug ('Type', $Type);
        $Handle->setDebug ('Subtype', $Type);
        $Handle->setDebug ('isAction', $isAction);
        $Handle->setDebug ('Byte5', bin2hex ($Byte5));
        $Handle->setDebug ('Zeros8', bin2hex ($Zeros8));
        $Handle->setDebug ('Unknown21', bin2hex ($Unknown21));
        $Handle->setDebug ('Unknown25', bin2hex ($Unknown25));
      } else
        $Handle = new $Types [$Type];
      
      if (!isset ($this->Status [$Type]))
        $this->Status [$Type] = array ();
      
      $Handle->setID ($ID);
      $Handle->setTimestamp ($Timestamp);
      $Handle->setUserID ($User);
      $Handle->setTraining ($isTraining);
      $Handle->setCopy ($isCopy);
      
      // Handle all records
      static $rTypes = array (
        #0x010D => 'libEJ_Record_Total_Netto',
        
        // General types
        0xFFFF => 'libEJ_Record_Text',
        
        // Seen on 0x00 / Normal transaction
        0x0100 => 'libEJ_Record_Entry',
        0x0200 => 'libEJ_Record_Entry',
        # 0x0401 => TODO! Check this one!
        0x0800 => 'libEJ_Record_Subtotal',
        0x0D01 => 'libEJ_Record_Tax',
        0x0D02 => 'libEJ_Record_Tax',
        0x0D03 => 'libEJ_Record_Tax',
        0x0D04 => 'libEJ_Record_Tax',
        0x1000 => 'libEJ_Record_Total',
        0x1201 => 'libEJ_Record_Total_Cash',
        0x1401 => 'libEJ_Record_Total_Credit',
        0x1501 => 'libEJ_Record_Change',
        0x1B00 => 'libEJ_Record_Unknown',
        0x1C00 => 'libEJ_Record_NoSale', // KEIN VERKAUF
        0x2600 => 'libEJ_Record_Subtotal_Storno',
        0x2610 => 'libEJ_Record_Total', // Total on training
        0x2701 => 'libEJ_Record_Total_Cheque',
        0x2901 => 'libEJ_Record_Tax',
        0x2902 => 'libEJ_Record_Tax',
        0x2903 => 'libEJ_Record_Tax',
        0x2904 => 'libEJ_Record_Tax',
        0x5101 => 'libEJ_Record_Tax',
        0x5102 => 'libEJ_Record_Tax',
        0x5103 => 'libEJ_Record_Tax',
        0x5104 => 'libEJ_Record_Tax',
        
        // Seen on 0x01 / Setup
        0x0019 => 'libEJ_Record_Unknown', // DATUM/UHRZEIT
        0x0500 => 'libEJ_Record_Unknown', // FISKAL DEUTSCHLAND
        
        // Seen on 0x03 / OPX
        0x0016 => 'libEJ_Record_OPX_Type', // IND BEDIENER
        0x00A0 => 'libEJ_Record_OPX_Type', // E-JOURNAL -> 
        
        0x0471 => 'libEJ_Record_OPX_User', // CLK# 01 Olly
        0x0472 => 'libEJ_Record_OPX_User', // CLK# 02 Olly
        0x0473 => 'libEJ_Record_OPX_User', // CLK# 03 Rosi
        0x0474 => 'libEJ_Record_OPX_User', // CLK# 04 Renate   
        0x0475 => 'libEJ_Record_OPX_User', // CLK# 05 Regina
        0x0476 => 'libEJ_Record_OPX_User', // CLK# 06 Regina
        0x0477 => 'libEJ_Record_OPX_User', // CLK# 07 Train
        
        # Floats
        0x3100 => 'libEJ_Record_OPX_Sum', // GES.BESTELLT *0.101
        0x3200 => 'libEJ_Record_OPX_Sum', // GES. BEZAHLT *0.102
        0x7100 => 'libEJ_Record_OPX_Sum', // BAR IN LADE  *0.10
        0x7200 => 'libEJ_Record_OPX_Sum', // BAR/SCHK LAD *0.10
        0xF318 => 'libEJ_Record_OPX_Sum', // DURSCHNITT   *0.05
        
        0x4000 => 'libEJ_Record_OPX_SumQuantity', // RETOURE      [1] Q - Followed by text -> Float
        0x4101 => 'libEJ_Record_OPX_SumQuantity', // STORNO       [4] Q - Followed by text -> Float
        0x4200 => 'libEJ_Record_OPX_SumQuantity', // STORNO       [1] Q - Followed by text -> Float   
        0x4300 => 'libEJ_Record_OPX_SumQuantity', // MGR-STORNO   [1] Q - Followed by text -> Float  
        0x4400 => 'libEJ_Record_OPX_SumQuantity', // ZWS-STORNO   [1] Q - Followed by text -> Float
        0x5000 => 'libEJ_Record_OPX_Quantity',    // KUNDEN       [2] Q
        0x7001 => 'libEJ_Record_OPX_SumQuantity', // BAR          [2] Q - Followed by text -> Float
        0x9001 => 'libEJ_Record_OPX_SumQuantity', // KREDIT 1     [1] Q - Followed by text -> Float
        
        // Seen on 0x06 / Reset
        0x0109 => 'libEJ_Record_Unknown', // GESAMT RAM SICH
        0x0201 => 'libEJ_Record_Unknown', // Unknown, appears on reset
        0x0502 => 'libEJ_Record_Unknown', // ORDNER WÄHLEN + 1 Text
        
        // Seen on 0x09 / Version
        0x0321 => 'libEJ_Record_Version',
        
        // Seen on 0x12 / PGM
        0x0002 => 'libEJ_Record_Unknown', // WARENGRUPPEN
        # 0x0016 => see 0x03
        0x0018 => 'libEJ_Record_Unknown', // BEDIENER
        # 0x0019 => see 0x01
        # 0x0023 => TODO! Check this one
        0x0024 => 'libEJ_Record_Unknown', // MEHRWERTSTEUER
        0x0025 => 'libEJ_Record_Unknown', // MWST 1  7.0000 %
        0x0026 => 'libEJ_Record_Unknown', // MWST 2 19.0000 %
        0x0027 => 'libEJ_Record_Unknown', // MWST 3  0.0000 %
        0x0028 => 'libEJ_Record_Unknown', // MWST 4  0.0000 %
        0x002A => 'libEJ_Record_Unknown', // AUTOTASTEN
        0x0039 => 'libEJ_Record_Unknown', // MANAGER
        0x003C => 'libEJ_Record_Unknown', // TRAININGSBEDIENEN
        
        // Seen on 0x13 / X1 (extends 0x03 / OPX)
        0x0001 => 'libEJ_Record_Text', // *X1* - Caption  
        
        # Type
        0x0003 => 'libEJ_Record_OPX_Type', // WARENGR./GRUPPEN (Allgemein)
        0x0006 => 'libEJ_Record_OPX_Type', // ARTIKEL !
        0x0007 => 'libEJ_Record_OPX_Type', // NACH WARENGRUPPE
        0x0010 => 'libEJ_Record_OPX_Type', // TRANSAKTION
        0x0015 => 'libEJ_Record_OPX_Type', // ALLE BEDIENER !
        # 0x0016 => see 0x03 (IND BEDIENER)
        0x001B => 'libEJ_Record_OPX_Type', // STUNDENBERICHT !
        0x0020 => 'libEJ_Record_OPX_Type', // TISCHE !
        0x0021 => 'libEJ_Record_OPX_Type', // NACH BEDIENER !
        # 0x00A0 => see 0x03 (E-JORUNAL) !
        
        0x0401 => 'libEJ_Record_OPX_ProductGroup', // D01      [3.000] Q - Followed by text [text float]
        0x0402 => 'libEJ_Record_OPX_ProductGroup', // D02      3.000 Q - Followed by 2x text
        0x0403 => 'libEJ_Record_OPX_ProductGroup', // D03      3.000 Q - Followed by 2x text
        0x0404 => 'libEJ_Record_OPX_ProductGroup', // D04      3.000 Q - Followed by 2x text
        0x0405 => 'libEJ_Record_OPX_ProductGroup', // D05      3.000 Q - Followed by 2x text
        0x0406 => 'libEJ_Record_OPX_ProductGroup', // D06      3.000 Q - Followed by 2x text
        0x0407 => 'libEJ_Record_OPX_ProductGroup', // D07      3.000 Q - Followed by 2x text
        0x0408 => 'libEJ_Record_OPX_ProductGroup', // D08      3.000 Q - Followed by 2x text
        0x0409 => 'libEJ_Record_OPX_ProductGroup', // D09      3.000 Q - Followed by 2x text
        0x040A => 'libEJ_Record_OPX_ProductGroup', // D10      3.000 Q - Followed by 2x text
        0x040B => 'libEJ_Record_OPX_ProductGroup', // D11      3.000 Q - Followed by 2x text
        0x040C => 'libEJ_Record_OPX_ProductGroup', // D12      3.000 Q - Followed by 2x text
        0x040D => 'libEJ_Record_OPX_ProductGroup', // D13      1.000 Q - Followed by 2x text
        0x040E => 'libEJ_Record_OPX_ProductGroup', // D14      1.000 Q - Followed by 2x text
        0x040F => 'libEJ_Record_OPX_ProductGroup', // D15      1.000 Q - Followed by 2x text
        0x0410 => 'libEJ_Record_OPX_ProductGroup', // D16      1.000 Q - Followed by 2x text
        0x0411 => 'libEJ_Record_OPX_ProductGroup', // D17      1.000 Q - Followed by 2x text
        0x0412 => 'libEJ_Record_OPX_ProductGroup', // D18      1.000 Q - Followed by 2x text
        0x0413 => 'libEJ_Record_OPX_ProductGroup', // D19      1.000 Q - Followed by 2x text
        0x0414 => 'libEJ_Record_OPX_ProductGroup', // D20      1.000 Q - Followed by 2x text
        
        # 0x047X => see 0x03
        0x2001 => 'libEJ_Record_OPX_Sum', // MWST 1 ZWS  *0.10
        0x2002 => 'libEJ_Record_OPX_Sum', // MWST 2 ZWS  *5.00
        0x2101 => 'libEJ_Record_OPX_Sum', // MWST 7%     *0.01
        0x2102 => 'libEJ_Record_OPX_Sum', // MWST 19%    *0.80 
        0x3001 => 'libEJ_Record_OPX_Sum', // NETTO 1     *0.100
        # 0x3100 => see 0x03
        # 0x3200 => see 0x03
        # 0x4000 => see 0x03
        # 0x4101 => see 0x03
        # 0x4200 => see 0x03
        # 0x4300 => see 0x03
        # 0x4400 => see 0x03
        # 0x5000 => see 0x03
        0x5100 => 'libEJ_Record_OPX_Quantity', // KEIN VERKAUF  1 Q
        0x5900 => 'libEJ_Record_OPX_Quantity', // RECHG ZÄHLER  1 Q
        # 0x7001 => see 0x03
        # 0x7100 => see 0x03
        # 0x7200 => see 0x03
        0x8100 => 'libEJ_Record_OPX_Sum', // SCHECK LADE   *0.10
        0x8301 => 'libEJ_Record_OPX_SumQuantity', // BAR/SCHECK    1 Q - Followed by text
        # 0x9001 => see 0x03
        0xF301 => 'libEJ_Record_OPX_SumQuantity', // WGR GEAMT  3.000 Q - Followed by 2x text
        0xF30F => 'libEJ_Record_Unknown', // GESAMT
        # 0xF318 => see 0x03
        0xF32A => 'libEJ_Record_OPX_Sum', // MWST GESAMT *0.01
        0xF356 => 'libEJ_Record_OPX_Sum', // NETTO       *0.09
        
        0xF501 => 'libEJ_Record_OPX_SumQuantity', // GRUPPE 01  3.000 Q - Followed by 2x text
        0xF502 => 'libEJ_Record_OPX_SumQuantity', // GRUPPE 02  3.000 Q - Followed by 2x text
        0xF503 => 'libEJ_Record_OPX_SumQuantity', // Lotto -   -3.000 Q - Followed by text
        
        // Seen on 0x83 / OPZ (extends 0x03 / OPX)
        # 0x0016 => see 0x03
        # 0x047# => see 0x03
        # 0x3100 => see 0x03
        # 0x3200 => see 0x03
        # 0x4000 => see 0x03
        # 0x4101 => see 0x03
        # 0x4200 => see 0x03
        # 0x4300 => see 0x03
        # 0x4400 => see 0x03
        # 0x5000 => see 0x03
        # 0x7001 => see 0x03
        # 0x7100 => see 0x03
        # 0x7200 => see 0x03
        # 0x8100 => see 0x13
        # 0x8301 => see 0x13
        # 0x9001 => see 0x03
        # 0xF318 => see 0x03
        
        // Seen on 0x92
        # 0x0002 => see 0x012
        # 0x0003 => see 0x13
        0x0005 => 'libEJ_Record_Unknown', // ARTIKELBEREICH   
        0x000C => 'libEJ_Record_Unknown', // ZAHLUNGSARTEN
        0x000E => 'libEJ_Record_Unknown', // SCHECK 1    L0.00 00
        0x000F => 'libEJ_Record_Unknown', // SCHECK 2    L0.00 00
        # 0x0010 => see 0x13
        0x0011 => 'libEJ_Record_Unknown', // KREDIT 2    L0.00 00
        # 0x0018 => see 0x12
        # 0x0019 => see 0x01
        0x001D => 'libEJ_Record_Unknown', // DRUCKAUSWAHL
        0x0022 => 'libEJ_Record_Unknown', // BONLOGO
        0x0023 => 'libEJ_Record_Unknown', // GERÄTE CONFIG
        # 0x002A => see 0x12
        0x0033 => 'libEJ_Record_Unknown', // TERMINAL
        # 0x0039 => see 0x12
        0x003A => 'libEJ_Record_Unknown', // FESTRATE    MÖGLICH - Followed by text
                                          // OFFENE RATE MÖGLICH - Followed by text
        # 0x003C => see 0x12
        0x003D => 'libEJ_Record_Unknown', // MODUS PASSWORT
        0x003E => 'libEJ_Record_Unknown', // BASISEINSTELLUNG - Followed by text
        0x0040 => 'libEJ_Record_Unknown', // FUNKTIONEN ERL
        # 0x0301 => TODO! Check this one
        # 0x0302 => TODO! Check this one
        # 0x0303 => TODO! Check this one
        # 0x0304 => TODO! Check this one
        
        // Seen on 0x93 / Z1 (extends 0x13 / X1)
        # 0x0001 => see 0x13 / Caption
        # 0x0003 => see 0x13
        # 0x0006 => see 0x13
        # 0x0010 => see 0x13
        # 0x0015 => see 0x13
        # 0x001B => see 0x13
        0x0301 => 'libEJ_Record_Unknown', // GT1  *00000000.10
        0x0302 => 'libEJ_Record_Unknown', // GT2  *00000000.10
        0x0303 => 'libEJ_Record_Unknown', // GT3  *00000000.00  
        0x0304 => 'libEJ_Record_Unknown', // TR   *00000100.65
        0x0305 => 'libEJ_Record_Unknown', // SAL  *00000000.00
        0x0310 => 'libEJ_Record_Unknown', // Z1 0001
        0x03FC => 'libEJ_Record_Unknown', // 2D273184-97F3EC30-ABE6A6D7 + Text
        # 0x0401 => see 0x13
        # 0x0402 => see 0x13
        # 0x0404 => see 0x13
        # 0x0405 => see 0x13
        # 0x0406 => see 0x13
        # 0x0407 => see 0x13
        # 0x0408 => see 0x13
        # 0x0409 => see 0x13
        # 0x040A => see 0x13
        # 0x040B => see 0x13
        # 0x040C => see 0x13
        # 0x040D => see 0x13
        # 0x040E => see 0x13
        # 0x040F => see 0x13
        # 0x0410 => see 0x13
        # 0x0411 => see 0x13
        # 0x0412 => see 0x13
        # 0x0413 => see 0x13
        # 0x047# => see 0x03
        # 0x2001 => see 0x13
        # 0x2002 => see 0x13
        # 0x2101 => see 0x13
        # 0x2102 => see 0x13
        # 0x3001 => see 0x13
        # 0x3100 => see 0x03
        # 0x3200 => see 0x03
        # 0x4000 => see 0x03
        # 0x4101 => see 0x03
        # 0x4200 => see 0x03
        # 0x4300 => see 0x03
        # 0x4400 => see 0x03
        # 0x5000 => see 0x03
        # 0x5100 => see 0x13
        # 0x5900 => see 0x13
        # 0x7001 => see 0x03
        # 0x7100 => see 0x03
        # 0x7200 => see 0x03
        # 0x8100 => see 0x13
        # 0x8301 => see 0x13
        # 0x9001 => see 0x13
        # 0xF301 => see 0x13
        # 0xF30F => see 0x13
        # 0xF318 => see 0x03
        # 0xF32A => see 0x13
        # 0xF356 => see 0x13
        # 0xF501 => see 0x13
        # 0xF502 => see 0x13
        # 0xF503 => see 0x13
      );
      $lHandle = null;
      
      while (count ($Records) > 0) {
        // Retrive the next record
        $Record = array_shift ($Records);
        
        // Retrive the type of the record
        if (strlen ($Record) >= 32)
          $rType = (ord ($Record [30]) << 8) + ord ($Record [31]);
        else
          $rType = 0;
        
        if (isset ($this->Status [$Type][$rType]))
          $this->Status [$Type][$rType]++;
        else
          $this->Status [$Type][$rType] = 1;
        
        // Special handling for tax-records
        if ((($rType > 0x0D00) && ($rType < 0x0D05)) ||
            (($rType > 0x2900) && ($rType < 0x2905)) ||
            (($rType > 0x5100) && ($rType < 0x5105))) {
          $TaxGroup = $rType & 0xFF;
          $rHandle = null;
          
          // Try to find an existing tax-record
          foreach ($Handle as $R)
            if (($R instanceof libEJ_Record_Tax) && ($R->getGroup () == $TaxGroup)) {
              $rHandle = $R;
              break;
            }
          
          // Create a new tax-record
          if (!is_object ($rHandle)) {
            $Handle [] = $rHandle = new libEJ_Record_Tax ($Handle);
            $rHandle->setGroup ($TaxGroup);
          }
          
          // Handle depending on type
          if ($rType > 0x5100) {
            $rHandle->setAmount ($this->readFinalPrice ($Record));
            
            if (($p2 = strpos ($Record, '%')) !== false)
              for ($p = $p2 - 1; $p > 0; $p--)
                if ($Record [$p] == ' ') {
                  $rHandle->setRate (floatval (substr ($Record, $p + 1, $p2 - $p - 1)));
                  break;
                }
          } else
            $rHandle->setValue ($this->readFinalPrice ($Record), (($rType & 0xFF00) == 0x2900));
          
          continue;
        // Check if the current type is mapped
        } elseif (!isset ($rTypes [$rType]) || !class_exists ($rTypes [$rType])) {
          trigger_error ('Unknown record-type 0x' . dechex ($rType) . ' on ' . $ID . ' / ' . $this->fn);
          
          continue;
        // Create a new handle of mapped type
        } else
          $rHandle = new $rTypes [$rType] ($Handle);
        
        // Handle entries
        if ($rHandle instanceof libEJ_Record_Entry) {
          // Extract human readable value
          $Info = $this->readString ($Record);
          
          // Read the Amount-Counter
          if (($p = strpos ($Info, 'x')) === false) {
            trigger_error ('Missing count-marker on record: ' . $Info . ' / ' . $this->fn . ' / ' . $ID);
            $Handle [] = $rHandle;
            
            continue;
          }
          
          $rHandle->setCount (intval (substr ($Info, 0, $p)));
          
          // Read the single price of this item
          $p += 2;
          
          if (($p > strlen ($Info)) || (($s = strpos ($Info, ' ', $p)) === false)) {
            # trigger_error ('Missing space after single price on record: ' . $Info . ' / ' . $this->fn . ' / ' . $ID);
            
            # continue;
            $s = strlen ($Info);
          } else
            $rHandle->setPrice (floatval (substr ($Info, $p, $s - $p)));
          
          // Retrive tax-id
          $Info = trim (substr ($Info, $s));
          
          if (($s = strpos ($Info, ' ')) !== false) {
            $taxIndex = ord (substr ($Info, $s + 1)) - 64;
            $Info = substr ($Info, 0, $s);
          } else
            $taxIndex = null;
          
          $rHandle->setTaxID ($taxIndex);
          
          // Retrive flags and final price
          /*
          if (($p = strpos ($Info, '*')) !== false) {
            $flags = substr ($Info, 0, $p);
            $finalPrice = floatval (substr ($Info, $p + 1));
          } elseif (($p = strpos ($Info, '-')) !== false) { 
            $flags = substr ($Info, 0, $p);
            $finalPrice = floatval (substr ($Info, $p));
          } else {
            trigger_error ('Missing final price on record: ' . $this->readString ($Record) . ' / ' . $this->fn . ' / ' . bin2hex (substr ($header, 9, 4)));
            continue;
          }
          */
        }
        
        // Handle total values
        if (($rHandle instanceof libEJ_Record_Total) ||
            ($rHandle instanceof libEJ_Record_Subtotal) ||
            ($rHandle instanceof libEJ_Record_Subtotal_Storno) ||
            ($rHandle instanceof libEJ_Record_Change))
          $rHandle->setValue ($this->readFinalPrice ($Record));
        
        // Handle text-records
        if ($rHandle instanceof libEJ_Record_Text) {
          $rHandle->setText ($this->readString ($Record));
          
          if ($rHandle instanceof libEJ_Record_Unknown)
            $rHandle->setDebug ('Type', $rType);
          
          // Push this text-object back to last entry
          if (($lHandle instanceof libEJ_Record_Entry) && !is_subclass_of ($rHandle, 'libEJ_Record_Text')) {
            $lHandle->setTitle ($rHandle->getText ());
            $lHandle = $rHandle;
            
            continue;
          } elseif ($lHandle instanceof libEJ_Record_OPX_SumQuantity) {
            $lHandle->setValue ($this->readFinalPrice ($Record));
            $lHandle->setTitle ($lHandle->getTitle () . ' ' . substr ($rHandle->getText (), 0, strrpos ($rHandle->getText (), ' ')));
            $lHandle = $rHandle;
            
            continue;
          }
        
        // Handle OPX
        } elseif ($rHandle instanceof libEJ_Record_OPX_Type) {
          static $OPX_Types = array (
            0x0003 => libEJ_Record_OPX_Type::TYPE_PRODUCTGROUPS,
            0x0010 => libEJ_Record_OPX_Type::TYPE_TRANSACTION,
            0x0015 => libEJ_Record_OPX_Type::TYPE_USERS,
            0x0016 => libEJ_Record_OPX_Type::TYPE_USER,
            0x001B => libEJ_Record_OPX_Type::TYPE_HOURS,
            0x00A0 => libEJ_Record_OPX_Type::TYPE_JOURNAL,
          );
          
          if (isset ($OPX_Types [$rType]))
            $rHandle->setValue ($OPX_Types [$rType]);
          else
            trigger_error ('Unknown OPX-Type ' . $rType);
        
        } elseif ($rHandle instanceof libEJ_Record_OPX_User) {
          $Info = $this->readString ($Record);
          
          if (substr ($Info, 0, 4) == 'CLK#') {
            $rHandle->setValue (substr ($Info, 4, 2));
            $rHandle->setUsername (substr ($Info, 7));
          } else
            trigger_error ('Unknown User-Signature: ' . $Info);
        } elseif (($rHandle instanceof libEJ_Record_OPX_Quantity) || ($rHandle instanceof libEJ_Record_OPX_SumQuantity)) {
          $Info = $this->readString ($Record);
          
          if (substr ($Info, -1, 1) == 'Q') {
            $Info = substr ($Info, 0, -2);
            $rHandle->setCount (substr ($Info, $p = strrpos ($Info, ' ')));
            $rHandle->setTitle (rtrim (substr ($Info, 0, $p)));
            
            if ($rHandle instanceof libEJ_Record_OPX_ProductGroup)
              $rHandle->setGroupID (substr ($rHandle->getTitle (), 1));
          } else
            trigger_error ('Unknown Counter-Value: ' . $Info);
        } elseif ($rHandle instanceof libEJ_Record_OPX_Sum) {
          $Info = $this->readString ($Record);
          
          $rHandle->setValue ($this->readFinalPrice ($Record));
          $rHandle->setTitle (rtrim (substr ($Info, 0, strrpos ($Info, ' '))));
        }
        
        $Handle [] = $rHandle;
        $lHandle = $rHandle;
      }
      
      return $Handle;
    }
    // }}}
    
    // {{{ readString
    /**
     * Convert a human readable string
     * 
     * @param string $Data
     * 
     * @access private
     * @return string
     **/
    private function readString ($Data) {
      // Truncate last bytes
      $Data = substr ($Data, 0, -2);
      
      // Remove bold-formatting
      $Data = str_replace ("\xFF", '', $Data);
      
      // Convert to UTF-8
      $Data = mb_convert_encoding ($Data, 'UTF-8', 'CP850');
      
      return trim ($Data);
    }
    // }}}
    
    // {{{ readFinalPrice
    /**
     * Extract the final price from a given string
     * 
     * @param string $Data
     * 
     * @access private
     * @return float
     **/
    private function readFinalPrice ($Data) {
      $Data = $this->readString ($Data);
      
      if (($p = strpos ($Data, '*')) !== false)
        $p += 1;
      elseif (($p = strrpos ($Data, '-')) === false)
        return false;
      
      return floatval (substr ($Data, $p));
    }
    // }}}
  }

?>