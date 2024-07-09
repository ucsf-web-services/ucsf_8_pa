import {Command} from 'ckeditor5/src/core';

export default class UcsfCalloutboxCommand extends Command {
  execute(attributes) {
      const model = this.editor.model;
      const selection = model.document.selection;
      const selectedCalloutBox = getSelectedCalloutBoxWidget( selection );
      model.change( writer => {
      if ( selectedCalloutBox ) {     
          writer.setAttribute('data-align', attributes.align, selectedCalloutBox );
          writer.setAttribute('data-image', attributes.image, selectedCalloutBox );
        
      } else {
        model.insertContent(createCalloutBoxElement(writer, attributes));
      }
    } );
      // const isCallout = selection.getSelectedElement();
    // console.log(model)
    //   if (isCallout && isCallout.name == "ucsfcalloutbox") {
    //     writer.setAttribute('data-align', attributes.align, model)
    //     writer.setAttribute('data-image', attributes.image, model)
    //     return isCallout
    //   } else {
    //     model.insertContent(createCalloutBoxElement(writer, attributes));

    //   }
  }

    refresh() {
      const model = this.editor.model;
      const selection = model.document.selection;
      const isAllowed = model.schema.checkChild( selection.focus.parent, 'ucsfcalloutbox' );
      const allowedIn = model.schema.findAllowedParent(
        selection.getFirstPosition(),
        'ucsfcalloutbox',
      );
      this.isEnabled = allowedIn !== null;
    }

}
function createCalloutBoxElement( writer, attributes ) {
    const calloutBox = writer.createElement( 'ucsfcalloutbox');
    writer.setAttribute('data-align', attributes.align, calloutBox)
		writer.setAttribute('data-image', attributes.image, calloutBox)
    const image = writer.createElement( 'calloutBoxImage' );
    const content = writer.createElement( 'calloutBoxContent' );
    const eyebrowTitle = writer.createElement('calloutBoxEyebrowTitle');
    const time = writer.createElement('calloutBoxTime');
    const paragraph1 = writer.createElement('calloutBoxParagraph');
    const paragraph2 = writer.createElement('calloutBoxParagraph');
    const link = writer.createElement('calloutBoxLink');

    writer.insertText('Remove this text and use the embed button to add an image.', image);
    writer.append(image, calloutBox);

    writer.insertText('Take Action', eyebrowTitle);
    writer.append(eyebrowTitle, content);

    writer.insertText('Oct. 24, 2020', time);
    writer.append(time, content);

    writer.insertText('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum ultricies sit amet.', paragraph1);
    writer.append(paragraph1, content);

    writer.insertText('Learn More', link);
    writer.setAttribute('href', '/', link);
    writer.append(link, paragraph2);
    writer.append(paragraph2, content);

    writer.append(content, calloutBox);
    return calloutBox;
  }

  function getSelectedCalloutBoxWidget( selection ) {
    const selectedElement = selection.getSelectedElement();
  
    if ( selectedElement && selectedElement.is( 'element', 'ucsfcalloutbox' ) ) {
      return selectedElement;
    }
  
    return null;
  }  
  
