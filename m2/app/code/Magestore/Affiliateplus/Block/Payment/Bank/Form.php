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
namespace Magestore\Affiliateplus\Block\Payment\Bank;


/**
 * Class Request
 * @package Magestore\Affiliateplus\Block\Payment
 */
class Form extends \Magestore\Affiliateplus\Block\Payment\Form
{
    /**
     * @var
     */
    protected $_countryCollection;
    /**
     * @return $this
     */
    public function _prepareLayout(){
        parent::_prepareLayout();
        $this->setTemplate('Magestore_Affiliateplus::payment/bank/form.phtml');
        return $this;
    }

    /**
     * @return bool
     */
    public function bankAccountIsVerified(){
        $bankAccountId = $this->getBankAccountId();
        $account = $this->_sessionModel->getAccount();
        $verifyCollection = $this->getModelPaymentVerify()
            ->getCollection()
            ->addFieldToFilter('account_id',$account->getId())
            ->addFieldToFilter('payment_method','bank')
            ->addFieldToFilter('field',$bankAccountId)
            ->addFieldToFilter('verified','1')
        ;
        if($verifyCollection->getSize())
            return true;
        return false;
    }

    /**
     * @return \Magestore\Affiliateplus\Model\Session
     */
    protected function _getSession(){
        return $this->_sessionModel;
    }

    /**
     * @return mixed
     */
    public function getAccount(){
        return $this->_getSession()->getAccount();
    }

    /**
     * @return mixed
     */
    public function hasBankAccount(){
        return $this->getBankAccounts()->getSize();
    }

    /**
     * @return mixed
     */
    public function getPostData(){
        $data = $this->_objectManager->get('Magento\Framework\App\RequestInterface')->getParams();
        return $data;
    }

    /**
     * @return mixed
     */
    public function getBank(){
        $bank = $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment\Bankaccount');
        $data = $this->getPostData();
        if($this->isShowForm() && isset($data['bank']))
            $bank->setData($data['bank']);
        return $bank;
    }

    /**
     * @return mixed
     */
    public function getBankAccounts(){
        if (!$this->_bank_accounts){
            $bankAccounts = $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment\Bankaccount')
                ->getBankAccounts($this->getAccount());
            $this->_bank_accounts  = $bankAccounts;
        }
        return $this->_bank_accounts;
    }

    /**
     * @return bool
     */
    public function isShowForm(){
        $data = $this->getPostData();
        if(isset($data['payment_bankaccount_id']))
            if(!$data['payment_bankaccount_id'])
                return true;
        return false;
    }

    /**
     * @param $type
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBankAccountHtmlSelect($type){
        $data = $this->getPostData();
        if ($this->hasBankAccount()){
            $options = array();
            foreach ($this->getBankAccounts() as $bankAccount) {
                $options[] = [
                    'value' => $bankAccount->getId(),
                    'label'	=> $bankAccount->format(false)
                ];
                $bankAccountId = $bankAccount->getId();
            }

            if(isset($data['payment_bankaccount_id'])){
                $bankAccountId = $data['payment_bankaccount_id'];
            }
            if($bankAccountId){
                $this->setBankAccountId($bankAccountId);
            }
            $select = $this->_blockFactory->createBlock('Magento\Framework\View\Element\Html\Select')
                ->setName($type.'_bankaccount_id')
                ->setId($type.'-bank-select')
                ->setClass('bank-select')
                ->setExtraParams('onchange=lsRequestTrialNewAccount(this.value);')
                ->setValue($bankAccountId)
                ->setOptions($options);

            $select->addOption('', __('New Bank Account'));
            return $select->getHtml();
        }
        return '';
    }

    /**
     * @return \Magestore\Affiliateplus\Helper\Payment
     */
    public function getPaymentHelper()
    {
        return $this->_paymentHelper;
    }
}
