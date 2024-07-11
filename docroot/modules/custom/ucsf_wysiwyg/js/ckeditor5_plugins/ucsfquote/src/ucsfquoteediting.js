import {Plugin} from 'ckeditor5/src/core';
import UcsfQuoteCommand from './ucsfquotecommand';
import { toWidget, toWidgetEditable } from 'ckeditor5/src/widget';
import { Widget } from 'ckeditor5/src/widget';
export default class UcsfQuoteEditing extends Plugin {
    static get pluginName() {
        return 'UcsfQuoteEditing';
      }
      static get requires() {
        return [Widget];
      }
    init() {
        this._defineSchema();
        this._defineConverters();
        this.editor.commands.add(
          'insertUcsfQuote', new UcsfQuoteCommand(this.editor)
        );
    }
    _defineSchema() {
        const schema = this.editor.model.schema;
    
        schema.register( 'ucsfquote', {
            isObject: true,
            allowWhere: '$block',
            allowAttributes: [ 'colorAccent', 'align', 'class' ]
        } );
        schema.register( 'ucsfQuoteContent', {
            isLimit: true,
            allowIn: 'ucsfquote',
            allowContentOf: '$block'
        } );
        schema.register( 'ucsfQuoteCite', {
            allowIn: 'ucsfquote',
            allowContentOf: '$block'
        } );
      }
    _defineConverters() {
        const conversion = this.editor.conversion;
        conversion.attributeToAttribute( {
            model: {
                name: 'ucsfquote',
                key: 'align',
                values: [ 'half-left', 'half-right', 'full-right' ]
            },
            view: {
                'half-left': {
                    key: 'class',
                    value: 'blockquote--half-left'
                },
                'half-right': {
                    key: 'class',
                    value: 'blockquote--half-right'
                },
                'full-right': {
                    key: 'class',
                    value: 'blockquote--full-right'
                }
            }
        } );
        conversion.for( 'upcast' ).elementToElement( {
            view: {
                name: 'blockquote',
                classes: 'blockquote',
            },
            model: ( viewElement, { writer } ) => {
                const classes = viewElement.getClassNames()
                const dataAlign =  classes.find(v => v !== 'blockquote')
                const dataImage = viewElement.getAttribute('data-image')
                return writer.createElement( 'ucsfquote', { 'data-image': dataImage, 'align': dataAlign } );
            }
            
        } );

        conversion.for( 'dataDowncast' ).elementToElement( {
            model: 'ucsfquote',            
            view: ( modelElement, { writer: viewWriter } ) => {
                let opt = { 'class': 'blockquote' }

                const align = modelElement.getAttribute( 'align' )
                opt["class"] = opt["class"] + " blockquote--" + align;
                const blockquote = viewWriter.createContainerElement( 'blockquote', opt );

                // Enable widget handling on a placeholder element inside the editing view.
                return toWidget( blockquote, viewWriter);
            }
        } )

        conversion.for( 'editingDowncast' ).elementToElement( {
            model: 'ucsfquote',
            view: ( modelElement, { writer: viewWriter } ) => {
				let opt = { 'class': 'blockquote' }

                const align = modelElement.getAttribute( 'align' )
                opt["class"] = opt["class"] + " blockquote--" + align;
                const blockquote = viewWriter.createContainerElement( 'blockquote', opt );
                // Enable widget handling on a placeholder element inside the editing view.
                return toWidget( blockquote, viewWriter, { label: 'callout box widget' } );
            }
        } )

        conversion.for( 'upcast' ).elementToElement( {
            model: 'ucsfQuoteContent',
            view: {
                name: 'p',
                classes: 'blockquote-content__text'
            }
        } );

        conversion.for( 'dataDowncast' ).elementToElement( {
            model: 'ucsfQuoteContent',
            view: {
                name: 'p',
                classes: 'blockquote-content__text'
            }
        } );

        conversion.for( 'editingDowncast' ).elementToElement( {
            model: 'ucsfQuoteContent',
            view: ( modelElement, { writer: viewWriter } ) => {
                const div = viewWriter.createEditableElement( 'p', { class: 'blockquote-content__text' } );

                return toWidgetEditable( div, viewWriter );
            }
        } );

        conversion.for( 'upcast' ).elementToElement( {
            model: 'ucsfQuoteCite',
            view: {
                name: 'p',
                classes: 'blockquote-content__cite'
            }
        } );

        conversion.for( 'dataDowncast' ).elementToElement( {
            model: 'ucsfQuoteCite',
            view: {
                name: 'p',
                classes: 'blockquote-content__cite'
            }
        } );

        conversion.for( 'editingDowncast' ).elementToElement( {
            model: 'ucsfQuoteCite',
            view: ( modelElement, { writer: viewWriter } ) => {
                const div = viewWriter.createEditableElement( 'p', { class: 'blockquote-content__cite' } );

                return toWidgetEditable( div, viewWriter );
            }
        } );
  
      } 
}