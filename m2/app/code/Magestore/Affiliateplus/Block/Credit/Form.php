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
namespace Magestore\Affiliateplus\Block\Credit;
use \Magestore\Affiliateplus\Block\AbstractTemplate;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Form extends \Magento\Payment\Block\Form
{

    /**
     * @var \Magestore\Affiliateplus\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magestore\Affiliateplus\Helper\Account
     */
    protected $_helperAccount;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * Form constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magestore\Affiliateplus\Helper\Data $helper
     * @param \Magestore\Affiliateplus\Helper\Account $helperAccount
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magestore\Affiliateplus\Helper\Data $helper,
        \Magestore\Affiliateplus\Helper\Account $helperAccount,
        \Magento\Directory\Model\Currency $currency,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_helperAccount = $helperAccount;
        $this->_helper = $helper;
        $this->_currency = $currency;
        $this->_priceCurrency = $priceCurrency;
    }

    /**
     * get Helper Data
     *
     * @return  \Magestore\Affiliateplus\Helper\Data
     */
    public function _getHelper(){
        return $this->_helper;
    }

    /**
     * get Account helper
     *
     * @return \Magestore\Affiliateplus\Helper\Account
     */
    protected function _getAccountHelper() {
        return $this->_helperAccount;
    }



    /**
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckoutSession(){
        return $this->_helper->getCheckoutSession();
    }

    /**
     * @return mixed
     */
    public function getAffiliateCredit() {
        return round($this->getCheckoutSession()->getAffiliateCredit(), 2);
    }

    /**
     * @return mixed
     */
    public function getUsingAmount() {
        return $this->_priceCurrency->format(
            $this->getAffiliateCredit(),
            false
        );
    }

    public function getFormatedBalance() {
        $balance = $this->_getAccountHelper()->getAccount()->getBalance();
        $balance = $this->_priceCurrency->convert($balance);
        if ($this->getAffiliateCredit() > 0) {
            $balance -= $this->getAffiliateCredit();
        }
        return $this->_priceCurrency->format($balance, false);
    }

    /**
     * @return array
     */
    public function getAffiliateCreditInfo() {
        $result = array();
        $result['enableCredit'] = $this->_isEnableCredit();
        $result['usedAffiliateCredit'] = $this->_getUseAffiliateCredit();
        $result['formatedBalance'] = $this->getFormatedBalance();
        $result['opcAjaxLoader'] = $this->getViewFileUrl('Magestore_Affiliateplus::images/opc-ajax-loader.gif');
        $result['editButtonImage'] = $this->getViewFileUrl('Magestore_Affiliateplus::images/btn_edit.png');
        $result['seccessMsgImage'] = $this->getViewFileUrl('Magestore_Affiliateplus::images/i_msg-success.gif');
        $result['usingAmount'] = $this->getUsingAmount();
        $result['affiliateCredit'] = $this->getAffiliateCredit();

        return $result;
    }

    /**
     * @return bool
     */
    protected function _isEnableCredit(){
        if ($this->_helperAccount->disableStoreCredit() || !$this->_helperAccount->isEnoughBalance()){
            return false;
        }
        return true;
    }

    /**
     * @return mixed
     */
    protected function _getUseAffiliateCredit() {
        $useAffiliateCredit = $this->getCheckoutSession()->getUseAffiliateCredit();
        if ($useAffiliateCredit == 1) {
            return true;
        }
        return false;
    }
}