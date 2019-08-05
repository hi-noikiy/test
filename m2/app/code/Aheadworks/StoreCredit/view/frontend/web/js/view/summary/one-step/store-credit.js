/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'ko',
        'Aheadworks_StoreCredit/js/view/summary/store-credit',
        'Aheadworks_StoreCredit/js/model/is-applied-flag',
        'Aheadworks_StoreCredit/js/action/remove-store-credit',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (
        ko,
        Component,
        isAppliedFlag,
        removeStoreCreditAction,
        fullScreenLoader
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Aheadworks_StoreCredit/summary/one-step/store-credit'
            },

            /**
             * Remove store credit
             */
            remove: function () {
                var isLoading = ko.observable(true);

                fullScreenLoader.startLoader();
                isLoading.subscribe(function (flag) {
                    if (!flag) {
                        fullScreenLoader.stopLoader();
                    }
                });
                removeStoreCreditAction(isAppliedFlag, isLoading);
            }
        });
    }
);
