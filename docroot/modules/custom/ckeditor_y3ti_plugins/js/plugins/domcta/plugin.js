CKEDITOR.plugins.add('domcta', {
  requires: 'widget',
  icons: 'domcta',
  beforeInit: function (editor) {

    // Configure CKEditor DTD for custom drupal-entity element.
    // @see https://www.drupal.org/node/2448449#comment-9717735
    var dtd = CKEDITOR.dtd, tagName;

    dtd['dom-cta'] = {'div': 1, 'a': 1, 'h5': 1, 'img': 1, 'p': 1};
    dtd['a'].div = 1;
    dtd['a'].h5 = 1;
    dtd['a'].p = 1;
    for (tagName in dtd) {
      if (dtd[tagName].p) {
        dtd[tagName]['dom-cta'] = 1;
      }
    }
  },
  init: function(editor) {
    CKEDITOR.dialog.add('domcta', this.path + 'dialogs/domcta.js' );
    var pluginDirectory = this.path;
    editor.addContentsCss( pluginDirectory + 'css/domcta.css' );
    editor.widgets.add('domcta', {
      // allowedContent: 'dom-cta h2 p a(*);',

      // requiredContent: 'dom-cta; h2 p a(*);',

      editables: {
        title: {
          selector: '.simple-cta-content',
        },
        cta: {
          selector: '.link',
        },
        image: {
          selector: '.image',
        },
      },

      template:
        '<dom-cta class="simple-cta">' +
          '<p class="image"><img src="'+pluginDirectory + 'images/image-placeholder.jpg" /></p>' +
          '<a class="link" href="/change-me">' +
          '<div class="simple-cta-content"><h5>Message from the Chair</h5></div>' +
          '</a>' +
        '</dom-cta>',
        // '<dom-cta>' +
        //   '<div slot="title" class="title"><h2>Lorem ipsum dolor</h2></div>' +
        //   '<div slot="description" class="description"><p>Ex labore vivendo laboramus has, vel at putant legendos. Quod appareat id eos, noster malorum et mea.</p></div>' +
        //   '<div slot="cta" class="cta"><a class="btn--small btn--blue" href="/change-me">Lorem ipsum dolor</a></div>' +
        // '</dom-cta>',
      button: 'Create a CTA',
      dialog: 'domcta',

      upcast: function(element) {
        return element.name == 'dom-cta';
      },
      init: function() {
        var ctaColor = this.element.getAttribute('data-cta-color')
        this.setData( 'CTA_color', ctaColor);

        var cta = this.element;
        console.log(this.element);
        var ctaFontColor = 'white';
        if (ctaColor == 'transparent' ||
            ctaColor == 'interactive-yellow') {
              ctaFontColor = 'black';
            }

        cta.setAttribute('style', '--cta-color: var(--bg-'+ctaColor+'); --cta-font-color: var(--font-' + ctaFontColor + ')' )
      },
      data: function() {
        var dataCtaColor = '';

        if ( this.data.CTA_color ) {
          dataCtaColor = this.data.CTA_color;

          this.element.data('cta-color', dataCtaColor);
        }
        var cta = this.element
        var ctaFontColor = 'white';
        if (dataCtaColor == 'transparent' ||
            dataCtaColor == 'interactive-yellow') {
              ctaFontColor = 'black';
            }

        cta.setAttribute('style', '--cta-color: var(--bg-'+dataCtaColor+'); --cta-font-color: var(--font-' + ctaFontColor + ')')
      }
    });
  }
});
