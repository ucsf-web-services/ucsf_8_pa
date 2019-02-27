CKEDITOR.plugins.add('domnumbercallout', {
  requires: 'widget',
  icons: 'domnumbercallout',
  beforeInit: function (editor) {

    // Configure CKEditor DTD for custom drupal-entity element.
    // @see https://www.drupal.org/node/2448449#comment-9717735
    var dtd = CKEDITOR.dtd, tagName;

    dtd['dom-numbercallout'] = {'p': 1, 'div': 1, 'span': 1};
    for (tagName in dtd) {
      if (dtd[tagName].p) {
        dtd[tagName]['dom-numbercallout'] = 1;
      }
    }
  },
  init: function(editor) {
    CKEDITOR.dialog.add('domnumbercallout', this.path + 'dialogs/domnumbercallout.js' );
    var pluginDirectory = this.path;
    editor.addContentsCss( pluginDirectory + 'css/domnumbercallout.css' );
    editor.widgets.add('domnumbercallout', {
      // allowedContent: 'dom-numbercallout h2 p a(*);',

      // requiredContent: 'dom-numbercallout; h2 p a(*);',

      editables: {
        numberhighlight: {
          selector: '.number-highlight',
        },
        numberdesc: {
          selector: '.number-desc',
        },
        numberlink: {
          selector: '.number-link',
        }
      },

      template:
        '<dom-numbercallout>' +
          '<p slot="number-highlight" class="number-highlight">$30+</p>' +
          '<div slot="number-desc" class="number-desc">AFFILIATED INSTITUTES AND PROGRAMS</div>' +
          '<div slot="number-link" class="number-link"><a class="btn-text" href="/change-me"">See our publications</a></div>' +
        '</dom-numbercallout>',

      button: 'Create a Number Callout',
      dialog: 'domnumbercallout',

      upcast: function(element) {
        return element.name == 'dom-numbercallout';
      },
      init: function() {
        var numberColor = this.element.getAttribute('data-number-color')
        this.setData( 'number_color', numberColor);

        var number = this.element.findOne('.number-highlight')
        number.setAttribute('style', '--number-color: var(--font-'+numberColor+');)' )
      },
      data: function() {
        var dataNumberColor = '';

        if ( this.data.number_color ) {
          dataNumberColor = this.data.number_color;
          this.element.data('number-color', dataNumberColor);
        }

        var number = this.element.findOne('.number-highlight')
        number.setAttribute('style', '--number-color: var(--font-'+dataNumberColor+');)' )
        // var extractNum = number.getHtml().replace(/<(?:.|\n)*?>/gm, '').split(/(\d+)/);
        // newNum = '';
        // extractNum.forEach(function (item) {
        //
        //   if (item && !isNaN(item) ) {
        //     newNum += '<span class="counter">'+item+'</span>' ;
        //   } else {
        //     newNum += item;
        //   }
        // })
        // number.setHtml(newNum);
      }
    });
  }
});
