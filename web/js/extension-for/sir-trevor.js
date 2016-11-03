if( typeof(SirTrevor) == "object" ) {

    SirTrevor.Blocks.Brightcove = SirTrevor.Block.extend({

        extensionUrl: document.currentScript.getAttribute('data-extension-url'),
        extensionWebPath: document.currentScript.getAttribute('data-extension-web-path'),

        fieldId: null,

        type: 'brightcove',
        title: function() { return 'Brightcove'; },
        icon_name: 'video',
        toolbarEnabled: true,
        // Custom html that is shown when a block is being edited.
        textable: true,

        editorHTML: '<div class="st-block frontend-target brightcove">'+
                    '    <h2>Brightcove</h2>'+
                    '    <input class="form-control ui-autocomplete-input ui-autocomplete-loading" id="" maxlength="30" name="" placeholder="Enter a term or id to search for brightcove videos" type="text" value="" autocomplete="off">'+
                    '    <img src="" class="spinner">'+
                    '    <input class="data-target" id="" type="hidden" value="">'+
                    '    <div class="current-wrapper">'+
                    '        <div class="current"></div>'+
                    '    </div>'+
                    '</div>',

        /**
         * Loads the json data in to the field
         * @param data
         */
        loadData: function(data){
            $(this.$('.data-target')).val(data.id);
        },

        /**
         * Sets the data form the ImageService into the Block store
         */
        save: function(){
            var data = $(this.$('.data-target')).val();
            this.setData({id: data});
        },

        /**
         * Creates the new image service block
         */
        onBlockRender: function() {

            this.fieldId = 'brightcove-st-' + String(new Date().valueOf());

            $(this.$('img.spinner')).attr('src', this.extensionWebPath+'/images/ajax-loader.gif');
            $(this.$('input.data-target')).attr('id', this.fieldId);
            $(this.$('.frontend-target')).addClass('brightcove-'+this.fieldId);
            $(this.$('.ui-autocomplete-input')).attr('id','lookup-'+this.fieldId);
            $(this.$('.ui-autocomplete-input')).attr('name','lookup-'+this.fieldId);
            
            // Gives the container an unique id
            initBrightcoveField(this.fieldId, this.extensionUrl);
        }
    });

}

