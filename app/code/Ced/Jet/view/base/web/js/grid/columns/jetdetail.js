define([
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'Magento_Ui/js/modal/modal'
], function(Column, $, modal) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html',
        },

        getProducturl: function(row) {
            return row[this.index + '_producturl'];
        },

        getTitle: function(row) {
            return row[this.index + '_title'];
        },

        getLabel: function(row) {
            return row[this.index + '_html'];
        },

        getProductId: function(row) {
            return row[this.index + '_productid'];
        },

        startView: function(row) {

            if ($("#jetpop"+this.getProductId(row)).length) {
                var previewAdded = $("#jetpop"+this.getProductId(row));
                previewAdded.modal({
                    opened: function(row) { }
                }).trigger('openModal');

            } else {
                var url_link = this.getProducturl(row);
                var previewPopup = $('<div/>',{id : 'jetpop'+this.getProductId(row) });
                var jetpopup = previewPopup.modal({
                    title: this.getTitle(row),
                    innerScroll: true,
                    modalLeftMargin: 15,
                    buttons: [],
                    opened: function(row) {
                        $.ajax({
                            showLoader: true,
                            url: url_link,
                            type: 'POST',
                            data: {'form_key':FORM_KEY}
                        }).done(function(a) {
                            jetpopup.append(a);
                        });
                    },
                    closed: function(row) { }
                }).trigger('openModal');
            }
        }, 

        getFieldHandler: function(row) {
            return this.startView.bind(this, row);
        },
    
    });

});
