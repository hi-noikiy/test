<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Block_Adminhtml_Renderer_Price extends Mage_Adminhtml_Block_Widget implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_element = null;
    protected $_listWebsites = null;

    public function __construct()
    {
        $this->setTemplate('amasty/amstcred/renderer/prices_grid.phtml');
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        //$isAddButtonDisabled = ($element->getData('readonly_disabled') === true) ? true : false;
        $this->setChild('buttonAdd',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('amstcred')->__('Add Amount'),
                    'onclick' => "AmastyStoreCreditPriceOptions.addPriceRow('" . $this->getElement()->getHtmlId() . "')",
                    'class' => 'add',
                    //'disabled'  => $isAddButtonDisabled,
                    'disabled' => false,
                )));

        return $this->toHtml();
        //return '<tr><td>asdasdasd</td></tr>';
    }

    public function setElement(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this;
    }

    public function getElement()
    {
        return $this->_element;
    }

    public function getListWebsites()
    {
        if (is_null($this->_listWebsites)) {
            $this->_listWebsites = array(
                array(
                    'name' => $this->__('All Websites'),
                    'currency' => Mage::app()->getBaseCurrencyCode()
                ),
            );

            if (!Mage::app()->isSingleStoreMode() && !$this->getElement()->getEntityAttribute()->isScopeGlobal()) {
                $product = Mage::registry('product');
                if ($storeId = $product->getStoreId()) {
                    $website = Mage::app()->getStore($storeId)->getWebsite();
                    $this->_listWebsites[$website->getId()] = array(
                        'name' => $website->getName(),
                        'currency' => $website->getConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
                    );
                } else {
                    foreach (Mage::app()->getWebsites() as $website) {
                        if (!in_array($website->getId(), $product->getWebsiteIds())) {
                            continue;
                        }
                        $this->_listWebsites[$website->getId()] = array(
                            'name' => $website->getName(),
                            'currency' => $website->getConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
                        );
                    }
                }
            }


        }

        return $this->_listWebsites;
    }

    public function isSingleStoreMode()
    {
        return Mage::app()->isSingleStoreMode();
    }

    public function getButtonAdd()
    {
        return $this->getChildHtml('buttonAdd');
    }

    public function getListPrices()
    {
        $value = $this->getElement()->getValue();
        return is_array($value) ? $value : array();
    }

}
