(($ => {

  // Wait for the document to be ready.
  $(() => {
    const header = document.querySelector('.combined-header-region');
    const headerSubnav = document.querySelector('.header-subnav-wrapper');
    // add class for css styling
    $( header).has( headerSubnav ).addClass( "combined-header-region--has-subnav" );
  });
}))(jQuery);
