CKEDITOR.plugins.add('ucsfquote', {
  requires: 'widget',
  icons: 'ucsfquote',

  init: function(editor) {
    CKEDITOR.dialog.add('ucsfquote', this.path + 'dialogs/ucsfquote.js' );
    var pluginDirectory = this.path;
//    window.alert(pluginDirectory);
    editor.addContentsCss( pluginDirectory + 'css/ckeditstyles.css' );
    editor.ui.addButton( 'ucsfquote', {
            label: 'Insert ucsfquote',
            command: 'ucsfquote'
            //toolbar: 'insert'
        });

    editor.widgets.add('ucsfquote', {
     allowedContent:
       'div(!wysiwyg_quote,align--left,align--right,align--center,bg--white,bg--grey,bg--blue,bg--teal,bg--lime,bg--orange,size--full,size--half,size--twofifth,size--third);' +
       'p(!wysiwyg_quote--content); p(!wysiwyg_quote--author); p(!wysiwyg_quote--org)',

     requiredContent: 'div(wysiwyg_quote);p(wysiwyg_quote--content);p(wysiwyg_quote--author);p(wysiwyg_quote--org)',

      editables: {
        content: {
          selector: '.wysiwyg_quote--content',
          allowedContent: 'br strong em; a[!href]'
        },
        author: {
          selector: '.wysiwyg_quote--author',
          allowedContent: 'br'
        },
        org: {
          selector: '.wysiwyg_quote--org',
          allowedContent: 'br'
        }
      },

      template:
        '<div class="wysiwyg_quote">' +
          '<p class="wysiwyg_quote--content">Lorem ipsum dolor sit amet, ex labore vivendo laboramus has, vel at putant legendos. Quod appareat id eos, noster malorum et mea.</p>' +
          '<p class="wysiwyg_quote--author">Firstname Lastname, Title</p>' +
          '<p class="wysiwyg_quote--org">Organization/Company/Source</p>' +
        '</div>',

      button: 'Create a Ucsf Quote',
      dialog: 'ucsfquote',

      upcast: function(element) {
        return element.name == 'div' && element.hasClass('wysiwyg_quote');
      },
      init: function() {
        if ( this.element.hasClass( 'bg--white' ) )
          this.setData( 'bgColor', 'white' );
        if ( this.element.hasClass( 'bg--grey' ) )
          this.setData( 'bgColor', 'grey' );
        if ( this.element.hasClass( 'bg--blue' ) )
          this.setData( 'bgColor', 'blue' );
        if ( this.element.hasClass( 'bg--teal' ) )
          this.setData( 'bgColor', 'teal' );
        if ( this.element.hasClass( 'bg--lime' ) )
          this.setData( 'bgColor', 'lime' );
        if ( this.element.hasClass( 'bg--orange' ) )
          this.setData( 'bgColor', 'orange' );

        if ( this.element.hasClass( 'align--center' ) )
          this.setData( 'align', 'center' );
        if ( this.element.hasClass( 'align--left' ) )
          this.setData( 'align', 'left' );
        if ( this.element.hasClass( 'align--right' ) )
          this.setData( 'align', 'right' );

        if ( this.element.hasClass( 'size--full' ) )
          this.setData( 'size', 'full' );
        if ( this.element.hasClass( 'size--half' ) )
          this.setData( 'size', 'half' );
        if ( this.element.hasClass( 'size--twofifth' ) )
          this.setData( 'size', 'twofifth' );
        if ( this.element.hasClass( 'size--third' ) )
          this.setData( 'size', 'third' );

      },
      data: function() {
        // Brutally remove all align classes and set a new one if "align" widget data is set.
        this.element.removeClass( 'align--center' );
        this.element.removeClass( 'align--left' );
        this.element.removeClass( 'align--right' );
        if ( this.data.align )
          this.element.addClass( 'align--' + this.data.align );

        this.element.removeClass( 'size--full' );
        this.element.removeClass( 'size--half' );
        this.element.removeClass( 'size--twofifth' );
        this.element.removeClass( 'size--third' );
        if ( this.data.size )
          this.element.addClass( 'size--' + this.data.size );

        this.element.removeClass( 'bg--white' );
        this.element.removeClass( 'bg--grey' );
        this.element.removeClass( 'bg--blue' );
        this.element.removeClass( 'bg--teal' );
        this.element.removeClass( 'bg--lime' );
        this.element.removeClass( 'bg--orange' );
        if ( this.data.bgColor )
          this.element.addClass( 'bg--' + this.data.bgColor );
      }
    });
  },
});
