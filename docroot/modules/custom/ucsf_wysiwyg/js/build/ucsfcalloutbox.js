!function(e,t){"object"==typeof exports&&"object"==typeof module?module.exports=t():"function"==typeof define&&define.amd?define([],t):"object"==typeof exports?exports.CKEditor5=t():(e.CKEditor5=e.CKEditor5||{},e.CKEditor5.ucsfcalloutbox=t())}(self,(()=>(()=>{var __webpack_modules__={"./js/ckeditor5_plugins/ucsfcalloutbox/src/index.js":(__unused_webpack_module,__webpack_exports__,__webpack_require__)=>{"use strict";eval('__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _ucsfcalloutbox__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ucsfcalloutbox */ "./js/ckeditor5_plugins/ucsfcalloutbox/src/ucsfcalloutbox.js");\n/**\n * @module ucsf_wysiwyg\n */\n\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n    UcsfCalloutbox: _ucsfcalloutbox__WEBPACK_IMPORTED_MODULE_0__["default"]\n});\n\n//# sourceURL=webpack://CKEditor5.ucsfcalloutbox/./js/ckeditor5_plugins/ucsfcalloutbox/src/index.js?')},"./js/ckeditor5_plugins/ucsfcalloutbox/src/ucsfcalloutbox.js":(__unused_webpack_module,__webpack_exports__,__webpack_require__)=>{"use strict";eval('__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   "default": () => (/* binding */ UcsfCalloutbox)\n/* harmony export */ });\n/* harmony import */ var _ucsfcalloutboxediting__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ucsfcalloutboxediting */ "./js/ckeditor5_plugins/ucsfcalloutbox/src/ucsfcalloutboxediting.js");\n/* harmony import */ var _ucsfcalloutboxui__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ucsfcalloutboxui */ "./js/ckeditor5_plugins/ucsfcalloutbox/src/ucsfcalloutboxui.js");\n/* harmony import */ var ckeditor5_src_core__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ckeditor5/src/core */ "ckeditor5/src/core.js");\n/**\n * @file This is what CKEditor refers to as a master (glue) plugin. Its role is\n * just to load the “editing” and “UI” components of this Plugin. Those\n * components could be included in this file, but\n *\n * I.e, this file\'s purpose is to integrate all the separate parts of the plugin\n * before it\'s made discoverable via index.js.\n */\n\n\n\n\n\nclass UcsfCalloutbox extends ckeditor5_src_core__WEBPACK_IMPORTED_MODULE_2__.Plugin {\n  static get requires() {\n    return [_ucsfcalloutboxediting__WEBPACK_IMPORTED_MODULE_0__["default"], _ucsfcalloutboxui__WEBPACK_IMPORTED_MODULE_1__["default"]];\n  }\n}\n\n//# sourceURL=webpack://CKEditor5.ucsfcalloutbox/./js/ckeditor5_plugins/ucsfcalloutbox/src/ucsfcalloutbox.js?')},"./js/ckeditor5_plugins/ucsfcalloutbox/src/ucsfcalloutboxcommand.js":(__unused_webpack_module,__webpack_exports__,__webpack_require__)=>{"use strict";eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* binding */ UcsfCalloutboxCommand)\n/* harmony export */ });\n/* harmony import */ var ckeditor5_src_core__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ckeditor5/src/core */ \"ckeditor5/src/core.js\");\n\n\nclass UcsfCalloutboxCommand extends ckeditor5_src_core__WEBPACK_IMPORTED_MODULE_0__.Command {\n  execute(attributes) {\n      const model = this.editor.model;\n      const selection = model.document.selection;\n      const selectedCalloutBox = getSelectedCalloutBoxWidget( selection );\n      model.change( writer => {\n      if ( selectedCalloutBox ) {     \n          writer.setAttribute('data-align', attributes.align, selectedCalloutBox );\n          writer.setAttribute('data-image', attributes.image, selectedCalloutBox );\n        \n      } else {\n        model.insertContent(createCalloutBoxElement(writer, attributes));\n      }\n    } );\n      // const isCallout = selection.getSelectedElement();\n    // console.log(model)\n    //   if (isCallout && isCallout.name == \"ucsfcalloutbox\") {\n    //     writer.setAttribute('data-align', attributes.align, model)\n    //     writer.setAttribute('data-image', attributes.image, model)\n    //     return isCallout\n    //   } else {\n    //     model.insertContent(createCalloutBoxElement(writer, attributes));\n\n    //   }\n  }\n\n    refresh() {\n      const model = this.editor.model;\n      const selection = model.document.selection;\n      const isAllowed = model.schema.checkChild( selection.focus.parent, 'ucsfcalloutbox' );\n      const allowedIn = model.schema.findAllowedParent(\n        selection.getFirstPosition(),\n        'ucsfcalloutbox',\n      );\n      this.isEnabled = allowedIn !== null;\n    }\n\n}\nfunction createCalloutBoxElement( writer, attributes ) {\n    const calloutBox = writer.createElement( 'ucsfcalloutbox');\n    writer.setAttribute('data-align', attributes.align, calloutBox)\n\t\twriter.setAttribute('data-image', attributes.image, calloutBox)\n    const image = writer.createElement( 'calloutBoxImage' );\n    const content = writer.createElement( 'calloutBoxContent' );\n    const eyebrowTitle = writer.createElement('calloutBoxEyebrowTitle');\n    const time = writer.createElement('calloutBoxTime');\n    const paragraph1 = writer.createElement('calloutBoxParagraph');\n    const paragraph2 = writer.createElement('calloutBoxParagraph');\n    const link = writer.createElement('calloutBoxLink');\n\n    writer.insertText('Remove this text and use the embed button to add an image.', image);\n    writer.append(image, calloutBox);\n\n    writer.insertText('Take Action', eyebrowTitle);\n    writer.append(eyebrowTitle, content);\n\n    writer.insertText('Oct. 24, 2020', time);\n    writer.append(time, content);\n\n    writer.insertText('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum ultricies sit amet.', paragraph1);\n    writer.append(paragraph1, content);\n\n    writer.insertText('Learn More', link);\n    writer.setAttribute('href', '/', link);\n    writer.append(link, paragraph2);\n    writer.append(paragraph2, content);\n\n    writer.append(content, calloutBox);\n    return calloutBox;\n  }\n\n  function getSelectedCalloutBoxWidget( selection ) {\n    const selectedElement = selection.getSelectedElement();\n  \n    if ( selectedElement && selectedElement.is( 'element', 'ucsfcalloutbox' ) ) {\n      return selectedElement;\n    }\n  \n    return null;\n  }  \n  \n\n\n//# sourceURL=webpack://CKEditor5.ucsfcalloutbox/./js/ckeditor5_plugins/ucsfcalloutbox/src/ucsfcalloutboxcommand.js?")},"./js/ckeditor5_plugins/ucsfcalloutbox/src/ucsfcalloutboxediting.js":(__unused_webpack_module,__webpack_exports__,__webpack_require__)=>{"use strict";eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* binding */ UcsfCalloutboxEditing)\n/* harmony export */ });\n/* harmony import */ var ckeditor5_src_core__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ckeditor5/src/core */ \"ckeditor5/src/core.js\");\n/* harmony import */ var _ucsfcalloutboxcommand__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ucsfcalloutboxcommand */ \"./js/ckeditor5_plugins/ucsfcalloutbox/src/ucsfcalloutboxcommand.js\");\n/* harmony import */ var ckeditor5_src_widget__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ckeditor5/src/widget */ \"ckeditor5/src/widget.js\");\n\n\n\n\nclass UcsfCalloutboxEditing extends ckeditor5_src_core__WEBPACK_IMPORTED_MODULE_0__.Plugin {\n    static get pluginName() {\n        return 'UcsfCalloutboxEditing';\n      }\n      static get requires() {\n        return [ckeditor5_src_widget__WEBPACK_IMPORTED_MODULE_2__.Widget];\n      }\n    init() {\n        this._defineSchema();\n        this._defineConverters();\n        this.editor.commands.add(\n          'insertCalloutBox', new _ucsfcalloutboxcommand__WEBPACK_IMPORTED_MODULE_1__[\"default\"](this.editor)\n        );\n    }\n    _defineSchema() {\n        const schema = this.editor.model.schema;\n    \n        schema.register( 'ucsfcalloutbox', {\n            isObject: true,\n            allowWhere: '$block',\n            allowAttributes: [ 'data-image', 'data-align','class' ]\n        } );\n\n        schema.register( 'calloutBoxImage', {\n            isLimit: true,\n            allowIn: 'ucsfcalloutbox',\n            allowContentOf: '$block'\n        } );\n\n        schema.register( 'calloutBoxContent', {\n            isLimit: true,\n            allowIn: 'ucsfcalloutbox',\n            allowContentOf: '$block'\n        } );\n        schema.register( 'calloutBoxEyebrowTitle', {\n            allowIn: 'calloutBoxContent',\n            allowContentOf: '$block'\n        } );\n\n        schema.register( 'calloutBoxTime', {\n            allowIn: 'calloutBoxContent',\n            allowContentOf: '$block'\n        } );\n\n        schema.register( 'calloutBoxParagraph', {\n            allowIn: 'calloutBoxContent',\n            allowContentOf: '$block'\n        } );\n\n        schema.register( 'calloutBoxLink', {\n            allowIn: 'calloutBoxContent',\n            allowContentOf: '$block'\n        } );\n      }\n    _defineConverters() {\n        const conversion = this.editor.conversion;\n        conversion.attributeToAttribute( {\n            model: {\n                name: 'ucsfcalloutbox',\n                key: 'data-align',\n                values: [ 'right', 'left' ]\n            },\n            view: {\n                left: {\n                    key: 'class',\n                    value: 'callout-left'\n                },\n                right: {\n                    key: 'class',\n                    value: 'callout-right'\n                }\n            }\n        } );\n        conversion.attributeToAttribute( {\n            model: {\n                name: 'ucsfcalloutbox',\n                key: 'data-image'\n                \n            },\n            view: {\n                    key: 'data-image'\n                },\n            \n        } );\n        conversion.for( 'upcast' ).elementToElement( {\n            view: {\n                name: 'aside',\n                classes: 'ucsfcallout',\n            },\n            model: ( viewElement, { writer } ) => {\n                const dataAlign = viewElement.getAttribute('data-align')\n                const dataImage = viewElement.getAttribute('data-image')\n                return writer.createElement( 'ucsfcalloutbox', { 'data-image': dataImage, 'data-align': dataAlign } );\n            }\n            \n        } );\n\n        conversion.for( 'dataDowncast' ).elementToElement( {\n            model: 'ucsfcalloutbox',\n            // view: {\n            //     name: 'aside',\n            //     classes: 'ucsfcallout'\n            // }\n            \n            view: ( modelElement, { writer: viewWriter } ) => {\n                let opt = { 'class': 'ucsfcallout', 'data-image': '0' }\n\n              const align = modelElement.getAttribute( 'data-align' ) || 'left';\n                const image = modelElement.getAttribute( 'data-image' ) || '0';\n                opt[\"class\"] = opt[\"class\"] + \" callout-\" + align;\n                opt[\"data-image\"] = image;\n                opt[\"data-align\"] = align;\n\n                const aside = viewWriter.createContainerElement( 'aside', opt );\n\n                // Enable widget handling on a placeholder element inside the editing view.\n                return (0,ckeditor5_src_widget__WEBPACK_IMPORTED_MODULE_2__.toWidget)( aside, viewWriter);\n            }\n        } )\n\n        conversion.for( 'editingDowncast' ).elementToElement( {\n            model: 'ucsfcalloutbox',\n            view: ( modelElement, { writer: viewWriter } ) => {\n\t\t\t\tlet opt = { 'class': 'ucsfcallout', 'data-image': '0' }\n                const align = modelElement.getAttribute( 'data-align' ) || 'left';\n                const image = modelElement.getAttribute( 'data-image' ) || '0';\n                opt[\"class\"] = opt[\"class\"] + \" callout-\" + align;\n                opt[\"data-image\"] = image;\n                opt[\"data-align\"] = align;\n\n                const aside = viewWriter.createContainerElement( 'aside', opt );\n                // Enable widget handling on a placeholder element inside the editing view.\n                return (0,ckeditor5_src_widget__WEBPACK_IMPORTED_MODULE_2__.toWidget)( aside, viewWriter, { label: 'callout box widget' } );\n            }\n        } )\n\n        conversion.for( 'upcast' ).elementToElement( {\n            // model: 'calloutBoxImage',\n            view: {\n                name: 'div',\n                classes: 'callout__image'\n            },\n            model: 'calloutBoxImage'\n        } );\n\n        conversion.for( 'dataDowncast' ).elementToElement( {\n            model: 'calloutBoxImage',\n            view: {\n                name: 'div',\n                classes: 'callout__image'\n            }\n        } );\n\n        conversion.for( 'editingDowncast' ).elementToElement( {\n            model: 'calloutBoxImage',\n            view: ( modelElement, { writer: viewWriter } ) => {\n                const div = viewWriter.createEditableElement( 'div', { class: 'callout__image' } )\n                return (0,ckeditor5_src_widget__WEBPACK_IMPORTED_MODULE_2__.toWidgetEditable)( div, viewWriter );\n            }\n        } );\n\n        conversion.for( 'upcast' ).elementToElement( {\n            model: 'calloutBoxContent',\n            view: {\n                name: 'div',\n                classes: 'callout__content'\n            }\n        } );\n\n        conversion.for( 'dataDowncast' ).elementToElement( {\n            model: 'calloutBoxContent',\n            view: {\n                name: 'div',\n                classes: 'callout__content'\n            }\n        } );\n\n        conversion.for( 'editingDowncast' ).elementToElement( {\n            model: 'calloutBoxContent',\n            view: ( modelElement, { writer: viewWriter } ) => {\n                const div = viewWriter.createEditableElement( 'div', { class: 'callout__content' } );\n\n                return (0,ckeditor5_src_widget__WEBPACK_IMPORTED_MODULE_2__.toWidgetEditable)( div, viewWriter );\n            }\n        } );\n        conversion.for('upcast').elementToElement({\n            model: 'calloutBoxEyebrowTitle',\n            view: {\n                name: 'h3',\n                classes: 'eyebrow-title'\n            }\n        });\n\n        conversion.for('dataDowncast').elementToElement({\n            model: 'calloutBoxEyebrowTitle',\n            view: {\n                name: 'h3',\n                classes: 'eyebrow-title'\n            }\n        });\n\n        conversion.for('editingDowncast').elementToElement({\n            model: 'calloutBoxEyebrowTitle',\n            view: (modelElement, { writer: viewWriter }) => {\n                const h3 = viewWriter.createEditableElement('h3', { class: 'eyebrow-title' });\n\n                return (0,ckeditor5_src_widget__WEBPACK_IMPORTED_MODULE_2__.toWidgetEditable)(h3, viewWriter);\n            }\n        });\n\n        conversion.for('upcast').elementToElement({\n            model: 'calloutBoxTime',\n            view: {\n                name: 'time'\n            }\n        });\n\n        conversion.for('dataDowncast').elementToElement({\n            model: 'calloutBoxTime',\n            view: {\n                name: 'time'\n            }\n        });\n\n        conversion.for('editingDowncast').elementToElement({\n            model: 'calloutBoxTime',\n            view: (modelElement, { writer: viewWriter }) => {\n                const time = viewWriter.createEditableElement('time');\n\n                return (0,ckeditor5_src_widget__WEBPACK_IMPORTED_MODULE_2__.toWidgetEditable)(time, viewWriter);\n            }\n        });\n\n        conversion.for('upcast').elementToElement({\n            model: 'calloutBoxParagraph',\n            view: {\n                name: 'p'\n            }\n        });\n\n        conversion.for('dataDowncast').elementToElement({\n            model: 'calloutBoxParagraph',\n            view: {\n                name: 'p'\n            }\n        });\n\n        conversion.for('editingDowncast').elementToElement({\n            model: 'calloutBoxParagraph',\n            view: (modelElement, { writer: viewWriter }) => {\n                const p = viewWriter.createEditableElement('p');\n\n                return (0,ckeditor5_src_widget__WEBPACK_IMPORTED_MODULE_2__.toWidgetEditable)(p, viewWriter);\n            }\n        });\n\n        conversion.for('upcast').elementToElement({\n            model: 'calloutBoxLink',\n            view: {\n                name: 'a',\n                classes: 'link link--cta'\n            }\n        });\n\n        conversion.for('dataDowncast').elementToElement({\n            model: 'calloutBoxLink',\n            view: {\n                name: 'a',\n                classes: 'link link--cta'\n            }\n        });\n\n        conversion.for('editingDowncast').elementToElement({\n            model: 'calloutBoxLink',\n            view: (modelElement, { writer: viewWriter }) => {\n                const a = viewWriter.createEditableElement('a', { class: 'link link--cta' });\n\n                return (0,ckeditor5_src_widget__WEBPACK_IMPORTED_MODULE_2__.toWidgetEditable)(a, viewWriter);\n            }\n        });\n      } \n}\n\n//# sourceURL=webpack://CKEditor5.ucsfcalloutbox/./js/ckeditor5_plugins/ucsfcalloutbox/src/ucsfcalloutboxediting.js?")},"./js/ckeditor5_plugins/ucsfcalloutbox/src/ucsfcalloutboxui.js":(__unused_webpack_module,__webpack_exports__,__webpack_require__)=>{"use strict";eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* binding */ UcsfCalloutboxUI)\n/* harmony export */ });\n/* harmony import */ var ckeditor5_src_core__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ckeditor5/src/core */ \"ckeditor5/src/core.js\");\n/* harmony import */ var ckeditor5_src_ui__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ckeditor5/src/ui */ \"ckeditor5/src/ui.js\");\n/* harmony import */ var _ucsfcalloutboxview__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./ucsfcalloutboxview */ \"./js/ckeditor5_plugins/ucsfcalloutbox/src/ucsfcalloutboxview.js\");\n\n\n\n\nclass UcsfCalloutboxUI extends ckeditor5_src_core__WEBPACK_IMPORTED_MODULE_0__.Plugin {\n    static get requires() {\n\t\treturn [ ckeditor5_src_ui__WEBPACK_IMPORTED_MODULE_1__.ContextualBalloon ];\n\t}\n\n\tinit() {\n\t\tconst editor = this.editor;\n\t\t\n        // Create the balloon and the form view.\n\t\tthis._balloon = this.editor.plugins.get( ckeditor5_src_ui__WEBPACK_IMPORTED_MODULE_1__.ContextualBalloon );\n\t\tconst viewDocument = this.editor.editing.view.document;\n\n\t\t// Handle click on view document and show panel when selection is placed inside the link element.\n\t\t// Keep panel open until selection will be inside the same link element.\n\t\tthis.listenTo( viewDocument, 'click', () => {\n\t\t\t\n\t\t\tconst isCallout = this._getSelectedElement();\n\n\t\t\tif ( isCallout ) {\n\t\t\t\tthis.formView = this._createFormView();\n\t\t\t\tthis._showUI();\n\t\t\t}\n\t\t} );\n\n\t\teditor.ui.componentFactory.add( 'ucsfCalloutbox', (locale) => {\n\t\t\tconst command = editor.commands.get('insertCalloutBox');\n\t\t\tconst button = new ckeditor5_src_ui__WEBPACK_IMPORTED_MODULE_1__.ButtonView(locale);\n\t\t\tbutton.set({\n                label: 'UcsfCalloutbox',\n                withText: true,\n                tooltip: true,\n            });\n\n\t\t\tbutton.bind('isOn', 'isEnabled').to(command, 'value', 'isEnabled');\n\t\t\t// Show the UI on button click.\n\t\t\tthis.listenTo( button, 'execute', () => {\n\t\t\t\t// editor.execute('insertCalloutBox')\n\t\t\t\tthis.formView = this._createFormView();\n\t\t\t\tthis._showUI();\n\t\t\t} );\n\n\t\t\treturn button;\n\t\t} );\n\t}\n\t_createFormView() {\n\t\tconst editor = this.editor;\n\t\tconst formView = new _ucsfcalloutboxview__WEBPACK_IMPORTED_MODULE_2__[\"default\"]( editor);\n\n\t\t// Execute the command after clicking the \"Save\" button.\n\t\tthis.listenTo( formView, 'submit', () => {\n\t\t\t// Grab values from the abbreviation and title input fields.\n\t\t\tconst align = formView.element.querySelector('#align-dropdown').value;\n\t\t\tconst image = formView.element.querySelector('input[name=\"corner\"]:checked').value;\n\t\t\tconst formValues = { align, image}\n\t\t\t// editor.model.change( writer => {\n\t\t\t// \tconst isCallout = this._getSelectedElement()\n\t\t\t// \twriter.setAttribute('data-align', align, isCallout)\n\t\t\t// \twriter.setAttribute('data-image', image, isCallout)\n\t\t\t// } );\n\t\t\teditor.execute('insertCalloutBox', formValues)\n            // Hide the form view after submit.\n\t\t\tthis._hideUI();\n\t\t} );\n\n\t\t// Hide the form view after clicking the \"Cancel\" button.\n\t\tthis.listenTo( formView, 'cancel', () => {\n\t\t\tthis._hideUI();\n\t\t} );\n\n\t\t// Hide the form view when clicking outside the balloon.\n\t\t(0,ckeditor5_src_ui__WEBPACK_IMPORTED_MODULE_1__.clickOutsideHandler)( {\n\t\t\temitter: formView,\n\t\t\tactivator: () => this._balloon.visibleView === formView,\n\t\t\tcontextElements: [ this._balloon.view.element ],\n\t\t\tcallback: () => this._hideUI()\n\t\t} );\n\n\t\treturn formView;\n\t}\n\n\t_showUI() {\n\t\tthis._balloon.add( {\n\t\t\tview: this.formView,\n\t\t\tposition: this._getBalloonPositionData()\n\t\t} );\n\n\t}\n\n\t_hideUI() {\n\t\t// Clear the input field values and reset the form.\n\t\t// this.formView.abbrInputView.fieldView.value = '';\n\t\t// this.formView.titleInputView.fieldView.value = '';\n\t\t// this.formView.element.reset();\n\n\t\tthis._balloon.remove( this.formView );\n\n\t\t// Focus the editing view after inserting the abbreviation so the user can start typing the content\n\t\t// right away and keep the editor focused.\n\t\tthis.editor.editing.view.focus();\n\t}\n\t_getSelectedElement() {\n\t\tconst model = this.editor.model;\n      \tconst selection = model.document.selection;\n      \tconst selectedElement = selection.getSelectedElement();\n\t\tif (selectedElement && selectedElement.name == \"ucsfcalloutbox\") {\n\t\t\treturn selectedElement\n\t\t} else {\n\t\t\treturn false\n\t\t}\n\t}\n\t_getBalloonPositionData() {\n\t\tconst view = this.editor.editing.view;\n\t\tconst viewDocument = view.document;\n\t\tlet target = null;\n\n\t\t// Set a target position by converting view selection range to DOM\n\t\ttarget = () => view.domConverter.viewRangeToDom( viewDocument.selection.getFirstRange() );\n\n\t\treturn {\n\t\t\ttarget\n\t\t};\n\t}\n}\n\n\n//# sourceURL=webpack://CKEditor5.ucsfcalloutbox/./js/ckeditor5_plugins/ucsfcalloutbox/src/ucsfcalloutboxui.js?")},"./js/ckeditor5_plugins/ucsfcalloutbox/src/ucsfcalloutboxview.js":(__unused_webpack_module,__webpack_exports__,__webpack_require__)=>{"use strict";eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* binding */ UcsfCalloutboxView)\n/* harmony export */ });\n/* harmony import */ var ckeditor5_src_ui__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ckeditor5/src/ui */ \"ckeditor5/src/ui.js\");\n/* harmony import */ var ckeditor5_src_core__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ckeditor5/src/core */ \"ckeditor5/src/core.js\");\n\n  \n  \n  // cSpell:ignore UcsfCalloutboxview\n  class UcsfCalloutboxView extends ckeditor5_src_ui__WEBPACK_IMPORTED_MODULE_0__.View {\n    constructor( editor ) {\n      super( editor.locale );\n      const model = editor.model;\n      const selection = model.document.selection;\n      const selectedElement = selection.getSelectedElement();\n      let align = 'left'\n      let image = '0'\n      if (selectedElement && selectedElement.name == \"ucsfcalloutbox\") {\n        align = selectedElement.getAttribute('data-align')\n        image = selectedElement.getAttribute('data-image')\n      }\n      this.selectLabel = new ckeditor5_src_ui__WEBPACK_IMPORTED_MODULE_0__.Template({\n        tag: 'label',\n        attributes: {\n            for: 'select-dropdown'\n        },\n        children: ['Align Option:']\n    });\n\n    this.select = new ckeditor5_src_ui__WEBPACK_IMPORTED_MODULE_0__.Template({\n        tag: 'select',\n        attributes: {\n            id: 'align-dropdown',\n            class: 'ck-balloon-panel__select'\n        },\n        children: [\n            new ckeditor5_src_ui__WEBPACK_IMPORTED_MODULE_0__.Template({\n                tag: 'option',\n                attributes: {\n                    value: 'left',\n                    selected: (align && align != 'right') ? true : false\n                },\n                children: ['Align Left']\n            }),\n            new ckeditor5_src_ui__WEBPACK_IMPORTED_MODULE_0__.Template({\n                tag: 'option',\n                attributes: {\n                    value: 'right',\n                    selected: (align && align == 'right') ? true : false\n                },\n                children: ['Align Right']\n            })\n        ]\n    });\n\n    this.radioLabel = new ckeditor5_src_ui__WEBPACK_IMPORTED_MODULE_0__.Template({\n        tag: 'label',\n        attributes: {\n            for: 'corner-on'\n        },\n        children: ['Corner Image: On']\n    });\n\n    this.radioInputOn = new ckeditor5_src_ui__WEBPACK_IMPORTED_MODULE_0__.Template({\n        tag: 'input',\n        attributes: {\n            type: 'radio',\n            name: 'corner',\n            value: '1',\n            id: 'corner-on',\n            checked: (image && image == '1') ? true : false\n        }\n    });\n\n    this.radioLabelOff = new ckeditor5_src_ui__WEBPACK_IMPORTED_MODULE_0__.Template({\n        tag: 'label',\n        attributes: {\n            for: 'corner-off'\n        },\n        children: ['Corner Image: Off']\n    });\n\n    this.radioInputOff = new ckeditor5_src_ui__WEBPACK_IMPORTED_MODULE_0__.Template({\n        tag: 'input',\n        attributes: {\n            type: 'radio',\n            name: 'corner',\n            value: '0',\n            id: 'corner-off',\n            checked: (image && image != '1') ? true : false\n        }\n    });\n  \n      this.saveButtonView = this._createButton( 'Save', ckeditor5_src_core__WEBPACK_IMPORTED_MODULE_1__.icons.check, 'ck-button-save' );\n      // Submit type of the button will trigger the submit event on entire form when clicked \n          // (see submitHandler() in render() below).\n      this.saveButtonView.type = 'submit';\n  \n      this.cancelButtonView = this._createButton( 'Cancel', ckeditor5_src_core__WEBPACK_IMPORTED_MODULE_1__.icons.cancel, 'ck-button-cancel' );\n  \n      // Delegate ButtonView#execute to FormView#cancel\n      this.cancelButtonView.delegate( 'execute' ).to( this, 'cancel' );\n\n      this.setTemplate( {\n        tag: 'form',\n        attributes: {\n          class: [ 'ck', 'ck-ucsfcalloutbox-form', 'ck-reset_all-excluded' ],\n          tabindex: '-1'\n        },\n        children:  [\n          this.selectLabel,\n        this.select,\n        this.radioLabel,\n        this.radioInputOn,\n        this.radioLabelOff,\n        this.radioInputOff,\n        this.saveButtonView,\n        this.cancelButtonView\n      ],\n      } );\n    }\n  \n    render() {\n      super.render();\n  \n      // Submit the form when the user clicked the save button or pressed enter in the input.\n      (0,ckeditor5_src_ui__WEBPACK_IMPORTED_MODULE_0__.submitHandler)( {\n        view: this\n      } );\n    }\n\n    _createInput( label ) {\n      const labeledInput = new ckeditor5_src_ui__WEBPACK_IMPORTED_MODULE_0__.LabeledFieldView( this.locale, ckeditor5_src_ui__WEBPACK_IMPORTED_MODULE_0__.createLabeledInputText );\n  \n      labeledInput.label = label;\n  \n      return labeledInput;\n    }\n  \n    _createButton( label, icon, className ) {\n      const button = new ckeditor5_src_ui__WEBPACK_IMPORTED_MODULE_0__.ButtonView();\n  \n      button.set( {\n        label,\n        icon,\n        tooltip: true,\n        class: className\n      } );\n  \n      return button;\n    }\n  }\n\n\n//# sourceURL=webpack://CKEditor5.ucsfcalloutbox/./js/ckeditor5_plugins/ucsfcalloutbox/src/ucsfcalloutboxview.js?")},"ckeditor5/src/core.js":(module,__unused_webpack_exports,__webpack_require__)=>{eval('module.exports = (__webpack_require__(/*! dll-reference CKEditor5.dll */ "dll-reference CKEditor5.dll"))("./src/core.js");\n\n//# sourceURL=webpack://CKEditor5.ucsfcalloutbox/delegated_./core.js_from_dll-reference_CKEditor5.dll?')},"ckeditor5/src/ui.js":(module,__unused_webpack_exports,__webpack_require__)=>{eval('module.exports = (__webpack_require__(/*! dll-reference CKEditor5.dll */ "dll-reference CKEditor5.dll"))("./src/ui.js");\n\n//# sourceURL=webpack://CKEditor5.ucsfcalloutbox/delegated_./ui.js_from_dll-reference_CKEditor5.dll?')},"ckeditor5/src/widget.js":(module,__unused_webpack_exports,__webpack_require__)=>{eval('module.exports = (__webpack_require__(/*! dll-reference CKEditor5.dll */ "dll-reference CKEditor5.dll"))("./src/widget.js");\n\n//# sourceURL=webpack://CKEditor5.ucsfcalloutbox/delegated_./widget.js_from_dll-reference_CKEditor5.dll?')},"dll-reference CKEditor5.dll":e=>{"use strict";e.exports=CKEditor5.dll}},__webpack_module_cache__={};function __webpack_require__(e){var t=__webpack_module_cache__[e];if(void 0!==t)return t.exports;var n=__webpack_module_cache__[e]={exports:{}};return __webpack_modules__[e](n,n.exports,__webpack_require__),n.exports}__webpack_require__.d=(e,t)=>{for(var n in t)__webpack_require__.o(t,n)&&!__webpack_require__.o(e,n)&&Object.defineProperty(e,n,{enumerable:!0,get:t[n]})},__webpack_require__.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),__webpack_require__.r=e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})};var __webpack_exports__=__webpack_require__("./js/ckeditor5_plugins/ucsfcalloutbox/src/index.js");return __webpack_exports__=__webpack_exports__.default,__webpack_exports__})()));