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
 * Bank model
 *
 * @category    Gearup
 * @package     Gearup_EMI
 * @author      Ultimate Module Creator
 */
class Gearup_EMI_Model_Banks extends Mage_Core_Model_Abstract
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY    = 'gearup_emi_banks';
    const CACHE_TAG = 'gearup_emi_banks';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'gearup_emi_banks';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'banks';

    /**
     * constructor
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('gearup_emi/banks');
    }

    /**
     * before save bank
     *
     * @access protected
     * @return Gearup_EMI_Model_Banks
     * @author Ultimate Module Creator
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $now = Mage::getSingleton('core/date')->gmtDate();
        if ($this->isObjectNew()) {
            $this->setCreatedAt($now);
        }
        $this->setUpdatedAt($now);
        return $this;
    }

    /**
     * get the url to the bank details page
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getBanksUrl()
    {
        return Mage::getUrl('gearup_emi/banks/view', array('id'=>$this->getId()));
    }

    /**
     * get the bank Terms & Conditions
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getTermsCondition()
    {
        $terms_condition = $this->getData('terms_condition');
        $helper = Mage::helper('cms');
        $processor = $helper->getBlockTemplateProcessor();
        $html = $processor->filter($terms_condition);
        return $html;
    }

    /**
     * save bank relation
     *
     * @access public
     * @return Gearup_EMI_Model_Banks
     * @author Ultimate Module Creator
     */
    protected function _afterSave()
    {
        return parent::_afterSave();
    }

    /**
     * get default values
     *
     * @access public
     * @return array
     * @author Ultimate Module Creator
     */
    public function getDefaultValues()
    {
        $values = array();
        $values['status'] = 1;
        return $values;
    }
    
}
