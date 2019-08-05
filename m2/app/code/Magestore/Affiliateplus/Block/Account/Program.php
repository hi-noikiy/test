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
namespace Magestore\Affiliateplus\Block\Account;

class Program extends \Magestore\Affiliateplus\Block\AbstractTemplate
{


    /**
     * @return mixed
     */
    public function getMinPaymentRelease(){
        return $this->_configHelper->getPaymentConfig('payment_release');
    }

    /**
     * @return mixed
     */
    public function getListProgram(){
        $programList = array();
        if ($this->_configHelper->getCommissionConfig('commission_value')
            || $this->_configHelper->getDiscountConfig('discount')){
            $defaultProgram = new \Magento\Framework\DataObject(
                [
                'name'				=> __('Affiliate Program'),
                'commission_type'	=> $this->_configHelper->getCommissionConfig('commission_type'),
                'commission'		=> $this->_configHelper->getCommissionConfig('commission_value'),
                'sec_commission'    => $this->_configHelper->getCommissionConfig('use_secondary'),
                'sec_commission_type'   => $this->_configHelper->getCommissionConfig('secondary_type'),
                'secondary_commission'  => $this->_configHelper->getCommissionConfig('secondary_commission'),
                'discount_type'		=> $this->_configHelper->getDiscountConfig('discount_type'),
                'discount'			=> $this->_configHelper->getDiscountConfig('discount'),
                'sec_discount'      => $this->_configHelper->getDiscountConfig('use_secondary'),
                'sec_discount_type' => $this->_configHelper->getDiscountConfig('secondary_type'),
                'secondary_discount'=> $this->_configHelper->getDiscountConfig('secondary_discount'),
            ]
        );
            $this->_eventManager->dispatch('affiliateplus_prepare_program',['info' => $defaultProgram]);
            $programList['default'] = $defaultProgram;
        }
        $programListObj = new \Magento\Framework\DataObject(
            [
            'program_list'	=> $programList,
            ]
        );
        $this->_eventManager->dispatch('affiliateplus_get_list_program_welcome',
           [
            'program_list_object'	=> $programListObj,
        ]
        );
        return $programListObj->getProgramList();
    }

    /**
     * @param $program
     * @return bool
     */
    public function hasSecondaryCommission($program) {
        return ($program->getData('sec_commission')
            && ($program->getData('sec_commission_type') != $program->getData('commission_type')
                || $program->getData('secondary_commission') != $program->getData('commission')
            ));
    }

    /**
     * @param $program
     * @return bool
     */
    public function hasSecondaryDiscount($program) {
        return ($program->getData('sec_discount')
            && ($program->getData('sec_discount_type') != $program->getData('discount_type')
                || $program->getData('secondary_discount') != $program->getData('discount')
            ));
    }

    /**
     * @return \Magento\Framework\CurrencyInterface
     */
    public function getCurrency(){
        return $this->_currencyInterface;
    }

    /**
     * @return bool
     */
    public function affiliateTypeIsProfit(){
        return $this->_dataHelper->affiliateTypeIsProfit();
    }

    /**
     * @return \Magento\Framework\Event\ManagerInterface
     */
    public  function getEventManager(){
        return $this->_eventManager;
    }
}