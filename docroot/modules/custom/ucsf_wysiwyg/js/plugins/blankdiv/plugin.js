
CKEDITOR.plugins.add('blankdiv', {
  requires: 'widget',
  icons: 'blankdiv',
  beforeInit : function (editor) {

  },
  init: function(editor) {

    CKEDITOR.dialog.add('blankdiv', this.path + 'dialogs/blankdiv.js' );
    editor.addContentsCss( this.path + 'css/blankdiv.css' );
    editor.widgets.add('blankdiv', {
      allowedContent:
        'div(!wysiwyg_blankdiv,align-*,half-image-*, full-bleed-image, callout__image, quarter, half__image, full_bleed_*)',
      requiredContent: 'div(!wysiwyg_blankdiv)',
      template:
        '<div class="wysiwyg_blankdiv">'+
        '</div>',

      dialog: 'blankdiv',
      button: 'Create a Empty div',
      upcast: function(element) {
        return element.name == 'div' && element.hasClass('wysiwyg_blankdiv');
      },
      init: function() {
        // align options
        if ( this.element.hasClass( 'align-center' ) ) this.setData( 'align', 'align-center' );
        if ( this.element.hasClass( 'align-left' ) ) this.setData( 'align', 'align-left' );
        if ( this.element.hasClass( 'align-right' ) ) this.setData( 'align', 'align-right' );
        if ( this.element.hasClass( 'half-image-right' ) ) this.setData( 'align', 'half-image-right' );
        if ( this.element.hasClass( 'half-image-left' ) ) this.setData( 'align', 'half-image-left' );
        if ( this.element.hasClass( 'half-image-right-full' ) ) this.setData( 'align', 'half-image-right-full' );
        if ( this.element.hasClass( 'half-image-left-full' ) ) this.setData( 'align', 'half-image-left-full' );
        if ( this.element.hasClass( 'full-bleed-image' ) ) this.setData( 'align', 'full-bleed-image'  );
        // size - style options
        if ( this.element.hasClass( 'full_bleed_half__image' ) ) this.setData( 'size', 'full_bleed_half__image' );
        if ( this.element.hasClass( 'full_bleed__image' ) ) this.setData( 'size', 'full_bleed__image' );
        if ( this.element.hasClass( 'half__image' ) ) this.setData( 'size', 'half__image' );
        if ( this.element.hasClass( 'quarter' ) ) this.setData( 'size', 'quarter' );
        if ( this.element.hasClass( 'w' ) ) this.setData( 'size', 'w' );
        if ( this.element.hasClass( 'callout__image' ) ) this.setData( 'size', 'callout__image' );

        if ( this.element.getHtml() ) this.setData( 'script', this.element.getHtml() );
      },
      data: function() {
        // Brutally remove all align classes and set a new one if "align" widget data is set.
        const align = ['align-center', 'align-left', 'align-right',
          'half-image-right', 'half-image-left',
          'half-image-right-full', 'half-image-left-full',
          'full-bleed-image'];

        align.forEach(function (item, index) {
          console.log(item, index);
          if (this.element.hasClass( item )) this.element.removeClass( item );
        }, this);

        const size = ['full_bleed_half__image', 'full_bleed__image',
          'half__image', 'quarter',  'w',
          'callout__image'];

        size.forEach(function (item, index) {
          console.log(item, index);
          if (this.element.hasClass( item )) this.element.removeClass( item );
        }, this);

        // Add new classes
        if ( this.data.align ) this.element.addClass( this.data.align );
        if ( this.data.size ) this.element.addClass(  this.data.size );
        if ( this.data.script ) this.element.setHtml(this.data.script)
      }
    });
  }
});
