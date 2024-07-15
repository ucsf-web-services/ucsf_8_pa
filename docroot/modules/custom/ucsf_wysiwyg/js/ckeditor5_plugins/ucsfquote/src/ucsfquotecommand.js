import {Command} from 'ckeditor5/src/core';

export default class UcsfQuoteCommand extends Command {
  execute(attributes) {
      const model = this.editor.model;
      const selection = model.document.selection;
      const selectedUcsfQuote = getSelectedUcsfQuoteWidget( selection );
      model.change( writer => {
      if ( selectedUcsfQuote ) {     
          writer.removeAttribute( 'colorAccent', selectedUcsfQuote)
          writer.removeAttribute( 'align', selectedUcsfQuote)
          writer.setAttribute('align', attributes.align, selectedUcsfQuote );
          writer.setAttribute('colorAccent', attributes.colorAccent, selectedUcsfQuote );
        
      } else {
        model.insertContent(createUcsfQuoteElement(writer, attributes));
      }
    } );
  }

    refresh() {
      const model = this.editor.model;
      const selection = model.document.selection;
      const isAllowed = model.schema.checkChild( selection.focus.parent, 'ucsfquote' );
      const allowedIn = model.schema.findAllowedParent(
        selection.getFirstPosition(),
        'ucsfquote',
      );
      this.isEnabled = allowedIn !== null;
    }

}
function createUcsfQuoteElement( writer, attributes ) {
    const ucsfquote = writer.createElement( 'ucsfquote');
    writer.setAttribute('align', attributes.align, ucsfquote)
	  writer.setAttribute('colorAccent', attributes.colorAccent, ucsfquote)
    const content = writer.createElement( 'ucsfQuoteContent' );
    const cite = writer.createElement('ucsfQuoteCite');

    writer.insertText('Lorem ipsum dolor sit amet, ex labore vivendo laboramus has, vel at putant legendos. Quod appareat id eos, noster malorum et mea.', content);
    writer.append(content, ucsfquote);

    writer.insertText('Vivendo Laboramus, PhD', cite);
    writer.append(cite, ucsfquote);

    return ucsfquote;
  }

  function getSelectedUcsfQuoteWidget( selection ) {
    const selectedElement = selection.getSelectedElement();
  
    if ( selectedElement && selectedElement.is( 'element', 'ucsfquote' ) ) {
      return selectedElement;
    }
  
    return null;
  }  
  
