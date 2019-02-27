// CKEDITOR.config.extraPlugins = 'paper-collapse-item';
// CKEDITOR.config.allowedContent = true;
CKEDITOR.on('instanceReady', function (ev) {
        ev.editor.setKeystroke(CKEDITOR.ALT + 32 /*0*/, false);
    });
CKEDITOR.plugins.add('domcollapsible', {
  requires: 'widget',

  icons: 'domcollapsible',
  beforeInit: function (editor) {

    // Configure CKEditor DTD for custom drupal-entity element.
    // @see https://www.drupal.org/node/2448449#comment-9717735
    var dtd = CKEDITOR.dtd, tagName;

    dtd['dom-collapsible'] = {'div': 1, 'h5': 1};
    for (tagName in dtd) {
      if (dtd[tagName].p) {
        dtd[tagName]['dom-collapsible'] = 1;
      }
    }
  },
  init: function(editor) {
    CKEDITOR.dialog.add('domcollapsible', this.path + 'dialogs/domcollapsible.js' );
    var pluginDirectory = this.path;
    editor.addContentsCss( pluginDirectory + 'css/domcollapsible.css' );
    editor.addContentsCss('https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css');
    editor.widgets.add('domcollapsible', {
      allowedContent:
        'dom-collapsible',

      requiredContent: 'dom-collapsible',

      editables: {
        header: '.header-text',
        content: '.content-text'
      },

      // Define the template of a new Simple Box widget.
      // The template will be used when creating new instances of the Simple Box widget.
      template:
        '<dom-collapsible>'+
          '<h5 slot="header-text" class="header-text">Item 1</h5>'+
          '<div slot="content-text" class="content-text">Enter content.</div>'+
        '</dom-collapsible>',

      button: 'Create a collapsible item',
      dialog: 'domcollapsible',

      upcast: function(element) {
        return element.name == 'dom-collapsible';
      },
      init: function() {
        this.setData( 'opened', this.element.getAttribute('opened') );
      },
      data: function() {
        if ( this.data.opened )
          this.element.setAttribute( 'opened', this.data.opened );
      }
    });
  }
});
