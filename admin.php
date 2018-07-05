<?php
/**
 *
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Myron Turner <turnermm03@shaw.ca>
 */
if (!defined('DOKU_INC')) 
{    
    die();
}
if(!defined('DOKU_PLUGIN')) define("DOKU_PLUGIN", DOKU_INC . '/lib/plugins/' );
if (!defined ('ABORTLOGIN_DIR')) define ('ABORTLOGIN_DIR', DOKU_PLUGIN . 'abortlogin/' );
require_once  ABORTLOGIN_DIR . "Math/ckg_ipv6Test.php";
require_once ABORTLOGIN_DIR . 'Math/BigInteger.php';

class admin_plugin_abortlogin extends DokuWiki_Admin_Plugin {

    var $output = '';
    var $action;
    var $tests;
    /**
     * handle user request
     */
    function __construct() {
        $this->action =  &plugin_load('action', 'abortlogin');
    }
    function handle() {
    
      if (!isset($_REQUEST['cmd'])) return;   // first time - nothing to do

      $this->output = '';
      if (!checkSecurityToken()) return;
      if (!is_array($_REQUEST['cmd'])) return;
      global $VERBOSE;
      $VERBOSE = false;
      // verify valid values
      switch (key($_REQUEST['cmd'])) {
         case 'ipv6_all' :
            $VERBOSE = true;
            $this->output = 'ipv6';
             break;
        case 'ipv6_brief' :
            $VERBOSE = false;
            $this->output = 'ipv6';           
            break;
        case 'cfg_tests':
            $this->output = 'config_tests';           
            break;
        case 'ip_single';
           $this->output = 'ip_single';              
           break;            
      }  
          
    }
 
    /**
     * output appropriate html
     */
    function html() {
       ptln('<div style="padding:4px; display:none;" id="abortlogin_info">');         
       ptln('<h2>' . $this->getLang('info') .  '</h2>');
       ptln('<div style="text-align:right;">');
              ptln('<button onclick="jQuery(\'#abortlogin_info\').hide();">'.  $this->getLang('hide_info') . '</button>');  
        ptln('</div>')  ;
            ptln($this->locale_xhtml(info)) ;   
      ptln('</div>');
       
      
      ptln('<h3>'.$this->getLang($this->output) .'</h3>');
      ptln('<button onclick="jQuery(\'#abortlogin_info\').toggle();">'. $this->getLang('show_info')  . '</button>');    ptln('<button onclick="jQuery(\'#abortlogin_display\').toggle();">' . $this->getLang('toggle_data') . '</button><br />');  
      ptln('<div id="abortlogin_display"  style = "white-space:pre;">');
      if($this->output == 'ipv6') {
          $this->get_ipv6();
      }
      else if($this->output == 'config_tests') {
          $this->config_tests(); 
      }
      else if($this->output == 'ip_single') {         
         $this->config_tests($_REQUEST['opt_single']);
      }

      ptln('</div>');
      
      ptln('<form action="'.wl($ID).'" method="post">');      
      
      // output hidden values to ensure dokuwiki will return back to this plugin
      ptln('  <input type="hidden" name="do"   value="admin" />');
      ptln('  <input type="hidden" name="page" value="'.$this->getPluginName().'" />');
      formSecurityToken();

      ptln('  <input type="submit" name="cmd[ipv6_all]"  value="'.$this->getLang('ipv6_all').'" />');
      ptln('  <input type="submit" name="cmd[ipv6_brief]"  value="'.$this->getLang('ipv6_brief').'" />');
      ptln('  <input type="submit" name="cmd[cfg_tests]"  value="'.$this->getLang('cfg_tests').'" />');
     
      ptln('<span style="padding-left: 1em"> <input type="submit" name="cmd[ip_single]"  value="'.$this->getLang('ip_single').'" /></span>');     
      ptln('<input type="text" name="opt_single"  value=""/>');       
      ptln('</form>');
    }
    
    
    function config_tests($single="") {           
        if(empty($single)) {
        $this->tests = $this->action->getTests() ;        
         $tests = explode(',',$this->tests);
        }        
         else $tests = array($single);         
          foreach ($tests as $test) {           
              $test = trim($test);  
              if(!$this->action->is_allowed($allowed, $test)) {
                  echo "<span style='color:red;'>$test ". $this->getLang('invalid') . '</span>' ;                
              }    
              else echo   "<span'>$test " . $this->getLang('valid')  .'</span>';
              echo "\n";
                   
          }   
    }
    function get_ipv6($ini_test="", $ini_range="") {
        $file =  ABORTLOGIN_DIR . 'Math/iv6_test.file';
        if(file_exists($file)) {
            $ini = parse_ini_file($file,1);
            $keys = array_keys($ini) ;      
            foreach($keys as $key){                       
                foreach($ini[$key]['test']as $ip) {                       
                     $this->ckg_test_process_ipv6(trim($key),trim($ip));        
                  }
                echo "\n";
            }
        } else echo "$file not found";
    }
    function ckg_test_process_ipv6($test_range="", $ip_to_test="") {
      global $VERBOSE;        
       global $ckg_ip_hex, $ckg_first_hex, $ckg_last_hex;
       $result = ckg_ipv6Test($test_range, $ip_to_test);
        echo "\n\nCIDR range: $test_range \n";
        echo "IP to test:  $ip_to_test \n";
        if($result) 
        {
            echo "<span style='color:blue;'>IP  is in range\n</span>";
        }
        else echo "<span style='color:red;'>IP is not in range</span>\n"; 
    
        
        $a = new Math_BigInteger($ckg_last_hex, 16);    
        $b =new Math_BigInteger($ckg_first_hex,16); 
        if($VERBOSE) {
            echo "ip: $ckg_ip_hex\nLower limit: $ckg_first_hex\nUpper limit: $ckg_last_hex\n";
            echo "Lower base 10: " .$b->toString();    
            echo "\n";
            echo "Upper base 10:  " .$a->toString(); 
             echo  "\n";
             $ip_10 = new Math_BigInteger($ckg_ip_hex,16);
             echo"IP Base 10:  " .  $ip_10->toString();
             echo  "\n";              
        } 
       $d =new Math_BigInteger($a->toString(),10);
        $e =new Math_BigInteger($b->toString(),10);    
        $c = $a->subtract($b);
        echo "Number of IPs in range: " .$c->toString(); 
        echo  "\n-------------\n"; 
   }

 }
    