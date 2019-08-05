/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
         'Magento_Checkout/js/model/resource-url-manager'
    ],
    function (urlManager) {
        'use strict';
        return {
            /**
             * Retrieve apply store credit url
             * 
             * @return {string}
             */
            getApplyStoreCreditUrl: function (quoteId) {
                var params = (urlManager.getCheckoutMethod() == 'guest') ? {quoteId: quoteId} : {};
                var urls = {
                        'guest': '',
                        'customer': '/carts/mine/apply-aw-store-credit/'
                };
                return urlManager.getUrl(urls, params);
            },
            
            /**
             * Retrieve remove store credit url
             * 
             * @return {string}
             */
            getRemoveStoreCreditUrl: function  (quoteId) {
                var params = (urlManager.getCheckoutMethod() == 'guest') ? {quoteId: quoteId} : {};
                var urls = {
                        'guest': '', 
                        'customer': '/carts/mine/remove-aw-store-credit/'
                };
                return urlManager.getUrl(urls, params);
            },
            
            /**
             * Retrieve get customer store credit balance url
             * 
             * @return {string}
             */
            getCustomerStoreCreditBalanceUrl: function  (customerId) {
                var params = {customerId: customerId};
                var urls = {
                        'customer': '/carts/mine/aw-get-customer-store-credit'
                };
                return urlManager.getUrl(urls, params);
            }
        };
    }
);