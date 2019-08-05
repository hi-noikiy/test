/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'Magento_Checkout/js/model/totals',
        'Magento_Catalog/js/price-utils',
        'Magento_Customer/js/model/customer',
        'Aheadworks_StoreCredit/js/action/apply-store-credit',
        'Aheadworks_StoreCredit/js/action/remove-store-credit',
        'Aheadworks_StoreCredit/js/action/get-customer-store-credit-balance',
        'Aheadworks_StoreCredit/js/model/store-credit-balance',
        'Aheadworks_StoreCredit/js/model/is-applied-flag',
        'mage/translate'
     ],
    function (
            $, 
            ko, 
            Component, 
            totals, 
            priceUtils,
            customer, 
            applyStoreCredit, 
            removeStoreCredit, 
            getCustomerStoreCreditBalanceAction,
            storeCreditBalance,
            isAppliedFlag,
            $t
        ){
        'use strict';

        var isLoading = ko.observable(false);

        return Component.extend({
            defaults: {
                template: 'Aheadworks_StoreCredit/payment/store-credit'
            },

            /**
             * Check if store credit is apply
             *
             * @return {boolean}
             */
            isApplied: isAppliedFlag,

            /**
             * Is loading
             *
             * @return {boolean}
             */
            isLoading: isLoading,
            
            /**
             * Check if customer is logged in
             * 
             * @return {boolean}
             */
            isCustomerLoggedIn: function(){
                return customer.isLoggedIn();
            },
            
            /**
             * Is display store credit block
             * 
             * @return {boolean}
             */
            isDisplayed: function() {
                return this.isCustomerLoggedIn();
            },
            
            /**
             * Apply store credit
             * 
             * @return {void}
             */
            apply: function() {
                if (this.validate()) {
                    isLoading(true);
                    applyStoreCredit(isAppliedFlag, isLoading);
                }
            },
            
            /**
             * Remove store credit
             * 
             * @return {void}
             */
            remove: function() {
                if (this.validate()) {
                    isLoading(true);
                    removeStoreCredit(isAppliedFlag, isLoading);
                }
            },
            
            /**
             * Validate
             * 
             * @return {boolean}
             */
            validate: function() {
                return true;
            },
            
            /**
             * Retrieve available store credit text
             * 
             * @return {String}
             */
            getAvailableStoreCreditText: function() {
                getCustomerStoreCreditBalanceAction();
                return $t('Available: ') +
                    this.getFormattedPrice(storeCreditBalance.customerStoreCreditBalanceCurrency());
            },
            
            /**
             * Retrieve used store credit text
             * 
             * @return {String}
             */
            getUsedStoreCreditText: function() {
                var storeCredit = totals.getSegment('aw_store_credit');

                if (storeCredit) {
                    return $t('Applied: ') + this.getFormattedPrice(storeCredit.value);
                } else {
                    return '';
                }
            },

            /**
             * Format price
             * 
             * @return {String}
             */
            getFormattedPrice: function(price) {
                return priceUtils.formatPrice(price, window.checkoutConfig.priceFormat);
            }
        });
    }
);
