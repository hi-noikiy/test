define([
    'jquery',
    'mage/utils/wrapper',
    'underscore',
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/quote',
    'mage/storage',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Customer/js/customer-data',
    'uiRegistry',
    'Amasty_Conditions/js/model/subscriber'
], function ($, wrapper, _, resourceUrlManager, quote, storage, totalsService, errorProcessor, customerData, registry, subscriber) {
    'use strict';

    return function () {
        var serviceUrl,
            payload,
            address,
            paymentMethod,
            requiredFields = ['countryId', 'region', 'regionId', 'postcode'],
            paymentForm,
            newAddress = quote.billingAddress() ? quote.billingAddress() : quote.shippingAddress(),
            city,
            sameAsBilling = false;

        // Start loader for totals block
        totalsService.isLoading(true);
        subscriber.isLoading(true);
        serviceUrl = resourceUrlManager.getUrlForTotalsEstimationForNewAddress(quote);
        address = _.pick(newAddress, requiredFields);
        paymentMethod = quote.paymentMethod() ? quote.paymentMethod().method : null;
        paymentForm = registry.get('checkout.steps.billing-step.payment.payments-list.'
            + paymentMethod +'-form');
        sameAsBilling = paymentForm ? paymentForm.isAddressSameAsShipping() : false;
        city = quote.shippingAddress() ? quote.shippingAddress().city : null;

        address.extension_attributes = {
            advanced_conditions: {
                custom_attributes: quote.shippingAddress() ? quote.shippingAddress().custom_attributes : [],
                payment_method: paymentMethod,
                same_as_billing : sameAsBilling,
                city: city,
                address_line: !sameAsBilling && quote.billingAddress()
                    ? quote.billingAddress().street
                    : quote.shippingAddress()
                        ? quote.shippingAddress().street
                        : null
            }
        };

        payload = {
            addressInformation: {
                address: address
            }
        };

        if (quote.shippingMethod() && quote.shippingMethod()['method_code']) {
            payload.addressInformation['shipping_method_code'] = quote.shippingMethod()['method_code'];
            payload.addressInformation['shipping_carrier_code'] = quote.shippingMethod()['carrier_code'];
        }

        storage.post(
            serviceUrl, JSON.stringify(payload), false
        ).done(function (result) {
            quote.setTotals(result);
        }).fail(function (response) {
            errorProcessor.process(response);
        }).always(function () {
            // Stop loader for totals block
            totalsService.isLoading(false);
            subscriber.isLoading(false);
        });
    };
});