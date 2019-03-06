CKEDITOR.plugins.add('blankdiv', {
  requires: 'widget',
  icons: 'blankdiv',

  init: function(editor) {
    CKEDITOR.dialog.add('blankdiv', this.path + 'dialogs/blankdiv.js' );
    var config = editor.config.y3titemplatemenuButtons.split(',');
    var button =  config['blankdiv'] == undefined ? 'Create a Empty div' : undefined;

    editor.widgets.add('blankdiv', {
      allowedContent:
        'div(!wysiwyg_blankdiv,align--left,align--right,align--center,size--full,size--half,size--twofifth,size--third);',

      requiredContent: 'div(wysiwyg_blankdiv);',

      template:
        '<div class="wysiwyg_blankdiv">'+
        '</div>',

      button: button,
      dialog: 'blankdiv',

      upcast: function(element) {
        return element.name == 'div' && element.hasClass('wysiwyg_blankdiv');
      },
      init: function() {
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
        if (this.element.getHtml())
          this.setData( 'script', this.element.getHtml())
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

        if (this.data.script)
          this.element.setHtml(this.data.script)
      }
    });
  },
});
