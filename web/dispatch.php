<?PHP

  // Add custom include-paths
  set_include_path (dirname (realpath (dirname (__FILE__))) . ':../../../lib:../../libs/qc/:' . get_include_path ());
  
  // Setup REST
  require_once ('qcEvents/Base.php');
  require_once ('qcREST/Controller/Native.php');
  require_once ('qcREST/Processor/JSON.php');
  require_once ('qcREST/Resource.php');
  require_once ('qcREST/Resource/Collection.php');
  
  $Base = qcEvents_Base::singleton ();
  
  $Controller = new qcREST_Controller_Native;
  $Controller->addProcessor (new qcREST_Processor_JSON);
  
  // Setup Root-Collection for REST
  require_once ('libEJ/Parser/Sharp.php');
  require_once ('libEJ/REST/Transaction.php');
  
  $RootCollection = new qcREST_Resource_Collection (null, true, false, false);
  $Controller->setRootElement (new qcREST_Resource ('EJ-Webservice', array (), true, false, false, $RootCollection));
  
  if (is_object ($d = @dir ('files/'))) {
    while ($f = $d->read ())
      if (($f [0] != '.') && is_file ('files/' . $f)) {
        $Collection = new qcREST_Resource_Collection (null, true, false, false, true);
        $Collection->addChildCallback (function (qcREST_Resource_Collection $Self, $Name = null, array $Children, callable $Callback, $Private = null) use ($f) {
          // Check if the file has already been parsed
          if (count ($Children) > 0)
            return call_user_func ($Callback, $Private);
          
          // Create an EJ-Parser for this file
          $Parser = new libEJ_Parser_Sharp (dirname (realpath (__FILE__)) . '/files/' . $f);
          
          while ($Transaction = $Parser->getTransaction ())
            $Self->addChild (new libEJ_REST_Transaction ($Transaction));
          
          // Just raise the callback
          call_user_func ($Callback, $Private);
        });
        
        $RootCollection->addChild (new qcREST_Resource (substr ($f, 0, strpos ($f, '.')), array (), true, false, false, $Collection), $f);
      }
    
    $d->close ();
  }
  
  // Try to process the request
  $Controller->handle (
    function (qcREST_Interface_Controller $Self, qcREST_Interface_Request $Request = null, qcREST_Interface_Response $Response = null, $Status) {
      if (!$Request)
        trigger_error ('Failed to retrive a request');
      elseif (!$Response)
        trigger_error ('Failed to create response');
      elseif (!$Status)
        trigger_error ('Request could not be finished');

      exit ();
    }
  );

  $Base->loop ();

?>