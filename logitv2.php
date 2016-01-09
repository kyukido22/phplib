<?php
function logit($logname, $msg, $echotoo = false) {
    error_log('[' . date('Y-m-d h:i:s A') . '] ' . session_id() . ' ' . $msg . "\r\n", 3, '/tmp/phplogs/' . $logname . '-' . date("Ymd") . '.log');
    if ($echotoo == TRUE) {
        echo $msg . "\n";
    }
}

function startthelog($logname, $quick = FALSE) {
    logit($logname, '-----------------------------------------------------------');
    $line = '';

    //logit($logname, $_SERVER['HTTP_REFERER']);
    if (!$quick) {
        // doing the dns lookup takes some extra time so use $quick to speed things up a bid
        $line = gethostbyaddr($_SERVER["REMOTE_HOST"]);
        if ($line == $_SERVER["REMOTE_ADDR"]) {
            $line = '** No DNS entry found for calling IP';
        }
        $line = ' - ' . $line;
    }
    
    logit($logname, $_SERVER["REMOTE_ADDR"] . $line);
    if (key_exists('HTTP_USER_AGENT', $_SERVER)) {
        logit($logname, $_SERVER['HTTP_USER_AGENT'] . $line);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (substr(str_replace(chr(10), '', print_r($_POST, true)), 10) == '') {
            logit($logname, 'Called as a POST, but NO values were passed in');
        } else {
            logit($logname, 'POST values: ' . substr(str_replace(chr(10), '', print_r($_POST, true)), 10));
        }
    }
    if ($_SERVER["QUERY_STRING"] != '') {
        logit($logname, 'GET param string: ' . $_SERVER["QUERY_STRING"]);

    }
}
?>
