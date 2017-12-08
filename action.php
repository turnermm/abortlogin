<?php
/**  
 * Action Plugin to limit logins to selected ip addresses
 * @author  Myron Turner
 * 
 */

if (!defined('DOKU_INC')) 
{    
    die();
}

class action_plugin_abortlogin extends DokuWiki_Action_Plugin
{

    function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook('DOKUWIKI_STARTED', 'BEFORE', $this, 'dw_start');
    }
    
    function dw_start(&$event, $param)
    {
      global $ACT, $INPUT, $USERINFO;

      $ip = $_SERVER['REMOTE_ADDR'];
     
      $u = $INPUT->str('u'); $p=$INPUT->str('p');  $action = $INPUT->post->str('do');
      $test = $this->getConf('test');
      $allowed = $this->getConf('allowed');
    
      if($_REQUEST['do'] =='admin' && empty($_REQUEST['http_credentials']) && empty($USERINFO)) {               
             header("HTTP/1.0 403 Forbidden");           
             exit("<div style='text-align:center; padding-top:2em;'><h1>403: Login Forbidden</h1></div>");
       }  
       
      if( !empty($u) && !empty($p) && $action != 'login'  ) {
              header("HTTP/1.0 403 Forbidden");           
              exit("<div style='text-align:center; padding-top:2em;'><h1>403: Login Forbidden</h1></div>");
      }
      
      
       if( empty($u) && empty($p) && empty($_REQUEST['http_credentials']) && !empty($USERINFO) && !$this->is_allowed($allowed, $ip)){
             unset($USERINFO) ;
             global $ACT;  $ACT = 'logout';          
      }   
    
      if($test && isset($USERINFO) && in_array('admin', $USERINFO['grps'])) {         
          $tests = explode(',',$test);
          foreach ($tests as $test) {           
              $test = trim($test);  
              if(!$this->is_allowed($allowed, $test)) {
                  msg("$test is not a valid IP");
              }    
               else  msg("$test is a valid IP",2);         
          }           
          return;          
      } 
    
      if($ACT == 'login' && !$this->is_allowed($allowed, $ip)) {
              if($this->getConf('log')) {
              $this->log($ip); 
             }              
              header("HTTP/1.0 403 Forbidden");           
              exit("<div style='text-align:center; padding-top:2em;'><h1>403: Login Not Available</h1></div>");
            
        } 
    } 
    
     function is_allowed($allowed, $ip) {
         if ($this->valid_ipv6_address( $ip )){
             //msg("valid ip6 format: $ip");
             return ($this->is_allowed_v6($allowed, $ip));
         }
        
         
      if( preg_match('/(\.\d+)+/',$ip)) 
     {
          //msg("valid ip4 format: $ip");
         return ($this->is_allowed_v4($allowed, $ip));
     } 
        return false;
    }   

    function is_allowed_v6($allowed, $ip) {
        $orig = $ip;
          $allowed =  trim($allowed,', '); 
          $ip = $this->ipaddress_to_ipnumber($ip); 
          $ar = explode(",",$allowed);
          
          foreach ($ar as $addr) {
                $test = $this->ipaddress_to_ipnumber($addr);
             //   msg("testing: ($orig) $ip  against $addr: " . $test);
                if($ip == $test) return true;  
            } 
          return false;
     }   
     
    function is_allowed_v4($allowed, $ip) {
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
          
         if(!$allowed || preg_match("/" . $allowed . "/", $ip) ) {    // if allowed string is empty then all ips are allowed                
               return true;
        }
        return false;  
     }
     
    function valid_ipv6_address( $ipv6 )
    {
        $regex = '/^(((?=(?>.*?(::))(?!.+\3)))\3?|([\dA-F]{1,4}(\3|:(?!$)|$)|\2))(?4){5}((?4){2}|(25[0-5]|(2[0-4]|1\d|[1-9])?\d)(\.(?7)){3})\z/i';
            if(!preg_match($regex, $ipv6))
            return (false); // is not a valid IPv6 Address

        return true;
    }

    function ipaddress_to_ipnumber($ipaddress) {
        $pton = @inet_pton($ipaddress);
        if (!$pton) { return false; }
        $number = '';
        foreach (unpack('C*', $pton) as $byte) {
            $number .= str_pad(decbin($byte), 8, '0', STR_PAD_LEFT);
        }
        return base_convert(ltrim($number, '0'), 2, 10);
    }

     function log($ip) {         
        $log = metaFN('abortlogin:aborted_ip','.log');   
        io_saveFile($log,"$ip\n",1);
     }
}
?>
