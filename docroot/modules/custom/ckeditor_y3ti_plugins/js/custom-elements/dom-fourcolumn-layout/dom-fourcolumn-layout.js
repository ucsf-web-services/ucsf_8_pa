class DomFourColumnLayout extends HTMLElement {
    constructor() {
      super();
      const shadow = this.attachShadow({ mode: 'open' });
      shadow.innerHTML += `
      <style>
        :host {
            margin: 2% 0;
            display: flex;
            flex-direction: column;
            --font-white: #FFF;
            --font-black: #404040;
            --size-25: 25%;
            --size-20: 20%;
            --size-60: 60%;
            --bg-interactive-blue: #0071ad;
            --bg-interactive-dark-blue: #052049;
            --bg-interactive-light-navy: #506380;
            --bg-interactive-green: #6EA400;
            --bg-interactive-dark-grey: #737373;
            --bg-interactive-orange: #F26D04;
            --bg-interactive-teal: #058488;
            --bg-interactive-purple: #716FB2;
            --bg-interactive-red: #EB093C;
            --bg-interactive-yellow: #FFDD00;
            --bg-transparent: transparent;
            --padding-transparent: 0 !important;
            --padding-normal: 10px 25px !important;
        }
        ::slotted([slot=column1]), .column1 {
            padding: var(--column1-padding);
            margin-bottom: 2% !important;
            background-color: var(--column1-color);
            color: var(--column1-font-color);
        }
        ::slotted([slot=column2]), .column2 {
            padding: var(--column2-padding);

            background-color: var(--column2-color);
            color: var(--column2-font-color);
        }
        ::slotted([slot=column3]), .column3 {
            padding: var(--column3-padding);
            margin-top: 2% !important;
            background-color: var(--column3-color);
            color: var(--column3-font-color);
        }
        ::slotted([slot=column4]), .column4 {
            padding: var(--column4-padding);

            background-color: var(--column4-color);
            color: var(--column4-font-color);
        }

        @media (min-width: 769px) {
            :host {
            flex-direction: row;
            flex-wrap: wrap;
            }
            ::slotted([slot=column1]), .column1 {
            flex: 0 0 49%;
            margin-right: 2% !important;
            margin-bottom: 0 !important;
            }
            ::slotted([slot=column2]), .column2 {
            flex: 0 0 49%;
            }
            ::slotted([slot=column3]), .column3 {
            flex: 0 0 49%;

            margin-top: 0 !important;
            }
            ::slotted([slot=column4]), .column4 {
            flex: 0 0 49%;
            margin-left: 2% !important;
            }

        }
        @media (min-width: 1024px) {
            :host {
            flex-direction: row;
            flex-wrap: nowrap;
            }
            ::slotted([slot=column1]), .column1 {
            flex: 1 var(--column1-size);
            margin-right: 2% !important;
            margin-bottom: 0 !important;
            }
            ::slotted([slot=column2]), .column2 {
            flex: 1 var(--column2-size);
            }
            ::slotted([slot=column3]), .column3 {
            flex: 1 var(--column3-size);
            margin-left: 2% !important;
            margin-top: 0 !important;
            }
            ::slotted([slot=column4]), .column4 {
            flex: 1 var(--column4-size);
            }

        }

        </style>
        <slot name="column1" class="column1"></slot>
        <slot name="column2" class="column2"></slot>
        <slot name="column3" class="column3"></slot>
        <slot name="column4" class="column4"></slot>
      `;
    }
  
    connectedCallback() {
      this.dataset.column1Size = this.dataset.column1Size || '25';
      this.dataset.column2Size = this.dataset.column2Size || '25';
      this.dataset.column3Size = this.dataset.column3Size || '25';
      this.dataset.column4Size = this.dataset.column4Size || '25';
      this.dataset.column1Color = this.dataset.column1Color || 'transparent';
      this.dataset.column2Color = this.dataset.column2Color || 'transparent';
      this.dataset.column3Color = this.dataset.column3Color || 'transparent';
      this.dataset.column4Color = this.dataset.column4Color || 'transparent';
      this._initializeStyles();
    }
  
    _initializeStyles() {
      const column1FontColor = (this.dataset.column1Color === 'transparent' || this.dataset.column1Color === 'interactive-yellow') ? 'black' : 'white';
      const column2FontColor = (this.dataset.column2Color === 'transparent' || this.dataset.column2Color === 'interactive-yellow') ? 'black' : 'white';
      const column3FontColor = (this.dataset.column3Color === 'transparent' || this.dataset.column3Color === 'interactive-yellow') ? 'black' : 'white';
      const column4FontColor = (this.dataset.column4Color === 'transparent' || this.dataset.column4Color === 'interactive-yellow') ? 'black' : 'white';

      const column1Padding = (this.dataset.column1Color === 'transparent' || this.dataset.column1Color === 'interactive-yellow') ? 'transparent' : 'normal';
      const column2Padding = (this.dataset.column2Color === 'transparent' || this.dataset.column2Color === 'interactive-yellow') ? 'transparent' : 'normal';
      const column3Padding = (this.dataset.column3Color === 'transparent' || this.dataset.column3Color === 'interactive-yellow') ? 'transparent' : 'normal';
      const column4Padding = (this.dataset.column4Color === 'transparent' || this.dataset.column4Color === 'interactive-yellow') ? 'transparent' : 'normal';
 
  
      this.style.setProperty('--column1-font-color', `var(--font-${column1FontColor})`);
      this.style.setProperty('--column1-color', `var(--bg-${this.dataset.column1Color})`);
      this.style.setProperty('--column1-size', `var(--size-${this.dataset.column1Size})`);
      this.style.setProperty('--column2-font-color', `var(--font-${column2FontColor})`);
      this.style.setProperty('--column2-color', `var(--bg-${this.dataset.column2Color})`);
      this.style.setProperty('--column2-size', `var(--size-${this.dataset.column2Size})`);
      this.style.setProperty('--column3-font-color', `var(--font-${column3FontColor})`);
      this.style.setProperty('--column3-color', `var(--bg-${this.dataset.column3Color})`);
      this.style.setProperty('--column3-size', `var(--size-${this.dataset.column3Size})`);
      this.style.setProperty('--column4-font-color', `var(--font-${column4FontColor})`);
      this.style.setProperty('--column4-color', `var(--bg-${this.dataset.column4Color})`);
      this.style.setProperty('--column4-size', `var(--size-${this.dataset.column4Size})`);
      this.style.setProperty('--column1-padding', `var(--padding-${column1Padding})`);
      this.style.setProperty('--column2-padding', `var(--padding-${column2Padding})`);
      this.style.setProperty('--column3-padding', `var(--padding-${column3Padding})`);
      this.style.setProperty('--column4-padding', `var(--padding-${column4Padding})`);
    }
  
    static get observedAttributes() {
      return ['data-column1-size', 'data-column2-size', 'data-column3-size', 'data-column4-size', 'data-column1-color', 'data-column2-color', 'data-column3-color', 'data-column4-color'];
    }
  
    attributeChangedCallback(name, oldValue, newValue) {
      if (oldValue !== newValue) {
        this._initializeStyles();
      }
    }
  }
  
  customElements.define('dom-fourcolumn-layout', DomFourColumnLayout);
  