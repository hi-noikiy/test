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



class Mirasvit_Rma_Helper_Rma_Save_PostDataProcessor extends Mage_Core_Helper_Abstract
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @param array $data
     * @return void
     */
    public function setData($data){
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getItems() {
        if (!isset($this->data['items'])) {
            return array();
        }
        return $this->filterData($this->data['items']);
    }

    /**
     * @return array
     */
    public function getOfflineItems() {
        if (!isset($this->data['offline_items'])) {
            return array();
        }
        return $this->filterData($this->data['offline_items']);
    }

    /**
     * @param array $orders
     * @return array
     */
    protected function filterData($orders) {
        foreach($orders as $k=>$items) {
            foreach ($items as $k2 => $item) {
                if (isset($item['reason_id']) && $item['reason_id'] === '') {
                    unset($item['reason_id']);
                }
                if (isset($item['resolution_id']) && !(int)$item['resolution_id']) {
                    unset($item['resolution_id']);
                }
                if (isset($item['condition_id']) && !(int)$item['condition_id']) {
                    unset($item['condition_id']);
                }
                if (isset($item['order_id']) && $item['order_id'] == '') {
                    unset($item['order_id']);
                }
                $orders[$k][$k2] = $item;
            }
        }
        return $orders;
    }

    /**
     * @return array
     */
    public function getRmaData() {
        $data = $this->data;
        unset($data['form_key']);
        unset($data['items']);
        unset($data['offline_items']);
        $data['is_gift'] = isset($data['is_gift']);
        return $data;
    }

    /**
     * @throws \Mage_Core_Exception
     * @return bool
     */
    public function validate() {
        $isEmpty = true;
        $items = array_merge($this->getItems(), $this->getOfflineItems());
        foreach ($items as $itemList) {
            foreach ($itemList as $item) {
                if (isset( $item['qty_requested']) && (int) $item['qty_requested'] > 0) {
                    $isEmpty = false;
                    break;
                }
            }
        }
        if ($isEmpty) {
            throw new Mage_Core_Exception("Please, add order items to the RMA (set 'Qty to Return')");
        }
        return true;
    }


    /**
     * @return array
     */
    public function getNewCustomerData() {
        if (isset($this->data['new_customer'])) {
            return $this->data['new_customer'];
        }
    }

}