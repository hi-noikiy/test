/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        '../model/shipping-rates-validator',
        '../model/shipping-rates-validation-rules'
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        chcanadapostShippingRatesValidator,
        chcanadapostShippingRatesValidationRules
    ) {
        "use strict";
        defaultShippingRatesValidator.registerValidator('chcanadapost', chcanadapostShippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('chcanadapost', chcanadapostShippingRatesValidationRules);
        return Component;
    }
);
