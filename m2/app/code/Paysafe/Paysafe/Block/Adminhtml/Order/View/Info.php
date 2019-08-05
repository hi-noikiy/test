<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Paysafe\Paysafe\Block\Adminhtml\Order\View;

class Info extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder
{
    /**
     * get additional information
     * @return array
     */
    public function getArrayAdditionalInformation()
    {
        $additionalInformation = $this->getOrder()->getPayment()->getAdditionalInformation();
        foreach ($additionalInformation as $key => $value) {
            $informationItem[$key] = $value;
        }
        return $informationItem;
    }

    /**
     *  get an update order URL
     * @return string
     */
    public function getUpdateOrderUrl()
    {
        $orderId = $this->_request->getParam('order_id');

        return $this->getUrl('paysafe/order/update', ['order_id' => $orderId, '_secure' => true]);
    }
}
