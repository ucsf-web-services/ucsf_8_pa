/**
 * Copyright (c) 2014, CKSource - Frederico Knabben. All rights reserved.
 * Licensed under the terms of the MIT License (see LICENSE.md).
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
              [ editor.lang.common.alignLeft, 'left' ],
              [ editor.lang.common.alignRight, 'right' ]
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
            label: 'Size',
            items: [
              [ 'Full Width', 'full' ],
              [ 'Half Width', 'half' ],
              [ 'Two Fifth', 'twofifth' ],
              [ 'One Third', 'third' ]
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
