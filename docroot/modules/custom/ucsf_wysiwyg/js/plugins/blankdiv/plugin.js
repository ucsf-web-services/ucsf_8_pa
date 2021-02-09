/*

 .left
 .center
 .right
 .half-image-left
 .half-image-right
 .half-image-left-full
 .half-image-right-full
 .full-bleed-image

 full_bleed_half__image
 full_bleed__image
 half__image
 quarter
 w
 */

CKEDITOR.plugins.add('blankdiv', {
  requires: 'widget',
  icons: 'blankdiv',

  init: function(editor) {
    CKEDITOR.dialog.add('blankdiv', this.path + 'dialogs/blankdiv.js' );
    var button = undefined;

    if (editor.config.y3titemplatemenuButtons) {
      var config = editor.config.y3titemplatemenuButtons.split(',');
      var button =  config['blankdiv'] == undefined ? 'Create a Empty div' : button;
    }

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


        var alignSet = [
          'left',
          'center',
          'right',
          'half-image-left',
          'half-image-right',
          'half-image-left-full',
          'half-image-right-full',
          'full-bleed-image'
        ];

        alignSet.forEach(function (item, index) {
          if ( this.element.hasClass( item ) ) {
            console.log('Add align class: ' + item);
            this.setData('align', item);
          }
        }, this);

        var sizeSet = [
          'full_bleed_half__image',
          'full_bleed__image',
          'half__image',
          'quarter',
          'w'
        ];

        sizeSet.forEach(function (item, index) {

          if ( this.element.hasClass( item ) ) {
            this.setData( 'size', item );
            console.log('Add Size class: ' + item);
          }
        }, this);


        /*
        if ( this.element.hasClass( 'center' ) )
          this.setData( 'align', 'center' );
        if ( this.element.hasClass( 'left' ) )
          this.setData( 'align', 'left' );
        if ( this.element.hasClass( 'right' ) )
          this.setData( 'align', 'right' );
        if ( this.element.hasClass( 'half-image-left' ) )
          this.setData( 'align', 'half-image-left' );
        if ( this.element.hasClass( 'half-image-right' ) )
          this.setData( 'align', 'half-image-right' );
        if ( this.element.hasClass( 'half-image-left-full' ) )
          this.setData( 'align', 'half-image-left-full' );
        if ( this.element.hasClass( 'half-image-right-full' ) )
          this.setData( 'align', 'half-image-right-full' );
        if ( this.element.hasClass( 'full-bleed-image' ) )
          this.setData( 'align', 'full-bleed-image' );


        if ( this.element.hasClass( 'full_bleed_half__image' ) )
          this.setData( 'size', 'full_bleed_half__image' );
        if ( this.element.hasClass( 'full_bleed__image' ) )
          this.setData( 'size', 'full_bleed__image' );
        if ( this.element.hasClass( 'half__image' ) )
          this.setData( 'size', 'half__image' );
        if ( this.element.hasClass( 'quarter' ) )
          this.setData( 'size', 'quarter' );
        if ( this.element.hasClass( 'w' ) )
          this.setData( 'size', 'w' );
        */
        if (this.element.getHtml())
          this.setData( 'script', this.element.getHtml())
      },
      data: function() {

        var classRemovals = [
              'left',
              'center',
              'right',
              'half-image-left',
              'half-image-right',
              'half-image-left-full',
              'half-image-right-full',
              'full-bleed-image',
              'full_bleed_half__image',
              'full_bleed__image',
              'half__image',
              'quarter',
              'w'
        ];
        // Brutally remove all align classes and set a new one if "align" widget data is set.
        classRemovals.forEach(function (item, index) {
          console.log('Removed class: ' + item);
          this.element.removeClass( item );
        }, this);

        if ( this.data.align )
          this.element.addClass( this.data.align );

        if ( this.data.size )
          this.element.addClass( this.data.size );

        if (this.data.script)
          this.element.setHtml(this.data.script)
      }
    });
  }
});
