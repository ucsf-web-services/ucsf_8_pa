CKEDITOR.plugins.add('domquote', {
  requires: 'widget',
  icons: 'domquote',
  beforeInit: function (editor) {

    // Configure CKEditor DTD for custom drupal-entity element.
    // @see https://www.drupal.org/node/2448449#comment-9717735
    var dtd = CKEDITOR.dtd, tagName;

    dtd['dom-quote'] = {'p': 1, 'div': 1, 'footer': 1};
    for (tagName in dtd) {
      if (dtd[tagName].p) {
        dtd[tagName]['dom-quote'] = 1;
      }
    }
  },
  init: function(editor) {
    // CKEDITOR.dialog.add('domquote', this.path + 'dialogs/domquote.js' );
    var pluginDirectory = this.path;
    editor.addContentsCss( pluginDirectory + 'css/domquote.css' );
    editor.widgets.add('domquote', {
      // allowedContent: 'dom-quote h2 p a(*);',

      // requiredContent: 'dom-quote; h2 p a(*);',

      editables: {
        quote: {
          selector: '.blockquote-content',
        },
        name: {
          selector: '.blockquote-title',
        },
        source: {
          selector: '.blockquote-source',
        }
      },

      template:
        '<dom-quote>' +
          '<div slot="blockquote-content" class="blockquote-content">You’ll be under relentless pressure to figure out how to deliver the highest quality, safest, most satisfying care… at the lowest possible cost.</div>' +
          '<div slot="blockquote-title" class="blockquote-title">Robert Wachter, MD</div>' +
          '<div slot="blockquote-source" class="blockquote-source">Chair, UCSF Department of Medicine</div>' +
        '</dom-quote>',

      button: 'Create a Number Callout',
      // dialog: 'domquote',

      upcast: function(element) {
        return element.name == 'dom-quote';
      },
      init: function() {
      },
      data: function() {
      }
    });
  }
});
