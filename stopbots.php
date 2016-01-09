<?php

// ignore spiders and bots

function is_this_a_bot() {
    $caller = $_SERVER["REMOTE_HOST"];// . ' - ' . gethostbyaddr($_SERVER["REMOTE_HOST"]);
    if (key_exists('HTTP_USER_AGENT', $_SERVER)) {
        $caller .=$_SERVER["HTTP_USER_AGENT"];
    }    
    $caller = strtolower($caller);
    
    if (//
    //(strpos($caller, 'sharedmarketing') >1) or // this is for testing
    //(strpos($caller, '20.10.5.') === 0) or // this is for testing
    (strpos($caller, 'crawl.baidu.com') > 1) or //
    (strpos($caller, 'amazonaws.com') > 1) or //    
    (strpos($caller, 'google') > 1) or //
        
    (strpos($caller, '66.249.') === 0) or //
    (strpos($caller, '64.233.172.') === 0) or //
    (strpos($caller, '54.209.') === 0) or //
    (strpos($caller, '180.76.') === 0) or //
    
    
// these are not actually spiders, but are sites that other companies are using to check
// if our system is up.  they've been hitting our site every 2 min so we'v added them 
// to the list of things to ignore.    
    (strpos($caller, '212.13.200.') === 0) or //
    (strpos($caller, '108.61.196.') === 0) or //
    (strpos($caller, '65.39.204.') === 0) or //
    (strpos($caller, '72.5.230.') === 0) or //
    (strpos($caller, '37.235.48.') === 0) or //
    (strpos($caller, '65.39.204.') === 0) or //
    (strpos($caller, '107.170.227.') === 0) or //
    (strpos($caller, '31.220.7.') === 0) or //

    (strpos($caller, 'statuscake') > 1) or //    
    (strpos($caller, 'alertsite') > 1) or //
    (strpos($caller, 'monitor.site24x7.com') > 1) or //
    (strpos($caller, 'securefastserver.com') > 1) or //
    (strpos($caller, 'vultr.com') > 1) or //
    (strpos($caller, 'gq1.yahoo.net') > 1) or //
    (strpos($caller, 'incero.com') > 1) or //
    (strpos($caller, 'crawl-192-99') > 1)) {
        return TRUE;
    } else {
        return FALSE;        
    }
    
}    
?>