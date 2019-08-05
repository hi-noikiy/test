define(
    [
		'ko',
		'uiComponent',
		'Magento_Checkout/js/model/quote',
		'Magento_Catalog/js/price-utils',
		'Magento_Checkout/js/model/totals',
        'Magecomp_Paymentfee/js/view/checkout/summary/paymentfee'
    ],
    function (ko, Component, quote, priceUtils, totals) {
        'use strict';
 
        return Component.extend({
            /**
             * @override
             * use to define amount is display setting
             */
			totals: quote.getTotals(),
            isDisplayed: function () {
				return this.getValues() != 0;
			},
			getValue: function() {
				var price = 0;
				if (this.totals() && totals.getSegment('paymentfee')) {
					price = totals.getSegment('paymentfee').value;
				}
				return priceUtils.formatPrice(price, quote.getBasePriceFormat());
			},
			getValues: function() {
				var price = 0;
				if (this.totals() && totals.getSegment('paymentfee')) {
					price = totals.getSegment('paymentfee').value;
				}
				return price;
			},
			getPaymentfeeTitle: function () 
			{
				return window.checkoutConfig.paymentfee.getinfo.paymentfeelabel;
			}
        });
    }
);