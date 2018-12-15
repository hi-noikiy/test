<?php
class EM_Apiios_Helper_Captcha extends Mage_Captcha_Helper_Data
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
     * Get Captcha
     *
     * @param string $formId
     * @return Mage_Captcha_Model_Interface
     */
    public function getCaptcha($formId)
    {
        if (!array_key_exists($formId, $this->_captcha)) {
            $type = $this->getConfigNode('type',$this->getStore());
            $this->_captcha[$formId] = Mage::getModel('apiios/captcha_' . $type, array('formId' => $formId));
        }
        return $this->_captcha[$formId];
    }

    /**
     * Returns value of the node with respect to current area (frontend or backend)
     *
     * @param string $id The last part of XML_PATH_$area_CAPTCHA_ constant (case insensitive)
     * @param Mage_Core_Model_Store $store
     * @return Mage_Core_Model_Config_Element
     */
    public function getConfigNode($id, $store = null)
    {
        $areaCode = Mage::app()->getStore($store)->isAdmin() ? 'admin' : 'customer';
        $store = ($store) ? $store : $this->getStore();
        return Mage::getStoreConfig( $areaCode . '/captcha/' . $id, $store);
    }

    /**
     * Validate Captcha
     *
     * @param string $value
     * @param string $formId
     * @return boolean
     */

    public function validate($value,$formId){//print_r($this->getStore());
        $captchaModel = $this->getCaptcha($formId);
        $captchaModel->setStore($this->getStore());//print_r($captchaModel);exit;

        if ($captchaModel->isRequired()) {
            if (!$captchaModel->isCorrect($value)) {
                //$this->_error(Mage::helper('captcha')->__('Incorrect CAPTCHA.'), Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                throw new Exception(Mage::helper('captcha')->__('Incorrect CAPTCHA.'));
            }
        }
        return true;
    }
}
?>