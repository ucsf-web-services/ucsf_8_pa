CKEDITOR.plugins.add('domonecolumn', {
  requires: 'widget',
  icons: 'domonecolumn',
  beforeInit: function (editor) {

    // Configure CKEditor DTD for custom drupal-entity element.
    // @see https://www.drupal.org/node/2448449#comment-9717735
    var dtd = CKEDITOR.dtd, tagName;

    dtd['dom-onecolumn-layout'] = {'div': 1};
    for (tagName in dtd) {
      if (dtd[tagName].p) {
        dtd[tagName]['dom-onecolumn-layout'] = 1;
      }
    }
  },
  init: function(editor) {
    CKEDITOR.dialog.add('domonecolumn', this.path + 'dialogs/domonecolumn.js' );
    var pluginDirectory = this.path;
    editor.addContentsCss( pluginDirectory + 'css/domonecolumn.css' );
    editor.widgets.add('domonecolumn', {
      allowedContent: 'dom-onecolumn-layout(*)',

      requiredContent: 'dom-onecolumn-layout',

      editables: {
        firstCol: {
          selector: '.column1',
        }
      },

      template:
        '<dom-onecolumn-layout>' +
          '<div slot="column1" class="column1"><p>Lorem ipsum dolor sit amet, ex labore vivendo laboramus has, vel at putant legendos. Quod appareat id eos, noster malorum et mea.</p></div>' +
        '</dom-onecolumn-layout>',

      button: 'Create one columns',
      dialog: 'domonecolumn',

      upcast: function(element) {
        return element.name == 'dom-onecolumn-layout';
      },
      init: function() {
        var column1Color = this.element.getAttribute('data-column1-color')
        this.setData( 'FirstColumn_color', column1Color);

        var column1 = this.element.findOne('.column1')
        var column1FontColor = 'white';
        if (column1Color == 'transparent' ||
            column1Color == 'interactive-yellow') {
              column1FontColor = 'black';
            }
        column1.setAttribute('style', '--column1-color: var(--bg-'+column1Color+'); --column1-font-color: var(--font-' + column1FontColor + ')' )
      },
      data: function() {
        var dataColumn1Color = '';

        if ( this.data.FirstColumn_color ) {
          dataColumn1Color = this.data.FirstColumn_color;

          this.element.data('column1-color', dataColumn1Color);
        }

        var column1 = this.element.findOne('.column1')
        var column1FontColor = 'white';
        if (dataColumn1Color == 'transparent' ||
            dataColumn1Color == 'interactive-yellow') {
              column1FontColor = 'black';
            }

        column1.setAttribute('style', '--column1-color: var(--bg-'+dataColumn1Color+'); --column1-font-color: var(--font-' + column1FontColor + ')')

      }
    });
  }
});
