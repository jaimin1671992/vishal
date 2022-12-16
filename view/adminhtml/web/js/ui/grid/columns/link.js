define([
    'Magento_Ui/js/grid/columns/column'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            bodyTmpl: 'Tvape_ReferralProgram/ui/grid/cells/link'
        },
        
        /**
         * Retrieve link rows
         * 
         * @returns {Array}
         */
        getLinkRows: function (record) {
            if (record[this.index] && !Array.isArray(record[this.index])) {
                return [record[this.index]];
            }
            return record[this.index];
        },
    });
});