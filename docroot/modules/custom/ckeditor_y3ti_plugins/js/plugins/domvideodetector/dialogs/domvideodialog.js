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
var video;
var originalURL;


CKEDITOR.dialog.add( 'domvideodialog', function( editor ) {

    return {
        title: 'Insert a Youtube, Vimeo or Dailymotion URL',
        minWidth: 400,
        minHeight: 100,
        contents: [
            {
                id: 'tab-basic',
                label: 'Basic Settings',
                elements: [
                    {
                        type: 'text',
                        id: 'src',
                        label: 'Youtube, Vimeo or Dailymotion URL',
                        validate:  function ()
  											{
  												if ( this.isEnabled() )
  												{
  													if ( !this.getValue() )
  													{
  														alert( 'Enter something' );
  														return false;
  													}
  												}
  											},
                        setup: loadValue,
			                  commit: commitValue
                    },
                    {
                      id: 'align',
                      type: 'select',
                      label: 'Align',
                      items: [
                        [ editor.lang.common.alignCenter, 'center' ],
                        [ editor.lang.common.alignLeft, 'left' ],
                        [ editor.lang.common.alignRight, 'right' ]
                      ],
                      'default': 'center',
                      setup: function( iframeNode ) {
                        if ( iframeNode.getAttribute('data-align') ) {
                          var value = iframeNode.getAttribute('data-align');
                          this.setValue( value);
                        }
                      },
                      commit: function( iframeNode ) {
                        if(this.getValue() !== ''){
                          iframeNode.setAttributes( {'data-align':this.getValue()} );
                        }
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
                      'default': 'full',
                      setup: function( iframeNode ) {
                          this.setValue( iframeNode.getAttribute('data-size') ? iframeNode.getAttribute('data-size') : 'full');
                      },
                      commit: function( iframeNode ) {
                        if(this.getValue() !== ''){
                          iframeNode.setAttributes( {'data-size':this.getValue()} );
                        }
                      }
                    }
                ]
            }
        ],
        onOk: function() {
          var dialog = this;
          var iframeNode;
          var align = '';
          var size;
          var divNode;
          if ( !this.fakeImage ) {
             iframeNode = new CKEDITOR.dom.element( 'iframe' );
          } else {
            iframeNode = this.iframeNode;
          }
          iframeNode.setStyles({
            'position': 'absolute',
            'top': '0',
            'left': '0',
            'width': '100%',
            'height': '100%'
          })
          this.commitContent( iframeNode);
          if (!this.divNode) {
            divNode  = new CKEDITOR.dom.element( 'div' );
          } else {
            divNode = this.divNode;
          }

          if(iframeNode.getAttribute('data-align')){
            align = 'align--'+iframeNode.getAttribute('data-align');
          }
          if (iframeNode.getAttribute('data-size')) {
            size = 'size--'+iframeNode.getAttribute('data-size');
          } else {
            size = 'size--full';
          }

          divNode.setAttribute('class', 'responsive-video '+align+' '+size)
          // var divStyle = {
          //   'position':'relative',
          // };
          // if(divNode.hasClass('size--full')){
          //   divStyle['padding-bottom'] = '60%';
          // } else if (divNode.hasClass('size--half')) {
          //   divStyle['padding-bottom'] = '28%';
          // } else if (divNode.hasClass('size--twofifth')) {
          //   divStyle['padding-bottom'] = '22%';
          // } else if (divNode.hasClass('size--third')) {
          //   divStyle['padding-bottom'] = '18%';
          // }
          // divNode.append( iframeNode );
          // divNode.setStyles(divStyle)

          var extraAttributes = {
           'src': iframeNode.getAttribute('data-video-image'),
           //'class': 'cke_videoDetector '+align+' '+size,
          };
          // var newFakeImage = editor.createFakeElement( divNode, 'cke_videoDetector', 'div', true );
          var newFakeImage = editor.createFakeElement( iframeNode, 'cke_videoDetector', 'iframe', true );
          newFakeImage.setAttributes( extraAttributes );
          divNode.append( newFakeImage );

          if ( this.fakeImage ) {
                newFakeImage.replace( this.fakeImage );
                editor.getSelection().selectElement( newFakeImage );
			    } else {
  				      editor.insertElement( divNode );
          }
        },
        onShow: function () {
          // Clear previously saved elements.
          this.fakeImage = this.iframeNode = null;

          var fakeImage = this.getSelectedElement();
          if ( fakeImage && fakeImage.data( 'cke-real-element-type' ) && fakeImage.data( 'cke-real-element-type' ) == 'iframe' && fakeImage.hasClass('cke_videoDetector')) {
            this.fakeImage = fakeImage;
            var divNode = fakeImage.getParent();
            this.divNode = divNode;
            //var iframeNode = divNode.getFirst();
            var iframeNode = editor.restoreRealElement( fakeImage );
            this.iframeNode = iframeNode;
            this.setupContent( iframeNode );
          }
        }
    };
});
//function divStyle
function loadValue( iframeNode ) {
  if ( iframeNode.getAttribute('data-original-input') ) {
    var value = iframeNode.getAttribute('data-original-input');
    this.setValue( value);
  }
}

function commitValue( iframeNode ) {
  var isRemove = this.getValue() === '',
      value = originalURL = this.getValue();
      video = detectar(value,iframeNode);
  if ( isRemove ) {
    iframeNode.removeAttribute( this.att || this.id );
  } else {
    iframeNode.setAttributes( {
        'src': video.urlFinal,
        'data-original-input': video.urlInput,
        'data-video-image':video.imageLink} );
  }
}

//funcion para detectar el id y la plataforma (youtube, vimeo o dailymotion) de los videos
function detectar(url,iframeNode){

    var reproductor,
        id,
        urlInput = url,
        urlFinal,
        imageLink;

    if(url.indexOf('youtu.be') >= 0){
        reproductor = 'youtube';
        id          = url.substring(url.lastIndexOf("/")+1, url.length);
        urlFinal = "https://www.youtube.com/embed/"+id+"?autohide=1&controls=1&showinfo=0";
    } else if(url.indexOf("youtube") >= 0){
        reproductor = 'youtube'
        if(url.indexOf("</iframe>") >= 0){
            var fin = url.substring(url.indexOf("embed/")+6, url.length)
            id      = fin.substring(fin.indexOf('"'), 0);
        }else{
            if(url.indexOf("&") >= 0)
                id = url.substring(url.indexOf("?v=")+3, url.indexOf("&"));
            else
                id = url.substring(url.indexOf("?v=")+3, url.length);
        }
        url_comprobar = "https://gdata.youtube.com/feeds/api/videos/" + id + "?v=2&alt=json";
        //"https://gdata.youtube.com/feeds/api/videos/" + id + "?v=2&alt=json"
        urlFinal = "https://www.youtube.com/embed/"+id+"?autohide=1&controls=1&showinfo=0";
    } else if(url.indexOf("vimeo") >= 0){
        reproductor = 'vimeo'
        if(url.indexOf("</iframe>") >= 0){
            var fin = url.substring(url.lastIndexOf('vimeo.com/"')+6, url.indexOf('>'))
            id      = fin.substring(fin.lastIndexOf('/')+1, fin.indexOf('"',fin.lastIndexOf('/')+1))
        }else{
            id = url.substring(url.lastIndexOf("/")+1, url.length)
        }
        url_comprobar = 'http://vimeo.com/api/v2/video/' + id + '.json';
        //'http://vimeo.com/api/v2/video/' + video_id + '.json';
        urlFinal = "https://player.vimeo.com/video/"+id+"?portrait=0";;
    } else if(url.indexOf('dai.ly') >= 0){
        reproductor = 'dailymotion';
        id          = url.substring(url.lastIndexOf("/")+1, url.length);
        urlFinal = "https://www.dailymotion.com/embed/video/"+id;
    } else if(url.indexOf("dailymotion") >= 0){
        reproductor = 'dailymotion';
        if(url.indexOf("</iframe>") >= 0){
            var fin = url.substring(url.indexOf('dailymotion.com/')+16, url.indexOf('></iframe>'))
            id      = fin.substring(fin.lastIndexOf('/')+1, fin.lastIndexOf('"'))
        }else{
            if(url.indexOf('_') >= 0)
                id = url.substring(url.lastIndexOf('/')+1, url.indexOf('_'))
            else
                id = url.substring(url.lastIndexOf('/')+1, url.length);
        }
        url_comprobar = 'https://api.dailymotion.com/video/' + id;
        // https://api.dailymotion.com/video/x26ezrb
        urlFinal = "https://www.dailymotion.com/embed/video/"+ id;
    } else {
      urlFinal = urlInput;
    }
    imageLink = getVideoImage(reproductor, id, iframeNode);
    //return urlFinal;￼

    return {'urlInput':urlInput,'id_video':id,'urlInput':urlInput,'urlFinal':urlFinal,'imageLink':imageLink };
}
function getVideoImage (reproductor, id, iframeNode) {
  var videoImage;
  switch (reproductor) {
    case 'youtube':
      videoImage = 'http://img.youtube.com/vi/' + id + '/0.jpg';
      break;
    case 'vimeo':
      url = 'http://vimeo.com/api/v2/video/' + id + '.json';
      videoImage = CKEDITOR.plugins.getPath('domvideodetector') + 'icons/image-bg.png';
      loadVimeoImage(url, function (data) {

        iframeNode.setAttributes( {
            'data-video-image':data[0].thumbnail_medium} );
      })
          //videoImage = data[0].thumbnail_medium;
      break;
    case 'dailymotion':
      videoImage = 'http://www.dailymotion.com/thumbnail/video/' + id ;
      break;
    default:
      videoImage = CKEDITOR.plugins.getPath('domvideodetector') + 'icons/image-bg.png';

  }


  return videoImage;
}

function loadVimeoImage(url, callback) {
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.open('GET', url, true);
  xmlhttp.send(null);
  xmlhttp.onreadystatechange = function() {
      if (xmlhttp.readyState == 4) {
          if(xmlhttp.status == 200) {
              var obj = JSON.parse(xmlhttp.responseText);
              callback(obj);
           }
      }
  };
}
