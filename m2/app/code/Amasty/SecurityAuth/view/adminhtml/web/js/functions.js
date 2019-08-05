require([
    'jquery',
    'mage/translate'
], function ($) {
    'use strict';

    function verifyCode(url, userId, secret, code) {
        $.ajax({
            url: url,
            data: {
                user_id: userId,
                secret: secret,
                code: code
            },
            showLoader: true,
            success: function(response) {
                var responseParsed = $.parseJSON(response);
                if(responseParsed.result == true) {
                    $('#is_configured')[0].value = 1;
                    $('#code-verification-message')[0].innerHTML = '<span style="color: #008800;font-weight: bold">'+ $.mage.__('Valid!') +'</span>';
                } else {
                    $('#code-verification-message')[0].innerHTML = '<span style="color: #aa1717;font-weight: bold">'+ $.mage.__('Invalid!') +'</span>';
                }
            }.bind(this)
        });
    }

    window.verifyCode = verifyCode;
});