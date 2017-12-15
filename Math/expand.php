<?php
/**
 @author mike macintosh <https://stackoverflow.com/users/1431239/mike-mackintosh>
 @source https://stackoverflow.com/questions/12095835/quick-way-of-expanding-ipv6-addresses-with-php
 */
  
function expand($ip){
    $hex = unpack("H*hex", inet_pton($ip));
 print_r($hex);
    $ip = substr(preg_replace("/([A-f0-9]{4})/", "$1:", $hex['hex']), 0, -1);

    return $ip;
}

