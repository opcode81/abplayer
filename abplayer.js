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
};

Track.prototype.createPlayer = function() {
	var $jplayer = $('<div id="jquery_jplayer"></div>');
	$jplayer.jPlayer({});
	$jplayer.jPlayer('setMedia', this.data.media);
	return $jplayer;
}

Track.prototype.play = function(time) {
	var t = time !== undefined ? time : currentTime();
	if (this.audition.restart)
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

function Audition(tracks, restart=false) {
    this.tracks = tracks;
    this.restart = restart;
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
        var $title = $('<div class="title">&#11208;	(' + String.fromCharCode(65+$i) + ")&nbsp;&nbsp;" + track.data.title + '</div>');
        $info.append($title);
        var play = function() { track.play(); };
        $container.click(play);
        track.$ui = $container;
        track.audition = that;
        $tracksContainer.append($container);
        ++$i;
    });
    
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