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
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";

    $.widget('mage.magestoreRegister', {
        options: {
            CaptchaButtonLink: '#affiliate-captcha-link',
            RefreshLink: '#affiliate-captcha-link',
            AccountImage: '#account_captcha_image',
            PleaseWaitCaptcha: '#affiliate-please-wait-captcha',
            CheckEmailRegister: '#email_address'
        },
        _create: function () {
            var self = this;
            $(this.options.CaptchaButtonLink).on('click', $.proxy(function () {
                $(self.options.PleaseWaitCaptcha).show();
                $(self.options.AccountImage).hide();
                jQuery.ajax({
                    url: self.options.url,
                    type: 'get',
                    success: function(data, textStatus, xhr) {
                        var imageCapcha = new Image();
                        imageCapcha.src = xhr.responseText;
                        $(self.options.AccountImage).attr("src", imageCapcha.src);
                        $(self.options.PleaseWaitCaptcha).hide();
                        $(self.options.AccountImage).show();
                        $(self.options.RefreshLink).show();

                    },
                    error: function(xhr, textStatus, errorThrown) {
                        $(self.options.PleaseWaitCaptcha).hide();
                        $(self.options.AccountImage).show();
                        $(self.options.RefreshLink).show();
                        alert('Exception: ' + errorThrown);
                    }
                });

            }, this));

            $(this.options.CaptchaButtonLink).on('change', $.proxy(function () {
                $(self.options.PleaseWaitCaptcha).show();
                $(self.options.AccountImage).hide();
                jQuery.ajax({
                    url: self.options.url,
                    type: 'get',
                    success: function(data, textStatus, xhr) {
                        var imageCapcha = new Image();
                        imageCapcha.src = xhr.responseText;
                        $(self.options.AccountImage).attr("src", imageCapcha.src);
                        $(self.options.PleaseWaitCaptcha).hide();
                        $(self.options.AccountImage).show();
                        $(self.options.RefreshLink).show();

                    },
                    error: function(xhr, textStatus, errorThrown) {
                        $(self.options.PleaseWaitCaptcha).hide();
                        $(self.options.AccountImage).show();
                        $(self.options.RefreshLink).show();
                        alert('Exception: ' + errorThrown);
                    }
                });

            }, this));

        }
    });

    return $.mage.magestoreRegister;
});