/**
 * Copyright (c) 2014, CKSource - Frederico Knabben. All rights reserved.
 * Licensed under the terms of the MIT License (see LICENSE.md).
 *
 * Plugin by Eric Guerin @ UCSF
 * Image size corrilates to the Image Style in Drupal, not CSS,
 * however we will need CSS classes for the iframe to fix the size like the image
 *
 * Left	                .left
 * Center	            .center
 * Right	            .right
 * Half Image Right	    .half-image-left
 * Half Image Left	    .half-image-right
 * Full Bleed Right	    .half-image-left-full
 * Full Bleed Left	    .half-image-right-full
 * Full Bleed	        .full-bleed-image
 *
 *
 * None (Original Image)
 * WYSIWYG Full Bleed Half Image (and 680 x Y)	full_bleed_half__image
 * WYSIWYG Full-Bleed Image (1280 x y)  		full_bleed__image
 * WYSIWYG Half Image (480 x y)				    half__image
 * Quarter Image (220 x y)					    quarter
 * WYSIWYG Full Image (850 x y)				    w
 * WYSIWYG Callout square (150 x 150)			wysiwyg_callout_square_150_x_150_
 *
 */

CKEDITOR.dialog.add('blankdiv', function( editor ) {
  return {
    title: 'Edit blankdiv Settings',
    minWidth: 250,
    minHeight: 100,
    contents: [
      {
        id: 'info',
        elements: [
          {
            id: 'align',
            type: 'select',
            label: 'Align',
            items: [
              [ editor.lang.common.alignCenter, 'center' ],
              [ editor.lang.common.alignLeft,   'left' ],
              [ editor.lang.common.alignRight,  'right' ],
              [ 'Half Image Left',    'half-image-left' ],
              [ 'Half Image Right',   'half-image-right' ],
              [ 'Full Bleed Left',    'half-image-left-full' ],
              [ 'Full Bleed Right',   'half-image-right-full' ],
              [ 'Full Bleed',         'full-bleed-image' ]
            ],
            setup: function( widget ) {
              this.setValue( widget.data.align ? widget.data.align : 'right' );
            },
            commit: function( widget ) {
              widget.setData( 'align', this.getValue() );
            }
          },
          {
            id: 'size',
            type: 'select',
            label: 'Size or Style',
            items: [
              [ 'None', '' ],
              [ 'WYSIWYG Full Bleed Half Image (680 x Y)', 'full_bleed_half__image' ],
              [ 'WYSIWYG Full-Bleed Image (1280 x y)', 'full_bleed__image' ],
              [ 'WYSIWYG Half Image (480 x y)', 'half__image' ],
              [ 'Quarter Image (220 x y)', 'quarter' ],
              [ 'WYSIWYG Full Image (850 x y)', 'w' ]
            ],
            setup: function( widget ) {
              this.setValue( widget.data.size ? widget.data.size : 'twofifth' );
            },
            commit: function( widget ) {
              widget.setData( 'size', this.getValue() );
            }
          },
          {
            id: 'script',
            type: 'textarea',
            label: 'Script',
            setup: function( widget ) {
              this.setValue( widget.data.script);
            },
            commit: function( widget ) {
              widget.setData( 'script', this.getValue() );
            }
          }
        ]
      }
    ]
  };
} );
