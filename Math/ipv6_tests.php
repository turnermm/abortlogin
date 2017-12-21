<?php
global $CKG_IPV6_DEBUG;

if($CKG_IPV6_DEBUG) {
     $test_range ='fe80::19c9:eb59:c1c7:fbcc/48';
    $ip_to_test='fe80::19c9:eb59:c1c7:fbdc';
    $result = ckg_ipv6Test($test_range, $ip_to_test);
    if($result) 
    {
        echo "yes\n";
    }
    else echo "no\n"; 
  }
  
