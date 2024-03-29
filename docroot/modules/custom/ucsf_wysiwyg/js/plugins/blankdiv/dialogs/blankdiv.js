

CKEDITOR.dialog.add('blankdiv', function( editor ) {
  return {
    title: 'Edit blankdiv Settings',
    minWidth: 400,
    minHeight: 350,
    contents: [
      {
        id: 'info',
        elements: [
          {
            id: 'align',
            type: 'select',
            label: 'Align',
            title: 'Align - set the elements container alignment.',
            items: [
              //[ editor.lang.common.alignCenter, 'align-center' ],
              //[ editor.lang.common.alignLeft, 'align-left' ],
              //[ editor.lang.common.alignRight, 'align-right' ],
              ['Half Image Right', 'half-image-right'],
              ['Half Image Left', 'half-image-left'],
              ['Full Bleed Right', 'half-image-right-full'],
              ['Full Bleed Left', 'half-image-left-full'],
              ['Full Bleed',  'full-bleed-image' ]
            ],
            setup: function( widget ) {
              this.setValue( widget.data.align ? widget.data.align : 'half-image-left' );
            },
            commit: function( widget ) {
              widget.setData( 'align', this.getValue() );
            }
          },
          {
            id: 'blankdiv',
            type: 'textarea',
            label: 'Script',
            rows: 12,
            setup: function( widget ) {
              var blankdiv = decodeURIComponent(widget.data.blankdiv);
              if (blankdiv == 'undefined') {
                blankdiv = '';
              }
              // must remove <!--{cke_protected} and --> but protect comments.
              if (blankdiv.indexOf("<!--{cke_protected}") > -1) {
                //console.log('Index of cke_protected is: ' + blankdiv.indexOf("<!--{cke_protected}") );
                blankdiv = blankdiv.replace('<!--{cke_protected}', '');
                blankdiv = blankdiv.replace('-->', '');
              }
              this.setValue( blankdiv );
            },
            commit: function( widget ) {
              widget.setData( 'blankdiv', this.getValue() );
            }
          }
        ]
      }
    ]
  };
} );


/*
 We decided against this second field for now, doesn't seem needed,
 as the alignment property contains the width attributes in % of canvas.

 {
 id: 'size',
 type: 'select',
 label: 'Style',
 title: 'Style - Also known as Size, defines the size of the elements container.',
 items: [
 ['None (original image)', 'none'],
 ['WYSIWYG Full Bleed Half Image (680 x Y)', 'full_bleed_half__image'],
 ['WYSIWYG Full-Bleed Image (1280 x y)', 'full_bleed__image'],
 ['WYSIWYG Half Image (480 x Y)', 'half__image'],
 ['Quarter Image (220 x y)', 'quarter'],
 ['WYSIWYG Full Image (850 x y)', 'w'],
 ['WYSIWYG Callout square (150 x 150)', 'callout__image']
 ],
 setup: function( widget ) {
 this.setValue( widget.data.size ? widget.data.size : 'none' );
 },
 commit: function( widget ) {
 widget.setData( 'size', this.getValue() );
 }
 },


 OLD SETTINGS
 //[ 'Full Width', 'full' ],
 //[ 'Half Width', 'half' ],
 //[ 'Two Fifth', 'twofifth' ],
 //[ 'One Third', 'third' ]

 Left
 Center
 Right
 Half Image Right
 Half Image Left
 Full Bleed Right
 Full Bleed Left
 Full Bleed

 'half-image-right' => t('Half Image Right'),
 'half-image-left' => t('Half Image Left'),
 'half-image-right-full' => t('Full Bleed Right'),
 'half-image-left-full' => t('Full Bleed Left'),
 'full-bleed-image' => t('Full Bleed')

 None (Original Image)
 WYSIWYG Full Bleed Half Image (and 680 x Y)
 WYSIWYG Full-Bleed Image (1280 x y)
 WYSIWYG Half Image (480 x y)
 Quarter Image (220 x y)
 WYSIWYG Full Image (850 x y)
 WYSIWYG Callout square (150 x 150)
 */

