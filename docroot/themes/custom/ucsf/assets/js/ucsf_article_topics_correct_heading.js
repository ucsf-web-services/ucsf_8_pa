"use strict";

(function () {
  var articleContentHasH2 = document.querySelector(".page_narrow h2");

  if (!articleContentHasH2) {
    var tagsTitle = document.querySelector("h3.tags-menu__title");
    tagsTitle.outerHTML = '<h2 class="tags-menu__title">' + tagsTitle.innerHTML + '</h2>';
  }
})();
//# sourceMappingURL=ucsf_article_topics_correct_heading.js.map
