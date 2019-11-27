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
            onChange: function() {
              var dialog = this.getDialog()
              var typeValue = this.getValue()
              var colorSelect = dialog.getContentElement( 'info', 'colorAccent' )

              if (typeValue === 'full-right') {
                colorSelect.getElement().show();
              } else {
                colorSelect.getElement().hide()
              }
            },
            setup: function( widget ) {
              this.setValue( widget.data.align ? widget.data.align : 'half-left' );
            },
            commit: function( widget ) {
              widget.setData( 'align', this.getValue() );
            }
          },

          // Full quote border
          {
            id: 'colorAccent',
            type: 'select',
            label: 'Color Accent',
            items: [
              [ 'Blue', 'blue' ],
              [ 'Grey', 'grey' ],
              [ 'Green', 'green' ],
              [ 'Teal', 'teal' ],
              [ 'Navy', 'navy' ],
              [ 'Purple', 'purple' ]
            ],
            setup: function( widget ) {
              this.setValue( widget.data.colorAccent ? widget.data.colorAccent : 'grey' );
            },
            commit: function( widget ) {
              widget.setData( 'colorAccent', this.getValue() );
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
