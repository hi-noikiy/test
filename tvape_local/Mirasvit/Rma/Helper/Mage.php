<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_rma
 * @version   2.4.7
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rma_Helper_Mage extends Mirasvit_MstCore_Helper_Help
{
    public function getBackendCustomerUrl($customerId)
    {
        if (version_compare(Mage::getVersion(), '1.4.1.1') != -1) {
            return Mage::helper('adminhtml')->getUrl('adminhtml/customer/edit', array('id' => $customerId));
        } else {
            return Mage::helper('adminhtml')->getUrl('adminhtml/customer/edit', array('customer_id' => $customerId));
        }
    }

    public function getOrderCollection()
    {
        if (version_compare(Mage::getVersion(), '1.4.1.1') != -1) {
            $collection = Mage::getResourceModel('sales/order_grid_collection')
                ->setOrder('entity_id');
        } else {
            $collection = Mage::getModel('sales/order')->getCollection()
                ->addAttributeToSelect('*')
                ->joinAttribute('billing_firstname', 'order_address/firstname', 'billing_address_id', null, 'left')
                ->joinAttribute('billing_lastname', 'order_address/lastname', 'billing_address_id', null, 'left')
                ->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
                ->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
                ->addExpressionAttributeToSelect('billing_name',
                    'CONCAT({{billing_firstname}}, " ", {{billing_lastname}})',
                    array('billing_firstname', 'billing_lastname'))
                ->addExpressionAttributeToSelect('shipping_name',
                    'CONCAT({{shipping_firstname}},  IFNULL(CONCAT(\' \', {{shipping_lastname}}), \'\'))',
                    array('shipping_firstname', 'shipping_lastname'))
                ;
        }

        return $collection;
    }
}
