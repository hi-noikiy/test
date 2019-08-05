/**
 * You are allowed to use this API in your web application.
 *
 * Copyright (C) 2018 by customweb GmbH
 *
 * This program is licenced under the customweb software licence. With the
 * purchase or the installation of the software in your application you
 * accept the licence agreement. The allowed usage is outlined in the
 * customweb software licence which can be found under
 * http://www.sellxed.com/en/software-license-agreement
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at http://www.sellxed.com/shop.
 *
 * See the customweb software licence agreement for more details.
 *
 *
 * @category	Customweb
 * @package		Customweb_PayEngine3Cw
 * 
 */

define([
	'uiComponent',
	'Magento_Checkout/js/model/payment/renderer-list'
], function(
	Component,
	rendererList
) {
	'use strict';
	
	rendererList.push(
			{
			    type: 'payengine3cw_creditcard',
			    component: 'Customweb_PayEngine3Cw/js/view/payment/method-renderer/payengine3cw_creditcard-method'
			},
			{
			    type: 'payengine3cw_paypal',
			    component: 'Customweb_PayEngine3Cw/js/view/payment/method-renderer/payengine3cw_paypal-method'
			},
			{
			    type: 'payengine3cw_paydirekt',
			    component: 'Customweb_PayEngine3Cw/js/view/payment/method-renderer/payengine3cw_paydirekt-method'
			},
			{
			    type: 'payengine3cw_ratepayopeninvoice',
			    component: 'Customweb_PayEngine3Cw/js/view/payment/method-renderer/payengine3cw_ratepayopeninvoice-method'
			},
			{
			    type: 'payengine3cw_ratepaydirectdebits',
			    component: 'Customweb_PayEngine3Cw/js/view/payment/method-renderer/payengine3cw_ratepaydirectdebits-method'
			},
			{
			    type: 'payengine3cw_ratepayinstallments',
			    component: 'Customweb_PayEngine3Cw/js/view/payment/method-renderer/payengine3cw_ratepayinstallments-method'
			},
			{
			    type: 'payengine3cw_sofortueberweisung',
			    component: 'Customweb_PayEngine3Cw/js/view/payment/method-renderer/payengine3cw_sofortueberweisung-method'
			});
	return Component.extend({});
});