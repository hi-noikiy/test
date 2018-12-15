<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_rma
 * @version   2.4.7
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



abstract class Mirasvit_Rma_Model_ItemAbstract extends Mage_Core_Model_Abstract
{
    protected $_reason = null;

    /**
     * @return bool|Mirasvit_Rma_Model_Reason
     */
    public function getReason()
    {
        if (!$this->getReasonId()) {
            return false;
        }
        if ($this->_reason === null) {
            $this->_reason = Mage::getModel('rma/reason')->load($this->getReasonId());
        }

        return $this->_reason;
    }

    protected $_resolution = null;

    /**
     * @return bool|Mirasvit_Rma_Model_Resolution
     */
    public function getResolution()
    {
        if (!$this->getResolutionId()) {
            return false;
        }
        if ($this->_resolution === null) {
            $this->_resolution = Mage::getModel('rma/resolution')->load($this->getResolutionId());
        }

        return $this->_resolution;
    }

    protected $_condition = null;

    /**
     * @return bool|Mirasvit_Rma_Model_Condition
     */
    public function getCondition()
    {
        if (!$this->getConditionId()) {
            return false;
        }
        if ($this->_condition === null) {
            $this->_condition = Mage::getModel('rma/condition')->load($this->getConditionId());
        }

        return $this->_condition;
    }


    /**
     * @return string
     */
    public function getReasonName()
    {
        return Mage::helper('rma/locale')->getLocaleValue($this, 'reason_name');
    }

    /**
     * @return string
     */
    public function getConditionName()
    {
        return Mage::helper('rma/locale')->getLocaleValue($this, 'condition_name');
    }

    /**
     * @return string
     */
    public function getResolutionName()
    {
        return Mage::helper('rma/locale')->getLocaleValue($this, 'resolution_name');
    }

    protected $_rma = null;

    /**
     * @return bool|Mirasvit_Rma_Model_Rma
     */
    public function getRma()
    {
        if (!$this->getRmaId()) {
            return false;
        }
        if ($this->_rma === null) {
            $this->_rma = Mage::getModel('rma/rma')->load($this->getRmaId());
        }

        return $this->_rma;
    }

}