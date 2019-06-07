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
 * Bank admin edit tabs
 *
 * @category    Gearup
 * @package     Gearup_EMI
 * @author      Ultimate Module Creator
 */
class Gearup_EMI_Block_Adminhtml_Banks_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize Tabs
     *
     * @access public
     * @author Ultimate Module Creator
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('banks_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('gearup_emi')->__('Bank'));
    }

    /**
     * before render html
     *
     * @access protected
     * @return Gearup_EMI_Block_Adminhtml_Banks_Edit_Tabs
     * @author Ultimate Module Creator
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_banks',
            array(
                'label'   => Mage::helper('gearup_emi')->__('Bank'),
                'title'   => Mage::helper('gearup_emi')->__('Bank'),
                'content' => $this->getLayout()->createBlock(
                    'gearup_emi/adminhtml_banks_edit_tab_form'
                )
                ->toHtml(),
            )
        );
        $this->addTab(
            'form_meta_banks',
            array(
                'label'   => Mage::helper('gearup_emi')->__('Meta'),
                'title'   => Mage::helper('gearup_emi')->__('Meta'),
                'content' => $this->getLayout()->createBlock(
                    'gearup_emi/adminhtml_banks_edit_tab_meta'
                )
                ->toHtml(),
            )
        );
        
//          $this->addTab(
//            'form_meta_emis',
//            array(
//                'label'   => Mage::helper('gearup_emi')->__('Installments'),
//                'title'   => Mage::helper('gearup_emi')->__('Installments'),
//                'content' => $this->getLayout()->createBlock(
//                    'gearup_emi/adminhtml_banks_edit_tab_manageEMI'
//                )
//                ->toHtml(),
//            )
//        );
          
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addTab(
                'form_store_banks',
                array(
                    'label'   => Mage::helper('gearup_emi')->__('Store views'),
                    'title'   => Mage::helper('gearup_emi')->__('Store views'),
                    'content' => $this->getLayout()->createBlock(
                        'gearup_emi/adminhtml_banks_edit_tab_stores'
                    )
                    ->toHtml(),
                )
            );
        }
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve bank entity
     *
     * @access public
     * @return Gearup_EMI_Model_Banks
     * @author Ultimate Module Creator
     */
    public function getBanks()
    {
        return Mage::registry('current_banks');
    }
}
