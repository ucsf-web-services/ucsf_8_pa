import {Plugin} from 'ckeditor5/src/core';
import UcsfCalloutboxCommand from './ucsfcalloutboxcommand';
import { toWidget, toWidgetEditable } from 'ckeditor5/src/widget';
import { Widget } from 'ckeditor5/src/widget';
export default class UcsfCalloutboxEditing extends Plugin {
    static get pluginName() {
        return 'UcsfCalloutboxEditing';
      }
      static get requires() {
        return [Widget];
      }
    init() {
        this._defineSchema();
        this._defineConverters();
        this.editor.commands.add(
          'insertCalloutBox', new UcsfCalloutboxCommand(this.editor)
        );
    }
    _defineSchema() {
        const schema = this.editor.model.schema;
    
        schema.register( 'ucsfcalloutbox', {
            isObject: true,
            allowWhere: '$block',
            allowAttributes: [ 'data-image', 'data-align','class' ]
        } );

        schema.register( 'calloutBoxImage', {
            isLimit: true,
            allowIn: 'ucsfcalloutbox',
            allowContentOf: '$block'
        } );

        schema.register( 'calloutBoxContent', {
            isLimit: true,
            allowIn: 'ucsfcalloutbox',
            allowContentOf: '$block'
        } );
        schema.register( 'calloutBoxEyebrowTitle', {
            allowIn: 'calloutBoxContent',
            allowContentOf: '$block'
        } );

        schema.register( 'calloutBoxTime', {
            allowIn: 'calloutBoxContent',
            allowContentOf: '$block'
        } );

        schema.register( 'calloutBoxParagraph', {
            allowIn: 'calloutBoxContent',
            allowContentOf: '$block'
        } );

        schema.register( 'calloutBoxLink', {
            allowIn: 'calloutBoxContent',
            allowContentOf: '$block'
        } );
      }
    _defineConverters() {
        const conversion = this.editor.conversion;
        conversion.attributeToAttribute( {
            model: {
                name: 'ucsfcalloutbox',
                key: 'data-align',
                values: [ 'right', 'left' ]
            },
            view: {
                left: {
                    key: 'class',
                    value: 'callout-left'
                },
                right: {
                    key: 'class',
                    value: 'callout-right'
                }
            }
        } );
        conversion.attributeToAttribute( {
            model: {
                name: 'ucsfcalloutbox',
                key: 'data-image'
                
            },
            view: {
                    key: 'data-image'
                },
            
        } );
        conversion.for( 'upcast' ).elementToElement( {
            view: {
                name: 'aside',
                classes: 'ucsfcallout',
            },
            model: ( viewElement, { writer } ) => {
                const dataAlign = viewElement.getAttribute('data-align')
                const dataImage = viewElement.getAttribute('data-image')
                return writer.createElement( 'ucsfcalloutbox', { 'data-image': dataImage, 'data-align': dataAlign } );
            }
            
        } );

        conversion.for( 'dataDowncast' ).elementToElement( {
            model: 'ucsfcalloutbox',
            // view: {
            //     name: 'aside',
            //     classes: 'ucsfcallout'
            // }
            
            view: ( modelElement, { writer: viewWriter } ) => {
                let opt = { 'class': 'ucsfcallout', 'data-image': '0' }

              const align = modelElement.getAttribute( 'data-align' ) || 'left';
                const image = modelElement.getAttribute( 'data-image' ) || '0';
                opt["class"] = opt["class"] + " callout-" + align;
                opt["data-image"] = image;
                opt["data-align"] = align;

                const aside = viewWriter.createContainerElement( 'aside', opt );

                // Enable widget handling on a placeholder element inside the editing view.
                return toWidget( aside, viewWriter);
            }
        } )

        conversion.for( 'editingDowncast' ).elementToElement( {
            model: 'ucsfcalloutbox',
            view: ( modelElement, { writer: viewWriter } ) => {
				let opt = { 'class': 'ucsfcallout', 'data-image': '0' }
                const align = modelElement.getAttribute( 'data-align' ) || 'left';
                const image = modelElement.getAttribute( 'data-image' ) || '0';
                opt["class"] = opt["class"] + " callout-" + align;
                opt["data-image"] = image;
                opt["data-align"] = align;

                const aside = viewWriter.createContainerElement( 'aside', opt );
                // Enable widget handling on a placeholder element inside the editing view.
                return toWidget( aside, viewWriter, { label: 'callout box widget' } );
            }
        } )

        conversion.for( 'upcast' ).elementToElement( {
            // model: 'calloutBoxImage',
            view: {
                name: 'div',
                classes: 'callout__image'
            },
            model: 'calloutBoxImage'
        } );

        conversion.for( 'dataDowncast' ).elementToElement( {
            model: 'calloutBoxImage',
            view: {
                name: 'div',
                classes: 'callout__image'
            }
        } );

        conversion.for( 'editingDowncast' ).elementToElement( {
            model: 'calloutBoxImage',
            view: ( modelElement, { writer: viewWriter } ) => {
                const div = viewWriter.createEditableElement( 'div', { class: 'callout__image' } )
                return toWidgetEditable( div, viewWriter );
            }
        } );

        conversion.for( 'upcast' ).elementToElement( {
            model: 'calloutBoxContent',
            view: {
                name: 'div',
                classes: 'callout__content'
            }
        } );

        conversion.for( 'dataDowncast' ).elementToElement( {
            model: 'calloutBoxContent',
            view: {
                name: 'div',
                classes: 'callout__content'
            }
        } );

        conversion.for( 'editingDowncast' ).elementToElement( {
            model: 'calloutBoxContent',
            view: ( modelElement, { writer: viewWriter } ) => {
                const div = viewWriter.createEditableElement( 'div', { class: 'callout__content' } );

                return toWidgetEditable( div, viewWriter );
            }
        } );
        conversion.for('upcast').elementToElement({
            model: 'calloutBoxEyebrowTitle',
            view: {
                name: 'h3',
                classes: 'eyebrow-title'
            }
        });

        conversion.for('dataDowncast').elementToElement({
            model: 'calloutBoxEyebrowTitle',
            view: {
                name: 'h3',
                classes: 'eyebrow-title'
            }
        });

        conversion.for('editingDowncast').elementToElement({
            model: 'calloutBoxEyebrowTitle',
            view: (modelElement, { writer: viewWriter }) => {
                const h3 = viewWriter.createEditableElement('h3', { class: 'eyebrow-title' });

                return toWidgetEditable(h3, viewWriter);
            }
        });

        conversion.for('upcast').elementToElement({
            model: 'calloutBoxTime',
            view: {
                name: 'time'
            }
        });

        conversion.for('dataDowncast').elementToElement({
            model: 'calloutBoxTime',
            view: {
                name: 'time'
            }
        });

        conversion.for('editingDowncast').elementToElement({
            model: 'calloutBoxTime',
            view: (modelElement, { writer: viewWriter }) => {
                const time = viewWriter.createEditableElement('time');

                return toWidgetEditable(time, viewWriter);
            }
        });

        conversion.for('upcast').elementToElement({
            model: 'calloutBoxParagraph',
            view: {
                name: 'p'
            }
        });

        conversion.for('dataDowncast').elementToElement({
            model: 'calloutBoxParagraph',
            view: {
                name: 'p'
            }
        });

        conversion.for('editingDowncast').elementToElement({
            model: 'calloutBoxParagraph',
            view: (modelElement, { writer: viewWriter }) => {
                const p = viewWriter.createEditableElement('p');

                return toWidgetEditable(p, viewWriter);
            }
        });

        conversion.for('upcast').elementToElement({
            model: 'calloutBoxLink',
            view: {
                name: 'a',
                classes: 'link link--cta'
            }
        });

        conversion.for('dataDowncast').elementToElement({
            model: 'calloutBoxLink',
            view: {
                name: 'a',
                classes: 'link link--cta'
            }
        });

        conversion.for('editingDowncast').elementToElement({
            model: 'calloutBoxLink',
            view: (modelElement, { writer: viewWriter }) => {
                const a = viewWriter.createEditableElement('a', { class: 'link link--cta' });

                return toWidgetEditable(a, viewWriter);
            }
        });
      } 
}