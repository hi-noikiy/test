/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'Aheadworks_StoreCredit/js/model/resource-url-manager',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/model/customer',
        'Aheadworks_StoreCredit/js/model/store-credit-balance'
    ],
    function ($, urlBuilder, storage, errorProcessor, customer, storeCreditBalance) {
        'use strict';

        return function (deferred, messageContainer) {
            var serviceUrl;

            deferred = deferred || $.Deferred();
            
            serviceUrl = urlBuilder.getCustomerStoreCreditBalanceUrl(customer.customerData.id);

            return storage.get(
                serviceUrl, false
            ).done(
                function (response) {
                    if (response.customer_store_credit_balance) {
                        storeCreditBalance.customerStoreCreditBalance(
                            response.customer_store_credit_balance
                        );
                    }

                    if (response.customer_store_credit_balance_currency) {
                        storeCreditBalance.customerStoreCreditBalanceCurrency(
                            response.customer_store_credit_balance_currency
                        );
                    }
                    
                    deferred.resolve();
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response, messageContainer);
                    deferred.reject();
                }
            );
        };
    }
);
