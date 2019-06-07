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
 * Bank widget block
 *
 * @category    Gearup
 * @package     Gearup_EMI
 * @author      Ultimate Module Creator
 */
class Gearup_EMI_Block_Banks_Widget_View extends Mage_Core_Block_Template implements
    Mage_Widget_Block_Interface
{
    protected $_htmlTemplate = 'gearup_emi/banks/widget/view.phtml';

    /**
     * Prepare a for widget
     *
     * @access protected
     * @return Gearup_EMI_Block_Banks_Widget_View
     * @author Ultimate Module Creator
     */
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
        $banksId = $this->getData('banks_id');
        if ($banksId) {
            $banks = Mage::getModel('gearup_emi/banks')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($banksId);
            if ($banks->getStatus()) {
                $this->setCurrentBanks($banks);
                $this->setTemplate($this->_htmlTemplate);
            }
        }
        return $this;
    }
}
