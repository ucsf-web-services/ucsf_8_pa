CKEDITOR.plugins.add('domthreecolumn', {
  requires: 'widget',
  icons: 'domthreecolumn',
  beforeInit: function (editor) {

    // Configure CKEditor DTD for custom drupal-entity element.
    // @see https://www.drupal.org/node/2448449#comment-9717735
    var dtd = CKEDITOR.dtd, tagName;

    dtd['dom-threecolumn-layout'] = {'div': 1};
    for (tagName in dtd) {
      if (dtd[tagName].p) {
        dtd[tagName]['dom-threecolumn-layout'] = 1;
      }
    }
  },
  init: function(editor) {
    CKEDITOR.dialog.add('domthreecolumn', this.path + 'dialogs/domthreecolumn.js' );
    var pluginDirectory = this.path;
    editor.addContentsCss( pluginDirectory + 'css/domthreecolumn.css' );
    editor.widgets.add('domthreecolumn', {
      allowedContent: 'dom-threecolumn-layout(*)',

      requiredContent: 'dom-threecolumn-layout',

      editables: {
        firstCol: {
          selector: '.column1',
        },
        secondCol: {
          selector: '.column2',
        },
        thirdCol: {
          selector: '.column3',
        }
      },

      // Define the template of a new Simple Box widget.
      // The template will be used when creating new instances of the Simple Box widget.
      template:
        '<dom-threecolumn-layout>' +
          '<div slot="column1" class="column1"><p>Lorem ipsum dolor sit amet, ex labore vivendo laboramus has, vel at putant legendos. Quod appareat id eos, noster malorum et mea.</p></div>' +
          '<div slot="column2" class="column2"><p>Lorem ipsum dolor sit amet, ex labore vivendo laboramus has, vel at putant legendos. Quod appareat id eos, noster malorum et mea.</p></div>' +
          '<div slot="column3" class="column3"><p>Lorem ipsum dolor sit amet, ex labore vivendo laboramus has, vel at putant legendos. Quod appareat id eos, noster malorum et mea.</p></div>' +
        '</dom-threecolumn-layout>',

      button: 'Create three columns',
      dialog: 'domthreecolumn',

      upcast: function(element) {
        return element.name == 'dom-threecolumn-layout';
      },
      init: function() {

        var column1Color = this.element.getAttribute('data-column1-color')
        var column2Color = this.element.getAttribute('data-column2-color')
        var column3Color = this.element.getAttribute('data-column3-color')
        this.setData( 'FirstColumn_color', column1Color);
        this.setData( 'SecondColumn_color', column2Color);
        this.setData( 'ThirdColumn_color', column3Color);

        var column1Size = this.element.getAttribute('data-column1-size');
        var column2Size = this.element.getAttribute('data-column2-size');
        var column3Size = this.element.getAttribute('data-column3-size');
        this.setData( 'layout',column1Size+'-'+column2Size+'-'+column3Size);

        var column1 = this.element.findOne('.column1')
        var column2 = this.element.findOne('.column2')
        var column3 = this.element.findOne('.column3')
        var column1FontColor = column2FontColor = column3FontColor = 'white';
        if (column1Color == 'transparent' ||
            column1Color == 'interactive-yellow') {
              column1FontColor = 'black';
            }
        if (column2Color == 'transparent' ||
            column2Color == 'interactive-yellow') {
              column2FontColor = 'black';
            }
        if (column3Color == 'transparent' ||
            column3Color == 'interactive-yellow') {
              column3FontColor = 'black';
            }
            column1.setAttribute('style', '--column1-color: var(--bg-'+column1Color+'); --column1-size: var(--size-'+column1Size+'); --column1-font-color: var(--font-' + column1FontColor + ')' )
            column2.setAttribute('style', '--column2-color: var(--bg-'+column2Color+'); --column2-size: var(--size-'+column2Size+'); --column2-font-color: var(--font-' + column2FontColor + ')' )
        column3.setAttribute('style', '--column3-color: var(--bg-'+column3Color+'); --column3-size: var(--size-'+column3Size+'); --column3-font-color: var(--font-' + column3FontColor + ')' )

      },
      data: function() {
        var dataColumn1Color = '';
        var dataColumn2Color = '';
        var dataColumn3Color = '';
        var dataColumn1Size = '';
        var dataColumn2Size = '';
        var dataColumn3Size = '';
        var sizeArray = [];
        if ( this.data.FirstColumn_color ) {
          dataColumn1Color = this.data.FirstColumn_color;
          this.element.data('column1-color', dataColumn1Color);
        }

        if ( this.data.SecondColumn_color ) {
          dataColumn2Color = this.data.SecondColumn_color;
          this.element.data('column2-color', dataColumn2Color);
        }

        if ( this.data.ThirdColumn_color ) {
          dataColumn3Color = this.data.ThirdColumn_color;
          this.element.data('column3-color', dataColumn3Color);
        }

        if ( this.data.layout ) {
          sizeArray = this.data.layout.split('-');
          dataColumn1Size = sizeArray[0];
          dataColumn2Size = sizeArray[1];
          dataColumn3Size = sizeArray[2];
          this.element.data('column1-size', dataColumn1Size);
          this.element.data('column2-size', dataColumn2Size);
          this.element.data('column3-size', dataColumn3Size);
        }
        var column1 = this.element.findOne('.column1')
        var column2 = this.element.findOne('.column2')
        var column3 = this.element.findOne('.column3')
        var column1FontColor = column2FontColor = column3FontColor = 'white';
        if (dataColumn1Color == 'transparent' ||
            dataColumn1Color == 'interactive-yellow') {
              column1FontColor = 'black';
            }
        if (dataColumn2Color == 'transparent' ||
            dataColumn2Color == 'interactive-yellow') {
              column2FontColor = 'black';
            }
        if (dataColumn3Color == 'transparent' ||
            dataColumn3Color == 'interactive-yellow') {
              column3FontColor = 'black';
            }
        column1.setAttribute('style', '--column1-color: var(--bg-'+dataColumn1Color+'); --column1-size: var(--size-'+dataColumn1Size+'); --column1-font-color: var(--font-' + column1FontColor + ')')
        column2.setAttribute('style', '--column2-color: var(--bg-'+dataColumn2Color+'); --column2-size: var(--size-'+dataColumn2Size+'); --column2-font-color: var(--font-' + column2FontColor + ')' )
        column3.setAttribute('style', '--column3-color: var(--bg-'+dataColumn3Color+'); --column3-size: var(--size-'+dataColumn3Size+'); --column3-font-color: var(--font-' + column3FontColor + ')')
      }
    });
  }
});
