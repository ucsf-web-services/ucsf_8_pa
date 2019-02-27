CKEDITOR.plugins.add('domtwocolumn', {
  requires: 'widget',
  icons: 'domtwocolumn',
  beforeInit: function (editor) {

    // Configure CKEditor DTD for custom drupal-entity element.
    // @see https://www.drupal.org/node/2448449#comment-9717735
    var dtd = CKEDITOR.dtd, tagName;

    dtd['dom-twocolumn-layout'] = {'div': 1};
    for (tagName in dtd) {
      if (dtd[tagName].p) {
        dtd[tagName]['dom-twocolumn-layout'] = 1;
      }
    }
  },
  init: function(editor) {
    CKEDITOR.dialog.add('domtwocolumn', this.path + 'dialogs/domtwocolumn.js' );
    var pluginDirectory = this.path;
    editor.addContentsCss( pluginDirectory + 'css/domtwocolumn.css' );
    editor.widgets.add('domtwocolumn', {
      allowedContent: 'dom-twocolumn-layout(*)',

      requiredContent: 'dom-twocolumn-layout',

      editables: {
        firstCol: {
          selector: '.column1',
        },
        secondCol: {
          selector: '.column2',
        },
      },

      template:
        '<dom-twocolumn-layout>' +
          '<div slot="column1" class="column1"><p>Lorem ipsum dolor sit amet, ex labore vivendo laboramus has, vel at putant legendos. Quod appareat id eos, noster malorum et mea.</p></div>' +
          '<div slot="column2" class="column2"><p>Lorem ipsum dolor sit amet, ex labore vivendo laboramus has, vel at putant legendos. Quod appareat id eos, noster malorum et mea.</p></div>' +
        '</dom-twocolumn-layout>',

      button: 'Create two columns',
      dialog: 'domtwocolumn',

      upcast: function(element) {
        return element.name == 'dom-twocolumn-layout';
      },
      init: function() {
        var column1Color = this.element.getAttribute('data-column1-color')
        var column2Color = this.element.getAttribute('data-column2-color')
        this.setData( 'FirstColumn_color', column1Color);
        this.setData( 'SecondColumn_color', column2Color);

        var column1Size = this.element.getAttribute('data-column1-size');
        var column2Size = this.element.getAttribute('data-column2-size');
        this.setData( 'layout',column1Size+'-'+column2Size);

        var column1 = this.element.findOne('.column1')
        var column2 = this.element.findOne('.column2')
        var column1FontColor = column2FontColor = 'black';
        if (column1Color == 'transparent' ||
            column1Color == 'interactive-yellow') {
              column1FontColor = 'black';
            }
        if (column2Color == 'transparent' ||
            column2Color == 'interactive-yellow') {
              column2FontColor = 'black';
            }

        column1.setAttribute('style', '--column1-color: var(--bg-'+column1Color+'); --column1-size: var(--size-'+column1Size+'); --column1-font-color: var(--font-' + column1FontColor + ')' )
        column2.setAttribute('style', '--column2-color: var(--bg-'+column2Color+'); --column2-size: var(--size-'+column2Size+'); --column2-font-color: var(--font-' + column2FontColor + ')' )
      },
      data: function() {
        var dataColumn1Color = '';
        var dataColumn2Color = '';
        var dataColumn1Size = '';
        var dataColumn2Size = '';
        var sizeArray = [];
        if ( this.data.FirstColumn_color ) {
          dataColumn1Color = this.data.FirstColumn_color;

          this.element.data('column1-color', dataColumn1Color);
        }

        if ( this.data.SecondColumn_color ) {
          dataColumn2Color = this.data.SecondColumn_color;
          this.element.data('column2-color', dataColumn2Color);
        }

        if ( this.data.layout ) {
          sizeArray = this.data.layout.split('-');
          dataColumn1Size = sizeArray[0];
          dataColumn2Size = sizeArray[1];
          this.element.data('column1-size', dataColumn1Size);
          this.element.data('column2-size', dataColumn2Size);
        }
        var column1 = this.element.findOne('.column1')
        var column2 = this.element.findOne('.column2')
        var column1FontColor = column2FontColor = 'white';
        if (dataColumn1Color == 'transparent' ||
            dataColumn1Color == 'interactive-yellow') {
              column1FontColor = 'black';
            }
        if (dataColumn2Color == 'transparent' ||
            dataColumn2Color == 'interactive-yellow') {
              column2FontColor = 'black';
            }
        column1.setAttribute('style', '--column1-color: var(--bg-'+dataColumn1Color+'); --column1-size: var(--size-'+dataColumn1Size+'); --column1-font-color: var(--font-' + column1FontColor + ')')
        column2.setAttribute('style', '--column2-color: var(--bg-'+dataColumn2Color+'); --column2-size: var(--size-'+dataColumn2Size+'); --column2-font-color: var(--font-' + column2FontColor + ')' )

      }
    });
  }
});
