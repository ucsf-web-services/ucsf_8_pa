'use strict';

var player;
function onYouTubeIframeAPIReady() {
  player = new YT.Player('autoplay-video', {
    events: {
      'onReady': onPlayerReady,
      'onStateChange': onStateChange
    }
  });
}

function onPlayerReady() {
  if (autoplayEnabled()) {
    if (!player.isMuted()) {
      player.mute();
      player.playVideo();
    } else {
      player.playVideo();
    }
  } else {
    // Prevents the video from loading further.
    player.stopVideo();
  }
}

function onStateChange(evt) {
  if (autoplayEnabled()) {
    player.unloadModule('captions'); // removes the captions.
    if (evt.data === 0) {
      // 0 means the video has finished.
      player.mute();
      player.playVideo();
    }
  }
}

function autoplayEnabled() {
  return true;
  // only if autoplay for big screens
  // return getWindowWidth() >= 1200 ? true : false;
}

function autoplayToggle() {
  if (autoplayEnabled()) {
    // Even if it should be enabled, check the class to limit the interaction
    // w/player API.
    if (!document.documentElement.classList.contains('is-enabled-autoplay')) {
      // Add the class and play the video.
      document.documentElement.classList.add('is-enabled-autoplay');
      player.mute();
      player.playVideo();
    }
  } else {
    if (document.documentElement.classList.contains('is-enabled-autoplay')) {
      // Remove the class and pause the video.
      document.documentElement.classList.remove('is-enabled-autoplay');
      player.pauseVideo();
    }
  }
}

var resizeId;
var windowWidth = jQuery(window).width();
jQuery(window).on('resize', function () {
  // Check if the window width has actually changed and it's not just iOS
  // triggering a resize event on scroll.
  if (jQuery(window).width() != windowWidth) {
    // Update the width.
    windowWidth = jQuery(window).width();
    clearTimeout(resizeId);
    resizeId = setTimeout(function () {
      autoplayToggle();
    }, 250);
  }
});

function getWindowWidth() {
  var windowWidth = 0;
  if (document.documentElement && document.documentElement.clientWidth) {
    windowWidth = document.documentElement.clientWidth;
  }
  return windowWidth;
}
//# sourceMappingURL=autoplay-video.js.map
