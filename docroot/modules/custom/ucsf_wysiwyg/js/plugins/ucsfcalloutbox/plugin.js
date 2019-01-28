CKEDITOR.plugins.add('ucsfcalloutbox', {
    requires: 'widget',
    icons: 'ucsfcalloutbox',

    init: function (editor) {
        CKEDITOR.dialog.add('ucsfcalloutbox', this.path + 'dialogs/ucsfcalloutbox.js');
        //var config = editor.config.y3titemplatemenuButtons.split(',');
        //var button =  config['pullquote'] == undefined ? 'Create a Pullquote' : undefined;
        var pluginDirectory = this.path;
        editor.addContentsCss(pluginDirectory + 'css/ucsfcalloutbox.css');

        editor.ui.addButton('ucsfcalloutbox', {
            label: 'Insert UCSF Callout Box',
            command: 'ucsfcalloutbox'

        });

        editor.widgets.add('ucsfcalloutbox', {
            allowedContent: 'aside(!ucsfcallout,callout-left,callout-right,align-center);',
            //'div(!ucsfcallout--content); div(!ucsfcallout--title); div(!ucsfcallout--related); div(!ucsfcallout--cta); ' +
            //'div(!ucsfcallout--image);',

            //requiredContent: 'div(ucsfcallout); div(ucsfcallout--title); div(ucsfcallout--content);',

            editables: {
                content: {
                    selector: '.callout__content',
                    allowedContent: 'p br strong em a[!href] '
                },
                title: {
                    selector: '.callout__title',
                    allowedContent: 'h1 h2 h3 h4 h5 h6 p br strong em;'
                },
                image: {
                    selector: '.callout__image',
                    allowedContent: 'img picture p br strong em a[!href] source video'
                },
                cta: {
                    selector: '.callout__cta',
                    allowedContent: 'img picture p br strong em a[!href] source'
                }
            },

            template: '<aside class="callout-left">' +
            '<div class="callout__image">Image</div>' +
            '<h3 class="callout__title">Title</h3>' +
            '<div class="callout__content">Content</div>' +
            '<div class="callout__cta">' +
            '<a href="" class="callout__cta">Learn more</a>' +
            '</div>' +
            '</aside>',

            button: 'Create a Callout Box',
            dialog: 'ucsfcalloutbox',

            upcast: function (element) {
                return element.name == 'div' && element.hasClass('ucsfcallout');
            },
            init: function () {

                if (this.element.hasClass('callout-left'))
                    this.setData('align', 'left');
                if (this.element.hasClass('callout-right'))
                    this.setData('align', 'right');
                if (this.element.hasClass('callout-center'))
                    this.setData('align', 'center');
            },
            data: function () {
                // Brutally remove all align classes and set a new one if "align" widget data is set.
                this.element.removeClass('callout-center');
                this.element.removeClass('callout-left');
                this.element.removeClass('callout-right');
                if (this.data.align)
                    this.element.addClass('callout-' + this.data.align);
                /*
                 if ( this.data.bgColor ) {
                 this.element.addClass( 'bg--' + this.data.bgColor );
                 }
                 */
            }
        });
    }
});
