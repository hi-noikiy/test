<?php

class Gearup_Findparts_Model_Observer {

    /**
     * 
     * @param type $observer
     * @return $this
     */
    public function checkFindParts($observer) {
        $formId = 'user_findparts';
        $captchaModel = Mage::helper('captcha')->getCaptcha($formId);
        if ($captchaModel->isRequired()) {
            $controller = $observer->getControllerAction();
            if (!$captchaModel->isCorrect($this->_getCaptchaString($controller->getRequest(), $formId))) {
                $request = $controller->getRequest();
                $isAjax = $request->getParam('json');
                // insert form data to session, allowing to re-populate the contact us form
                $data = $controller->getRequest()->getPost();
                Mage::getSingleton('customer/session')->setFormData($data);
                $controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                if ($isAjax) {
                    $controller->getResponse()->setBody(Zend_Json::encode(array('error' => Mage::helper('captcha')->__('Incorrect CAPTCHA.'))));
                } else {
                    Mage::getSingleton('customer/session')->addError(Mage::helper('captcha')->__('Incorrect CAPTCHA.'));
                    $controller->getResponse()->setRedirect(Mage::getUrl('*/*/'));
                }
            }
        }

        return $this;
    }

    /**
     * Get Captcha String
     *
     * @param Varien_Object $request
     * @param string        $formId
     *
     * @return string
     */
    protected function _getCaptchaString($request, $formId) {
        $captchaParams = $request->getPost(Mage_Captcha_Helper_Data::INPUT_NAME_FIELD_VALUE);

        return $captchaParams[$formId];
    }

}
