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
  
  // cSpell:ignore UcsfQuoteView
  export default class UcsfQuoteView extends View {
    constructor( editor ) {
        super( editor.locale );
        const model = editor.model;
        const selection = model.document.selection;
        const selectedElement = selection.getSelectedElement();
        const bind = this.bindTemplate;

        let align = 'half-left'
        let color = 'blue'
        if (selectedElement && selectedElement.name == "ucsfquote") {
            align = selectedElement.getAttribute('align')
            color = selectedElement.getAttribute('color')
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
            on: {
                change: bind.to( evt => {
                    console.log( `${ evt.target } has been change!` );
                    console.log(this.select)
                } )
            },
            children: [
                new Template({
                    tag: 'option',
                    attributes: {
                        value: 'half-left',
                        selected: (align && align == 'half-left') ? true : false
                    },
                    children: ['Align Left']
                }),
                new Template({
                    tag: 'option',
                    attributes: {
                        value: 'half-right',
                        selected: (align && align == 'half-right') ? true : false
                    },
                    children: ['Align Right']
                }),
                new Template({
                    tag: 'option',
                    attributes: {
                        value: 'full-right',
                        selected: (align && align == 'full-right') ? true : false
                    },
                    children: ['Align Right Full-Width']
                })
            ]
        });
        this.colorLabel = new Template({
            tag: 'label',
            attributes: {
                for: 'color-dropdown'
            },
            children: ['color Option:']
            });

        this.color = new Template({
            tag: 'select',
            attributes: {
                id: 'color-dropdown',
                class: 'ck-balloon-panel__select'
            },
            children: [
                new Template({
                    tag: 'option',
                    attributes: {
                        value: 'blue',
                        selected: (color && color == 'blue') ? true : false
                    },
                    children: ['Blue']
                }),
                new Template({
                    tag: 'option',
                    attributes: {
                        value: 'light-blue',
                        selected: (color && color == 'light-blue') ? true : false
                    },
                    children: ['Light Blue']
                }),
                new Template({
                    tag: 'option',
                    attributes: {
                        value: 'teal',
                        selected: (color && color == 'teal') ? true : false
                    },
                    children: ['Teal']
                }),
                new Template({
                    tag: 'option',
                    attributes: {
                        value: 'cool-green',
                        selected: (color && color == 'cool-green') ? true : false
                    },
                    children: ['Cool Green']
                }),
                new Template({
                    tag: 'option',
                    attributes: {
                        value: 'warm-green',
                        selected: (color && color == 'warm-green') ? true : false
                    },
                    children: ['Warm Green']
                }),
                new Template({
                    tag: 'option',
                    attributes: {
                        value: 'purple',
                        selected: (color && color == 'light-blue') ? true : false
                    },
                    children: ['Purple']
                }),
                new Template({
                    tag: 'option',
                    attributes: {
                        value: 'violet',
                        selected: (color && color == 'violet') ? true : false
                    },
                    children: ['Violet']
                }),
                new Template({
                    tag: 'option',
                    attributes: {
                        value: 'light-blue',
                        selected: (color && color == 'magenta') ? true : false
                    },
                    children: ['Magenta']
                }),
                new Template({
                    tag: 'option',
                    attributes: {
                        value: 'gray',
                        selected: (color && color == 'gray') ? true : false
                    },
                    children: ['Cool Gray']
                }),
                new Template({
                    tag: 'option',
                    attributes: {
                        value: 'navy',
                        selected: (color && color == 'navy') ? true : false
                    },
                    children: ['Navy']
                }),
                new Template({
                    tag: 'option',
                    attributes: {
                        value: 'yellow',
                        selected: (color && color == 'yellow') ? true : false
                    },
                    children: ['Yellow']
                }),
                new Template({
                    tag: 'option',
                    attributes: {
                        value: 'dark-gray',
                        selected: (color && color == 'dark-gray') ? true : false
                    },
                    children: ['Dark Gray']
                })
            ]
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
          class: [ 'ck', 'ck-ucsfcalloutbox-form', 'ck-reset_all-excluded' ],
          tabindex: '-1'
        },
        children:  [
            this.selectLabel,
            this.select,
            this.colorLabel,
            this.color,
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
