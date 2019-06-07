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
class Justselling_Configurator_Model_Upload_Observer extends Mage_Core_Model_Abstract
{
    public function checkout_cart_product_add_after($observer)
    {

        $item = $observer->getQuoteItem();
        if (Mage::registry("quote_item")) {
            Mage::unregister("quote_item");
        }
        Mage::register("quote_item", $item);
    }

    public function checkout_cart_update_item_complete($observer)
    {

        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $quote_item = $observer->getItem();
        $session_id = Mage::getSingleton('core/session')->getSessionId();

        /* update current uploads */
        $uploads = Mage::getModel("configurator/upload")->getCollection();
        $uploads->addFieldToFilter("session_id", $session_id);
        $uploads->addFieldToFilter("quote_id", array('null' => true));
        foreach ($uploads as $upload) {
            $upload->setQuoteId($quote->getId());
            if ($quote_item)
                $upload->setQuoteItemId($quote_item->getItemId());
            try {
                $upload->save();
            } catch (Exception $e) {
            }
        }
    }

    public function checkout_cart_add_product_complete($observer)
    {

        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $quote_item = Mage::registry("quote_item");
        Mage::unregister("quote_item");
        $session_id = Mage::getSingleton('core/session')->getSessionId();

        /* Check for old uploads (other session with same quote)*/
        $uploads = Mage::getModel("configurator/upload")->getCollection();
        $uploads->addFieldToFilter("session_id", array('neq' => $session_id));
        $uploads->addFieldToFilter("quote_id", $quote->getId());
        foreach ($uploads as $upload) {
            try {
                $upload->delete();
            } catch (Exception $e) {
            }
        }

        /* update current uploads */
        $uploads = Mage::getModel("configurator/upload")->getCollection();
        $uploads->addFieldToFilter("session_id", $session_id);
        $uploads->addFieldToFilter("quote_id", array('null' => true));

        foreach ($uploads as $upload) {
            /* update current uploads */

            Mage::Log("found upload " . $upload->getId());
            if ($quote->getId()) { // We have already a quote-id
                $upload->setQuoteId($quote->getId());
                if ($quote_item)
                    $upload->setQuoteItemId($quote_item->getItemId());
                try {
                    $upload->save();
                } catch (Exception $e) {
                }
            } else { // Currently the quote don't has an id
                $action = array();
                if (Mage::registry("to_quote")) {
                    $action = Mage::registry("to_quote");
                    Mage::unregister("to_quote");
                }
                $action[] = $upload->getId();
                Mage::register("to_quote", $action);
            }
        }
    }

    public function sales_quote_save_after($observer)
    {
        $quote = $observer->getQuote();

        if (Mage::registry("to_quote")) {
            $action = Mage::registry("to_quote");
            foreach ($action as $upload_id) {
                $upload = Mage::getModel("configurator/upload")->load($upload_id);
                $upload->setQuoteId($quote->getId);
                try {
                    $upload->save();
                } catch (Exception $e) {
                }
            }
            Mage::unregister("to_quote");
        }
        $this->deleteOldFileuploads();
    }

    public function sales_convert_quote_to_order($observer)
    {

        $quote = $observer->getQuote();
        $order = $observer->getOrder();

        $uploads = Mage::getModel("configurator/upload")->getCollection();
        $uploads->addFieldToFilter("quote_id", $quote->getId());
        if ($uploads->getSize()) {
            foreach ($uploads as $upload) {
                if ($order->getId()) { // We have already a order-id
                    $upload->setOrderId($order->getId());
                    if ($order->getCustomerId())
                        $upload->setCustomer($order->getCustomerId());
                    try {
                        $upload->save();
                    } catch (Exception $e) {
                    }
                } else { // Currently the order don't has an id
                    $action = array();
                    if (Mage::registry("to_order")) {
                        $action = Mage::registry("to_order");
                        Mage::unregister("to_order");
                    }
                    $action[] = $upload->getId();
                    Mage::register("to_order", $action);
                }
            }
        }
    }

    public function customer_login($observer)
    {

        $quote = Mage::getSingleton('checkout/session')->getQuote();
        //Mage::log('Customer Id' . $observer->getCustomer()->getId());
        //Mage::log('quote Id' . $quote->getId());

        if ($observer->getCustomer()->getId() && $quote->getId()) {
            $uploads = Mage::getModel("configurator/upload")->getCollection();
            $uploads->addFieldToFilter("quote_id", $quote->getId());
            if ($uploads->getSize()) {
                foreach ($uploads as $upload) {
                    $upload->setCustomerId($observer->getCustomer()->getId());
                    try {
                        $upload->save();
                    } catch (Exception $e) {
                    }
                }
            }
        }
    }

    public function deleteOldFileuploads()
    {
        $_deletedays = Mage::getStoreConfig('fileuploader/general/deletedays');
        $_strtotime = '-90 days';
        try {
            $_deletedays = intval($_deletedays);
            if ($_deletedays > 1) {
                $_strtotime = '-' . $_deletedays . ' days';
            }
        } catch (Exception $e) {
            Mage::log('no interger value is set at admin panel');
        }
        $uploads = Mage::getModel("configurator/upload")->getCollection();
        $uploads->addFieldToFilter('order_id', '0');
        $uploads->addFieldToFilter('created_at', array(
            'to' => date('Y-m-d H:i:s', strtotime($_strtotime)),
            'date' => true, // specifies conversion of comparison values
        ));
        foreach ($uploads as $upload) {
            $upload->delete();
        }

    }

    public function sales_order_save_after($observer)
    {
        //Mage::Log("OBS Justselling_Configurator_Model_Upload_Observer::sales_order_save_after");
        $order = $observer->getOrder();
        $orderId = $order->getId();

        if (Mage::registry("to_order")) {
            Mage::unregister("to_order");
            /* @var $order Mage_Sales_Model_Order */
            foreach ($order->getAllItems() as $item) {
                /* @var $item Mage_Sales_Model_Order_Item */
                foreach ($item->getProductOptions() as $option) {
                    if (isset($option['options'])) {
                        foreach ($option['options'] as $productOptionId => $productOption) {
                            $templateId = Mage::getModel("configurator/mysql4_template")->getLinkedTemplateId($productOptionId, 0);
                            foreach ($productOption as $jsTemplateId => $jsTemplates) {

                                $optionIds = array();

                                $collection = Mage::getModel("configurator/option")->getCollection();
                                $collection->addFieldToFilter('template_id', $templateId);
                                $collection->addFieldToFilter('type', 'file');
                                foreach ($collection as $option) {
                                    $optionIds[] = $option->getId();
                                }

                                $pluginCollection = Mage::getModel("configurator/option")->getCollection();
                                $pluginCollection->addFieldToFilter('template_id', $templateId);
                                $pluginCollection->addFieldToFilter('frontend_type', array('neq' => 'NULL' ));
                                foreach ($pluginCollection as $option) {
                                    $optionIds[] = $option->getId();
                                }

                                $uploads = Mage::getModel("configurator/upload")->getCollection();
                                $uploads->addFieldToFilter('option_id', array("in" => $optionIds));
                                $uploads->addFieldToFilter('order_id', '0');
                                $uploads->addFieldToFilter('js_template_id', $jsTemplateId);
                                foreach ($uploads as $upload) {
                                    $upload->setOrderId($order->getId());
                                    $upload->setOrderItemId($item->getItemId());
                                    if ($order->getCustomerId()) {
                                        $upload->setCustomerId($order->getCustomerId());
                                    }
                                    // move file to orderId folder
                                    $this->moveUploadFileAndSave($upload, $order->getIncrementId());
                                }
                            }
                        }
                    }
                }
            }
        }
    }


    private
    function moveUploadFileAndSave($upload, $orderId)
    {

        $fileNameWithSessionIdFolder = $upload->getFile();
        $uploadSessionId = $upload->getSessionId();
        $filename = str_replace($uploadSessionId . '/', '', $fileNameWithSessionIdFolder);

        $tempFile = Mage::getBaseDir('media') . DS . Mage::getStoreConfig('fileuploader/general/mediapath') . DS . 'tmp' . DS . $fileNameWithSessionIdFolder;

        if (file_exists($tempFile)) {
            $targetFolder = Mage::getBaseDir('media') . DS . Mage::getStoreConfig('fileuploader/general/mediapath') . DS . $orderId . DS;
            if (!file_exists(str_replace('//', '/', $targetFolder))) {
                mkdir(str_replace('//', '/', $targetFolder), 0755, true);
            }
            $targetFile = $targetFolder . $filename;
            rename($tempFile, $targetFile);
            $upload->setFile($orderId . DS . $filename);
        }

        $upload->save();
    }


}