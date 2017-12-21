<?php 
require "ckg_ipv6Test.php";
require_once 'BigInteger.php';

global $CKG_IPV6_DEBUG;
$CKG_IPV6_DEBUG=0;
global $ckg_ip_hex, $ckg_first_hex, $ckg_last_hex;
if($argc > 1)
 {        
  $CKG_IPV6_DEBUG = 1;
 }
else {
    echo "\nUsage:\n";
    echo "\t" .$argv[0] . "  <IP_to_test>  <IP/CIDR>  -v\n\tExample: fe80::19c9:eb59:c1c7:cbcc   fe80::19c9:eb59:c1c7:fbcc/64\n";
}
if($CKG_IPV6_DEBUG) {
    if($argc > 1) {        
        if(!empty($argv[2])) {
            $test_range = $argv[2];
        }
        else $test_range ='fe80::19c9:eb59:c1c7:fbcc/64';
        $ip_to_test = $argv[1];
    }
    else {
     $test_range ='fe80::19c9:eb59:c1c7:fbcc/64';
      $ip_to_test='fe80::19c9:eb59:f1cf:fbfc';
    }
    if($argv[3] == "-v") 
        $VERBOSE = 1;
     else $VERBOSE = 0;
    $result = ckg_ipv6Test($test_range, $ip_to_test);

    echo "CIDR range: $test_range \n";
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
         echo  "\n";
    } 
   $d =new Math_BigInteger($a->toString(),10);
    $e =new Math_BigInteger($b->toString(),10);
    echo  "\n";
    $c = $a->subtract($b);
    echo "Number of IPs in range: " .$c->toString(); 
    echo  "\n"; 
  
  }
  
