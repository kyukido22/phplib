<?php

function PDOconnect($dbname, $server, $logfile) {
    $dbname = strtolower($dbname);

    $port = 5432;
    logit($logfile, "Connecting to " . $dbname . ' on ' . $server );

    try {

        $dbconn = new PDO("pgsql:dbname=$dbname; host=$server; port=$port", 'postgres', '123PASSword$%^');

        $dbconn -> setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $dbconn -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        logit($logfile, "  **ERROR** The client connection failed! \n" . $e);
        logit('caughterrors', '  **ERROR** on line ' . __LINE__ . ' of ' . __FILE__ . ' error message:' . $e -> getMessage());
        logit('caughterrors', print_r($_SESSION, true));

        echo "<h4>We're sorry, a database error occurred.  Please try again later.</h4>";

        // inform someone about the error
        mail('john.cantin@gmail.com', $logfile . ' Error', //
        'called from: ' . $_SERVER["REMOTE_HOST"] . "<br><br>" . //
        "database connection error<br>" . //
        "trying to connect to: $dbname $server $port<br>" . //
        "From: winmamserver@gmail.com \r\n" . //
        "Content-type: text/html; charset=iso-8859-1");

        //abort - someday should maybe change this so that the calling program deals with the failure
        exit ;
    }

    return $dbconn;
}
?>
