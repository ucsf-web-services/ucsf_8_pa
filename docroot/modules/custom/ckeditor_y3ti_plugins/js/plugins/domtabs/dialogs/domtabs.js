/**
 * Copyright (c) 2014, CKSource - Frederico Knabben. All rights reserved.
 * Licensed under the terms of the MIT License (see LICENSE.md).
 */

CKEDITOR.dialog.add('domtabs', function( editor ) {
  return {
    title: 'Tabs',
    minWidth: 400,
    minHeight: 100,
    contents: [
      {
        id: 'tab1',
        label: 'Tab 1',
        elements: [
          {
            type: 'select',
            id: 'number-of-tabs',
            label: "Number of Tabs",
            "default": 4,
            items: [ ['2'], ['3'], ['4'], ['5'], ['6'], ['7'], ['8'], ['9'] ],
            // Validation for empty values.
            validate: CKEDITOR.dialog.validate.notEmpty( 'Number of Tabs field cannot be empty.' ),
            setup: function( widget ) {
              this.setValue( widget.data.numOfTabs ? widget.data.numOfTabs : '' );
            },
            // When committing (saving) this field, set its value to the widget data.
            commit: function( widget ) {
              widget.setData( 'numOfTabs', this.getValue() );
            }
          }
        ]
      }
    ]
  };
} );
