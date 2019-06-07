<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Adminhtml sales order creditmemo controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
require_once(Mage::getModuleDir('controllers', 'Mage_Adminhtml') . DS . 'Sales/Order/CreditmemoController.php');

class Gearup_Configurator_Adminhtml_Sales_Order_CreditmemoController extends Mage_Adminhtml_Sales_Order_CreditmemoController {

    /**
     * Initialize creditmemo model instance
     *
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    protected function _initCreditmemo($update = false) {
        $this->_title($this->__('Sales'))->_title($this->__('Credit Memos'));

        $creditmemo = false;
        $creditmemoId = $this->getRequest()->getParam('creditmemo_id');
        $orderId = $this->getRequest()->getParam('order_id');
        if ($creditmemoId) {
            $creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemoId);
        } elseif ($orderId) {
            $data = $this->getRequest()->getParam('creditmemo');
            $order = Mage::getModel('sales/order')->load($orderId);
            $invoice = $this->_initInvoice($order);

            if (!$this->_canCreditmemo($order)) {
                return false;
            }

            $savedData = $this->_getItemData();

            $qtys = array();
            $backToStock = array();
            foreach ($savedData as $orderItemId => $itemData) {
                if (isset($itemData['qty'])) {
                    $qtys[$orderItemId] = $itemData['qty'];
                }
                if (isset($itemData['back_to_stock'])) {
                    $backToStock[$orderItemId] = true;
                }
            }
            $data['qtys'] = $qtys;

            $service = Mage::getModel('sales/service_order', $order);
            if ($invoice) {
                $creditmemo = $service->prepareInvoiceCreditmemo($invoice, $data);
            } else {
                $creditmemo = $service->prepareCreditmemo($data);
            }

            /**
             * Process back to stock flags
             */
            foreach ($creditmemo->getAllItems() as $creditmemoItem) {
                $orderItem = $creditmemoItem->getOrderItem();
                $parentId = $orderItem->getParentItemId();

                if (isset($backToStock[$orderItem->getId()])) {
                    $this->isConfigurator($orderItem, $qtys[$orderItemId], $order);
                    $creditmemoItem->setBackToStock(true);
                } elseif ($orderItem->getParentItem() && isset($backToStock[$parentId]) && $backToStock[$parentId]) {
                    $creditmemoItem->setBackToStock(true);
                } elseif (empty($savedData)) {
                    $creditmemoItem->setBackToStock(Mage::helper('cataloginventory')->isAutoReturnEnabled());
                } else {
                    $creditmemoItem->setBackToStock(false);
                }
            }
        }

        $args = array('creditmemo' => $creditmemo, 'request' => $this->getRequest());
        Mage::dispatchEvent('adminhtml_sales_order_creditmemo_register_before', $args);

        Mage::register('current_creditmemo', $creditmemo);
        return $creditmemo;
    }

    protected function isConfigurator($product, $qty, $order) {

       if($this->checkIfEventExist('gearup_configurator_cancelorder'. $order->getId()))
           return;
        $customOption = $product->getProduct()->getOptions();


        foreach ($customOption as $o) {
            $optionType = $o->getType();
            if ($optionType == 'configurator') {
                $optionId = $o->getOptionId();
                $requestData = $product->getBuyRequest()->getData();
                if (isset($requestData['options'][$optionId])) {
                    foreach ($requestData['options'][$optionId] as $templateJsId => $template) {
                        $templateOptionValues = $template['template'];
                        foreach ($templateOptionValues as $templateOptionId => $templateOptionValue) {
                            $templateOptionModel = Mage::getModel('configurator/value')->load($templateOptionValue);
                            $productId = $templateOptionModel->getProductId();
                            if ($productId) {
                                $subProduct = Mage::getModel('catalog/product')->load($productId);
                                Gearup_Configurator_Model_Observer::_SDSProcessing($subProduct, $order, $qty);
                            }
                        }
                    }
                }
            }
        }
    }

    private function checkIfEventExist($event) {
        try {
            $resource = Mage::getSingleton('core/resource');
            $writeAdapter = $resource->getConnection('core_write');
            $table = $resource->getTableName('gearup_configurator_events');
            $query = "INSERT INTO {$table} (`event_name`) VALUES (?);";
            $writeAdapter->query($query,$event);
            return false;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Update items qty action
     */
    public function updateQtyAction() {
        try {
            $creditmemo = $this->_initCreditmemo(true);
            $this->loadLayout();
            $response = $this->getLayout()->getBlock('order_items')->toHtml();
        } catch (Mage_Core_Exception $e) {
            $response = array(
                'error' => true,
                'message' => $e->getMessage()
            );
            $response = Mage::helper('core')->jsonEncode($response);
        } catch (Exception $e) {
            $response = array(
                'error' => true,
                'message' => $this->__('Cannot update the item\'s quantity.')
            );
            $response = Mage::helper('core')->jsonEncode($response);
        }
        $this->getResponse()->setBody($response);
    }

}
