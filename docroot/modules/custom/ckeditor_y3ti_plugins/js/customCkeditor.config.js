CKEDITOR.on( 'dialogDefinition', function( ev )
{
   var dialogName = ev.data.name;
   var dialogDefinition = ev.data.definition;
   if (dialogName == 'link')
   {
      var infoTab  = dialogDefinition.getContents( 'info' );
      var advancedTab = dialogDefinition.getContents( 'advanced' );

      var item = advancedTab.get('advCSSClasses');
      item['style'] = 'display:none';
      var buttonLink = {
            type: 'select',
            id: 'buttonLink',
            label: 'Button link Style',
            // items: [ [ 'Large', 'btn--large'], [ 'Small', 'btn--small' ], ['Button White', 'btn btn--more--white'], ['Button Navy', 'btn btn--more--reverse'], ['None', ''] ],
            items: [
              [ 'Blue Button', 'btn btn-primary' ],
              [ 'Blue Button Large Full Width', 'btn btn-primary btn-lg btn-block' ],
              [ 'Blue Button Small', 'btn btn-primary btn-xs' ],
              [ 'Dark Gray Button', 'btn btn-secondary' ],
              [ 'Dark Gray Button Large Full Width', 'btn btn-secondary btn-lg btn-block' ],
              [ 'Dark Gray Button Small', 'btn btn-secondary btn-xs' ],
              [ 'Purple Button', 'btn btn-warning' ],
              [ 'Purple Button Small', 'btn btn-warning btn-xs' ],
              [ 'Purple Button Large Full Width', 'btn btn-warning btn-lg btn-block' ],
              [ 'Navy Button', 'btn btn-info' ],
              [ 'Navy Button Small', 'btn btn-info btn-xs' ],
              [ 'Navy Button Large Full Width', 'btn btn-info btn-lg btn-block' ],
              [ 'Orange Button', 'btn btn-orange' ],
              [ 'Orange Button Small', 'btn btn-orange btn-xs' ],
              [ 'Orange Button Large Full Width', 'btn btn-orange btn-lg btn-block' ],
              [ 'Teal Button', 'btn btn-success' ],
              [ 'Teal Button Small', 'btn btn-success btn-xs' ],
              [ 'Teal Button Large Full Width', 'btn btn-success btn-lg btn-block' ],
              [ 'Green Button', 'btn btn-green' ],
              [ 'Green Button Small', 'btn btn-green btn-xs' ],
              [ 'Green Button Large Full Width', 'btn btn-green btn-lg btn-block' ],
              [ 'Red Button', 'btn btn-danger' ],
              [ 'Red Button Small', 'btn btn-danger btn-xs' ],
              [ 'Red Button Large Full Width', 'btn btn-danger btn-lg btn-block' ],
              [ 'Button Text', 'btn-text' ],
              ['None', ''] ],
            'default': '',
            onChange: function( data ) {
              var d = CKEDITOR.dialog.getCurrent();
              d.setValueOf("advanced", "advCSSClasses", this.getValue());
            },
            setup: function( data ) {
              if ( data['advanced'] )
				          this.setValue( data['advanced']['advCSSClasses'] || '' );
  					}

        }
      infoTab.add( buttonLink );
   }
})
