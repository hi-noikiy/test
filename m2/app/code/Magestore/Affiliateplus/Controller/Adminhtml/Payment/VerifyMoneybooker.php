<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplus\Controller\Adminhtml\Payment;

use Magento\Framework\Controller\ResultFactory;

/**
 * Action Index
 */
class VerifyMoneybooker extends \Magestore\Affiliateplus\Controller\Adminhtml\Affiliateplus
{
    /**
     * Execute action
     */
    public function execute()
    {
        $storeId = $this->_getStoreId();
        $request = $this->getRequest();
        $default = $request->getParam('default');
        if ($default){
            $email = $this->_helper->getConfig('moneybookers/settings/moneybookers_email', $storeId);
        }else{
            $email = $request->getParam('email');
        }
        $password = $request->getParam('password');
        $subject = $request->getParam('subject');
        $subject = $subject ? $subject : 'Affiliateplus';
        $note = $request->getParam('note');
        $note = $note ? $note : 'Affiliateplus';
        $link = 'https://www.moneybookers.com/app/pay.pl?action=prepare&email=' . $email . '&password=' . md5($password) . '&amount=1&currency=USD&bnf_email=' . $email . '&subject=' . $subject . '&note=' . $note;
        $data = $this->_getMoneybookerHelper()->readXml($link);
        $xml = simplexml_load_string($data);
        $body = '';
        if ($xml && $xml->error){
            if ($xml->error->error_msg){
                $body = (string) $xml->error->error_msg;
            }
        }
        if ($xml && $xml->sid){
            $body = (string) $xml->sid;
        }
        $errors = $this->_getMoneybookerHelper()->getErrorMessages();
        if (array_key_exists($body, $errors)) {
            $body = $errors[$body];
        } elseif ($body) {
            $body = 1;
        } else {
            $body = __('Can not connect to Moneybooker server at this time');
        }
        $this->getResponse()->setBody($body);
    }

    /**
     * @return mixed
     */
    protected function _getStoreId(){
        return $this->getRequest()->getParam('store', 0);
    }

    /**
     * @return mixed
     */
    protected function _getMoneybookerHelper(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Helper\Payment\Moneybooker');
    }
}
