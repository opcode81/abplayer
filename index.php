<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>A/B Player</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="lib/jplayer/skin/blue.monday/css/jplayer.black.monday.css" rel="stylesheet" type="text/css" />
<link href="lib/jquery-ui.css" rel="stylesheet" type="text/css" />
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
        margin: 0;
        padding: 0;
        overflow-x: hidden;
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
    
    body.mobile div#content {
        min-width: 0;
        width: 100%;
        box-sizing: border-box;
    }
    div#main {
        margin-top:101px;
    }
    #top {
        position: fixed; 
        top:0; 
        background: white;
        box-sizing: border-box;
    }
    #options {
        background: #eee;
        text-align: right;
        padding: 8px;
        padding-bottom: 0;
    }
    #title {
        font-size: 150%;
        background: black;
        color: white;
        padding: 10px;
    }
    #revealButton {
        text-align: middle;
        width: 100%;
        height: 3em;
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

    .track, div#about, .sort-placeholder {
        border: 2pt solid #ccc;    
        margin-bottom: 5px; 
    }
    .track {
        padding: 0px; 
        display: block;
        cursor: pointer;
        background: white;
        position: relative;
    }
    .sort-placeholder {
        height: 3em;
        background: yellow;
    }

    .track .title {
        font-size: 120%;
        padding-bottom: 10px;
        display: inline-block;
    }

    .track .track-notes {
        width: 100%;
        padding: 6px;
        margin-top: 8px;
        border: 1px solid #ccc;
        border-radius: 3px;
        font-size: 90%;
        box-sizing: border-box;
    }

    .track .track-notes:focus {
        outline: none;
        border-color: #029ae6;
    }

    .track .track-notes::placeholder {
        color: #bbb;
        opacity: 1;
    }

    .track .info, div#about {
        padding: 8px;
    }
    
    body.mobile .track .info {
        padding-right: 50px;
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
    
    div#about {
        font-size: 80%;
    }

    .playing {
        border: 2pt solid #029ae6;
    }
    
    .sort-handle {
        position: absolute;
        right: 0px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 24px;
        color: #ccc;
        cursor: grab;
        padding: 10px;
        user-select: none;
        display: none;
    }
    
    body.mobile .sort-handle {
        display: block;
    }
    
    .sort-handle:active {
        cursor: grabbing;
        color: #999;
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
<script type="text/javascript" src="lib/jquery-ui.js"></script>
<script type="text/javascript" src="lib/jquery.ui.touch-punch.min.js"></script>
<script type="text/javascript" src="lib/jplayer/jplayer/jquery.jplayer.min.js"></script>
<script type="text/javascript" src="abplayer.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	var tracks = [
            <?php
            $about = null;
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
                        else if ($entry == "about.html") {
                            $about = file_get_contents($dir."/".$entry);
                        }
                        else if ($entry == "about.txt") {
                            $about = nl2br(file_get_contents($dir."/".$entry));
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
	<?php $blind = isset($_GET["blind"]) && $_GET["blind"] ? "true" : "false" ?>
	window.audition = new Audition(tracks, <?php echo $blind; ?>);
    $(window).resize($.proxy(window.audition.adjustGeometry, window.audition));
});
</script>

</head>
<body>
    <div id="content">
        <div id="top">
            <div id="title">A/B Player</div>
            <div id="options">
            	<input id="restart" type="checkbox" <?php if(isset($_GET["restart"]) && $_GET["restart"]) echo "checked"; ?>> restart on select
            </div>
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
        	<?php
        	if ($about) {
        	    echo '<div id="about">', $about, '</div>';
        	}
        	?>
            <div id="tracks"></div>
            <div id="reveal"><button id="revealButton" class="pointer">Reveal track names</button></div>
        </div>
    </div>
</body>

</html>
