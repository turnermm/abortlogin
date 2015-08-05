<?php
/**
 * Action adding DW Edit button to page tools (useful with fckedit)
 *
 * @author     Anonymous
 * @author     Myron Turner
 */

if (!defined('DOKU_INC')) 
{    
    die();
}

class action_plugin_abortlogin extends DokuWiki_Action_Plugin
{

    function register(&$controller)
    {
        $controller->register_hook('DOKUWIKI_STARTED', 'BEFORE', $this, 'dw_start');
    }
    
    function dw_start(&$event, $param)
    {
      global $ACT, $INFO, $USERINFO ;

      $ip = $_SERVER['REMOTE_ADDR'];
      $test = $this->getConf('test');
      if($test && isset($USERINFO) && in_array('admin', $USERINFO['grps'])) {         
          if(!$this->is_allowed($test, $ip)) {
              msg("Test: current ip ($ip) does not match $test");
          }           
          return;          
      } 
    
   
      if($ACT == 'login' && !$this->is_allowed($this->getConf('allowed'), $ip)) {
              $this->log($ip); 
              header("HTTP/1.0 403 Forbidden");           
              exit("<div style='text-align:center; padding-top:2em;'><h1>403: Login Not Available</h1></div>");
      }
      
    } 
    
     function is_allowed($allowed, $ip) {
         $allowed = trim($allowed,', ');       
         $allowed = preg_quote($allowed);
         $allowed=str_replace(array(' ', ','), array("",'|'),$allowed);                   
         if(!$allowed || preg_match("/(^" . $allowed . "$)/", $ip) ) {    // if allowed string is empty then all ips are allowed                    
               return true;
        }
        return false;  
     }
     
     function log($ip) {         
        $handle=fopen('fckg.log','a');
        fwrite($handle, "$ip\n");
        fclose($handle);
     }
}
?>
