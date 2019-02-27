/*
*   Plugin developed by Netbroad, C.B.
*
*   LICENCE: GPL, LGPL, MPL
*   NON-COMMERCIAL PLUGIN.
*
*   Website: netbroad.eu
*   Twitter: @netbroadcb
*   Facebook: Netbroad
*   LinkedIn: Netbroad
*
*/
// CKEDITOR.config.autoParagraph = false;

CKEDITOR.plugins.add( 'domvideodetector', {
    //requires: 'fajeobjects',
    icons: 'domvideodetector',
    init: function( editor ) {

        editor.addCommand( 'domvideodetector', new CKEDITOR.dialogCommand( 'domvideodialog' ) );
        editor.ui.addButton( 'domvideodetector', {
            label: 'Insert a Youtube, Vimeo or Dailymotion video',
            command: 'domvideodetector',
            icon: CKEDITOR.plugins.getPath('domvideodetector') + '/icons/domvideodetector.svg'
        });
        editor.on('doubleclick',function (evt){
          var element = evt.data.element;

            if ( element.is( 'img' ) && element.data( 'cke-real-element-type' ) == 'iframe' && element.hasClass('cke_videoDetector'))
              evt.data.dialog = 'domvideodialog';
        });
        CKEDITOR.dialog.add( 'domvideodialog', this.path + 'dialogs/domvideodialog.js' );
        if ( editor.contextMenu ) {
  				editor.contextMenu.addListener( function( element ) {
  					if ( element && element.is( 'img' ) && element.data( 'cke-real-element-type' ) == 'iframe' && element.hasClass('cke_videoDetector'))
              return { domvideodetector: CKEDITOR.TRISTATE_OFF };
  				} );
	      }
        editor.addMenuItems({
         domvideodetector : {
            label : 'Video Detector',
            command : 'domvideodetector',
            group : 'image',
            order : 1
        }});
    },
    afterInit: function( editor ) {
			var dataProcessor = editor.dataProcessor,
				dataFilter = dataProcessor && dataProcessor.dataFilter;
			if ( dataFilter ) {
				dataFilter.addRules( {
					elements: {
						div: function( element ) {

              var iframeNode = element.getFirst();
              var align = '';
              var size;
              if (iframeNode.name == 'iframe'){
                element.setHtml('');
                var fakeImage = editor.createFakeParserElement( iframeNode, 'cke_videoDetector', 'iframe', true );
                element.add(fakeImage);
                fakeImage.attributes.src = iframeNode.attributes['data-video-image'];
  							return element;
              }
						},

					}
				});
			}
    }
});
