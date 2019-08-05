 /**
 * Copyright © 2016 Collinsharper. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'chmonerisredirect',
                component: 'CollinsHarper_Moneris/js/view/payment/method-renderer/chmonerisredirect'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);