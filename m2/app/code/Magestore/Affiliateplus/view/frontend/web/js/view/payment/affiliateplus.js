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
define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'mage/storage',
        'Magestore_Affiliateplus/js/model/affiliateplus',
        'Magento_Checkout/js/model/quote',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/action/get-totals',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-list',
        'Magento_Checkout/js/action/get-payment-information',
        'Magento_Checkout/js/model/totals',
        'prototype'
    ],
    function (
        $,
        ko,
        Component,
        storage,
        affiliateCreditModel,
        quote,
        messageList,
        getTotalsAction,
        paymentService,
        paymentMethodList,
        getPaymentInformationAction,
        totals
    ) {
        'use strict';
        var tempAffiliateCreditInfo = window.affiliateCreditInfo;
        var affiliateCreditInfo = ko.observable(tempAffiliateCreditInfo);
        var enableCredit = ko.observable(tempAffiliateCreditInfo.enableCredit);
        var usedAffiliateCredit = ko.observable(tempAffiliateCreditInfo.usedAffiliateCredit);
        var formatedBalance = ko.observable(tempAffiliateCreditInfo.formatedBalance);
        var opcAjaxLoader = ko.observable(tempAffiliateCreditInfo.opcAjaxLoader);
        var editButtonImage = ko.observable(tempAffiliateCreditInfo.editButtonImage);
        var seccessMsgImage = ko.observable(tempAffiliateCreditInfo.seccessMsgImage);
        var checkoutCreditPost = ko.observable(tempAffiliateCreditInfo.checkoutCreditPost);
        var usingAmount = ko.observable(tempAffiliateCreditInfo.usingAmount);
        var affiliateCredit = ko.observable(tempAffiliateCreditInfo.affiliateCredit);
        var showCreditInput = ko.observable(false);

        return Component.extend({
            defaults: {
                template: 'Magestore_Affiliateplus/payment/credit/form'
            },

            initialize: function () {
                this._super();
                var main = this;
                ko.computed(function() {
                    return quote.totals()['base_shipping_amount'];
                }).subscribe(function() {
                    var url = 'affiliateplus/checkout/reloadData';
                    main.sentRequest(url, main);
                });
                //ko.computed(function() {
                //    return quote.totals()['coupon_code'];
                //}).subscribe(function() {
                //    var url = 'affiliateplus/checkout/reloadData';
                //    main.sentRequest(url, main);
                //});
            },

            affiliateCreditInfo: affiliateCreditInfo,
            enableCredit: enableCredit,
            usedAffiliateCredit: usedAffiliateCredit,
            formatedBalance: formatedBalance,
            opcAjaxLoader: opcAjaxLoader,
            editButtonImage: editButtonImage,
            seccessMsgImage: seccessMsgImage,
            checkoutCreditPost: checkoutCreditPost,
            usingAmount: usingAmount,
            affiliateCredit: affiliateCredit,
            showCreditInput:showCreditInput,

            getAffiliateCreditInfo: function(){
                return affiliateCreditInfo;
            },

            changeUseAffiliateCredit: function() {
                var url = '';
                var url1 = 'affiliateplus/checkout/updateCredit/';
                if(usedAffiliateCredit.call()){
                    this.usedAffiliateCredit(false);
                    url = 'affiliateplus/checkout/changeUseCredit/usedaffiliatepluscredit/1';
                }else{
                    this.usedAffiliateCredit(true);
                    url = 'affiliateplus/checkout/changeUseCredit/usedaffiliatepluscredit/2';
                    url1 += 'affiliateCredit/'+ -affiliateCreditInfo()['affiliateCredit'];
                    this.sentRequest(url1, this);
                }
                this.sentRequest(url, this);
            },

            sentRequest: function(url, main){
                return storage.get(
                    url,
                    true
                ).done(
                    function (response) {
                        main.resetData(JSON.parse(response));
                    }
                )
            },

            updateCreditInput: function(){
                var url = 'affiliateplus/checkout/updateCredit/';
                if(affiliateCreditInfo()['usedAffiliateCredit'] == null || affiliateCreditInfo()['affiliateCredit'] == 0){
                    url = 'affiliateplus/checkout/changeUseCredit';
                }else{
                    url+='affiliateCredit/'+affiliateCreditInfo()['affiliateCredit'];
                }
                this.sentRequest(url, this)
            },

            enterUpdateCreditInput: function (data, e) {
                if (e.keyCode == 13) {
                    return false;
                }
                return true;
            },


            resetData: function(response){
                if(response){
                    if(!response.notice){
                        affiliateCreditModel.setData(response);
                        this.affiliateCreditInfo(response);
                        this.usedAffiliateCredit(response.usedAffiliateCredit);
                        this.formatedBalance(response.formatedBalance);
                        this.usingAmount(response.usingAmount);
                        var deferred = $.Deferred();
                        var result = paymentMethodList();

                        if (result.length == 1 && result[0].method == 'free') {
                            totals.isLoading(true);
                            getPaymentInformationAction(deferred);
                            $.when(deferred).done(function () {
                                totals.isLoading(false);
                            });
                        } else {
                            getTotalsAction([], deferred);
                            $.when(deferred).done(function() {
                                paymentService.setPaymentMethods(
                                    paymentMethodList()
                                );
                            });
                        }

                    }else{
                        messageList.addErrorMessage({'message': response.notice});
                    }
                }
                this.showCreditInput(false);
            },
        });
    }
);
