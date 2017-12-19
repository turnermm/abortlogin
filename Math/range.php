<?php
/**
 * Created by PhpStorm.
 * User: tower
 * Date: 2017-12-17
 * Time: 7:39 PM
 */
define('ABORTLOGIN_DIR',"Z:\html\detritus\lib\plugins\abortlogin");
require_once(ABORTLOGIN_DIR .'\Math\BigInteger.php');
include(ABORTLOGIN_DIR .'\Math\inet6.php');
require_once(ABORTLOGIN_DIR . '\Math\IPv6.php');

function expand($ip){
    $hex = unpack("H*hex", inet_pton($ip)); 
    $ip = substr(preg_replace("/([A-f0-9]{4})/", "$1:", $hex['hex']), 0, -1);

    return $ip;
}

function get_network_addr($addr, $prefix_len=64) {
    $addr = inet6_expand($addr);
    echo $addr ."\n";
    $parts = explode(':',$addr);
    //print_r($parts);
    $n = $prefix_len/16;
    $num = 8-$n;
    echo "$n\n";
    $end_range =  array_fill(0, $num, "0");
    $removed = array_splice($parts,$num,$n,$end_range);
    echo "Removed " . print_r($removed,1);
    echo "Result " . print_r($parts,1);

    return inet6_expand(implode(':',$parts));
}
$address = array('2001:db8:b:a::ffff','2001:db8:a::eff0','fe80:0:0:0:a299:9bff:fe18:fb6f','fe80::a299:9bff:fe18:fff0');
foreach ($address as $addr) {

   echo  get_network_addr($addr,64) ."\n\n----\n\n";
}
