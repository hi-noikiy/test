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
 * @copyright   Copyright © 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 **/
class Justselling_Configurator_Block_Product_View_Options_Type_Custom extends Mage_Catalog_Block_Product_View_Options_Abstract
{

    public function getOptionRenderer($jsToInstance, $init = false)
    {
        /* @var $block Justselling_Configurator_Block_Renderer */
        $block = $this->getLayout()->createBlock('configurator/loading');

        $block->setTemplate('configurator/loading.phtml');
        $block->setProductOption($this->getOption());
        $block->setSelectedTemplateOptions(NULL);
        $block->setJsTemplateOption($jsToInstance);

        return $block->toHtml();
    }

    public function getTemplateId()
    {
        if (($storeId = $this->getProduct()->getStoreId()) != 0)
            $templateId = Mage::getModel('configurator/template')->getLinkedTemplateId($this->getOption()->getId(), $storeId);
        else
            $templateId = Mage::getModel('configurator/template')->getLinkedTemplateId($this->getOption()->getId());

        return $templateId;
    }

    public function getConfigureValues()
    {
        $_product = Mage::registry('current_product');
        if (isset($_product) && $_product->getConfigureMode()) {
            $preconfigure_values = $_product->getPreconfiguredValues()->getOptions();

            // Get preconfigured option for the current option-id
            $preconfigure_values = $preconfigure_values[$this->getOption()->getId()];

            reset($preconfigure_values);
            $key = key($preconfigure_values);
            $preconfigure_values = $preconfigure_values[$key]['template'];

            return json_encode($preconfigure_values);
        }
    }

    public function getJsonConfig($jsToInstance)
    {

        $collection = Mage::getModel('configurator/option')->getTemplateOptions($this->getTemplateId());

        $options = array();

        foreach ($collection->getItems() as $item) {
            $options[] = array('id' => $item->getId(), 'title' => $item->getTitle(), 'price' => 0);
        }

        $json_doc = Zend_Json_Encoder::encode(array('options' => $options, 'jsid' => $jsToInstance, 'optionId' => $this->getOption()->getId()));
        return $json_doc;
    }

    public function getOptionId()
    {
        return $this->getOption()->getId();
    }

    public function getDynamics()
    {
        if ($this->getProduct() && $this->getProduct()->getConfigureMode()) {
            // edit product mode
            $quoteItemId = Mage::app()->getRequest()->getParam('id');

            if ($quoteItemId) {
                $productOptionId = $this->getOption()->getId();

                $quoteItemOptions = Mage::getModel('sales/quote_item_option')->getCollection();
                $quoteItemOptions->addFieldToFilter("item_id", $quoteItemId);

                foreach ($quoteItemOptions as $quoteItemOption) {
                    if ($quoteItemOption['code'] == ('option_' . $productOptionId)) {
                        $quoteItem = $quoteItemOption;
                    }
                }

                if ($quoteItem && $quoteItem->getValue()) {
                    $templateOptionArray = unserialize($quoteItem->getValue());

                    if (is_array($templateOptionArray)) {
                        // there no match of the jsTemplateId because of edit mode
                        foreach ($templateOptionArray as $templateOption) {
                            if (isset($templateOption['dynamics'])) {
                                return $templateOption['dynamics'];
                            }
                        }
                    }
                }
            }
        } else {
            // normal product view
            $dynamics = Mage::getSingleton('core/session')->getDynamics();
            if ($dynamics) {
                foreach ($dynamics as $values) {
                    return $values;
                }
            }
        }
        return false;
    }
}
