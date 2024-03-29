/**
 * Copyright (c) 2014, CKSource - Frederico Knabben. All rights reserved.
 * Licensed under the terms of the MIT License (see LICENSE.md).
 */

CKEDITOR.dialog.add('domtwocolumn', function( editor ) {
  var colorsSet = editor.config.colorsSetDomTwoColumn;
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
            id: 'layout',
            type: 'select',
            label: 'Layout',
            items: [
              [ '20/80', '20-80' ],
              [ '30/70', '30-70' ],
              [ '40/60', '40-60' ],
              [ '50/50', '50-50' ],
              [ '60/40', '60-40' ],
              [ '70/30', '70-30' ],
              [ '80/20', '80-20' ],
            ],
            // When setting up this field, set its value to the "align" value from widget data.
            // Note: Align values used in the widget need to be the same as those defined in the "items" array above.
            setup: function( widget ) {
              this.setValue( widget.data.layout ? widget.data.layout : '50-50' );
            },
            // When committing (saving) this field, set its value to the widget data.
            commit: function( widget ) {
              widget.setData( 'layout', this.getValue() );
            }
          },
		  {
            id: 'FirstColumn_color',
            type: 'select',
            label: 'First Column Background Color',
            items: colors,
            // When setting up this field, set its value to the "align" value from widget data.
            // Note: Align values used in the widget need to be the same as those defined in the "items" array above.
            setup: function( widget ) {
              this.setValue( widget.data.FirstColumn_color ? widget.data.FirstColumn_color : 'transparent' );
            },
            // When committing (saving) this field, set its value to the widget data.
            commit: function( widget ) {
              widget.setData( 'FirstColumn_color', this.getValue() );
            }
          },
		  {
            id: 'SecondColumn_color',
            type: 'select',
            label: 'Second Column Background Color',
            items: colors,
            // When setting up this field, set its value to the "align" value from widget data.
            // Note: Align values used in the widget need to be the same as those defined in the "items" array above.
            setup: function( widget ) {
              this.setValue( widget.data.SecondColumn_color ? widget.data.SecondColumn_color : 'transparent' );
            },
            // When committing (saving) this field, set its value to the widget data.
            commit: function( widget ) {
              widget.setData( 'SecondColumn_color', this.getValue() );
            }
          }
        ]
      }
    ]
  };
} );
