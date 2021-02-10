CKEDITOR.plugins.add('ucsfquote', {
  requires: 'widget',
  icons: 'ucsfquote',
  beforeInit: function (editor) {

    // Configure CKEditor DTD for custom drupal-entity element.
    // @see https://www.drupal.org/node/2448449#comment-9717735
    /*
    var dtd = CKEDITOR.dtd, tagName;
    dtd['ucsfquote'] = {'blockquote': 1, 'div': 1, 'p': 1, 'footer': 1, 'cite': 1};
    for (tagName in dtd) {
        if (dtd[tagName].p) {
        dtd[tagName]['ucsfquote'] = 1;
    }
    }
    */
  },

  init: function(editor) {
    CKEDITOR.dialog.add('ucsfquote', this.path + 'dialogs/ucsfquote.js' );
    editor.addContentsCss( this.path + 'css/ckeditstyles.css' );

    editor.ui.addButton( 'ucsfquote', {
        label: 'Create a UCSF Pull Quote',
        command: 'ucsfquote',
        icon: this.path + '/js/plugins/ucsfquote/icons/ucsfquote.png'
    });

    editor.widgets.add('ucsfquote', {
        //allowedContent:
        //'blockquote(!blockquote,*);',
        //'div(!wysiwyg_quote,*);' +
        //'p(*); footer[*](*);',
        // requiredContent: 'blockquote(!blockquote)',

        editables: {
            contenttext: {
                selector: '.blockquote-content__text',
                allowedContent: 'br strong em; a[!href];'
            },
            cite: {
                selector: '.blockquote-content__cite',
                allowedContent: 'br strong em; a[!href]; cite'
            },
            content: {
                selector: '.wysiwyg_quote--content',
                allowedContent: 'br strong em; a[!href];'
            },
            author: {
                selector: '.wysiwyg_quote--author',
                allowedContent: 'br strong em cite; a[!href];'
            },
            org: {
                selector: '.wysiwyg_quote--org',
                allowedContent: 'br strong em; a[!href];'
            }
        },

      template:
        '<blockquote class="blockquote">' +
            '<p class="blockquote-content__text">Lorem ipsum dolor sit amet, ex labore vivendo laboramus has, vel at putant legendos. Quod appareat id eos, noster malorum et mea.</p>' +
            '<footer>' +
            '<p class="blockquote-content__cite">Vivendo Laboramus, PhD</p>' +
            '</footer>' +
        '</blockquote>',

    /**
     * here for compatibility reference
     template:
     '<div class="wysiwyg_quote">' +
     '<p class="wysiwyg_quote--content">Lorem ipsum dolor sit amet, ex labore vivendo laboramus has, vel at putant legendos. Quod appareat id eos, noster malorum et mea.</p>' +
     '<p class="wysiwyg_quote--author">Firstname Lastname, Title</p>' +
     '<p class="wysiwyg_quote--org">Organization/Company/Source</p>' +
     '</div>',
     */
      button: 'Create a Pull Quote',
      dialog: 'ucsfquote',

      upcast: function(element) {
        return element.name == 'blockquote' && element.hasClass('blockquote');
      },
      init: function() {


        if ( this.element.hasClass( 'blockquote--half-left' ) )
          this.setData( 'align', 'half-left' );
        if ( this.element.hasClass( 'blockquote--half-right' ) )
          this.setData( 'align', 'half-right' );
        if ( this.element.hasClass( 'blockquote--full-right' ) )
          this.setData( 'align', 'full-right' );

        // Set data to selected color
        var classListArr = this.element.$.classList;
        for	( i = 0; i < classListArr.length; i++ ) {
          var currentClass = classListArr[i];
          if (currentClass.startsWith('blockquote--color-') !== false) {
            var color = currentClass.replace(/blockquote--color-/, '');
            this.setData( 'colorAccent', color );
          }
        }

      },
      data: function() {
        // Brutally remove all classes starting with 'blockquote--'
        var classListArr = this.element.$.classList;
        var toRemove = [];

        // Creates an array of classes to remove
        for	( i = 0; i < classListArr.length; i++ ) {
          var currentClass = classListArr[i];
          if (currentClass.startsWith('blockquote--') !== false) {
            toRemove.push(currentClass);
          }
        }

        // Removes classes
        for	( i = 0; i < toRemove.length; i++ ) {
          this.element.removeClass(toRemove[i]);
        }

        // Set new classes if "align" or "colorAccent" widget data is set.
        if ( this.data.align )
          this.element.addClass( 'blockquote--' + this.data.align );

        if ( this.data.align === 'full-right' && this.data.colorAccent) {
          this.element.addClass( 'blockquote--color-' + this.data.colorAccent);
        }

      }
    });
  }
});
