<?php
/**
 * Gearup_EMI extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Gearup
 * @package        Gearup_EMI
 * @copyright      Copyright (c) 2018
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Bank front contrller
 *
 * @category    Gearup
 * @package     Gearup_EMI
 * @author      Ultimate Module Creator
 */
class Gearup_EMI_BanksController extends Mage_Core_Controller_Front_Action
{

    /**
      * default action
      *
      * @access public
      * @return void
      * @author Ultimate Module Creator
      */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('checkout/session');
        if (Mage::helper('gearup_emi/banks')->getUseBreadcrumbs()) {
            if ($breadcrumbBlock = $this->getLayout()->getBlock('breadcrumbs')) {
                $breadcrumbBlock->addCrumb(
                    'home',
                    array(
                        'label' => Mage::helper('gearup_emi')->__('Home'),
                        'link'  => Mage::getUrl(),
                    )
                );
                $breadcrumbBlock->addCrumb(
                    'bankss',
                    array(
                        'label' => Mage::helper('gearup_emi')->__('Banks Manager '),
                        'link'  => '',
                    )
                );
            }
        }
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->addLinkRel('canonical', Mage::helper('gearup_emi/banks')->getBankssUrl());
        }
        if ($headBlock) {
            $headBlock->setTitle(Mage::getStoreConfig('gearup_emi/banks/meta_title'));
            $headBlock->setKeywords(Mage::getStoreConfig('gearup_emi/banks/meta_keywords'));
            $headBlock->setDescription(Mage::getStoreConfig('gearup_emi/banks/meta_description'));
        }
        $this->renderLayout();
    }

    /**
     * init Bank
     *
     * @access protected
     * @return Gearup_EMI_Model_Banks
     * @author Ultimate Module Creator
     */
    protected function _initBanks()
    {
        $banksId   = $this->getRequest()->getParam('id', 0);
        $banks     = Mage::getModel('gearup_emi/banks')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($banksId);
        if (!$banks->getId()) {
            return false;
        } elseif (!$banks->getStatus()) {
            return false;
        }
        return $banks;
    }

    /**
     * view bank action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function viewAction()
    {
        $banks = $this->_initBanks();
        if (!$banks) {
            $this->_forward('no-route');
            return;
        }
        Mage::register('current_banks', $banks);
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('checkout/session');
        if ($root = $this->getLayout()->getBlock('root')) {
            $root->addBodyClass('emi-banks emi-banks' . $banks->getId());
        }
        if (Mage::helper('gearup_emi/banks')->getUseBreadcrumbs()) {
            if ($breadcrumbBlock = $this->getLayout()->getBlock('breadcrumbs')) {
                $breadcrumbBlock->addCrumb(
                    'home',
                    array(
                        'label'    => Mage::helper('gearup_emi')->__('Home'),
                        'link'     => Mage::getUrl(),
                    )
                );
                $breadcrumbBlock->addCrumb(
                    'bankss',
                    array(
                        'label' => Mage::helper('gearup_emi')->__('Banks Manager '),
                        'link'  => Mage::helper('gearup_emi/banks')->getBankssUrl(),
                    )
                );
                $breadcrumbBlock->addCrumb(
                    'banks',
                    array(
                        'label' => $banks->getTitle(),
                        'link'  => '',
                    )
                );
            }
        }
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->addLinkRel('canonical', $banks->getBanksUrl());
        }
        if ($headBlock) {
            if ($banks->getMetaTitle()) {
                $headBlock->setTitle($banks->getMetaTitle());
            } else {
                $headBlock->setTitle($banks->getTitle());
            }
            $headBlock->setKeywords($banks->getMetaKeywords());
            $headBlock->setDescription($banks->getMetaDescription());
        }
        $this->renderLayout();
    }
}
