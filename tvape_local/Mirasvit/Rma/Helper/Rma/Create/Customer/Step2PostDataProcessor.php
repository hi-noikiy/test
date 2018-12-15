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



class Mirasvit_Rma_Helper_Rma_Create_Customer_Step2PostDataProcessor
{
    /**
     * @return array
     */
    protected function getData() {
        return Mage::app()->getRequest()->getParams();
    }

    /**
     * Result:
     * array
     *  order_id => array (
     *      item_id => array(
     *          qty => int
     *
     * @return array
     */
    public function getItems() {
        $data = $this->getData();
        if (!isset($data['items'])) {
            return array();
        }
        $result = array();
        foreach ($data['items'] as $orderId => $items) {
            $result[$orderId] = array();
            foreach ($items as $itemId => $item) {
                if (isset($item['checkbox'])) {
                    $result[$orderId][$itemId] = array(
                        'qty' => $item['qty_requested']
                    );
                }
            }
        }
        return $result;
    }

    /**
     * Result:
     * array
     *  receipt_number => array (
     *      item_name => array (
     *          qty => int
     * @return array
     */
    public function getOfflineItems() {
        $data = $this->getData();
        if (!isset($data['offline_items'])) {
            return array();
        }
        $result = array();
        foreach ($data['offline_items'] as $receiptNumber => $items) {
            $receiptNumber = Mage::helper('core')->escapeHtml($receiptNumber);
            $result[$receiptNumber] = array();
            foreach ($items as $itemId => $item) {
                $itemId       = Mage::helper('core')->escapeHtml($itemId);
                $item['name'] = Mage::helper('core')->escapeHtml($item['name']);
                if (isset($item['checkbox'])) {
                    $result[$receiptNumber][$itemId] = array(
                        'qty' => $item['qty_requested']
                    );
                }
                unset($result[$receiptNumber][$itemId]['is_offline']);
                unset($result[$receiptNumber][$itemId]['checkbox']);
            }
        }
        return $result;
    }
}