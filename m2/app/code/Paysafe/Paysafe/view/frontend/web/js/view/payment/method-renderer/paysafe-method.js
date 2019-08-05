/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 *
 * @package     Paysafe
 * @copyright   Copyright (c) 2017 Paysafe
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/*browser:true*/
/*global define*/

define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Paysafe_Paysafe/js/action/set-payment-method',
    ],
    function ($, Component, setPaymentMethodAction) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Paysafe_Paysafe/payment/paysafe-method'
            },
            /** Redirect to Payment Form */
            placeOrderAction: function () {
                this.selectPaymentMethod(); // save selected payment method in Quote
                setPaymentMethodAction(this.messageContainer);
                return false;
            },
            getLogos: function () {
                return window.checkoutConfig.payment.paysafe.logos[this.getCode()];
            }
        });
    }
);
