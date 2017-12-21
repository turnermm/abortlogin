<?php
include_once 'BigInteger.php';
global $CKG_IPV6_DEBUG;
global $ckg_ip_hex, $ckg_first_hex, $ckg_last_hex;
if($argc > 1)
 {        
  $CKG_IPV6_DEBUG = 1;
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
    $result = ckg_ipv6Test($test_range, $ip_to_test);
    if($result) 
    {
        echo "yes\n";
    }
    else echo "no\n"; 
    echo $test_range ."\n";
    echo $ip_to_test ."\n";
    echo "ip: $ckg_ip_hex\n First: $ckg_first_hex\n Last:  $ckg_last_hex\n";
    $a = new Math_BigInteger($ckg_last_hex, 16);    
    $b =new Math_BigInteger($ckg_first_hex,16); 
    echo "First base 10: " .$b->toString();    
    echo "\n";
    echo "Last base 10:  " .$a->toString(); 
     echo  "\n";
    //echo "$b \n";
  
    echo  "\n";
    
    
    
     $d =new Math_BigInteger($a->toString(),10);
    $e =new Math_BigInteger($b->toString(),10);
    echo  "\n";
    $c = $a->subtract($b);
    echo "Number of IPs in range: " .$c->toString(); 
    echo  "\n";

  //  echo $d->subtract($e);
    echo  "\n";
  }
  
