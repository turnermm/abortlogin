<?php
/*
  *  Adapted from PHPMyAdmin: libraries/ip_allow_deny.lib.php
 */
 require_once 'StringMB.class.php';
function ckg_ipv6Test($test_range, $ip_to_test)
{
    $result = true;
    global $ckg_ip_hex, $ckg_first_hex, $ckg_last_hex;
    /** @var PMA_String $pmaString */
  //  $pmaString = $GLOBALS['PMA_String'];
    $pmaString = new PMA_StringMB(); 
    // convert to lowercase for easier comparison
    $test_range = $pmaString->strtolower($test_range);
    $ip_to_test = $pmaString->strtolower($ip_to_test);

    $is_cidr = $pmaString->strpos($test_range, '/') > -1;
    $is_range = $pmaString->strpos($test_range, '[') > -1;
    $is_single = ! $is_cidr && ! $is_range;

    $ip_hex = bin2hex(inet_pton($ip_to_test));

    if ($is_single) {
        $range_hex = bin2hex(inet_pton($test_range));
        $result = $ip_hex === $range_hex;
        return $result;
    }

    if ($is_range) {
        // what range do we operate on?
        $range_match = array();
        $match = preg_match(
            '/\[([0-9a-f]+)\-([0-9a-f]+)\]/', $test_range, $range_match
        );
        if ($match) {
            $range_start = $range_match[1];
            $range_end   = $range_match[2];

            // get the first and last allowed IPs
            $first_ip  = str_replace($range_match[0], $range_start, $test_range);
            $first_hex = bin2hex(inet_pton($first_ip));
            $last_ip   = str_replace($range_match[0], $range_end, $test_range);
            $last_hex  = bin2hex(inet_pton($last_ip));

            // check if the IP to test is within the range
            $result = ($ip_hex >= $first_hex && $ip_hex <= $last_hex);
        }
        return $result;
    }

    if ($is_cidr) {
        // Split in address and prefix length
        list($first_ip, $subnet) = explode('/', $test_range);

        // Parse the address into a binary string
        $first_bin = inet_pton($first_ip);
        $first_hex = bin2hex($first_bin);

        $flexbits = 128 - $subnet;

        // Build the hexadecimal string of the last address
        $last_hex = $first_hex;

        $pos = 31;
        while ($flexbits > 0) {
            // Get the character at this position
            $orig = $pmaString->substr($last_hex, $pos, 1);

            // Convert it to an integer
            $origval = hexdec($orig);

            // OR it with (2^flexbits)-1, with flexbits limited to 4 at a time
            $newval = $origval | (pow(2, min(4, $flexbits)) - 1);

            // Convert it back to a hexadecimal character
            $new = dechex($newval);

            // And put that character back in the string
            $last_hex = substr_replace($last_hex, $new, $pos, 1);

            // We processed one nibble, move to previous position
            $flexbits -= 4;
            $pos -= 1;
        }
        $ckg_ip_hex =$ip_hex; $ckg_first_hex=$first_hex; $ckg_last_hex = $last_hex;
        // check if the IP to test is within the range
        $result = ($ip_hex >= $first_hex && $ip_hex <= $last_hex);
    }

    return $result;
} // end of the "PMA_ipv6MaskTest()" function


