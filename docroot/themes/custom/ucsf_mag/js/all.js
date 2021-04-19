/**
 * Main navigation, Desktop.
 */
 (($ => {
  // Wait for the document to be ready.
  // $(() => {
  //   /**
  //   * Set aria-expanded attribute value on element that triggers the submenu visibility
  //   * @param {jQuery Object} trigger
  //   * @param {String} value
  //   */
  //   const setAria = ( trigger, value ) => {
  //     trigger.attr('aria-expanded', value);
  //   }

  //   const $navbar = $('.mag-nav');
  //   const menuId =  document.querySelector('.mag-menu').getAttribute('id');
  //   const $navToggle = $('.nav-toggle');
  //   $navToggle.attr("aria-controls", menuId);
  //   const $subnavToggle = $('.subnav-toggle');

  //   $navToggle.on('click', function (e) {
  //     const $this = $(this);
  //     $this.toggleClass('nav-toggle--active');
  //     $navbar.toggleClass('mag-nav--active');
  //     if ($this.attr("aria-expanded") === "false") {
  //       setAria($this, "true");
  //     } else {
  //       setAria($this, "false");
  //     }
  //   });

  //   $subnavToggle.on('click', function (e) {
  //     const $this = $(this);
  //     const $subnavMenuCollapsable = $this.closest('.mag-menu__item');
  //     $subnavMenuCollapsable.toggleClass('mag-menu__item--active');
  //     if ($this.attr("aria-expanded") === "false") {
  //       setAria($this, "true");
  //     } else {
  //       setAria($this, "false");
  //     }
  //   });

  //   /**
  //    * Run code if medium/desktop display has been detected.
  //    *
  //    * @param {MediaQueryList} mql
  //    */
  //    const desktopDetect = (mql) => {
  //     // Desktop
  //     if (mql.matches) {
  //       magazineDesktopMenu();
  //       getMenuPanelHeight();
  //     } else {
  //       return;
  //     }
  //   }

  //   /**
  //    * Watch for when the screen resizes horizontally from mobile to desktop.
  //    */
  //   const watchResize = () => {
  //     // Use MatchMedia to ensure that desktop related code runs only on desktop
  //     const mql = matchMedia('(min-width: 850px)');
  //     // Detect Desktop on page load.
  //     desktopDetect(mql);
  //     // Watch to see if the page size changes.
  //     mql.addListener(desktopDetect);
  //   }

  //   // Initialize.
  //   watchResize();
  // });


}))(jQuery);
