require(['jquery'], function ($) {
    $(document).ready(function () {
        $(document).on('change', "#sales_representative", function (el) {
            var selectedRepresentativeId = el.target.value;
            var request = new Ajax.Request(
                jQuery("#salesrep_change_selectsalesrepadmin_url").val(),
                {
                    method: 'post',
                    parameters: {
                        selectedSalesrep: selectedRepresentativeId
                    }
                }
            );
        });
    });
});
