<?php
class Magestore_Affiliateplus_Block_Credit_Cart extends Mage_Core_Block_Template
{
	/**
	 * get Helper
	 *
	 * @return Magestore_Affiliateplus_Helper_Config
	 */
	public function _getHelper(){
		return Mage::helper('affiliateplus/config');
	}
    
    /**
     * get Account helper
     *
     * @return Magestore_Affiliateplus_Helper_Account
     */
    protected function _getAccountHelper() {
        return Mage::helper('affiliateplus/account');
    }
	
	public function _prepareLayout(){
		parent::_prepareLayout();
        $this->setTemplate('affiliateplus/credit/cart.phtml');
        return $this;
    }
    
    public function getFormatedBalance() {
        $balance = $this->_getAccountHelper()->getAccount()->getBalance();
        $balance = Mage::app()->getStore()->convertPrice($balance);
        if ($this->getAffiliateCredit() > 0) {
            $balance -= $this->getAffiliateCredit();
        }
        return Mage::app()->getStore()->formatPrice($balance);
        // return $this->_getAccountHelper()->getAccountBalanceFormated();
    }
    
    /**
     * check using affiliate credit or not
     * 
     * @return boolean
     */
    public function getUseAffiliateCredit() {
        return Mage::getSingleton('checkout/session')->getUseAffiliateCredit();
    }
    
    public function getAffiliateCredit() {
        return Mage::getSingleton('checkout/session')->getAffiliateCredit();
    }
}
