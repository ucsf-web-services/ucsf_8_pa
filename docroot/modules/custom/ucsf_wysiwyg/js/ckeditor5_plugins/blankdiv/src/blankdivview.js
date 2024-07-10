import {
    View,
    LabeledFieldView,
    createLabeledInputText,
    InputTextView,
    ButtonView,
    submitHandler,
    Template
  } from 'ckeditor5/src/ui';
  import { icons } from 'ckeditor5/src/core';
  
  // cSpell:ignore Blankdivview
  export default class BlankdivView extends View {
    constructor( editor ) {
      super( editor.locale );
      const model = editor.model;
      const selection = model.document.selection;
      const selectedElement = selection.getSelectedElement();
      let align = 'half-image-left'
      let blankdiv = ''
      if (selectedElement && selectedElement.name == "blankdiv") {
        align = selectedElement.getAttribute('align')
        blankdiv = selectedElement.getAttribute('blankdiv')
      }
      this.selectLabel = new Template({
        tag: 'label',
        attributes: {
            for: 'align-dropdown'
        },
        children: ['Align Option:']
        });

        this.select = new Template({
            tag: 'select',
            attributes: {
                id: 'align-dropdown',
                class: 'ck-balloon-panel__select'
            },
            children: [
                new Template({
                    tag: 'option',
                    attributes: {
                        value: 'half-image-right',
                        selected: (align && align == 'half-image-right') ? true : false
                    },
                    children: ['Half Image Right']
                }),
                new Template({
                    tag: 'option',
                    attributes: {
                        value: 'half-image-right-full',
                        selected: (align && align == 'half-image-right-full') ? true : false
                    },
                    children: ['Full Bleed Right']
                }),
                new Template({
                    tag: 'option',
                    attributes: {
                        value: 'half-image-left',
                        selected: (align && align == 'half-image-left') ? true : false
                    },
                    children: ['Half Image Left']
                }),
                new Template({
                    tag: 'option',
                    attributes: {
                        value: 'half-image-left-full',
                        selected: (align && align == 'half-image-left-full') ? true : false
                    },
                    children: ['Full Bleed Left']
                }),
                new Template({
                    tag: 'option',
                    attributes: {
                        value: 'full-bleed-image',
                        selected: (align && align == 'full-bleed-image') ? true : false
                    },
                    children: ['Full Bleed']
                })
            ]
        });
        this.scriptLabel =  new Template({
            tag: 'label',
            attributes: {
                for: 'blankdiv'
            },
            children: ['Script:']
        });
        this.script =  new Template({
            tag: 'textarea',
            attributes: {
                id: 'blankdiv',
                rows: 12
            },
            children: [blankdiv]
        });

      this.saveButtonView = this._createButton( 'Save', icons.check, 'ck-button-save' );
      // Submit type of the button will trigger the submit event on entire form when clicked 
          // (see submitHandler() in render() below).
      this.saveButtonView.type = 'submit';
  
      this.cancelButtonView = this._createButton( 'Cancel', icons.cancel, 'ck-button-cancel' );
  
      // Delegate ButtonView#execute to FormView#cancel
      this.cancelButtonView.delegate( 'execute' ).to( this, 'cancel' );

      this.setTemplate( {
        tag: 'form',
        attributes: {
          class: [ 'ck', 'ck-blankdiv-form', 'ck-reset_all-excluded' ],
          tabindex: '-1'
        },
        children:  [
            this.selectLabel,
            this.select,
            this.scriptLabel,
            this.script,
            this.saveButtonView,
            this.cancelButtonView
        ],
      } );
    }
  
    render() {
      super.render();
  
      // Submit the form when the user clicked the save button or pressed enter in the input.
      submitHandler( {
        view: this
      } );
    }

    _createInput( label ) {
      const labeledInput = new LabeledFieldView( this.locale, createLabeledInputText );
  
      labeledInput.label = label;
  
      return labeledInput;
    }
  
    _createButton( label, icon, className ) {
      const button = new ButtonView();
  
      button.set( {
        label,
        icon,
        tooltip: true,
        class: className
      } );
  
      return button;
    }
  }
