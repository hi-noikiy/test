require([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'Mirasvit_Helpdesk/js/view/helpdesk'
], function (Component, customerData) {
    'use strict';

    customerData.invalidate(['helpdesk']);
});
