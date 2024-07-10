import {Command} from 'ckeditor5/src/core';

export default class BlankdivCommand extends Command {
  execute(attributes) {
      const model = this.editor.model;
      const selection = model.document.selection;
      const selectedBlankdiv = getSelectedBlankdivxWidget( selection );
      console.log(model)
      model.change( writer => {
      if ( selectedBlankdiv ) {     
          writer.setAttribute('align', attributes.align, selectedBlankdiv );
          writer.setAttribute('blankdiv', attributes.blankdiv, selectedBlankdiv );
        
      } else {
        model.insertContent(createBlankdivElement(writer, attributes));
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
      const isAllowed = model.schema.checkChild( selection.focus.parent, 'blankdiv' );
      const allowedIn = model.schema.findAllowedParent(
        selection.getFirstPosition(),
        'blankdiv',
      );
      this.isEnabled = allowedIn !== null;
    }

}
function createBlankdivElement( writer, attributes ) {
    console.log(attributes)
    const blankdiv = writer.createElement( 'blankdiv');
    writer.setAttribute('align', attributes.align, blankdiv)
	  writer.setAttribute('blankdiv', attributes.blankdiv, blankdiv)
    return blankdiv;
  }

  function getSelectedBlankdivxWidget( selection ) {
    const selectedElement = selection.getSelectedElement();
  
    if ( selectedElement && selectedElement.is( 'element', 'blankdiv' ) ) {
      return selectedElement;
    }
  
    return null;
  }  