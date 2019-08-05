/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magestore_Affiliateplus/js/model/affiliatediscount',
        'Magestore_Affiliateplus/js/model/affiliateplus',
    ],
    function (Component, affiliate, affiliateplus) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magestore_Affiliateplus/checkout/summary/affiliateplus '
            },
            affiliate: affiliate.getData(),
            affiliateplus: affiliateplus.getData(),
            /**
             * Check is displayed
             * @returns {boolean}
             */
            isDisplayed: function() {
                return this.getPureValue() !=0;
            },
            /**
             * Get Pure Value
             * @returns {*|String}
             */
            getPureValue: function() {
                var price = 0;
                    if(this.affiliate().affiliateDiscount){
                        price  = this.affiliate().affiliateDiscount;
                    }
                return price;
            },
            /**
             * Get Value
             * @returns {*|String}
             */
            getValue: function(){
                return this.getFormattedPrice(this.getPureValue());
            },
            /**
             * Check is displayed Credit
             * @returns {boolean}
             */
            isDisplayedCredit: function() {
                if(!this.affiliateplus().usedAffiliateCredit){
                    return this.affiliateplus().usedAffiliateCredit;
                }
                return this.getPureCreditValue() >0;
            },
            /**
             *Get Pure Credit Value
             * @returns {*|String}
             */
            getPureCreditValue: function() {
                var price = 0;
                if(this.affiliateplus() && this.affiliateplus().affiliateCredit){
                    price  = this.affiliateplus().affiliateCredit;
                }else if(this.affiliate().affiliateCreditAmount){
                    price = this.affiliate().affiliateCreditAmount;
                }
                return price;
            },
            getCreditValue: function(){
                return this.getFormattedPrice(-this.getPureCreditValue());
            }
        });
    }
);