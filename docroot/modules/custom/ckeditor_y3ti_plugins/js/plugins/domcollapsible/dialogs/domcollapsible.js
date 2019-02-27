/**
 * Copyright (c) 2014, CKSource - Frederico Knabben. All rights reserved.
 * Licensed under the terms of the MIT License (see LICENSE.md).
 */

CKEDITOR.dialog.add('domcollapsible', function( editor ) {
  return {
    title: 'Collapsible Item',
    minWidth: 400,
    minHeight: 75,
    contents: [
      {
        id: 'info',
        elements: [
          {
            id: 'opened',
            type: 'checkbox',
            label: 'Box open by default',
            setup: function( widget ) {
              this.setValue( widget.data.opened ? widget.data.opened : '' );
            },
            // When committing (saving) this field, set its value to the widget data.
            commit: function( widget ) {
              widget.setData( 'opened', this.getValue() );
            }
          }
        ]
      }
    ],
    onLoad: function () {
      this.getElement().findOne('label').setAttribute('style','display:inline-block;')
    }
  };
} );
