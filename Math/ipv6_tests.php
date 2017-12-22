<?php 
require "ckg_ipv6Test.php";
require_once 'BigInteger.php';

function ckg_test_process_ipv6($ini_test="", $ini_range="") {
    global $argc,$argv;
    global $CKG_IPV6_DEBUG;
    $CKG_IPV6_DEBUG=0;
    global $ckg_ip_hex, $ckg_first_hex, $ckg_last_hex;
    if($argc > 1 || $ini_range)
     {        
      $CKG_IPV6_DEBUG = 1;
     }
    else if( !$ini_range) {
        echo "\nUsage:\n";
        echo "\t" .$argv[0] . "  <IP_to_test>  <IP/CIDR>  -v\n\tExample: fe80::19c9:eb59:c1c7:cbcc   fe80::19c9:eb59:c1c7:fbcc/64\n";
    }
    if($CKG_IPV6_DEBUG) {
        if($argc > 1 && !$ini_range) {        
            if(!empty($argv[2])) {                    
                   $test_range = $argv[2];
                  $ip_to_test=$argv[1];
            }
            else {
                if($argv[1] == '-t') {
                  $test_range ='fe80::19c9:eb59:c1c7:fbcc/64';
                   $ip_to_test='fe80::19c9:eb59:f1cf:fbfc';
                }
                else {
                    $test_range ='fe80::19c9:eb59:c1c7:fbcc/64';
                    $ip_to_test = $argv[1];
                }
            }
        }
        else {
         $test_range ='fe80::19c9:eb59:c1c7:fbcc/64';
          $ip_to_test='fe80::19c9:eb59:f1cf:fbfc';
        }
        if($argv[3] == "-v") 
            $VERBOSE = 1;
         else $VERBOSE = 0;
         if( !empty ($ini_range)) {
             $test_range = $ini_range;
             $ip_to_test = $ini_test;
         }
        $result = ckg_ipv6Test($test_range, $ip_to_test);

        echo "\n\nCIDR range: $test_range \n";
        echo "IP to test:  $ip_to_test \n";
        if($result) 
        {
            echo "IP to test is in range\n";
        }
        else echo "IP to test is not in range\n"; 
        echo "ip: $ckg_ip_hex\nLower limit: $ckg_first_hex\nUpper limit: $ckg_last_hex\n";
        
        $a = new Math_BigInteger($ckg_last_hex, 16);    
        $b =new Math_BigInteger($ckg_first_hex,16); 
        if($VERBOSE) {
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
global $argc;
$file = "iv6_test.file";
if($argc < 2 && file_exists($file)) {
     echo "using $file\n";
    $ini = parse_ini_file($file,1);
    $keys = array_keys($ini) ;
    foreach($keys as $key){       
        foreach($ini[$key]['test']as $ip) {
            ckg_test_process_ipv6(trim($ip),trim($key));        
          }
        echo "\n";
    }
} else ckg_test_process_ipv6();

