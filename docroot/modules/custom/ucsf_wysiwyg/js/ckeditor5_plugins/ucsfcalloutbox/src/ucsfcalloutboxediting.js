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
                name: 'ucsfcallout',
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
        conversion.for( 'upcast' ).elementToElement( {
            view: {
                name: 'aside',
                classes: 'ucsfcallout',
            },
            model: ( viewElement, { writer } ) => {
                console.log(viewElement)
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
                console.log(modelElement)
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
        } ).add( dispatcher => dispatcher.on( 'attribute', ( evt, data, conversionApi ) => {
            const modelElement = data.item;
    
            // Mark element as consumed by conversion.
            conversionApi.consumable.consume( data.item, evt.name );
    
            // Get mapped view element to update.
            const viewElement = conversionApi.mapper.toViewElement( modelElement );
            console.log(modelElement)
    console.log(viewElement)
            let opt = { 'class': 'ucsfcallout', 'data-image': '0' }
            const align = modelElement.getAttribute( 'data-align' ) || 'left';
            const image = modelElement.getAttribute( 'data-image' ) || '0';
            opt["class"] = opt["class"] + " callout-" + align;
            opt["data-image"] = image;
            opt["data-align"] = align;

            const aside = conversionApi.writer.createContainerElement( 'aside', opt );
            // Enable widget handling on a placeholder element inside the editing view.
            return toWidget( aside, conversionApi.writer, { label: 'callout box widget' } );
           
        } ) );

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
        } ).add( dispatcher => dispatcher.on( 'attribute', ( evt, data, conversionApi ) => {
            const modelElement = data.item;
    
            // Mark element as consumed by conversion.
            conversionApi.consumable.consume( data.item, evt.name );
    
            // Get mapped view element to update.
            const viewElement = conversionApi.mapper.toViewElement( modelElement );
            let opt = { 'class': 'ucsfcallout', 'data-image': '0' }
            const align = modelElement.getAttribute( 'data-align' ) || 'left';
            const image = modelElement.getAttribute( 'data-image' ) || '0';
            opt["class"] = opt["class"] + " callout-" + align;
            opt["data-image"] = image;
            opt["data-align"] = align;
console.log(viewElement)
conversionApi.writer.setAttribute("data-align", align, viewElement)
            return toWidget( viewElement, conversionApi.writer, { label: 'callout box widget' } );
           
        } ) );

        conversion.for( 'upcast' ).elementToElement( {
            model: 'calloutBoxImage',
            view: {
                name: 'div',
                classes: 'callout__image'
            },
            model: ( viewElement, { writer } ) => {
                console.log(viewElement)

                return writer.createElement( 'calloutBoxImage');
            }
        } );

        conversion.for( 'dataDowncast' ).elementToElement( {
            model: 'calloutBoxImage',
            view: {
                name: 'div',
                view: ( modelElement, { writer: viewWriter } ) => {
                    const imageClass = (modelElement.parent.getAttribute( 'data-image' ) && modelElement.parent.getAttribute( 'data-image' ) == '0') ? 'hidden' : '';
                    const classes = 'callout__image ' + imageClass
                    const div = viewWriter.createEditableElement( 'div', { class: classes } );
    
                    return toWidgetEditable( div, viewWriter );
                }
            }
        } );

        conversion.for( 'editingDowncast' ).elementToElement( {
            model: 'calloutBoxImage',
            view: ( modelElement, { writer: viewWriter } ) => {
                const imageClass = (modelElement.parent.getAttribute( 'data-image' ) && modelElement.parent.getAttribute( 'data-image' ) == '0') ? 'hidden' : '';
                const classes = 'callout__image ' + imageClass
                const div = viewWriter.createEditableElement( 'div', { class: classes } );

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
        function setAttrsData( modelItem, viewWriter ) {
            //console.log( modelItem, viewWriter );
            let opt = { 'class': 'ucsfcallout', 'data-image': '0' },
                preAttrs = null;
            const align = modelItem.getAttribute( 'align' );
            const image = modelItem.getAttribute( 'data-image' );
                opt["class"] = opt["class"] + " ucsfcallout-" + align;
                opt["data-image"] = image;
        

            return viewWriter.createContainerElement( 'aside', opt );
        }
      } 
}