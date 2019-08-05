require(['jquery'], function ($) {
    function changeRep(selectedRepresentativeId, orderId)
    {
        new Ajax.Request(
            jQuery("#salesrep_change_salesrep_url").val(),
            {
                method: 'post',
                parameters: {
                    salesrepId: selectedRepresentativeId,
                    orderId: orderId
                },
                onSuccess: function (response) {
                    var res = $.parseJSON(response.responseText);
                    if (res.success) {
                        $('.field-rep_commission_earned #rep_commission_earned').text('$' + res.rep_commission);

                        if (res.manager_commission != null) {
                            $('.field-manager_commission_earned #manager_commission_earned').text('$' + res.manager_commission);
                        }
                        if(res.manager_id != null) {
                            $('.field-manager_id #manager_id').val(res.manager_id);
                        }
                    }
                }
            }
        );
    }

    function changeManager(selectedManagerId, orderId)
    {
        new Ajax.Request(
            jQuery("#salesrep_change_manager_url").val(),
            {
                method: 'post',
                parameters: {
                    managerId: selectedManagerId,
                    orderId: orderId
                },
                onSuccess: function (response) {
                    var res = $.parseJSON(response.responseText);
                    if (res.success) {
                        $('.field-manager_commission_earned #manager_commission_earned').text('$' + res.manager_commission);
                        $('.field-manager_id #manager_id').val(res.manager_id);
                    }
                }
            }
    );
    }

    function changePaymentStatus(selectedStatus, isManager, orderId)
    {
        new Ajax.Request(
            jQuery("#salesrep_change_status_url").val(),
            {
                method: 'post',
                parameters: {
                    selectedStatus: selectedStatus,
                    isManager: isManager,
                    orderId: orderId
                },
                onSuccess: function (response) {
                    var res = $.parseJSON(response.responseText);
                    if (res.success) {
                        if (isManager) {
                            $('.field-manager_commission_status #manager_commission_status').val(res.selected_status);
                        } else {
                            $('.field-rep_commission_status #rep_commission_status').val(res.selected_status);
                        }
                    }
                }
            }
        );
    }

    $(document).ready(function () {
        $(document).on('change', "#rep_id", function (el) {
            var selectedRepresentativeId = el.target.value,
                orderId = $('#order_id')[0].value;
            changeRep(selectedRepresentativeId, orderId);
        });

        $(document).on('change', "#manager_id", function (el) {
            var selectedManagerId = el.target.value,
                orderId = $('#order_id')[0].value;
            changeManager(selectedManagerId, orderId);
        });

        $(document).on('change', "#rep_commission_status", function (el) {
            var selectedStatus = el.target.value,
                isManager = 0,
                orderId = $('#order_id')[0].value;
            changePaymentStatus(selectedStatus, isManager, orderId);
        });

        $(document).on('change', "#manager_commission_status", function (el) {
            var selectedStatus = el.target.value,
                isManager = 1,
                orderId = $('#order_id')[0].value;
            changePaymentStatus(selectedStatus, isManager, orderId);
        });
    });

});
