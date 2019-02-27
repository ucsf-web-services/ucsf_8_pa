// CKEDITOR.config.extraPlugins = 'paper-collapse-item';
// CKEDITOR.config.allowedContent = true;
var maxEditable = 10;
CKEDITOR.plugins.add('domtabs', {
  requires: 'widget',

  icons: 'domtabs',
  beforeInit: function (editor) {

    // Configure CKEditor DTD for custom drupal-entity element.
    // @see https://www.drupal.org/node/2448449#comment-9717735
    var dtd = CKEDITOR.dtd, tagName;

    dtd['dom-tabs'] = {'dom-tab': 1, 'dom-pages': 1};
    for (tagName in dtd) {
      if (dtd[tagName].p) {
        dtd[tagName]['dom-tabs'] = 1;
      }
    }
  },
  init: function(editor) {
    CKEDITOR.dialog.add('domtabs', this.path + 'dialogs/domtabs.js' );
    var pluginDirectory = this.path;
    editor.addContentsCss( pluginDirectory + 'css/domtabs.css' );
    editor.widgets.add('domtabs', {
      allowedContent:
        'dom-tabs(*); dom-tab(*); div',

      requiredContent: 'dom-tabs; dom-tab; div',

      editables: _editables(),

      // Define the template of a new Simple Box widget.
      // The template will be used when creating new instances of the Simple Box widget.
      template:
        '<dom-tabs>'+
            '<dom-tab selected><div class="tab1">Tab title</div></dom-tab>'+
            '<dom-tab><div class="tab2">Tab title</div></dom-tab>'+
            '<dom-tab><div class="tab3">Tab title</div></dom-tab>'+
            '<dom-tab><div class="tab4">Tab title</div></dom-tab>'+
            '<dom-tab><div class="tab5">Tab title</div></dom-tab>'+
            '<dom-tab><div class="tab6">Tab title</div></dom-tab>'+
            '<dom-tab><div class="tab7">Tab title</div></dom-tab>'+
            '<dom-tab><div class="tab8">Tab title</div></dom-tab>'+
            '<dom-tab><div class="tab9">Tab title</div></dom-tab>'+
            '<dom-tab><div class="tab10">Tab title</div></dom-tab>'+

            '<dom-pages slot="pages-content">'+
              '<div class="content1" selected>Tab content</div>'+
              '<div class="content2">Tab content</div>'+
              '<div class="content3">Tab content</div>'+
              '<div class="content4">Tab content</div>'+
              '<div class="content5">Tab content</div>'+
              '<div class="content6">Tab content</div>'+
              '<div class="content7">Tab content</div>'+
              '<div class="content8">Tab content</div>'+
              '<div class="content9">Tab content</div>'+
              '<div class="content10">Tab content</div>'+
            '</dom-iron-pages>'+

        '</dom-tabs>',

      button: 'Create a tab items',
      dialog: 'domtabs',

      upcast: function(element) {
        return element.name == 'dom-tabs';
      },

      init: function() {
        var tabs = this.element.find('dom-tab')
        // this.element.setHtml('<paper-tabs selected="0"></paper-tabs><iron-pages selected="0"></iron-pages>')
        if (tabs.$.length != 10) {
          this.setData( 'numOfTabs', tabs.$.length );
        }

        var pages = this.element.find('dom-pages div');
        var tabs = this.element.find('dom-tab');
        tabs.$.forEach(function(el, index){

          if (tabs.getItem(index).hasAttribute('selected')) {
            pages.getItem(index).setAttribute('selected',true) ;
          }
          el.addEventListener('click', function() {
            pages.$.forEach(function(el, index){;
              pages.getItem(index).removeAttribute('selected');
            });
            tabs.$.forEach(function(el, index){
              tabs.getItem(index).removeAttribute('selected') ;
            });

            tabs.getItem(index).setAttribute('selected',true) ;
            pages.getItem(index).setAttribute('selected',true) ;
          });
        });
      },
      data: function() {
        if ( this.data.numOfTabs && this.data.numOfTabs != this.element.find('dom-tab').$.length){
          var tabs = this.element.find('dom-tab')
          var content = this.element.find('dom-pages div')
          for (var i = 9; i >= this.data.numOfTabs; i--) {
            tabs.$[i].remove()
            content.$[i].remove()
          }
        }
      }
    });
  }
});
function _editables() {
  var editables = {};

  for (var i = 1; i <= maxEditable; i++) {
    editables['tab' + i] = {
      selector: '.tab' + i,
      // allowedContent: 'a[!href]'
    };
    editables['content' + i] = {
      selector: '.content' + i
    };
  }

  return editables;
}
