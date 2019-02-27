CKEDITOR.plugins.add('domfourcolumn', {
  requires: 'widget',
  icons: 'domfourcolumn',
  beforeInit: function (editor) {

    // Configure CKEditor DTD for custom drupal-entity element.
    // @see https://www.drupal.org/node/2448449#comment-9717735
    var dtd = CKEDITOR.dtd, tagName;

    dtd['dom-fourcolumn-layout'] = {'div': 1};
    for (tagName in dtd) {
      if (dtd[tagName].p) {
        dtd[tagName]['dom-fourcolumn-layout'] = 1;
      }
    }
  },
  init: function(editor) {
    CKEDITOR.dialog.add('domfourcolumn', this.path + 'dialogs/domfourcolumn.js' );
    var pluginDirectory = this.path;
    editor.addContentsCss( pluginDirectory + 'css/domfourcolumn.css' );
    editor.widgets.add('domfourcolumn', {
      allowedContent: 'dom-fourcolumn-layout(*)',

      requiredContent: 'dom-fourcolumn-layout',

      editables: {
        firstCol: {
          selector: '.column1',
        },
        secondCol: {
          selector: '.column2',
        },
        thirdCol: {
          selector: '.column3',
        },
        fourthCol: {
          selector: '.column4',
        }
      },

      // Define the template of a new Simple Box widget.
      // The template will be used when creating new instances of the Simple Box widget.
      template:
        '<dom-fourcolumn-layout>' +
          '<div slot="column1" class="column1"><p>Lorem ipsum dolor sit amet, ex labore vivendo laboramus has, vel at putant legendos. Quod appareat id eos, noster malorum et mea.</p></div>' +
          '<div slot="column2" class="column2"><p>Lorem ipsum dolor sit amet, ex labore vivendo laboramus has, vel at putant legendos. Quod appareat id eos, noster malorum et mea.</p></div>' +
          '<div slot="column3" class="column3"><p>Lorem ipsum dolor sit amet, ex labore vivendo laboramus has, vel at putant legendos. Quod appareat id eos, noster malorum et mea.</p></div>' +
          '<div slot="column4" class="column4"><p>Lorem ipsum dolor sit amet, ex labore vivendo laboramus has, vel at putant legendos. Quod appareat id eos, noster malorum et mea.</p></div>' +
        '</dom-fourcolumn-layout>',

      button: 'Create four columns',
      dialog: 'domfourcolumn',

      upcast: function(element) {
        return element.name == 'dom-fourcolumn-layout';
      },
      init: function() {

        var column1Color = this.element.getAttribute('data-column1-color')
        var column2Color = this.element.getAttribute('data-column2-color')
        var column3Color = this.element.getAttribute('data-column3-color')
        var column4Color = this.element.getAttribute('data-column4-color')
        this.setData( 'FirstColumn_color', column1Color);
        this.setData( 'SecondColumn_color', column2Color);
        this.setData( 'ThirdColumn_color', column3Color);
        this.setData( 'FourthColumn_color', column4Color);

        var column1Size = this.element.getAttribute('data-column1-size');
        var column2Size = this.element.getAttribute('data-column2-size');
        var column3Size = this.element.getAttribute('data-column3-size');
        var column4Size = this.element.getAttribute('data-column4-size');
        this.setData( 'layout',column1Size+'-'+column2Size+'-'+column3Size+'-'+column4Size);

        var column1 = this.element.findOne('.column1')
        var column2 = this.element.findOne('.column2')
        var column3 = this.element.findOne('.column3')
        var column4 = this.element.findOne('.column4')
        var column1FontColor = column2FontColor = column3FontColor = column4FontColor = 'white';
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
        if (column4Color == 'transparent' ||
            column4Color == 'interactive-yellow') {
              column4FontColor = 'black';
            }
            column1.setAttribute('style', '--column1-color: var(--bg-'+column1Color+'); --column1-size: var(--size-'+column1Size+'); --column1-font-color: var(--font-' + column1FontColor + ')' )
            column2.setAttribute('style', '--column2-color: var(--bg-'+column2Color+'); --column2-size: var(--size-'+column2Size+'); --column2-font-color: var(--font-' + column2FontColor + ')' )
        column3.setAttribute('style', '--column3-color: var(--bg-'+column3Color+'); --column3-size: var(--size-'+column3Size+'); --column3-font-color: var(--font-' + column3FontColor + ')' )
          column4.setAttribute('style', '--column4-color: var(--bg-'+column4Color+'); --column4-size: var(--size-'+column4Size+'); --column4-font-color: var(--font-' + column4FontColor + ')' )

      },
      data: function() {
        var dataColumn1Color = '';
        var dataColumn2Color = '';
        var dataColumn3Color = '';
        var dataColumn4Color = '';
        var dataColumn1Size = '';
        var dataColumn2Size = '';
        var dataColumn3Size = '';
        var dataColumn4Size = '';
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
        if ( this.data.FourthColumn_color ) {
          dataColumn4Color = this.data.FourthColumn_color;
          this.element.data('column4-color', dataColumn4Color);
        }
        if ( this.data.layout ) {
          sizeArray = this.data.layout.split('-');
          dataColumn1Size = sizeArray[0];
          dataColumn2Size = sizeArray[1];
          dataColumn3Size = sizeArray[2];
          dataColumn4Size = sizeArray[3];
          this.element.data('column1-size', dataColumn1Size);
          this.element.data('column2-size', dataColumn2Size);
          this.element.data('column3-size', dataColumn3Size);
          this.element.data('column4-size', dataColumn4Size);
        }
        var column1 = this.element.findOne('.column1')
        var column2 = this.element.findOne('.column2')
        var column3 = this.element.findOne('.column3')
        var column4 = this.element.findOne('.column4')
        var column1FontColor = column2FontColor = column3FontColor = column4FontColor = 'white';
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
        if (dataColumn4Color == 'transparent' ||
            dataColumn4Color == 'interactive-yellow') {
              column4FontColor = 'black';
            }
        column1.setAttribute('style', '--column1-color: var(--bg-'+dataColumn1Color+'); --column1-size: var(--size-'+dataColumn1Size+'); --column1-font-color: var(--font-' + column1FontColor + ')')
        column2.setAttribute('style', '--column2-color: var(--bg-'+dataColumn2Color+'); --column2-size: var(--size-'+dataColumn2Size+'); --column2-font-color: var(--font-' + column2FontColor + ')' )
        column3.setAttribute('style', '--column3-color: var(--bg-'+dataColumn3Color+'); --column3-size: var(--size-'+dataColumn3Size+'); --column3-font-color: var(--font-' + column3FontColor + ')')
        column4.setAttribute('style', '--column4-color: var(--bg-'+dataColumn4Color+'); --column4-size: var(--size-'+dataColumn4Size+'); --column4-font-color: var(--font-' + column4FontColor + ')')
      }
    });
  }
});
