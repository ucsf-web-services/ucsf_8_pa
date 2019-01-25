/**
 * Copyright (c) 2014, CKSource - Frederico Knabben. All rights reserved.
 * Licensed under the terms of the MIT License (see LICENSE.md).
 */

CKEDITOR.dialog.add('ucsfcalloutbox', function( editor ) {
  return {
    title: 'Edit Callout Box Settings',
    minWidth: 300,
    minHeight: 150,
    contents: [
      {
        id: 'info',
        elements: [
          {
            id: 'align',
            type: 'select',
            label: 'Align',
            items: [
              [ editor.lang.common.alignLeft, 'left' ],
              [ editor.lang.common.alignRight, 'right' ]
            ],
            setup: function( widget ) {
              this.setValue( widget.data.align ? widget.data.align : 'left' );
            },
            commit: function( widget ) {
              widget.setData( 'align', this.getValue() );
            }
          },
          {
            id: 'calltoactionlink',
            type: 'text',
            label: 'Align',
            items: [
              [ editor.lang.common.alignCenter, 'center' ],
              [ editor.lang.common.alignLeft, 'left' ],
              [ editor.lang.common.alignRight, 'right' ]
            ],
            setup: function( widget ) {
              this.setValue( widget.data.align ? widget.data.align : '' );
            },
            commit: function( widget ) {
              widget.setData( 'calltoactionlink', this.getValue() );
            }
          }
        ]
      }
    ]
  };
} );
