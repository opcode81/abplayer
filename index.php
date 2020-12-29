<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>A/B Player</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="dist/skin/blue.monday/css/jplayer.black.monday.css" rel="stylesheet" type="text/css" />
<meta charset="utf-8"> 
<style>
    * { 
        -moz-box-sizing: border-box; 
        -webkit-box-sizing: border-box; 
         box-sizing: border-box; 
    }
    html { overflow-y: scroll; }
    html, body {
        text-align: center;
        background: white;
    }
    body, table, td, th {
        font-family: Calibri, Verdana, sans-serif;
    }
    div#content {
        margin: auto;
        max-width: 970px;
        min-width: 520px;
        border: 1px solid #88888;
        text-align: left;
        background: white;
        padding: 0px;
    }
    div#main {
        margin-top:101px;
    }
    #top {
        position: fixed; 
        top:0; 
        background: white;
    }
    #title {
        font-size: 150%;
        background: black;
        color: white;
        padding: 10px;
    }
    #filter {
        padding: 10px;
        border: 1px solid #ccc;
    }
    .pointer {
        cursor: pointer;
    }
    
    
    body {
        font-family: sans-serif;
        font-size: 12pt;
    }
    
    body.mobile .nonmobile {
        display: none;
    }

    .track {
        padding:0px; 
        border:1px solid #ccc; 
        margin-bottom: 5px; 
        display: block;
        cursor: pointer;
    }

    .track .title {
        font-size: 120%;
        padding-bottom: 10px;
        display: inline-block;
    }

    .track .info {
        padding: 8px;
    }

    .track img {
        cursor: pointer;
    }

    .track .part {
        font-size: 100%;
        cursor: pointer;
        text-decoration: underline;
        display:inline-block; 
        margin-right: 8px;
    }

    .playing {
        border: 1px solid #029ae6;
    }
    
    .jp-audio {
        width: 100%;  /* total width of player */
        border: 0;
        line-height: 1.0;
        padding: 8px;
    }
    .jp-audio .jp-interface {
        height: auto;
    }
    .jp-audio td {
        vertical-align: top;
    }
    
    /* controls */
    td.td-controls {
        width: 30px;
    }
    .jp-audio .jp-controls {
        width: 100%;
        padding: 0;
    }
    .jp-stop {
        margin: 0px;
    }
    
    /* seek bar */
    td.td-seek {
        padding-top: 8px;
    }
    .jp-audio .jp-type-single .jp-time-holder {
        position: relative;
        top: 3px;
        width: 100%;
        left: 0;
    }
    .jp-audio .jp-type-single .jp-progress {
        position: relative;
        left: 0;
        width: 100%;
        top: 0px;
    }
    
    /* volume controls */
    td.td-volume-controls {
        padding-top: 8px;
        width: 20%;
    }    
    .jp-volume-controls {
        position: relative; 
        top: 0;
        left: 0;
        width: 100%;
    }
    .jp-volume-controls button {
        position: relative; 
        display: inline-block;
    }
    td.td-volume-button {
        width: 20px;
    }
    .jp-volume-bar {
        width: 100%; 
        position: relative;
        display: inline-block;
        left: 0;
        top: -3px;
    }
    .jp-volume-max {
        left: 5px;
    }
</style>
<script type="text/javascript" src="lib/jquery.min.js"></script>
<script type="text/javascript" src="dist/jplayer/jquery.jplayer.min.js"></script>
<script type="text/javascript" src="abplayer.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	var tracks = [
            <?php
            error_reporting(E_ALL);
            
            // create list of tracks to load
            $tracks = array();
            if (isset($_GET["d"])) {
                $dir = $_GET["d"];
                if (strstr($dir, ".") !== false)
                    die("Not gonna happen");
                $dir = "tracks/$dir";
                if ($handle = opendir($dir)) {
                    while (false !== ($entry = readdir($handle))) {
                        if (substr($entry, -4) === ".mp3") {
                            $name = str_replace(".mp3", "", $entry);
                            $tracks[] = 'new Track({"media": {"mp3": "'.$dir.'/'.$entry.'"}, "title": "'.$name.'"})';
                        }
                    }
                }
            }
            
            // write JS array
            sort($tracks);
            $i = 0;
            foreach($tracks as $t) {
                if ($i++)
                    echo ', ';
                echo $t;
            }
            ?>
        ];
    <?php
    $restart = isset($_GET["restart"]) && $_GET["restart"] ? "true" : "false";
    ?>
	window.audition = new Audition(tracks, <?=$restart?>);
    $(window).resize($.proxy(window.audition.adjustGeometry, window.audition));
});
</script>

</head>
<body>
    <div id="content">
        <div id="top">
            <div id="title">A/B Player</div>
            
                <div id="jp_container_1" class="jp-audio" role="application" aria-label="media player">
                    <div class="jp-type-single">
                        <div class="jp-gui jp-interface">
                            <table style="width:100%" border="0">
                            <tr>
                            <td class="td-controls">
                                <div class="jp-controls">
                                    <!--<button class="jp-play" role="button" tabindex="0">play</button>-->
                                    <button class="jp-stop" role="button" tabindex="0">stop</button>
                                </div>
                            </td>
                            <td class="td-seek">
                                <div class="jp-progress">
                                    <div class="jp-seek-bar">
                                        <div class="jp-play-bar"></div>
                                    </div>
                                </div>
                                <div class="jp-time-holder">
                                    <div class="jp-current-time" role="timer" aria-label="time">&nbsp;</div>
                                    <div class="jp-duration" role="timer" aria-label="duration">&nbsp;</div>
                                    <!--
                                    <div class="jp-toggles">
                                        <button class="jp-repeat" role="button" tabindex="0">repeat</button>
                                    </div>
                                    -->
                                </div>
                            </td>
                            </td>
                            <td class="td-volume-controls nonmobile" style="padding-left: 4px;">
                                <div class="jp-volume-controls">
                                    <table style="width:100%" cellspacing="0" cellpadding="0"><tr>
                                        <td class="td-volume-button"><button class="jp-mute" role="button" tabindex="0">mute</button></td>
                                        <td class="td-volume-bar">
                                            <div class="jp-volume-bar">
                                                <div class="jp-volume-bar-value"></div>
                                            </div>
                                        </td>
                                        <td class="td-volume-button" style="padding-right:2px;"><button class="jp-volume-max" role="button" tabindex="0">max volume</button></td>
                                    </tr></table>
                                </div>
                            </td>
                            </tr>
                            </table>
                        </div>
                        <!--<div class="jp-details"><div class="jp-title" aria-label="title">&nbsp;</div></div>-->
                        <div class="jp-no-solution">
                            <span>Update Required</span>
                            To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
                        </div>
                    </div>
                </div>

        </div>
        <div id="main">
            <div id="tracks"></div>
        </div>
    </div>
</body>



</html>
