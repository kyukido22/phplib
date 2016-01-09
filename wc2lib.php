<?php
// library of functions for wincams-2.0
/*
 * $_SESSION['local'] - localizations (can also be specifc to the client)
 * $_SESSION['clientdefaults'] - client related details (dbname, geonames)
 * $_SESSION['html'] - html text (can also be specifc to the client)
 *
 */


function SetupSortingSessionVals($page, $columns, $logname, $extras = '') {
    logit($logname, 'SetupSortingSessionVals for ' . $page);
    /*
     * $page - file name of the php page
     * $collumns - array of colmun names
     * $extras - any additional params that should be included in the link
     */
    $page = $page . '-';

    if (isset($_GET['SB'])) {
        $newSB = $_GET['SB'];
    } else {
        $newSB = $columns[0];
    }
    if (isset($_GET['SO'])) {
        $newSO = $_GET['SO'];
    } else {
        $newSO = 'ASC';
    }

    // initialize the page's sortcol and dir if they never have been set up before
    if (!isset($_SESSION[$page . "sortcol"])) {
        $_SESSION[$page . "sortcol"] = $columns[0];
        $_SESSION[$page . "sortdirection"] = 'ASC';
    }

    // remember what the current sort is
    $oldSB = $_SESSION[$page . "sortcol"];
    $oldSO = $_SESSION[$page . "sortdirection"];


    // set page sort and direction to the GET values
    $_SESSION[$page . "sortcol"] = $newSB;
    $_SESSION[$page . "sortdirection"] = $newSO;
    if ($_SESSION[$page . "sortdirection"] == 'ASC') {
        $revdir = 'DESC';
    } else {
        $revdir = 'ASC';
    }


    foreach ($columns as $colname) {
        $_SESSION[$colname . "param"] = 'SB=' . $colname . '&SO=ASC' . $extras;
        //logit($logname, $_SESSION[$colname . "param"]);
    }

    // now flip the current sort dir, overwriting one of the prev set
    $_SESSION[$_SESSION[$page . "sortcol"] . "param"] = 'SB=' . $_SESSION[$page . "sortcol"] . '&SO=' . $revdir . $extras;
    //logit($logname, $_SESSION[$_SESSION[$page . "sortcol"] . "param"]);

    //logit($logname, $_SESSION[$page . "sortcol"]);
    //logit($logname, $_SESSION[$page . "sortdirection"]);

}


function GetTheHTMLs($lang, $client, $webCntrl, $logname) {
    /*
     * get the htmls from the database and load them into $_SESSION
     */
    logit($logname, ' getting HTMLs');
    $theq = " select clientid, thelanguage, lower(htmlname) as htmlname, htmltext ";
    $theq .= " from html ";
    $theq .= " where thelanguage in (:lang,'zzzzz') ";
    $theq .= "  and clientid in (99999,:clientid) ";
    $theq .= " order by thelanguage desc, clientid desc ";

    try {
        $pdoquery = $webCntrl -> prepare($theq);
        $pdoquery -> setFetchMode(PDO::FETCH_OBJ);
        $pdoquery -> execute(array(//
        ':clientid' => $client, //
        ':lang' => $lang));

        while ($row = $pdoquery -> fetch()) {
            //erase excess blankspace
            $_SESSION["html"][$row -> htmlname] = $row -> htmltext;

            // erase unneccesarry blank space when running under production
            if ($_SERVER['SERVER_NAME'] != 'localhost') {
                $_SESSION["html"][$row -> htmlname] = str_replace(array('-=-', '  ', "\n", "\r"), '', $_SESSION["html"][$row -> htmlname]);
            }
            //echo $row -> htmlname.'<br>';
        }
        return true;

    } catch (PDOException $e) {
        logit($logname, '  **ERROR** on line ' . __LINE__ . ' with query - ' . $theq . ' ' . $e -> getMessage());
        logit('caughterrors', '  **ERROR** on line ' . __LINE__ . ' of ' . __FILE__ . ' with query - ' . $theq . ' ' . $e -> getMessage());
        logit('caughterrors', print_r($_SESSION, true));

        return FALSE;
    }
}


function CreateToolTip($page, $field, $fieldvalue, $xid, $webCntrl, $position, $logname) {
    /*
     * this function returns html for a tool tip
     * tool tip text is stored in the webcntrl table tooltips
     * the tool tip table is indexed on
     *      clientid
     *      page
     *      fieldandval
     *      xlang
     * clientid: can either be 99999 for a generic "any client" or text specific for a client
     * page: should match the name of the html template or be zzz for a generic tip
     * fieldandval: the field name that the tool tip is for.  if the tool tip needs to vary dependent upon
     *      the value of the field then it should be seperated from the column name with a period
     *      if the value is irrelevent then an empty string should be passed in to this function.
     */

    $tip = '';
    if ($fieldvalue != '') {
        $fieldandvalue = $field . '.' . $fieldvalue;
    } else {
        $fieldandvalue = $field;
    }

    if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'trident/4.0;') == 0) {
        // if not ie 8 then tool tips should work (what about even lower versions of ie?)
        $theq = 'select tiptext from tooltips ';
        $theq .= 'where clientid in (:clientid,99999) ';
        $theq .= "  and lower(thelanguage) in (lower(:xid),'zzzzz') ";
        $theq .= '  and lower(fieldandval)=lower(:fieldandvalue) ';
        $theq .= "  and lower(page) in (lower(:page),'zzzzz') ";
        $theq .= '  and active=true ';
        $theq .= 'order by clientid, page ';
        try {
            $webqry = $webCntrl -> prepare($theq);
            $webqry -> setFetchMode(PDO::FETCH_OBJ);
            $webqry -> execute(array(//
            ':clientid' => $_SESSION['clientdefaults']['clientid'], //
            ':xid' => $xid, //
            ':fieldandvalue' => $fieldandvalue, //
            ':page' => $page));
            $row = $webqry -> fetch();

            if ($webqry -> rowCount() != 0) {
                $tip = $row -> tiptext;
            } else {
                logit($logname, ' WARNING - no tool tip found for client:' . $_SESSION['clientdefaults']['clientid'] . //
                ' $page:"' . $page . '" $field:"' . $field . '" $fieldvalue:"' . $fieldvalue . '" $xid:"' . $xid . '" query: ' . $theq);
            }
        } catch (PDOException $e) {
            logit($logname, '  **ERROR** on line ' . __LINE__ . ' with query - ' . $theq . ' ' . $e -> getMessage());
            logit('caughterrors', '  **ERROR** on line ' . __LINE__ . ' of ' . __FILE__ . ' with query - ' . $theq . ' ' . $e -> getMessage());
            logit('caughterrors', print_r($_SESSION, true));

            $cancontinue = FALSE;
        }
    }

    if ($tip != '') {
        return '<span class="info-tooltip-html">' . $fieldvalue . //
        '<span class="info-tooltip"><span class="info-tooltip-text-' . $position . '"> ' . //
        $tip . ' </span> </span> </span>';
    } else {
        return $fieldvalue;
    }
}

function LoadTheHTML($thehtmlfile, $allrows, $logname, $mulitrow, $depth = 1, $changeoncol = '*') {
    /*
     * funtion to do row-by-row replacements on html file.  final result should be an
     * html ready to display to the client.
     *
     * $thehtmlfile : name of the $_SESSION['html'] key that holds the html text
     * $allrows     : an array of arrays of objects.  the first key must match the name of the html file that
     *                  will use that particular data.  objects, represetning all rows of the data make up the second key
     * $depth       : just for debugging info to tell you how many times the function has recursivly called itself
     * $multirow    : either 1 or x.  tells the function that only one row of data is expected for the sake of
     *                  doing replacements (there is then no need to repeat the html multiple times)
     * $changeoncol : column that will dictate the changing of the row color.  when this column changes
     *                  from one row of data to the next, the color of the row will change too. '*'
     *                  means the row color will change every time
     *
     * for testing purposes, leftover replacement fields are NOT removed.
     */
    if ($depth > 100) {
        //prevent infinte loops
        exit ;
    }

    $indent = substr('                                          ', 0, $depth);
    logit($logname, $indent . 'Loading HTML: ' . $thehtmlfile);
    $therow = '';
    // initialze some vars
    if (!key_exists($thehtmlfile, $_SESSION['html'])) {
        logit($logname, $indent . ' **ERROR** the html is MISSING');
        exit ;
    }
    $thehtml = $_SESSION['html'][$thehtmlfile];
    $rowtype = 'ReportDetailsEvenDataRow';

    // check if there are html's inside this html
    $i = 0;
    while ((strpos($thehtml, '%%%') != 0) and ($i < 10)) {
        // get next html replacement
        $nextpiece = substr($thehtml, strpos($thehtml, '%%%') + 3);
        $nextpiece = substr($nextpiece, 0, strpos($nextpiece, '%%%'));

        //fill var that indicates multi or single row replacements
        $nextmulitrow = substr($thehtml, strpos($thehtml, '%%%') + 3, 1);

        //extract the html name
        $thehtmlname = substr($nextpiece, 2);

        // get the color change col if present
        if (strpos($thehtmlname, '|') > 1) {
            $changeoncol = substr($thehtmlname, strpos($thehtmlname, '|') + 1);
            //$changeoncol = substr($changeoncol,0,strlen($changeoncol)-3);
            $thehtmlname = substr($thehtmlname, 0, strpos($thehtmlname, '|'));
        } else {
            $changeoncol = '*';
        }

        $newstuff = LoadTheHTML($thehtmlname, $allrows, $logname, $nextmulitrow, $depth + 1, $changeoncol);

        $thehtml = str_replace('%%%' . $nextpiece . '%%%', $newstuff, $thehtml);
        $i++;
    }

    //for each row of data in the query
    $lastchangeonvalue = '~%~%~';
    if ($mulitrow == 'x') {    	
        foreach ($allrows[$thehtmlfile] as $row) {
            $therow .= $thehtml;
            //alternating row colors
            if (($changeoncol == '*') or //
            ($row -> $changeoncol != $lastchangeonvalue)) {
                if ($changeoncol != '*') {
                    $lastchangeonvalue = $row -> $changeoncol;
                }
                if ($rowtype != 'ReportDetailsEvenDataRow') {
                    $rowtype = 'ReportDetailsEvenDataRow';
                } else {
                    $rowtype = 'ReportDetailsOddDataRow';
                }
            }
            $therow = str_replace('%%rowtype%%', $rowtype, $therow);

            // set tooltips if any
            if (strpos($therow, '%%tooltip|') != 0) {

                if (!isset($pdowebcntrl)) {
                    $pdowebcntrl = PDOconnect('wc2', $_SESSION["wc2host"], $logname);
                }
                while (strpos($therow, '%%tooltip|') != 0) {
                    $thecol = substr($therow, strpos($therow, '%%tooltip|') + 10);
                    $thecol = substr($thecol, 0, strpos($thecol, '%%'));
                    $thecolvalue = (array)$row;
                    $therow = str_replace('%%tooltip|' . $thecol . '%%', //
                    CreateToolTip($thehtmlfile, $thecol, $thecolvalue[$thecol], $_SESSION["userlanguage"], $pdowebcntrl, 'left', $logname), $therow);
                }
            }

            //for each column of the row of data, replace the merge fields with the data form the query
            foreach ($row as $key => $value) {
                $therow = str_replace('%%data-' . $key . '%%', $value, $therow);
                //echo $key.' ';
            }
            //echo '<br>';

        }
    } else {

        if (($mulitrow == '1') or isset($allrows[$thehtmlfile][0])) {
            // not a multi row
            $therow .= $thehtml;

            // set tooltips if any
            if (strpos($therow, '%%tooltip|') != 0) {

                if (!isset($pdowebcntrl)) {
                    $pdowebcntrl = PDOconnect('wc2', $_SESSION["wc2host"], $logname);
                }
                while (strpos($therow, '%%tooltip|') != 0) {
                    $thecol = substr($therow, strpos($therow, '%%tooltip|') + 10);
                    $thecol = substr($thecol, 0, strpos($thecol, '%%'));
                    //echo ' ' . $thecol;
                    $therow = str_replace('%%tooltip|' . $thecol . '%%', //
                    CreateToolTip($thehtmlfile, $thecol, '', $_SESSION["userlanguage"], $pdowebcntrl, 'left', $logname), $therow);
                }
            }

            //for each column of the row of data (if there is any data), replace the merge fields with the data form the query
            if (isset($allrows[$thehtmlfile][0])) {
                //var_dump($allrows[$thehtmlfile][0]);
                foreach ($allrows[$thehtmlfile][0] as $key => $value) {
                    $therow = str_replace('%%data-' . $key . '%%', $value, $therow);
                }
            }
        }

    }

    // put menu in
    if (isset($_SESSION['viewlevel'])) {
        if ($_SESSION['viewlevel'] == 5) {
            $therow = str_replace('%%vertmenu%%', $_SESSION['usermenu-account'], $therow);
        } else {
            $therow = str_replace('%%vertmenu%%', $_SESSION['usermenu-field'], $therow);
        }
    }

    //localization replacements
//    foreach ($_SESSION['local'] as $key => $value) {
        //$therow = str_replace('%%local-' . $key . '%%', $value, $therow);
        //logit($logname,  $key);
    //}

    //clientdefault replacements
    foreach ($_SESSION['clientdefaults'] as $key => $value) {
        $therow = str_replace('%%clientdefaults-' . $key . '%%', $value, $therow);
        //logit($logname,  $key);
    }


    // other misc replacements (accountinfo, userinfo, languagebar)
    foreach ($_SESSION as $key => $value) {
        if (!is_array($value)) {
            $therow = str_replace('%%' . $key . '%%', $value, $therow);
  //             echo $value.'<br>';
        }
    }

    // erase any leftovers ONLY when running under production    
    if ($_SERVER['SERVER_NAME'] != 'localhost') {
        $therow = preg_replace('/%%.+%%/', '', $therow);
    }

    return $therow;
}
?>