<?php

class Admitad_Tracking_Model_Observer
{

    /**
     * @param $observer Varien_Event_Observer
     *
     * @return $this
     */
    public function controller_front_send_response_before($observer)
    {
        $paramName = Mage::getStoreConfig(
            'admitadtracking/general/param_name',
            Mage::app()->getStore()
        );
        $lifeTime = 90 * 60 * 60 * 24;
        $affiliateId = Mage::app()->getRequest()->getParam($paramName, false);

        /** @var Mage_Core_Model_Cookie $cookie */
        $cookie = Mage::getSingleton('core/cookie');

        if ($affiliateId) {
            $cookie->set('_aid', $affiliateId, $lifeTime, '/');
        }

        /** @var Mage_Customer_Model_Session $customer */
        $customer = Mage::getSingleton('customer/session');

        if ($customer->isLoggedIn() && $cookie->get('_aid')) {
            $customer->getCustomer()->setAdmitadUid($cookie->get('_aid'))->save();
            $customer->getCustomer()->setAdmitadUidLifetime(time() + 90 * 60 * 60 * 24)->save();
        }

        return $this;
    }

    /**
     * @param $observer Varien_Event_Observer
     *
     * @return $this
     */
    public function sales_order_place_after($observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();

        $uid = $this->getAdmitadUid();
        if (!$uid && !$order->hasCouponCode()) {
            return $this;
        }

        if (!$uid && $order->hasCouponCode()) {
            $uid = array(
                'type' => 'cookie',
                'value' => '',
            );
        }

        $campaignCode = Mage::getStoreConfig(
            'admitadtracking/general/campaign_code',
            Mage::app()->getStore()
        );
        $postbackKey = Mage::getStoreConfig(
            'admitadtracking/general/postback_key',
            Mage::app()->getStore()
        );

        if (!$campaignCode || !$postbackKey) {
            return $this;
        }

        //$orderId = $order->getId();
        $orderId = $order->getIncrementId(); // Changed for Bug #62742 (pms2)

        $positions = array();

        /** @var Admitad_Tracking_Helper_Admitad $admitad */
        $admitad = Mage::helper('tracking/admitad');

        $orderItems = $order->getAllItems();

        foreach ($orderItems as $orderItem) {
            $product = Mage::getModel('catalog/product')->load($orderItem->getProductId());
            $tariffData = $admitad->getTariffData($product);
            if (!$tariffData) {
                continue;
            }

            $product = array(
                'product_id' => $product->getId(),
                'price' => $orderItem->getPrice(),
                'quantity' => $orderItem->getQtyOrdered(),
            );

            if ($order->hasCouponCode()) {
                $product['promocode'] = $order->getCouponCode();
            }

            $positions[] = array_merge($product, $tariffData);
        }

        $parameters = array();
        $parameters['adm_source'] = 'cookie';

        if ($uid['type'] == 'user') {
            $parameters['adm_source'] = 'crossdevice';
        }

        if (!empty($positions)) {
            $admitad::admitadPostback($campaignCode, $postbackKey, $orderId, $positions, $parameters, $uid['value']);
        }

        return $this;
    }

    protected function getAdmitadUid()
    {
        /** @var Mage_Core_Model_Cookie $cookie */
        $cookie = Mage::getSingleton('core/cookie');
        $uid = $cookie->get('_aid');
        if ($uid) {
            return array(
                'type' => 'cookie',
                'value' => $uid,
            );
        }

        /** @var Mage_Customer_Model_Session $customer */
        $customer = Mage::getSingleton('customer/session');
        $admitadUidValue = $customer->getCustomer()->getAdmitadUid();
        $admitadUidLifetimeValue = $customer->getCustomer()->getAdmitadUidLifetime();

        if (!empty($admitadUidValue)
            && !empty($admitadUidLifetimeValue)
            && (time() < $admitadUidLifetimeValue)
        ) {
            return array(
                'type' => 'user',
                'value' => $admitadUidValue,
            );
        }

        return false;
    }
}