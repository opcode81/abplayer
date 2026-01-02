var activePlayer = null;

// Detect if device has touch capability
var isTouchDevice = function() {
	return ('ontouchstart' in window) || 
	       (navigator.maxTouchPoints > 0) || 
	       (navigator.msMaxTouchPoints > 0);
};

// Decide whether to use touch mode (drag handles, etc.)
var useTouchMode = function() {
	var isSmallScreen = $("#main").width() <= 720;
	var hasTouch = isTouchDevice();
	return hasTouch || isSmallScreen;
};

var currentTime = function() {
	if (activePlayer)
		return activePlayer.data('jPlayer').status.currentTime;
	return 0;
};

function Track(data) {
	this.data = data;
	this.player = this.createPlayer();
    this.selected = false;
    this.$ui = null;
    this.audition = null;
    this.realTitle = null;
    this.$notes = null;
}

Track.prototype.createPlayer = function() {
	var $jplayer = $('<div id="jquery_jplayer"></div>');
	$jplayer.jPlayer({});
	$jplayer.jPlayer('setMedia', this.data.media);
	return $jplayer;
};

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
        trackTitle = trackTitle.replace(/_/g, ' ');
        if (that.blind) {
        	track.realTitle = trackTitle;
        	trackTitle = "hidden";
        }
        // Create track header with play button, letter circle, track name, and reorder icon
        var $header = $('<div class="track-header"></div>');
        
        // Play button icon
        var $playIcon = $('<div class="track-play-btn"><i data-lucide="play"></i></div>');
        $header.append($playIcon);
        
        // Letter circle
        var $letter = $('<div class="track-letter">' + String.fromCharCode(65+$i) + '</div>');
        $header.append($letter);
        
        // Track name (wrappable)
        var $trackName = $('<div class="track-name"><span id="title' + $i + '">' + trackTitle + '</span></div>');
        $header.append($trackName);
        
        $info.append($header);
        
        // Reorder handle (positioned absolutely, not in flexbox)
        var $sortHandle = $('<div class="sort-handle" title="Drag to reorder">&#8942;&#8942;&#8942;</div>');
        $container.append($sortHandle);
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
    		// Show the Copy Feedback button after revealing track names
    		$("#copyFeedbackButton").show();
    	});
    	
    	// add "Copy Feedback" button click handler
    	$("#copyFeedbackButton").click(function() {
    		let feedbackText = "";
    		$i = 0;
    		// iterate through tracks in their current (user-sorted) order
    		$("#tracks .track").each(function() {
    			const trackIndex = that.tracks.findIndex(t => t.$ui[0] === this);
    			if (trackIndex !== -1) {
    				const track = that.tracks[trackIndex];
    				const index = $i + 1;
    				const notes = track.$notes ? track.$notes.val().trim() : "";
    				feedbackText += index + ": " + track.realTitle;
    				if (notes) {
    					feedbackText += " - " + notes;
    				}
    				feedbackText += "\n";
    				$i++;
    			}
    		});
    		// copy to clipboard
    		navigator.clipboard.writeText(feedbackText).then(function() {
    			// provide visual feedback
    			const originalText = $("#copyFeedbackButton").text();
    			$("#copyFeedbackButton").text("Copied!");
    			setTimeout(function() {
    				$("#copyFeedbackButton").text(originalText);
    			}, 2000);
    		}).catch(function(err) {
    			alert("Failed to copy to clipboard: " + err);
    		});
    	});
    }
    else {
    	$("#reveal").hide();
    }
    
    // Initialize Lucide icons for track play icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Set up pause/play button (completely manual implementation)
    this.setupPausePlayButton();
    
    // Set up hamburger menu
    this.setupMenu();

    this.adjustGeometry();

    // make tracks sortable via drag and drop (only on desktop)
    this.updateSortable();
};

Audition.prototype.updateSortable = function() {
    var $tracksContainer = $("#tracks");
    var touchMode = useTouchMode();

    // Destroy existing sortable if it exists
    if ($tracksContainer.hasClass('ui-sortable')) {
        $tracksContainer.sortable('destroy');
    }

    // Create sortable with appropriate settings
    var sortableOptions = {
        placeholder: "sort-placeholder"
    };

    if (touchMode) {
        // On touch devices or small screens, only allow dragging via the sort handle
        sortableOptions.handle = ".sort-handle";
    }

    $tracksContainer.sortable(sortableOptions);

    if (!touchMode) {
        $tracksContainer.disableSelection();
    }
};

Audition.prototype.setupPausePlayButton = function() {
    var that = this;
    var $btn = $("#pausePlayBtn");
    var isPlaying = false;

    // Update button appearance manually with Lucide icons
    var updateButton = function() {
        $btn.empty();
        if (!activePlayer) {
            // No active player - show square (stop-like) icon
            $btn.html('<i data-lucide="square"></i>');
        } else if (isPlaying) {
            // Playing state - show pause icon
            $btn.html('<i data-lucide="pause"></i>');
        } else {
            // Paused state - show play icon
            $btn.html('<i data-lucide="play"></i>');
        }
        $btn.css("display", "block");
        
        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    };

    // Button click handler
    $btn.on("click", function(e) {
        e.preventDefault();
        e.stopPropagation();

        if (!activePlayer) {
            return false;
        }

        if (isPlaying) {
            // Currently playing, so pause
            activePlayer.jPlayer('pause');
        } else {
            // Currently paused, so play
            activePlayer.jPlayer('play');
        }

        return false;
    });

    // Listen to play events on all tracks
    $.each(that.tracks, function(_, track) {
        track.player.on($.jPlayer.event.play, function() {
            isPlaying = true;  // Now playing, so button should show pause
            updateButton();
        });

        track.player.on($.jPlayer.event.pause, function() {
            isPlaying = false;  // Now paused, so button should show play
            updateButton();
        });

        track.player.on($.jPlayer.event.ended, function() {
            isPlaying = false;  // Ended, button should show play
            updateButton();
        });
    });
    
    // Initialize button with square icon (always visible to prevent layout shift)
    updateButton();
};

Audition.prototype.setupMenu = function() {
    var $menuBtn = $("#menuBtn");
    var $options = $("#options");
    var isMenuOpen = false;
    
    // Set hamburger icon
    $menuBtn.html('<i data-lucide="menu"></i>');
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Toggle menu on click
    $menuBtn.on("click", function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        isMenuOpen = !isMenuOpen;
        if (isMenuOpen) {
            $options.slideDown(200);
        } else {
            $options.slideUp(200);
        }
        
        return false;
    });
};

Audition.prototype.adjustGeometry = function() {
    $content = $("#content");
    $top = $("#top");
    $main = $("#main");
    $top.width($main.width());
    $main.css("margin-top", ($top.height()+5) + "px");
    $body = $("body");
    
    if (useTouchMode()) {
        $body.addClass("mobile");
    }
    else {
        $body.removeClass("mobile");
    }
    
    this.updateSortable();
};

Audition.prototype.stop = function() {
    activePlayer.jPlayer('stop');
};