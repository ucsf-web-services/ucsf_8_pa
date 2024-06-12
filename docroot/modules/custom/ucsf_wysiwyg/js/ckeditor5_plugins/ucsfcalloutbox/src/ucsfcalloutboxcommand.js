import {Command} from 'ckeditor5/src/core';

export default class UcsfCalloutboxCommand extends Command {
  execute(attributes) {
    const { model } = this.editor;
    console.log(attributes)
    model.change((writer) => {
      // Insert <accordion>*</accordion> at the current selection position
      // in a way that will result in creating a valid model structure.
      model.insertContent(createCalloutBoxElement(writer));
    });
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
function createCalloutBoxElement( writer ) {
  const calloutBox = writer.createElement( 'ucsfcalloutbox' );

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
