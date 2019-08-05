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
namespace Magestore\Affiliateplus\Model\Payment;
use Magestore\Affiliateplus\Model\Payment;

/**
 * Class Credit
 * @package Magestore\Affiliateplus\Model\Payment
 */
class Verify extends \Magestore\Affiliateplus\Model\Payment\AbstractPayment
{
    /**
     *
     */
    const TEMPLATE_VERIFY_EMAIL = 'affiliateplus/email/verify_payment_email';
    /**
     *
     */
    const XML_PATH_EMAIL_IDENTITY = 'trans_email/ident_sales';

    /**
     *
     */
    public function _construct() {
        parent::_construct();
        $this->_init('Magestore\Affiliateplus\Model\ResourceModel\Payment\Verify');
    }

    /**
     * @param $accountId
     * @param $field
     * @param $paymentMethod
     * @return $this
     */
    public function loadExist($accountId, $field, $paymentMethod){
        $collection = $this->getCollection()
            ->addFieldToFilter('account_id', $accountId)
            ->addFieldToFilter('payment_method', $paymentMethod)
            ->addFieldToFilter('field', $field);
        if($collection->getSize()){
            $this->load($collection->getFirstItem()->getId());
        }

        return $this;
    }

    /**
     * @param $email
     * @param $method
     * @return string|void
     */
    public function sendMailAuthentication($email, $method){
        $account = $this->_helper->getAffiliateAccount();
        $length = 6;
        $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $str = '';
        if($this->getId()){
            $str = $this->getInfo();
        }else{
            $count = strlen($charset);
            while ($length--) {
                $str .= $charset[mt_rand(0, $count-1)];
            }
        }
        $store = $this->_storeManager->getStore();
        $link = $this->_urlBuilder->getUrl('affiliateplus/index/verifyCode',['account_id'=>$account->getId(),'payment_method'=>$method,'email'=>$email,'authentication_code'=>$str, 'from'=>'email']);
        $sender = $this->_helper->getSenderContact();
        $template = $this->_helper->getConfig(self::TEMPLATE_VERIFY_EMAIL, $store->getId());
        $transport = $this->_transportBuilder
            ->setTemplateIdentifier($template)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $store->getId()
                ]
            )
            ->setTemplateVars(
                [
                    'store' => $store,
                    'sender_name' => $sender['name'],
                    'code'=>$str,
                    'link'=>$link,
                    'name'=>$account->getName(),
                    'trans_email_ident_support' => $this->_helper->getConfig('trans_email/ident_support/email'),
                    'store_phone_information' => $this->_helper->getConfig('general/store_information/phone')
                ]
            )
            ->setFrom($sender)
            ->addTo($email, $account->getName())
            ->getTransport();

        $transport->sendMessage();

        return $str;
    }


    /**
     * @return bool
     */
    public function isVerified(){
        if($this->getVerified() == 1)
            return true;
        return false;
    }

    /**
     * @param $accountId
     * @param $field
     * @param $paymentMethod
     * @param null $code
     * @return bool
     */
    public function verify($accountId, $field, $paymentMethod, $code = null){
        $collection = $this->getCollection()
            ->addFieldToFilter('account_id', $accountId)
            ->addFieldToFilter('payment_method', $paymentMethod)
            ->addFieldToFilter('field', $field);
        if($code){
            $collection->addFieldToFilter('info',$code);
        }
        if($collection->getSize()){
            $model = $collection->getFirstItem();
            try{
                if($model->getVerified() == 2)
                    $model->setVerified(1)
                        ->save();
                return true;
            }  catch (\Exception $e){
                $this->_messageManager->addError($e->getMessage());
                return false;
            }
        }
        return false;
    }
}