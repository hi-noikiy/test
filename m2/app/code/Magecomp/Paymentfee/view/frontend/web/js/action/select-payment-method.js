/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
	    'jquery',
        'ko',
    	'Magento_Checkout/js/model/quote',
		'Magento_Checkout/js/model/full-screen-loader',
		'Magento_Checkout/js/action/get-totals',
		'Magecomp_Paymentfee/js/action/checkout/cart/totals'
    ],
    function ($, ko, quote,fullScreenLoader,getTotalsAction, totals) 
	{
        'use strict';
		var isLoading = ko.observable(false);
        return function (paymentMethod) 
		{
			totals(isLoading, paymentMethod.method);
			quote.paymentMethod(paymentMethod);
			/*fullScreenLoader.startLoader();
			getTotalsAction([]);
			fullScreenLoader.stopLoader();*/
        }
    }
);
