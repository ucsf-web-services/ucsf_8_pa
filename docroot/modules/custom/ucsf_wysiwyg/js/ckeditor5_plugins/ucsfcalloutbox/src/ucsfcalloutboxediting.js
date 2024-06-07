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
    
        conversion.for( 'upcast' ).elementToElement( {
            model: 'ucsfcalloutbox',
            view: {
                name: 'aside',
                classes: 'ucsfcallout'
            }
        } );

        conversion.for( 'dataDowncast' ).elementToElement( {
            model: 'ucsfcalloutbox',
            view: {
                name: 'aside',
                classes: 'ucsfcallout'
            }
        } );

        conversion.for( 'editingDowncast' ).elementToElement( {
            model: 'ucsfcalloutbox',
            view: ( modelElement, { writer: viewWriter } ) => {
                const aside = viewWriter.createContainerElement( 'aside', { class: 'ucsfcallout' } );

                return toWidget( aside, viewWriter, { label: 'callout box widget' } );
            }
        } );

        conversion.for( 'upcast' ).elementToElement( {
            model: 'calloutBoxImage',
            view: {
                name: 'div',
                classes: 'callout__image'
            }
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
                const div = viewWriter.createEditableElement( 'div', { class: 'callout__image' } );

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