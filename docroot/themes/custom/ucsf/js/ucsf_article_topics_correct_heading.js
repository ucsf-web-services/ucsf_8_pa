(function () {
  console.log("test");
  const articleContentHasH2 = document.querySelector( ".page_narrow h2");

  if ( !articleContentHasH2 ) {
    const tagsTitle = document.querySelector( "h3.tags-menu__title");
    tagsTitle.outerHTML = '<h2 class="tags-menu__title">' + tagsTitle.innerHTML + '</h2>';
  }
})();
