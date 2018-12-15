<?php

class EM_Onestepcheckout_Block_Onepage_Rewardpoints extends Mage_Checkout_Block_Onepage_Abstract
{
    protected function _construct()
    {
        $this->getCheckout()->setStepData('rewardpoints', array(
            'label'     => $this->__('Reward Points'),
            'is_show'   => $this->isShow()
        ));
        parent::_construct();
    }

    public function isShow()
    {
    	if(Mage::getSingleton('customer/session')->isLoggedIn()) {
    		return 1;
    	} else {
    		return 0;
    	}
    }

    public function isOneStepCheckout()
    {
        if (!$this->hasData('one_step_checkout')) {
//            $isOneStep = ($this->getRequest()->getModuleName() == 'onestepcheckout'
//                || $this->getRequest()->getActionName() == 'onestepcheckout');
            $isOneStep = $this->getRequest()->getModuleName() != 'checkout'  || Mage::helper('core')->isModuleOutputEnabled('Amasty_Scheckout');
            $this->setData('one_step_checkout', $isOneStep);
        }
        return $this->getData('one_step_checkout');
    }
    
    /**
     * check reward points system is enabled or not
     * 
     * @return boolean
     */
    public function isEnable()
    {
        return Mage::helper('rewardpoints')->isEnable();
    }

    public function getMethodTitle($method)
    {
        if (version_compare(Mage::getVersion(), '1.4.1.0', '<')) {
            return $method->getTitle();
        }
        return parent::getMethodTitle($method);
    }
    
    /**
     * get reward points spending block helper
     * 
     * @return Magestore_RewardPoints_Helper_Block_Spend
     */
    public function getBlockHelper()
    {
        return Mage::helper('rewardpoints/block_spend');
    }
    
    /**
     * get reward points helper
     * 
     * @return Magestore_RewardPoints_Helper_Point
     */
    public function getPointHelper()
    {
        return Mage::helper('rewardpoints/point');
    }
    
    /**
     * call method that defined from block helper
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args) {
        $helper = $this->getBlockHelper();
        if (method_exists($helper, $method)) {
            return call_user_func_array(array($helper, $method), $args);
            // return call_user_method_array($method, $helper, $args);
        }
        return parent::__call($method, $args);
    }
}
