'use strict';

gsap.registerPlugin('ScrollTrigger');

var gallery = document.querySelector('.scrolly-gallery');
var items = document.querySelectorAll('.scrolly-gallery__item');

function setGalleryWidth() {
  gallery.style.setProperty('--scrolly-gallery-width', gallery.offsetWidth + 'px');
}
// Set the initial gallery width:
setGalleryWidth();
window.addEventListener('resize', setGalleryWidth);

items.forEach(function (item) {
  var text = item.querySelector('.scrolly-gallery__text');
  var textInner = text.querySelector('.scrolly-gallery__text-inner');
  var image = item.querySelector('.scrolly-gallery__bg');

  var tl = gsap.timeline({
    //repeat: 2,
    //repeatDelay: 1,
    scrollTrigger: {
      trigger: item,
      //pin: true,   // pin the trigger element while active
      start: "33% center", // when the top of the trigger hits the top of the viewport
      end: "45%", // end after scrolling 500px beyond the start
      scrub: 1, // smooth scrubbing, takes 1 second to "catch up" to the scrollbar
      // toggleActions: 'play none reverse none',
      toggleClass: 'current'
      // markers: true,
    }
  });

  tl.from(text, {
    opacity: 0
  });
  tl.from(textInner, {
    y: '100%'
  });
  tl.to(textInner, {
    y: '-5%'
  });
  tl.to(textInner, {
    y: '-100%'
  });
  tl.to(text, {
    opacity: 0
  });
});

// // Use MatchMedia to ensure that collision events are only happening in Desktop
// const mql = matchMedia('(min-width: 1050px)');
// // On page load, check if desktop and listen to scroll event.
// if (mql.matches) {

//   // Set the initial gallery width:
//   setGalleryWidth();
//   window.addEventListener('resize', setGalleryWidth);

//   // setup the instance, pass callback functions
//   scroller
//     .setup({
//       step: '.scrolly-gallery__step',
//     })
//     .onStepEnter(stepEnter)
//     .onStepExit(stepExit);
// }

// // When screen size changes, add or remove the scroll listener.
// mql.addListener(event => {
//   if (event.matches) {
//       // Set the initial gallery width:
//     setGalleryWidth();
//     window.addEventListener('resize', setGalleryWidth);

//     // setup the instance, pass callback functions
//     scroller
//       .setup({
//         step: '.scrolly-gallery__step',
//       })
//       .onStepEnter(stepEnter)
//       .onStepExit(stepExit);
//   } else {
//     window.removeEventListener('resize', setGalleryWidth);
//     scrollama.destroy();
//   }
// })
//# sourceMappingURL=ucsf_parallax_image_with_text_overlay.js.map
