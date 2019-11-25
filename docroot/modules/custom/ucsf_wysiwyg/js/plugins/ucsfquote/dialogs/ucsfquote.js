/**
 * Copyright (c) 2014, CKSource - Frederico Knabben. All rights reserved.
 * Licensed under the terms of the MIT License (see LICENSE.md).
 */

CKEDITOR.dialog.add('ucsfquote', function( editor ) {
  return {
    title: 'Edit Quote Settings',
    minWidth: 250,
    minHeight: 100,
    contents: [
      {
         id: 'info',
         elements: [
//           {
//             id: 'bgColor',
//             type: 'select',
//             label: 'Background Color',
//             items: [
//               [ 'White', 'white' ],
//               [ 'Grey', 'grey' ],
//               [ 'Blue', 'blue' ],
//               [ 'Teal', 'teal' ],
//               [ 'Lime', 'lime' ],
//               [ 'Orange', 'orange' ]
//             ],
//             setup: function( widget ) {
//               this.setValue( widget.data.bgColor ? widget.data.bgColor : 'white' );
//             },
//             commit: function( widget ) {
//               widget.setData( 'bgColor', this.getValue() );
//             }
//           },
          {
            id: 'align',
            type: 'select',
            label: 'Align',
            items: [
              [ editor.lang.common.alignLeft, 'half-left' ],
              [ editor.lang.common.alignRight, 'half-right' ],
              [ 'Align Right Full-Width', 'full-right' ]
            ],
            setup: function( widget ) {
              this.setValue( widget.data.align ? widget.data.align : 'half-left' );
            },
            commit: function( widget ) {
              widget.setData( 'align', this.getValue() );
            }
          },
//           {
//             id: 'size',
//             type: 'select',
//             label: 'Size',
//             items: [
//               [ 'Full Width', 'full' ],
//               [ 'Half Width', 'half' ],
//               [ 'Two Fifth', 'twofifth' ],
//               [ 'One Third', 'third' ]
//             ],
//             setup: function( widget ) {
//               this.setValue( widget.data.size ? widget.data.size : 'twofifth' );
//             },
//             commit: function( widget ) {
//               widget.setData( 'size', this.getValue() );
//             }
//           }
        ]
      }
    ]
  };
} );
