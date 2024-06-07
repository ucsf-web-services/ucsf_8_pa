class DomOneColumnLayout extends HTMLElement {
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
            --size-50: 50%;
            --size-20: 20%;
            --size-30: 30%;
            --size-40: 40%;
            --size-60: 60%;
            --size-70: 70%;
            --size-80: 80%;
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
        .column1 > *:last-child,
        ::slotted([slot=column1]) > *:last-child {
            margin-bottom: 0;
        }

        @media (min-width: 769px) {
            :host {
            flex-direction: row;
            }
            ::slotted([slot=column1]), .column1 {
            flex: 1;
            }

        }

        </style>
        <slot name="column1" class="column1"></slot>
      `;
    }
  
    connectedCallback() {
        this.dataset.column1Color = this.dataset.column1Color || 'transparent';
        this._initializeStyles();
    }
  
    _initializeStyles() {  
      const column1FontColor = (this.dataset.column1Color === 'transparent' || this.dataset.column1Color === 'interactive-yellow') ? 'black' : 'white';
      const column1Padding = (this.dataset.column1Color === 'transparent' || this.dataset.column1Color === 'interactive-yellow') ? 'transparent' : 'normal';
  
  
      this.style.setProperty('--column1-font-color', `var(--font-${column1FontColor})`);
      this.style.setProperty('--column1-color', `var(--bg-${this.dataset.column1Color})`);
      this.style.setProperty('--column1-padding', `var(--padding-${column1Padding})`);
    }
  
    static get observedAttributes() {
      return ['data-column1-color'];
    }
  
    attributeChangedCallback(name, oldValue, newValue) {
      if (oldValue !== newValue) {
        this._initializeStyles();
      }
    }
  }
  
  customElements.define('dom-onecolumn-layout', DomOneColumnLayout);