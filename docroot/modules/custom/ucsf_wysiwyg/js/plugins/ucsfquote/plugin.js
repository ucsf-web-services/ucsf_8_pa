CKEDITOR.plugins.add('ucsfquote', {
  requires: 'widget',
  icons: 'ucsfquote',
  beforeInit: function (editor) {

    // Configure CKEditor DTD for custom drupal-entity element.
    // @see https://www.drupal.org/node/2448449#comment-9717735
    var dtd = CKEDITOR.dtd, tagName;
    window.alert('hello');
    dtd['ucsfquote'] = {'blockquote': 1, 'div': 1, 'p': 1, 'footer': 1, 'cite': 1};
    for (tagName in dtd) {
      if (dtd[tagName].p) {
        dtd[tagName]['ucsfquote'] = 1;
      }
    }
  },
  init: function(editor) {
    CKEDITOR.dialog.add('ucsfquote', this.path + 'dialogs/ucsfquote.js' );
    var pluginDirectory = this.path;

    editor.addContentsCss( pluginDirectory + 'css/ckeditstyles.css' );
//    window.alert(dtd['div']['ucsfquote']);
    editor.ui.addButton( 'ucsfquote', {
        label: 'Insert Pull Quote',
        command: 'ucsfquote'
    });

    editor.widgets.add('ucsfquote', {
     allowedContent:
     'blockquote(!blockquote,align--left,align--right,align--center,bg--white,bg--grey,bg--blue,bg--teal,bg--lime,bg--orange,size--full,size--half,size--twofifth,size--third);',
 //    'div(!wysiwyg_quote,align--left,align--right,align--center,bg--white,bg--grey,bg--blue,bg--teal,bg--lime,bg--orange,size--full,size--half,size--twofifth,size--third);' +
 //    'p(!wysiwyg_quote--content); p(!wysiwyg_quote--author); p(!wysiwyg_quote--org); div(!blockquote-content); p(!blockquote-content__text); cite(!blockquote-content__cite);',

//     requiredContent: 'div(!blockquote-content);p(!blockquote-content__text);cite(!blockquote-content__cite)',

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
        },
        content-text: {
          selector: '.blockquote-content__text',
          allowedContent: 'br'
        },
//         content-cite: {
//           selector: '.blockquote-content__cite',
//           allowedContent: 'br'
//        }
      },

      template:
        '<blockquote class="blockquote">' +
          '<div class="blockquote-content">' +
            '<p class="blockquote-content__text">Lorem ipsum dolor sit amet, ex labore vivendo laboramus has, vel at putant legendos. Quod appareat id eos, noster malorum et mea.</p>' +
//            '<footer>' +
//              '<cite class="blockquote-content__cite">Name some cite</cite>' +
//            '</footer>' +
          '</div>' +
        '</blockquote>',

      button: 'Create a Pull Quote',
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
