/**
 * Copyright (c) 2014, CKSource - Frederico Knabben. All rights reserved.
 * Licensed under the terms of the MIT License (see LICENSE.md).
 */

CKEDITOR.dialog.add('domnumbercallout', function( editor ) {
  var colorsSet = editor.config.colorsSetDomNumberCallout;
  var colors = []
  var i = 0;
  for (var color in colorsSet ){
    if (colorsSet.hasOwnProperty(color)) {
      if (colorsSet[color]) {
        colors[i] = [color.replace(/-/g, ' '),color]
        i++;
      }
    }
  }
  return {
    title: 'Two Columns',
    minWidth: 200,
    minHeight: 100,
    contents: [
      {
        id: 'info',
        elements: [
	        {
            id: 'Number_color',
            type: 'select',
            label: 'Number Color',
            items: colors,
            // When setting up this field, set its value to the "align" value from widget data.
            // Note: Align values used in the widget need to be the same as those defined in the "items" array above.
            setup: function( widget ) {
              this.setValue( widget.data.number_color ? widget.data.number_color : 'default' );
            },
            // When committing (saving) this field, set its value to the widget data.
            commit: function( widget ) {
              widget.setData( 'number_color', this.getValue() );
            }
          },
        ]
      }
    ]
  };
} );
