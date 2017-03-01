window.updateRowFields = function(button, fieldId, url)
{   
	console.log("ds");
    new Ajax.Request(url, {
        method: 'post',
        parameters: { 
        	id: fieldId, 
        	dcp: $('dcp' + fieldId).getValue(),
        	installments: $('installments' + fieldId).getValue(),
        	monthly: $('monthly' + fieldId).getValue(),
        	deposit: $('deposit' + fieldId).getValue(),
        	cpp: $('cpp' + fieldId).getValue(),
        	payment: $('payment' + fieldId).getValue(),
        	appnumber: $('appnumber' + fieldId).getValue(),
        }
    });
}

/* Update pickup orders */

window.updatePickupOrders = function(button, fieldId, url)
{   
    //var fields = $$('#pickuporderGrid_table tbody').first().select('input', 'select', 'textarea');
    //var data = Form.serializeElements(fields, true);
    url = url + (url.match(new RegExp('\\?')) ? '&isAjax=true' : '?isAjax=true') + '&id=' + fieldId;
    new Ajax.Request(url, {
        method: 'post',
        parameters: {
            //telephone: $('telephone' + fieldId).getValue(),
            //address: $('address' + fieldId).getValue(),
            //region: $('region' + fieldId).getValue(),
            attributes: $('attributes' + fieldId).getValue(),
            //payment_method: $('payment_method' + fieldId).getValue(), 
            //deposit: $('deposit' + fieldId).getValue(),
            wholesale_price: $('wholesale_price' + fieldId).getValue(),
            wholesaler_id: $('wholesaler' + fieldId).getValue(),
            pickup_address: $('pickupaddress' + fieldId).getValue(),
            //purchase_order: $('purchase_order' + fieldId).getValue(),
            pickup: $('pickup' + fieldId).getValue(),
            //pickup_comment: $('pickup_comment' + fieldId).getValue(),
            //delivery: $('delivery' + fieldId).getValue(),
            //delivery_comment: $('delivery_comment' + fieldId).getValue(),
            status: $('status' + fieldId).getValue(),
            //delivery_time: $('delivery_time' + fieldId).getValue(),
        },
        onSuccess: function(transport) {
            //$('delivery_time' + fieldId).update(transport.responseText);
        }
    });
}

window.updatePickupaddress = function(selectnav, fieldId, url)
{
    new Ajax.Request(url, {
        method: 'post',
        parameters: { 
            id: fieldId,
            wholesalerid: selectnav.getValue(),
            wholesaler: $('wholesaler' + fieldId).getValue(),
        },
        onSuccess: function(transport) {
            try {
                if (transport.responseText.isJSON()) {
                    var response = transport.responseText.evalJSON();
                    if (response.error) {
                        alert(response.message);
                    }
                } else {
                    $('pickupaddress' + fieldId).update(transport.responseText);
                }
            }
            catch (e) {
                alert(response.message);
            }
        }
    });
}

window.updateMarkupField = function(button, fieldId, url)
{
console.log("dsa");
    new Ajax.Request(url, {
        method: 'post',
        parameters: { 
            id: fieldId, 
            wholesalerprice: $('wholesale_price' + fieldId).getValue(),
        },
        onSuccess: function(transport) {
            $('markup' + fieldId).update(transport.responseText + "%");
            //$('markupinput' + fieldId).setValue(transport.responseText);
        }
    });
}

/* Update Delivery orders */

window.updateDeliveryOrders = function(button, fieldId, url)
{   
    //var fields = $$('#pickuporderGrid_table tbody').first().select('input', 'select', 'textarea');
    //var data = Form.serializeElements(fields, true);
    url = url + (url.match(new RegExp('\\?')) ? '&isAjax=true' : '?isAjax=true') + '&id=' + fieldId;
    new Ajax.Request(url, {
        method: 'post',
        parameters: {
            telephone: $('telephone' + fieldId).getValue(),
            address: $('address' + fieldId).getValue(),
            region: $('region' + fieldId).getValue(),
            attributes: $('attributes' + fieldId).getValue(),
            payment_method: $('payment_method' + fieldId).getValue(), 
            deposit: $('deposit' + fieldId).getValue(),
            customer_comment: $('customercomment' + fieldId).getValue(),
            //delivery: $('delivery' + fieldId).getValue(),
            delivery_comment: $('delivery_comment' + fieldId).getValue(),
            delivery_date_time: $('deliverydate' + fieldId).getValue(),
            status: $('status' + fieldId).getValue(),
            //delivery_time: $('delivery_time' + fieldId).getValue(),
        },
        onSuccess: function(transport) {
            $('delivery_time' + fieldId).update(transport.responseText);
        }
    });
}
