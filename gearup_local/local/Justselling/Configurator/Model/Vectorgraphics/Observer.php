<?php
/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_configurator
 * @copyright   Copyright (C) 2013 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 */

class Justselling_Configurator_Model_Vectorgraphics_Observer extends Mage_Core_Model_Abstract
{
    public function checkout_cart_add_product_complete($observer) {
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        /** @var $quote_item Mage_Sales_Model_Quote_Item */
        $quote_item = Mage::registry("quote_item");

        if ($quote_item) {
            $session_id = substr(Mage::getModel("core/session")->getEncryptedSessionId(), 0, 8);
            $files = Mage::getModel("configurator/vectorgraphics_file")->getCollection()
                ->addFieldToFilter("session_id", $session_id)
                ->addFieldToFilter("status", Justselling_Configurator_Model_Vectorgraphics_File::STATUS_CREATED)
                ->addFieldToFilter("quote_id", array('null' => true))
                ->addFieldToFilter("quote_item_id", array('null' => true));
            foreach ($files as $file) {
                $file->setQuoteItemId($quote_item->getItemId());
                $file->setQuoteId($quote_item->getQuoteId());
                $file->setStatus(Justselling_Configurator_Model_Vectorgraphics_File::STATUS_ASSIGNED_TO_QUOTE);
                $file->save();
            }
        }
    }

    public function sales_convert_quote_to_order($observer) {
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = $observer->getQuote();
        /** @var $quote_item Mage_Sales_Model_Quote_Item */
        $order = $observer->getOrder();

        $files = Mage::getModel("configurator/vectorgraphics_file")->getCollection();
        $files->addFieldToFilter("quote_id", $quote->getId());
        if ($files->getSize()) {
            foreach ($files as $file) {
                if ($order->getId()) { // We have already a order-id
                    $file->setOrderId($order->getId());
                    if ($order->getCustomerId())
                        $file->setCustomer($order->getCustomerId());
                    try {
                        $file->save();
                    } catch(Exception $e) {
                    }
                } else { // Currently the order don't has an id
                    $action = array();
                    if (Mage::registry("files_to_order")) {
                        $action = Mage::registry("files_to_order");
                        Mage::unregister("files_to_order");
                    }
                    $action[] = $file->getId();
                    Mage::register("files_to_order", $action);
                }
            }
        }
    }

    public function sales_order_save_after($observer) {
        /** @var $order Mage_Sales_Model_Order */
        $order = $observer->getOrder();

        if (Mage::registry("files_to_order")) {
            Mage::unregister("files_to_order");
            /* @var $order Mage_Sales_Model_Order */
            foreach ($order->getAllItems() as $item) {
                /* @var $item Mage_Sales_Model_Order_Item */
                foreach ($item->getProductOptions() as $option){
                    if(isset($option['options'])){
                        foreach ($option['options'] as $productOptionId => $productOption){
                            $template = Mage::getModel("configurator/mysql4_template")->getLinkedTemplateId($productOptionId, 0);
                            foreach ($productOption as $jsTemplateId => $jsTemplates) {
                                $files = Mage::getModel("configurator/vectorgraphics_file")->getCollection()
                                    ->addFieldToFilter("order_id", array('null' => true))
                                    ->addFieldToFilter("order_item_id", array('null' => true))
                                    ->addFieldToFilter("status", Justselling_Configurator_Model_Vectorgraphics_File::STATUS_ASSIGNED_TO_QUOTE)
                                    ->addFieldToFilter('js_template_id', $jsTemplateId);
                                foreach ($files as $file) {
                                    $file->setOrderId($order->getId());
                                    $file->setOrderItemId($item->getItemId());
                                    $file->setStatus(Justselling_Configurator_Model_Vectorgraphics_File::STATUS_ASSIGNED_TO_ORDER);
                                    if ($order->getCustomerId()) {
                                        $file->setCustomerId($order->getCustomerId());
                                    }
                                    $file->save();
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /* Cart Update */
    public function checkout_cart_update_item_complete($observer) {
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        /** @var $quote_item Mage_Sales_Model_Quote_Item */
        $quote_item = $observer->getItem();
        $session_id = substr ( Mage::getModel ( "core/session" )->getEncryptedSessionId (), 0, 8 );

        /* update current files */
        $files = Mage::getModel("configurator/vectorgraphics_file")->getCollection()
            ->addFieldToFilter("session_id",$session_id)
            ->addFieldToFilter("status", Justselling_Configurator_Model_Vectorgraphics_File::STATUS_CREATED)
            ->addFieldToFilter("quote_id", array('null' => true));

        foreach ($files as $file) {
            $file->setQuoteId($quote->getId());
            $file->setStatus(Justselling_Configurator_Model_Vectorgraphics_File::STATUS_ASSIGNED_TO_QUOTE);
            if ($quote_item)
                $file->setQuoteItemId($quote_item->getId());
            try {
                $file->save();
            } catch(Exception $e) {
            }
        }
    }
}