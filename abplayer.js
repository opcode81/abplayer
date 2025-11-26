var activePlayer = null;

var currentTime = function() {
	if (activePlayer)
		return activePlayer.data('jPlayer').status.currentTime;
	return 0;
}

function Track(data) {
	this.data = data;
	this.player = this.createPlayer();
    this.selected = false;
    this.$ui = null;
    this.audition = null;
    this.realTitle = null;
    this.$notes = null;
};

Track.prototype.createPlayer = function() {
	var $jplayer = $('<div id="jquery_jplayer"></div>');
	$jplayer.jPlayer({});
	$jplayer.jPlayer('setMedia', this.data.media);
	return $jplayer;
}

Track.prototype.play = function(time) {
	var t = time !== undefined ? time : currentTime();
	if ($("#restart").is(":checked") || activePlayer == this.player)
		t = 0;
	console.log("playing with currentTime=" + t);
	this.player.jPlayer('pauseOthers');
	this.player.jPlayer('play', t);
	activePlayer = this.player;
    $(".playing").removeClass("playing");
    this.$ui.addClass("playing");
};

Track.prototype.jplayer = function() {
	this.player.jPlayer.apply(this.player.jPlayer, arguments);
};

function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
}

function Audition(tracks, blind=true) {
	if (blind)
		shuffleArray(tracks);
	
    this.tracks = tracks;
    this.blind = blind;
    this.initUI();
}

Audition.prototype.initUI = function() {
	let that = this;
	
    $tracksContainer = $("#tracks");
    $i = 0;
    $.each(this.tracks, function(_, track) {
        var $container = $('<div class="track"></div>');
        $container.append(track.player);
        $info = $('<div class="info"></div>');
        $container.append($info);
        var trackTitle = track.data.title;
        if (that.blind) {
        	track.realTitle = trackTitle;
        	trackTitle = "hidden";
        }
        var $title = $('<div class="title">&#11208;	(' + String.fromCharCode(65+$i) + ')&nbsp;&nbsp;<span id="title' + $i + '">' + trackTitle + '</span></div>');
        $info.append($title);
        if (that.blind) {
            var $notes = $('<input type="text" class="track-notes" placeholder="Notes on track ' + String.fromCharCode(65+$i) + ' ..." />');
            $notes.click(function(e) { e.stopPropagation(); });
            $info.append($notes);
            track.$notes = $notes;
        }
        var play = function() { track.play(); };
        $container.click(play);
        track.$ui = $container;
        track.audition = that;
        $tracksContainer.append($container);
        ++$i;
    });
    
    if (that.blind) {
    	$("#reveal").show();
    	$("#revealButton").click(function() {
    		$i = 0;
    		$.each(that.tracks, function(_, track) {
    			$("#title" + $i).text(track.realTitle);
    			$i++;
    		});
    	});
    }
    else {
    	$("#reveal").hide();
    }
    
    // make tracks sortable via drag and drop
    $tracksContainer.sortable({
      placeholder: "sort-placeholder"
    });
    $tracksContainer.disableSelection();
    
    this.adjustGeometry();
};

Audition.prototype.adjustGeometry = function() {
    $content = $("#content");
    $top = $("#top");
    $main = $("#main");
    $top.width($main.width());
    $main.css("margin-top", ($top.height()+5) + "px");
    $body = $("body");
    if ($main.width() <= 720) {
        $body.addClass("mobile");
    }
    else {
        $body.removeClass("mobile");
    }
};

Audition.prototype.stop = function() {
    activePlayer.jPlayer('stop');
};