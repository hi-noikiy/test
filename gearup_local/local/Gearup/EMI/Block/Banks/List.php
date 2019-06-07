<?php
/**
 * Gearup_EMI extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Gearup
 * @package        Gearup_EMI
 * @copyright      Copyright (c) 2018
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Bank list block
 *
 * @category    Gearup
 * @package     Gearup_EMI
 * @author      Ultimate Module Creator
 */

class Gearup_EMI_Block_Banks_List extends Mage_Core_Block_Template
{
    /**
     * initialize
     *
     * @access public
     * @author Ultimate Module Creator
     */
    public function _construct()
    {
        parent::_construct();

        $_product = Mage::registry('current_product');
        $_priceIncludingTax = $this->getRoundedTaxPrice($_product->getFinalPrice());
        $amountToEnable = Mage::getStoreConfig('gearup_emi/banks/allowed_amount');

        if($_product->getHasOptions() == 0){
            if($_priceIncludingTax > $amountToEnable){
                $bankss = Mage::getResourceModel('gearup_emi/banks_collection')
                        ->addStoreFilter(Mage::app()->getStore())
                        ->addFieldToFilter('status', 1)
                        ->setOrder('title', 'asc');

                $this->setBankss($bankss);
            }
        }else{
            $bankss = Mage::getResourceModel('gearup_emi/banks_collection')
                        ->addStoreFilter(Mage::app()->getStore())
                        ->addFieldToFilter('status', 1)
                        ->setOrder('title', 'asc');

            $this->setBankss($bankss);
        }
    }

    /**
     * prepare the layout
     *
     * @access protected
     * @return Gearup_EMI_Block_Banks_List
     * @author Ultimate Module Creator
     */
    protected function _prepareLayout()
    {
        if($this->getBankss()){
            parent::_prepareLayout();
            $pager = $this->getLayout()->createBlock(
                'page/html_pager',
                'gearup_emi.banks.html.pager'
            )
            ->setCollection($this->getBankss());
            $this->setChild('pager', $pager);
            $this->getBankss()->load();
            return $this;
        }
    }

    /**
     * get the pager html
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getRoundedTaxPrice($price){
        $rate  = Mage::getModel('tax/config')->customRateRequest();    
        $currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        $currencyObj = new Mage_Directory_Model_Currency;
        $currencyObj->setCurrencyCode($currentCurrencyCode);
        $helper = Mage::helper('directory');
        $conShipPrice = $helper->currencyConvert($price,'USD',$currencyObj);;
        $shippingPrice  = $conShipPrice + ($conShipPrice * $rate  / 100);
        $amount = Mage::helper('rounding')->process($currencyObj,$shippingPrice);
        
        return $amount;
    }
}
