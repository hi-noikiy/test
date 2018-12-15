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



class Mirasvit_Rma_Block_Adminhtml_Rma_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Contruct.
     */
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'rma_id';
        $this->_controller = 'adminhtml_rma';
        $this->_blockGroup = 'rma';

        // $this->_updateButton('save', 'label', Mage::helper('rma')->__('Save'));
        $this->_removeButton('save');
        $this->_removeButton('delete');

        $this->_addButton('update_continue', array(
             'label' => Mage::helper('rma')->__('Update And Continue Edit'),
             'onclick' => 'saveAndContinueEdit()',
             'class' => 'save saveAndContinueRmaBtn',
         ), -100);

        $this->_addButton('update', array(
            'label' => Mage::helper('rma')->__('Update'),
            'onclick' => 'saveEdit()',
            'class' => 'save saveRmaBtn',
        ), -100);

        $this->_formScripts[] = "
            function saveEdit(){
                if (validateOfflineOrder()) {
                    editForm.submit($('edit_form').action);
                }
            }
            function saveAndContinueEdit(){
                if (validateOfflineOrder()) {
                    editForm.submit($('edit_form').action + 'back/edit/');
                } else {
                    alert('".
                        Mage::helper('sales')->__("\'Order or Receipt #\' and \'Returned Item\' are required").
                    "')
                }
            }
            function disableActionButton(button) {
                button.disabled = true;
                button.classList.add('disabled');
            }
            function validateOfflineOrder() {
                var isValid = true;
                if ($$('.UI-ORDER-CONTAINER').length) {
                    $$('.UI-OFFLINE-ORDER-INPUT').each(function(item) {
                        if (!item.value.length) {
                            isValid = false;
                        }
                    });
                    $$('.UI-ITEMNAME').each(function(item) {
                        if (!item.value.length) {
                            isValid = false;
                        }
                    });
                }

                return isValid;
            }

            function checkWYSIWYG(checked) {
                if(tinyMCE.activeEditor != null && document.getElementsByClassName('mceEditor').length) {
                    tinyMCE.activeEditor.setContent(tinyMCE.activeEditor.getContent() +
                        document.getElementById('htmltemplate-' + checked.value).innerHTML);

                }
            }
        ";
        $rma = $this->getRma();
        if ($rma) {
            $this->_addButton('print', array(
                'label' => Mage::helper('sales')->__('Print'),
                'onclick' => 'var win = window.open(\''.$rma->getPrintUrl().'\', \'_blank\');win.focus();',
            ));

            $this->_addButton('order_exchange', array(
                'label' => Mage::helper('sales')->__('Exchange Order'),
                'onclick' => 'disableActionButton(this); var win = window.open(\''.
                    $this->getCreateOrderUrl($rma).'\', \'_blank\');win.focus();',
            ));

            // Check, whether Replacements are allowed in current RMA
            $allowedReplacements = false;
            $replaceResolutions = Mage::getSingleton('rma/config')->getPolicyAllowReplacementResolutions();
            foreach ($replaceResolutions as $resolution) {
                $currentResolution = Mage::helper('rma')->getResolutionByCode($resolution);
                if ($rma->getHasItemsWithResolution($currentResolution->getId())) {
                    $allowedReplacements = true;
                    break;
                }
            }

            $this->_addButton('order_replace', array(
                'label' => Mage::helper('sales')->__('Replacement Order'),
                'class' => ($rma->getExchangeOrderIds() || !$allowedReplacements) ? 'disabled' : '',
                'disabled' => $rma->getExchangeOrderIds(),
                'onclick' => 'disableActionButton(this); var win = window.open(\''.
                    Mage::helper('adminhtml')->getUrl('adminhtml/rma_rma/createReplacement/',
                        array('customer_id' => $rma->getCustomerId(), 'store_id' => $rma->getStoreId(),
                            'rma_id' => $rma->getId())).'\', \'_blank\');win.focus();',
            ));
        }

        if ($this->getRma()) {
            if ($this->isArchive()) {
                $this->_addButton('restore', array(
                    'label' => Mage::helper('rma')->__('Restore RMA'),
                    'onclick' => 'setLocation(\''.$this->getRestoreUrl().'\')',
                    'class' => 'save rma-archive-button',
                ), -1, 1);
            } else {
                $this->_addButton('archive', array(
                    'label' => Mage::helper('rma')->__('Archive'),
                    'onclick' => "deleteConfirm('Are you sure you want to do this?','".$this->getArchiveUrl()."')",
                    'class' => 'delete rma-archive-button',
                ), -1, 1);
            }
            $this->_addButton('delete', array(
                'label' => Mage::helper('adminhtml')->__('Delete'),
                'class' => 'delete margin-right-40px',
                'label' => Mage::helper('rma')->__('Delete'),
                'onclick' => 'deleteConfirm(\''
                    .Mage::helper('core')->jsQuoteEscape(
                        Mage::helper('adminhtml')->__('Are you sure you want to do this?')
                    )
                    .'\', \''
                    .$this->getDeleteUrl()
                    .'\')',
            ), -1, 4);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isArchive()
    {
        return Mage::registry('is_archive');
    }

    /**
     * @return string
     */
    public function getArchiveUrl()
    {
        return $this->getUrl('*/*/archive', array('id' => $this->getRma()->getId()));
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', array('id' => $this->getRma()->getId()));
    }

    /**
     * @return string
     */
    public function getRestoreUrl()
    {
        return $this->getUrl('*/*/restore', array('id' => $this->getRma()->getId()));
    }

    /**
     * @param Mirasvit_Rma_Model_Rma $rma
     *
     * @return string
     */
    public function getCreateOrderUrl($rma)
    {
        return $this->getUrl('adminhtml/sales_order_create/index/',
            array('customer_id' => $rma->getCustomerId(), 'store_id' => $rma->getStoreId(), 'rma_id' => $rma->getId()));
    }

    /**
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

    /**
     * @return Mirasvit_Rma_Model_Rma
     */
    public function getRma()
    {
        if (Mage::registry('current_rma') && Mage::registry('current_rma')->getId()) {
            return Mage::registry('current_rma');
        }
    }

    /**
     * @return string
     */
    public function getHeaderText()
    {
        if ($rma = $this->getRma()) {
            return Mage::helper('rma')->__('RMA #%s - %s', $rma->getIncrementId(), $rma->getStatus()->getName());
        } else {
            return Mage::helper('rma')->__('Create New RMA');
        }
    }

    /************************/
}
