<?php
// Simple web interface to adjust Qt4 translation files.
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This software is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY
// KIND, either express or implied.
//
// (c) 2010 Dominik Riebeling
//
error_reporting(E_ALL);


function create_xml_stats($tsfile)
{
    $xml = simplexml_load_file($tsfile);
    $status['strings'] = 0;
    $status['empty'] = 0;
    $status['unfinished'] = 0;
    // count
    // - number of source strings
    // - number of empty destination strings
    // - number of destination strings maked as unfinished.
    foreach($xml->children() as $child) {
        $classname = $child->name;
        foreach($child->message as $msg) {
            $status['strings']++;
            $attributes = $msg->translation->attributes();
            if($attributes['type'] == "unfinished")
                $status['unfinished']++;
            if($msg->translation == "")
                $status['empty']++;
        }

    }
    return $status;
}


function create_svn_stats($tsfile)
{
    // this creates svn stats from the info file for $tsfile.
    $status['rev'] = 0;
    $status['date'] = 0;
    if(!is_file($tsfile . ".info"))
        return $status;
    $hdl = fopen($tsfile . ".info", "r");
    while(!feof($hdl)) {
        $line = fgets($hdl);
        if(preg_match('/^Last Changed Rev/', $line))
            $status['rev'] = trim(preg_replace('/^[a-zA-Z ]+:/', '', $line));
        else if(preg_match('/^Last Changed Date/', $line))
            $status['date'] = strtotime(preg_replace('/^[a-zA-Z ]+:(.+)\(.+\)/', '$1', $line));

    }
    fclose($hdl);
    return $status;
}


function parse_update_xml($tsfile, $mode, $update = 0)
{
    $row = 0;
    foreach($tsfile->children() as $child) {
        $classname = $child->name;
        if($classname == "")
            $classname = "(unknown)";
        if($update == 0)
            echo("\n<tr><td colspan='5' class='cppclass'>$classname</td></tr>\n");
        foreach($child->message as $msg) {
            $status = "(unknown)";
            $sourcestring = preg_replace("/\n/", "<span class='cr'>¶</span><br/>",
                htmlspecialchars($msg->source));
            $locations = $msg->location;
            $location = "";
            foreach($locations as $s) {
                $l = $s->attributes();
                if($location != "")
                    $location .= ", ";
                $location .= $l['filename'] . ":" . $l['line'];
            }
            $translation = htmlspecialchars($msg->translation);
            $transstatus = $msg->translation->attributes();
            $status = $transstatus['type'];
            $comment = $msg->comment . $msg->extracomment;
            if($comment == "")
                $comment = "(no translation comment available)";
            $rowclass = "c" . $row%2;
            if($mode == "empty" && $translation != "")
                continue;
            // FIXME: try to check for empty strings as well, don't rely on the
            // ts file having unfinished set for those.
            if($mode == "unfinished" && $status != "unfinished")
                continue;

            if($update == 0) {
                if($status == "unfinished") {
                    $statusimg = "dialog-warning.png";
                }
                else {
                    $statusimg = "dialog-information.png";
                    $status = "finished";
                }
                echo("<!-- $row -->\n");
                echo("<tr class='$rowclass'>");
                echo("<td>Status</td>");
                echo("<td><span id='sstatus-$row'>"
                    . "<img id='istatus-$row' src='$statusimg' alt='status' "
                    . "class='statusimg' />$status</span>");
                echo("<input type='hidden' id='status-$row' name='status-$row' "
                    . "value='$status'/> ");
                if($status == "unfinished")
                    echo("<span class='toggle'>"
                    . "<a href='javascript:toggleStatus(\"$row\");' "
                    . "id='tstatus-$row'>(mark finished)</a></span>");
                else
                    echo("<span class='toggle'>"
                    . "<a href='javascript:toggleStatus(\"$row\");' "
                    . "id='tstatus-$row'>(mark unfinished)</a></span>");
                echo("</td>");
                echo("</tr>\n");
                echo("<tr class='$rowclass'>");
                echo("<td>String:</td><td>$sourcestring</td>");
                echo("</tr>\n");
                echo("<tr class='$rowclass'>");
                echo("<td>Comment:</td><td>$comment</td>");
                echo("</tr>\n");
                echo("<tr class='$rowclass'>");
                echo("<td>Location:</td><td class='location'>$location</td>");
                echo("</tr>\n");
                echo("<tr class='$rowclass'>");
                echo("<td></td>");
                echo("<td><textarea class='translated' rows='3' cols='100' "
                    . "name='translation-$row'>$translation</textarea></td>");
                echo("</tr>\n");
            }
            else {
                if(array_key_exists("translation-$row", $_POST)) {
                    $msg->translation = $_POST["translation-$row"];
                    // unset the "unfinished" translation type if it contains
                    // text.
                    // FIXME: allow the user to control this.
                    if($_POST["translation-$row"] != ""
                        && $_POST["status-$row"] == "finished") {
                        // to remove an attribute unset() it.
                        unset($msg->translation['type']);
                    }
                }
            }
            $row++;
        }
    }
    return $row;
}

if(array_key_exists('translation', $_POST))
    $inputfile = $_POST['translation'];
if(array_key_exists('inputfile', $_GET)) {
    $inputfile = $_GET['inputfile'];
    if(preg_match("/\.\./", $inputfile))
        die("invalid request!");
}
else if(array_key_exists('inputfile', $_POST))
    $inputfile = $_POST['inputfile'];

// load input file
if(isset($inputfile))
    $tsfile = simplexml_load_file($inputfile);


// if we want the updated file send out xml
if(array_key_exists('update', $_POST)) {
    $filename = basename($inputfile);
    header("Content-Disposition: attachment; filename=$filename");
    // update data that has been sent in the POST request

    // send out the xml
    parse_update_xml($tsfile, $_POST['show'], 1);
    echo($tsfile->asXML());
    exit(0);
}
    
if(isset($inputfile))
    $title = $inputfile;
else
    $title = "Overview";

$show = "all";
if(array_key_exists('show', $_GET))
    $show = $_GET['show'];
else if(array_key_exists('show', $_POST))
    $show = $_POST['show'];


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="linguist.css" />
<script type="text/javascript" src="linguist.js"></script>
<title><?php echo("Weblinguist: $title"); ?></title>
</head>
<body>

<?php
date_default_timezone_set("UTC");
if(!isset($inputfile)) {
    $files = glob("lang/*.ts");
    $row = 0;
    echo("<table>\n");
    echo("<tr class='header'><td><b>Language</b></td><td>translation revision</td><td><b>edit</b></td><td>strings</td><td>unfinished</td><td>empty</td><td>Progress</td></tr>\n");
    foreach($files as $f) {
        $status = create_xml_stats($f);
        $svnstat = create_svn_stats($f);
        echo("<tr class='c" . $row%2 . "'>\n");
        echo("<td>$f</td>\n");
        if($svnstat['rev'] == 0) {
            echo("<td>(unknown)</td>\n");
        }
        else {
            echo("<td>r" . $svnstat['rev'] . " (" . date("Y-m-d", $svnstat['date']) . ")</td>\n");
        }
        echo("<td><a href='$_SERVER[PHP_SELF]?inputfile=$f&amp;show=unfinished'>edit</a></td>\n");
        echo("<td>" . $status['strings'] . "</td>\n");
        echo("<td>" . $status['unfinished'] . "</td>\n");
        echo("<td>" . $status['empty'] . "</td>\n");
        $progress = floor(100 * ($status['strings'] - $status['unfinished']) / $status['strings']);
        echo("<td><img src='graph.php?p=$progress' alt='Translation progress $progress%'/></td>\n");
        echo("</tr>\n");
        $row++;
    }
    echo("</table>\n");
}
else {
    echo("Translation language: " . $tsfile['language'] . "<br/>\n");
    echo("TS version: " . $tsfile['version'] . "<br/>\n");
    if(isset($inputfile)) {
        echo("<p>");
        echo("<a href='?inputfile=$inputfile&amp;show=all'>show all</a> * ");
        echo("<a href='?inputfile=$inputfile&amp;show=unfinished'>show unfinished</a> * ");
        echo("<a href='?inputfile=$inputfile&amp;show=empty'>show empty</a>");
        echo("</p>\n");
        echo("<p>");
        echo("Translation language: " . $tsfile['language'] . "<br/>\n");
        echo("TS version: " . $tsfile['version'] . "<br/>\n");
        echo("</p>\n");
    }

    if($show == "all")
        echo("<b>showing all strings</b>");
    else if($show == "empty")
        echo("<b>showing only empty translations.</b>");
    else if($show == "unfinished")
        echo("<b>showing unfinished (including empty) translations.</b>");

    echo("<p><b>Translating file $inputfile</b></p>\n");
    echo("<form action='$_SERVER[PHP_SELF]' method='post'>\n");
    echo("<input type='hidden' name='show' value='$show'/>\n");
    echo("<input type='hidden' name='update' value='true'/>\n");
    echo("<input type='hidden' name='inputfile' value='$inputfile' />\n");
    echo("<table>\n");
    $rows = parse_update_xml($tsfile, $show);
    echo("</table>");

    if($rows > 0)
        echo("<input type='submit'/>");
    echo("</form>");
    if($rows == 0)
        echo("<b>No matching strings found!</b>");
}
?>
</body>
</html>

