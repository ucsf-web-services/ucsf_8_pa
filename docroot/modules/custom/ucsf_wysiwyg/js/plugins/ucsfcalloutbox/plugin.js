CKEDITOR.plugins.add('ucsfcalloutbox', {
  requires: 'widget',
  icons: 'ucsfcalloutbox',

  init: function(editor) {
    CKEDITOR.dialog.add('ucsfcalloutbox', this.path + 'dialogs/ucsfcalloutbox.js' );
    //var config = editor.config.y3titemplatemenuButtons.split(',');
    //var button =  config['pullquote'] == undefined ? 'Create a Pullquote' : undefined;
    editor.ui.addButton( 'ucsfcalloutbox', {
        label: 'Insert UCSF Callout Box',
        command: 'ucsfcalloutbox'

    });

    editor.widgets.add('ucsfcalloutbox', {
     allowedContent:
       'div(!ucsfcallout,align--left,align--right,align--center);',
       //'div(!ucsfcallout--content); div(!ucsfcallout--title); div(!ucsfcallout--related); div(!ucsfcallout--cta); ' +
       //'div(!ucsfcallout--image);',

     //requiredContent: 'div(ucsfcallout); div(ucsfcallout--title); div(ucsfcallout--content);',

      editables: {
        content: {
          selector: '.ucsfcallout--content',
          allowedContent: 'p br strong em a[!href] '
        },
        title: {
          selector: '.ucsfcallout--title',
          allowedContent: 'h1 h2 h3 h4 h5 h6 p br strong em;'
        },
        image: {
            selector: '.ucsfcallout--image',
            allowedContent: 'img picture p br strong em a[!href] source video'
        },
        related: {
          selector: '.ucsfcallout--related',
          allowedContent: 'h4 h4 h5 h6 p br strong em a[!href] ul ol li'
        },
        cta: {
          selector: '.ucsfcallout--cta',
          allowedContent: 'img picture p br strong em a[!href] source'
        }
      },

      template:
        '<div class="ucsfcallout">' +
          '<div class="ucsfcallout--image">Image Placeholder</div>' +
          '<div class="ucsfcallout--title">Title</div>' +
          '<div class="ucsfcallout--content">Content</div>' +
          '<div class="ucsfcallout--related">Related Stories...</div>' +
            '<div class="ucsfcallout--cta">' +
                '<a href="" class="button ucsfcallout--cta">Call to Action Text</a>' +
            '</div>' +
        '</div>',

      button: 'Create a UCSF Callout Box',
      dialog: 'ucsfcalloutbox',

      upcast: function(element) {
        return element.name == 'div' && element.hasClass('ucsfcallout');
      },
      init: function() {

        if ( this.element.hasClass( 'align--left' ) )
          this.setData( 'align', 'left' );
        if ( this.element.hasClass( 'align--right' ) )
          this.setData( 'align', 'right' );

      },
      data: function() {


        /*
        // Brutally remove all align classes and set a new one if "align" widget data is set.
        this.element.removeClass( 'align--center' );
        this.element.removeClass( 'align--left' );
        this.element.removeClass( 'align--right' );
        if ( this.data.align )
          this.element.addClass( 'align--' + this.data.align );

        if ( this.data.size )
          this.element.addClass( 'size--' + this.data.size );

        this.element.removeClass( 'bg--orange' );

        if ( this.data.bgColor ) {
          this.element.addClass( 'bg--' + this.data.bgColor );
        }
        */
      }
    });
  }
});
