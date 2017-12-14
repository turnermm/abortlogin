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
   private $allowed_v4,$allowed_v6;

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
      $this->map_allowed($allowed);   
    
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
   // $test=false;
      if($test && isset($USERINFO) && in_array('admin', $USERINFO['grps'])) {         
          $tests = explode(',',$test);
          foreach ($tests as $test) {           
              $test = trim($test);  
              if(!$this->is_allowed($allowed, $test)) {
                  msg("$test ". $this->getLang('invalid'));
              }    
               else  msg("$test " . $this->getLang('valid'),2);         
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
             return ($this->is_allowed_v6($ip));
         }
        
      if( preg_match('/(\.\d+)+/',$ip)) 
     {
         return ($this->is_allowed_v4($ip));
     } 
        return false;
    }   

    function is_allowed_v6($ip) {
          $ip = inet_ptoi($ip);
          foreach ($this->allowed_v6 as $addr) {                         
                $test = inet_ptoi($addr);              
          
                if($ip === $test) {
                    return true;  
                }  
            } 
          return false;
     }   
     
    function is_allowed_v4($ip) {
         if(!$this->allowed_v4 || preg_match("/" . $this->allowed_v4 . "/", $ip) ) {    // if allowed string is empty then all ips are allowed
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

    function map_allowed($allowed){
         $allowed_v4 = array();
         $allowed_v6 = array();

       $allowed = explode(',',$allowed);

        foreach ($allowed AS $addr) {
            if($this->valid_ipv6_address($addr)){
                $allowed_v6[] = trim($addr);
            }
            else $allowed_v4[] = trim($addr);
        }
    
        $this->allowed_v4 = implode('|',$allowed_v4);
        $this->allowed_v6 = $allowed_v6;
        //   msg(print_r($this->allowed_v6,1) . $this->allowed_v4);
    }
     function log($ip) {         
        $log = metaFN('abortlogin:aborted_ip','.log');   
        io_saveFile($log,"$ip\n",1);
     }
}
  
  /**
 * Converts human readable representation to a 128 bit int
 * which can be stored in MySQL using DECIMAL(39,0).
 *
 * Requires PHP to be compiled with IPv6 support.
 
 * @param string $ip IPv4 or IPv6 address to convert
 * @return string 128 bit string that can be used with DECIMNAL(39,0) or false
  *@author Sam Clarke < sam@samclarke.com>
  *@source  https://www.samclarke.com/php-ipv6-to-128bit-int/
 */
 
if(!function_exists('inet_ptoi'))
{
     include (DOKU_INC . "lib/plugins/abortlogin/Math/BigInteger.php");
    function inet_ptoi($ip)
    {
        // make sure it is an ip
        if (filter_var($ip, FILTER_VALIDATE_IP) === false)
            return false;

        $parts = unpack('N*', inet_pton($ip));

        // fix IPv4
        if (strpos($ip, '.') !== false)
            $parts = array(1=>0, 2=>0, 3=>0, 4=>$parts[1]);

        foreach ($parts as &$part)
        {
            // convert any unsigned ints to signed from unpack.
            // this should be OK as it will be a PHP float not an int
            if ($part < 0)
                $part += 4294967296;
        }

        // Use BCMath if available
        if (function_exists('bcadd'))
        {
            $decimal = $parts[4];
            $decimal = bcadd($decimal, bcmul($parts[3], '4294967296'));
            $decimal = bcadd($decimal, bcmul($parts[2], '18446744073709551616'));
            $decimal = bcadd($decimal, bcmul($parts[1], '79228162514264337593543950336'));
        }
        // Otherwise use the pure PHP BigInteger class
        else
        {
            $decimal = new Math_BigInteger($parts[4]);
            $part3   = new Math_BigInteger($parts[3]);
            $part2   = new Math_BigInteger($parts[2]);
            $part1   = new Math_BigInteger($parts[1]);

            $decimal = $decimal->add($part3->multiply(new Math_BigInteger('4294967296')));
            $decimal = $decimal->add($part2->multiply(new Math_BigInteger('18446744073709551616')));
            $decimal = $decimal->add($part1->multiply(new Math_BigInteger('79228162514264337593543950336')));

            $decimal = $decimal->toString();
        }

        return $decimal;
    }
    }

?>
