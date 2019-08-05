/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, totals) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Aheadworks_StoreCredit/summary/store-credit'
            },
            
            /**
             * Order totals
             * 
             * @return {Object}
             */
            totals: totals.totals(),
            
            /**
             * Is display store credit totals
             * 
             * @return {boolean}
             */
            isDisplayed: function() {
                return this.isFullMode() && this.getPureValue() != 0;
            },
            
            /**
             * Get title 
             * 
             * @return {string} 
             */
            getTitle: function() {
                if (this.totals) {
                    var storeCredit = totals.getSegment('aw_store_credit');
                    
                    if (storeCredit) {
                        return storeCredit.title;
                    }
                    return null;
                }
            },
            
            /**
             * Get total value
             * 
             * @return {number}
             */
            getPureValue: function() {
                var price = 0;
                if (this.totals) {
                    var storeCredit = totals.getSegment('aw_store_credit');
                    
                    if (storeCredit) {
                        price = storeCredit.value;
                    }
                }
                return price; 
            },
            
            /**
             * Get total value
             * 
             * @return {string}
             */
            getValue: function() {
                return this.getFormattedPrice(this.getPureValue());
            }
        });
    }
);
