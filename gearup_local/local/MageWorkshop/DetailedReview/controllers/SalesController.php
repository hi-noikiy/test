<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */
class MageWorkshop_DetailedReview_SalesController extends Mage_Sales_Controller_Abstract
{

    /**
     * Products for Review
     */
    public function productsAction()
    {
        if (!$this->_loadValidOrder()) {
            return;
        }

        if (Mage::getStoreConfig('detailedreview/settings/enable')) {
            $customerData = Mage::helper('detailedreview')->getCustomerInfo();
            if ($customerData->getCustomerId() || $customerData->getCustomerEmail()) {
                $this->loadLayout();
                $this->renderLayout();
            } else {
                $this->_redirectUrl(Mage::getUrl('customer/account/login/', array( '_secure' => true)));
            }
        } else {
            $this->_forward('defaultNoRoute');
        }

    }



    /**
     * Try to load valid order by order_id and register it
     *
     * @param int $orderId
     * @return bool
     */
    protected function _loadValidOrder($orderId = null)
    {
        if (null === $orderId) {
            $orderId = (int) $this->getRequest()->getParam('order');
        }
        if (!$orderId) {
            $this->_forward('noRoute');
            return false;
        }

        $order = Mage::getModel('sales/order')->load($orderId);

        if ($this->_canViewOrder($order)) {
            Mage::register('current_order', $order);
            return true;
        } else {
            $this->_redirect('sales/order/history');
        }
        return false;
    }
}
