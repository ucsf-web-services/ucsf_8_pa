/**
 * @file This is what CKEditor refers to as a master (glue) plugin. Its role is
 * just to load the “editing” and “UI” components of this Plugin. Those
 * components could be included in this file, but
 *
 * I.e, this file's purpose is to integrate all the separate parts of the plugin
 * before it's made discoverable via index.js.
 */

import UcsfQuoteEditing from './ucsfquoteediting';
import UcsfQuoteUI from './ucsfquoteui';
import { Plugin } from 'ckeditor5/src/core';

export default class UcsfQuote extends Plugin {
  static get requires() {
    return [UcsfQuoteEditing, UcsfQuoteUI];
  }
}