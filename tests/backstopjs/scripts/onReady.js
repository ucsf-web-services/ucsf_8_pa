/**
 * Ready script, fires after pages have loaded, but before screenshots are captured.
 *
 * This script is used to hide or modify highly dynamic elements that may cause trouble
 * during visual regression testing.  If you are constantly seeing trivial failures for
 * an element, you can probably deal with it here.
 */
module.exports = async (page, scenario, vp) => {
  await page.addStyleTag({
    content: '' +
      // Force all animation to complete immediately.
      `*, *::before, *::after {
        animation-delay: 0ms !important;
        animation-duration: 0ms !important;
        transition-duration: 0ms !important;
        transition-delay: 0ms !important;
      }` +
      // Kill Video embeds (show black box instead)
      `iframe, .fluid-width-video-wrapper:after {
        background: black;
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 100;
      }` +
      // Kill google Maps (show a green box instead)
      `.js-google-map {
        position: relative;
      }
      .js-google-map:before {
        background: #B2DEA2;
        content: ' ';
        display: block;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 100;
      }` +
      // make element-fade visible
      `.element-fade {
        opacity: 1;
      }` +
      // Hide Twitter feed
      `.timeline-Viewport {
        visibility: hidden;
      }` +
      `.twitter__tweets {
        display: none;
      }`,
  });

  // Pause any carousels.
  await page.evaluate(async function () {
    const $slick = jQuery('.slick-slider')
    if ($slick.length) {
      $slick.slick('slickPause')
    }
  });

  await page.evaluate(async function () {
    // Disable jQuery animation for any future calls.
    jQuery.fx.off = true;
    // Immediately complete any in-progress animations.
    jQuery(':animated').finish();
  });

  // Finally, wait for ajax to complete - this is to give alerts
  // time to finish rendering. This can take a while, especially
  // in local environments.
  await page.waitForFunction('jQuery.active == 0');

  // Load the LazyLoaded images in immediately.
  await page.evaluate(async function () {
    Array.from(document.querySelectorAll('.lazyload')).forEach(img => {
      const src = img.getAttribute('data-src')
      if (src) {
        img.src = src
      }
    })
  });

  // Add a slight delay.  This covers up some of the jitter caused
  // by weird network conditions, slow javascript, etc. We should
  // work to reduce this number, since it represents instability
  // in our styling.
  await page.waitFor(4000);

};
