<?php
class EM_Apiios_Model_Captcha_Zend extends Mage_Captcha_Model_Zend
{
    protected $_store;

    public function setStore($store){
        $this->_store = $store;
        return $this;
    }

    public function getStore(){
        return $this->_store;
    }

    /**
     * Whether captcha is enabled at this area
     *
     * @return bool
     */
    protected function _isEnabled()
    {
        return (string)$this->_getHelper()->getConfigNode('enable',$this->getStore());
    }

    /**
     * Retrieve list of forms where captcha must be shown
     *
     * For frontend this list is based on current website
     *
     * @return array
     */
    protected function _getTargetForms()
    {
        $formsString = (string) $this->_getHelper()->getConfigNode('forms',$this->getStore());
        return explode(',', $formsString);
    }

    /**
     * Whether to show captcha for this form every time
     *
     * @return bool
     */
    protected function _isShowAlways()
    {
        if ((string)$this->_getHelper()->getConfigNode('mode',$this->getStore()) == Mage_Captcha_Helper_Data::MODE_ALWAYS) {
            return true;
        }

        $alwaysFor = $this->_getHelper()->getConfigNode('always_for',$this->getStore());
        foreach ($alwaysFor as $nodeFormId => $isAlwaysFor) {
            if ($isAlwaysFor && $this->_formId == $nodeFormId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns captcha helper
     *
     * @return Mage_Captcha_Helper_Data
     */
    protected function _getHelper()
    {
        if (empty($this->_helper)) {
            $this->_helper = Mage::helper('apiios/captcha')->setStore($this->getStore());
        }
        return $this->_helper;
    }

    /**
     * Get captcha image directory
     *
     * @return string
     */
    public function getImgDir()
    {
        return $this->_helper->getImgDir($this->getStore()->getWebsite());
    }

    /**
     * Get captcha image base URL
     *
     * @return string
     */
    public function getImgUrl()
    {
        return $this->_helper->getImgUrl($this->getStore()->getWebsite());
    }

    /**
     * Check is user auth
     *
     * @return bool
     */
    protected function _isUserAuth()
    {
        return $this->getStore()->isAdmin()
            ? Mage::getSingleton('admin/session')->isLoggedIn()
            : Mage::getSingleton('customer/session')->isLoggedIn();
    }
   
}
?>