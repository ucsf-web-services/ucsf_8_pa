import {Plugin} from 'ckeditor5/src/core';
import BlankdivCommand from './blankdivcommand';
import { toWidget, toWidgetEditable } from 'ckeditor5/src/widget';
import { Widget } from 'ckeditor5/src/widget';
import { createElement } from 'ckeditor5/src/utils';
export default class BlankdivEditing extends Plugin {
    static get pluginName() {
        return 'BlankdivEditing';
      }
      static get requires() {
        return [Widget];
      }
    init() {
        this._defineSchema();
        this._defineConverters();
        this.editor.commands.add(
          'insertBlankdiv', new BlankdivCommand(this.editor)
        );
    }
    _defineSchema() {
        const schema = this.editor.model.schema;

        schema.register( 'blankdiv', {
            isObject: true,
            allowWhere: '$block',
            allowAttributes: [ 'blankdiv', 'align', 'class' ]
        } );
    }
    _defineConverters() {
        const editor = this.editor;
        const conversion = this.editor.conversion;

        editor.data.registerRawContentMatcher( {
			name: 'div',
			classes: 'wysiwyg_blankdiv'
		} );

        conversion.attributeToAttribute( {
            model: {
                name: 'blankdiv',
                key: 'align',
                values: [ 'half-image-right', 'half-image-left', 'half-image-right-full', 'half-image-left-full',  'full-bleed-image' ]
            },
            // view: (modelElement) => {
            //     console.log(modelElement)
            // }
            view: {
                // key: 'class'
                'half-image-right': {
                    key: 'class',
                    value: 'half-image-right'
                },
                'half-image-right-full': {
                    key: 'class',
                    value: 'half-image-right-full'
                },
                'half-image-left': {
                    key: 'class',
                    value: 'half-image-left'
                },
                'half-image-left-full': {
                    key: 'class',
                    value: 'half-image-left-full'
                },
                'full-bleed-image': {
                    key: 'class',
                    value: 'full-bleed-image'
                }
                
            }
        } );
        // conversion.for( 'downcast' ).attributeToElement( {
        //     model: 'blankdiv',
        //     view: ( modelAttributeValue, { writer } ) => {
        //         // Do not convert empty attributes (lack of value means no mention).
        //         if ( !modelAttributeValue ) {
        //             return;
        //         }
        //         console.log(modelAttributeValue)
        //         // return writer.insert( 'a', {
        //         //     class: 'mention',
        //         //     'data-mention': modelAttributeValue.id,
        //         //     'data-user-id': modelAttributeValue.userId,
        //         //     'href': modelAttributeValue.link
        //         // })
        //     }
        // } );
        conversion.for( 'upcast' ).elementToElement( {
            view: {
                name: 'div',
                classes: 'wysiwyg_blankdiv',
            },
            model: ( viewElement, { writer } ) => {
               
                // const dataAlign = viewElement.getAttribute('align')
                const classes = viewElement.getClassNames()
                console.log(classes)
                const dataAlign =  classes.find(v => v !== 'wysiwyg_blankdiv')
                console.log(dataAlign)
                return writer.createElement( 'blankdiv', { align: dataAlign, blankdiv: viewElement.getCustomProperty( '$rawContent' ) } );
            }
            
        } );

        conversion.for( 'dataDowncast' ).elementToElement( {
            model: 'blankdiv',
            view: ( modelElement, { writer } ) => {
                let opt = { 'class': 'wysiwyg_blankdiv ' }

                const align = modelElement.getAttribute( 'align' );
                opt["class"] = opt["class"] + align;
				return writer.createRawElement( 'div', { class: opt["class"] }, function( domElement ) {
					domElement.innerHTML = modelElement.getAttribute( 'blankdiv' ) || '';
				} );
			}
        } )
        conversion.for( 'editingDowncast' ).elementToStructure( {
			model: { name: 'blankdiv', attributes: [ 'blankdiv', 'align' ] },
			view: ( modelElement, { writer } ) => {
                let domContentWrapper;
				let state;
				let props;
                let opt = { 'class': 'wysiwyg_blankdiv ' }

                const align = modelElement.getAttribute( 'align' );
                opt["class"] = opt["class"] + align;
				const viewContentWrapper = writer.createRawElement( 'div', {
					class: 'wysiwyg_blankdiv__content-wrapper'
				}, function( domElement ) {
					domContentWrapper = domElement;

					renderContent( { editor, domElement, modelElement, props } );

					// Since there is a `data-cke-ignore-events` attribute set on the wrapper element in the editable mode,
					// the explicit `mousedown` handler on the `capture` phase is needed to move the selection onto the whole
					// HTML embed widget.
					// domContentWrapper.addEventListener( 'mousedown', () => {
					// 	if ( state.isEditable ) {
					// 		const model = editor.model;
					// 		const selectedElement = model.document.selection.getSelectedElement();

					// 		// Move the selection onto the whole HTML embed widget if it's currently not selected.
					// 		if ( selectedElement !== modelElement ) {
					// 			model.change( writer => writer.setSelection( modelElement, 'on' ) );
					// 		}
					// 	}
					// }, true );
				} );
                const viewContainer = writer.createContainerElement( 'div', {
					class: opt["class"],
					'data-html-embed-label': 'HTML snippet',
				}, viewContentWrapper)
                return toWidget( viewContainer, writer, {
					label: 'HTML snippet',
					hasSelectionHandle: true
				} );
            }
        })
        function renderContent( {
			editor,
			domElement,
			modelElement,
			props
		}) {
            console.log(editor)
            console.log(domElement)
            console.log(modelElement)
			// Remove all children;
			domElement.textContent = '';

			const domDocument = domElement.ownerDocument;
			let domTextarea;

			// if ( state.isEditable ) {
			// 	const textareaProps = {
			// 		isDisabled: false,
			// 		placeholder: props.textareaPlaceholder
			// 	};

			// 	domTextarea = createDomTextarea( { domDocument, state, props: textareaProps } );

			// 	domElement.append( domTextarea );textareaPlaceholder
			// } else if ( state.showPreviews ) {
			// 	const previewContainerProps = {
			// 		sanitizeHtml: props.sanitizeHtml
			// 	};

			// 	domElement.append( createPreviewContainer( { domDocument, state, props: previewContainerProps, editor } ) );
			// } else {
			// 	const textareaProps = {
			// 		isDisabled: true,
			// 		placeholder: props.textareaPlaceholder
			// 	};

			// 	domElement.append( createDomTextarea( { domDocument, state, props: textareaProps } ) );
			// }

            domElement.append( createPreviewContainer( { domDocument, modelElement, editor } ) );
			// const buttonsWrapperProps = {
			// 	onEditClick: props.onEditClick,
			// 	onSaveClick: () => {
			// 		props.onSaveClick( domTextarea.value );
			// 	},
			// 	onCancelClick: props.onCancelClick
			// };

			// domElement.prepend( createDomButtonsWrapper( { editor, domDocument, state, props: buttonsWrapperProps } ) );
		}
        function createPreviewContainer( {
			domDocument,
			modelElement,
            editor
		} ) {
			const sanitizedOutput = modelElement.getAttribute('blankdiv');
			const placeholderText = sanitizedOutput.length > 0 ?
				'No preview available' :
				'Empty snippet content' ;

			const domPreviewPlaceholder = createElement( domDocument, 'div', {
				class: 'ck ck-reset_all raw-html-embed__preview-placeholder'
			}, placeholderText );

			const domPreviewContent = createElement( domDocument, 'div', {
				class: 'raw-html-embed__preview-content',
				dir: editor.locale.contentLanguageDirection
			} );

			// Creating a contextual document fragment allows executing scripts when inserting into the preview element.
			// See: #8326.
			const domRange = domDocument.createRange();
			const domDocumentFragment = domRange.createContextualFragment( sanitizedOutput );

			domPreviewContent.appendChild( domDocumentFragment );

			const domPreviewContainer = createElement( domDocument, 'div', {
				class: 'raw-html-embed__preview'
			}, [
				domPreviewPlaceholder, domPreviewContent
			] );

			return domPreviewContainer;
		}
        function createDomTextarea( {
			domDocument,
			state,
			props
		} ) {
			const domTextarea = createElement( domDocument, 'textarea', {
				placeholder: props.placeholder,
				class: 'ck ck-reset ck-input ck-input-text raw-html-embed__source'
			} );

			domTextarea.disabled = props.isDisabled;
			domTextarea.value = state.getRawHtmlValue();

			return domTextarea;
		}

    }
}