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
      $allowed = $this->getConf('allowed');
      if($test && isset($USERINFO) && in_array('admin', $USERINFO['grps'])) {         
          $tests = explode(',',$test);
          foreach ($tests as $test) {           
              $test = trim($test);  
              if(!$this->is_allowed($allowed, $test)) {
                  msg("$test is not a valid IP");
              }    
               else  msg("$test is a valid IP");         
          }           
          return;          
      } 
    
      if($ACT == 'login' && !$this->is_allowed($allowed, $ip)) {
              if($this->getConf('log')) {
              $this->log($ip); 
              header("HTTP/1.0 403 Forbidden");           
              exit("<div style='text-align:center; padding-top:2em;'><h1>403: Login Not Available</h1></div>");
      }
        } 
    } 
    
     function is_allowed($allowed, $ip) {
         static $cache = '';     
         
         if($cache) {
              $allowed = $cache;              
         }
         else {
         $allowed = trim($allowed,', ');       
         $allowed = preg_quote($allowed);
         $allowed=str_replace(array(' ', ','), array("",'|'),$allowed);                   
             $cache = $allowed;                   
         }
          
         if(!$allowed || preg_match("/^" . $allowed . "$/", $ip) ) {    // if allowed string is empty then all ips are allowed                    
               return true;
        }
        return false;  
     }
     
     function log($ip) {         
        $log = metaFN('abortlogin:aborted_ip','.log');   
        io_saveFile($log,"$ip\n",1);
     }
}
?>
