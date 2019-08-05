<?php
/**
 * Copyright © 2016 CollinsHarper. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace CollinsHarper\Moneris\Model\Transaction;

use CollinsHarper\Moneris\Model\Transaction;
use Magento\Framework\DataObject;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Moneres OnSite Payment Method model.
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Vault extends Transaction
{
    /**
     * Vault constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \CollinsHarper\Moneris\Helper\Data $helper
     * @param \CollinsHarper\Moneris\Model\Vault $modelVault
     * @param SessionManagerInterface $customerSession
     * @param Encryptor $encryptor
     * @param DataObject $dataObject
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory $transactionSearchFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \CollinsHarper\Moneris\Helper\Data $helper,
        \CollinsHarper\Moneris\Model\Vault $modelVault,
        SessionManagerInterface $customerSession,
        Encryptor $encryptor,
        DataObject $dataObject,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory $transactionSearchFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $helper,
            $modelVault,
            $customerSession,
            $encryptor,
            $dataObject,
            $countryFactory,
            $transactionSearchFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function buildTransactionArray()
    {
        // @TODO: XAVI make vault here, learn it
        $payment = $this->getPayment();
        $order = $payment->getOrder();

        if (!$payment) {
            return [];
        }
        
        $type = 'res_add_cc';
        
        return [
            'type'          => $type,
            'cust_id'       => $order->getCustomerId(),
            'email'         => $order->getCustomerEmail(),
            'pan'           => $payment->getCcNumber(),
            'expdate'       => $this->getFormattedExpiry($payment),
            self::CRYPT_FIELD   => $this->getCryptType()
        ];
    }

    /**
     * @param array $txnArray
     * @param bool $save
     * @return DataObject
     */
    protected function _post($txnArray, $save = true)
    {
        $mpgTxn = $this->buildMpgTransaction($txnArray);
        $mpgRequest = $this->buildMpgRequest($mpgTxn);
        $mpgHttpPost = $this->buildMpgHttpsPost($mpgRequest);
        $mpgResponse=$mpgHttpPost->getMpgResponse();
        
        if (isset($mpgResponse) && $mpgResponse && $mpgResponse->getResponseCode() == '001') {
            if ($save) {
                $this->_prepareData($mpgResponse);
            }

            return $mpgResponse;
        }
    }
    
    protected function _prepareData($data)
    {
        $payment = $this->getPayment();
        
        $this->modelVault->setCcExpMonth($payment->getCcExpMonth());
        $this->modelVault->setCcExpYear($payment->getCcExpYear());
        $this->modelVault->setCreatedDate(gmdate("Y-m-d\TH:i:s\Z"));
    
        if ($data->getPaymentType()) {
            $this->modelVault->setPaymentType($data->getPaymentType());
            $this->modelVault->setCardType($payment->getCcType());
        }
        
        if ($data->getDataKey()) {
            $this->modelVault->setDataKey($data->getDataKey());
        }
        
        if ($data->getResDataCustId()) {
            $this->modelVault->setCustomerId($data->getResDataCustId());
        }
        
        if ($data->getResDataMaskedPan()) {
            $this->modelVault->setCcLast($data->getResDataMaskedPan());
        }
        
        if ($data->getResDataEmail()) {
            $this->modelVault->setCustomerEmail($data->getResDataEmail());
        }
        
        if ($data->getResDataExpDate()) {
            $this->modelVault->setCardExpire($data->getResDataExpDate());
        }

        $this->modelVault->save();
    }
    
    public function getCustomerData()
    {
        if ($this->getCustomerSession()->isLoggedIn()) {
            return  $this->getCustomerSession()->getCustomer();
        }
        
        return null;
    }
}
