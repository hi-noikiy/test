define([
    "jquery",
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/modal',
    'viewport',
    "jquery/ui",
    'jquery/jquery.cookie',
    'mage/validation/validation'
], function ($, alert, modal, viewport) {
    'use strict';

//        $('.swatch-option').click(function () {
//            $('.swatch-attribute-selected-option').addClass('swat');
//            $('.swatch-attribute-label').css('display','none');
//        }),

        $(window).on('beforeunload', function(){
            $.cookie('popupShown',null);
        });

    $.widget('mage.addonpopup', {
        options:
                {
                    addPopupButton: '.btn-popup-add-product',
                    addConfigButton: '#config-addtocart-button',
                    productForm: '#product_addtocart_form',
                    moreinfo: '.more-info'
                },

        _create: function () {

            var self = this;
            jQuery(self.options.productForm).validation();
            self._clickFunction();
        },
        _clickFunction: function ()
        {
            var self = this;
            $(self.options.addPopupButton).click(function ()
            {
                jQuery('.addonpopup-loader').show();
                var currentProduct = $(this);
                if (jQuery(currentProduct).hasClass('btn-popup-add-product')) {
                    jQuery.ajax({type: 'POST',
                        url: $(this).attr("data-url"),
                        data: {'product_id': $(this).attr("data-id"),'addon_price':$(this).attr("data-addonprice")},
                        async: true,
                        success: function (result) {
                            var response = JSON.parse(result);
                            if (response.status == 'success') {
                                jQuery('#' + currentProduct.attr('id') + '').html('<span>Added</span>');
                                jQuery('.btn-popup span').html('Checkout');
                                jQuery('.btn-popup').attr('class', 'btn-popup checkout');
                                jQuery(currentProduct).attr('class', 'btn-add added');
                                jQuery(currentProduct).attr('disabled',true);
                                jQuery('.modal-footer').append('<div class="popup-msg" id="product-added-smg" style="display: block;">ZEUS Grime Wipes™  was added to your cart</div>');

                                jQuery('.popup-msg').show().empty().html(response.message);
                                jQuery('.addonpopup-loader').hide();
                            } else if (response.status == 'error') {
                                jQuery('.popup-msg').show().empty().html(response.message);
                                jQuery('.addonpopup-loader').hide();
                            }
                        },
                        error: function (xhr) {
                            alert('ajax error');
                        }});
                }
            });
            $(self.options.addConfigButton).click(function ()
            {
                if (!jQuery(self.options.productForm).validation('isValid'))
                    return;
                if($('#addon-popup').length){
                    
                    if ( $.cookie('popupShown') != true ) {

                    var options = {
                        type: 'popup',
                        responsive: true,
                        innerScroll: true,
                        title: '',
                        modalClass:'detail-addtocart-popup',
                        overlayClass: false,
                        buttons: [{
                                text: $.mage.__('No Thanks'),
                                class: 'btn-popup',
                                click: function () {
                                   $('.addonpopup-loader').show(); 
                                   $(self.options.productForm).attr('action', $('.cart-url').attr("data-url"));
                                   $(self.options.productForm).submit();
                                }
                            }]
                        };

                        var popup = modal(options, $('#addon-popup')); 
                        $.cookie('popupShown',true);
                        if(!viewport.isMobile()){
                            $("html, body").stop().animate({scrollTop:0}, 500, 'swing', function() {});
                            $('#addon-popup').modal('openModal');
                        }
                        else{
                            $(self.options.productForm).submit(); 
                        }
                    }
                 }else{
                     $(self.options.productForm).attr('action', $('.cart-url').attr("data-url"));
                     $(self.options.productForm).submit();
                 }

            });
            $(self.options.moreinfo).click(function () {
                $.ajax({
                    url: $(this).attr("data-url"),
                    type:'GET',
                    dataType: "json",
                    showLoader: false,
                    data:{'id':$(this).attr("id")},
                    complete: function(result) {
                        $('#moreinfo').css('display','block');
                        $('#moreinfo').html(result.responseJSON.description);
                        //$('#popup-modal').modal('openModal');
                    },
                });
                var scrolled=0;
                scrolled=scrolled+200;

                $(".modal-content").animate({
                    scrollTop:  scrolled                    
                });
            });
        },
        escapeKey: function (e) {
                e.preventDefault();
                return false;
            }

    });
    return $.mage.addonpopup;
});