/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

/*global define,alert*/
define(
    [
        'jquery',
        'Aheadworks_StoreCredit/js/model/resource-url-manager',
        'Aheadworks_StoreCredit/js/model/payment/store-credit-messages',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/action/get-payment-information',
        'mage/storage',
        'mage/translate'
    ],
    function (
            $, 
            urlManager,
            messageContainer,
            quote, 
            totals,
            errorProcessor,
            getPaymentInformationAction, 
            storage, 
            $t
    ) {
        'use strict';
        return function (isApplied, isLoading, deferred) {
            var quoteId = quote.getQuoteId(),
                url = urlManager.getRemoveStoreCreditUrl(quoteId), 
                message = $t('Store Credit were successfully removed.');
            
            messageContainer.clear();
            if (typeof deferred == 'undefined') {
                deferred = $.Deferred();
            }

            return storage.delete(
                url,
                true
            ).done(
                function () {
                    var totalsDeferred = $.Deferred();

                    totals.isLoading(true);
                    getPaymentInformationAction(totalsDeferred);
                    $.when(totalsDeferred).done(function () {
                        isApplied();
                        totals.isLoading(false);
                        deferred.resolve();
                    });
                    messageContainer.addSuccessMessage({
                        'message': message
                    });
                }
            ).fail(
                function (response) {
                    totals.isLoading(false);
                    errorProcessor.process(response, messageContainer);
                    deferred.reject();
                }
            ).always(
                function () {
                    isLoading(false);
                }
            );
        };
    }
);
