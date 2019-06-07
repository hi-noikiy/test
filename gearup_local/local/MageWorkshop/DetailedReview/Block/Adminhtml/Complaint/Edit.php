<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Comment edit form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class MageWorkshop_DetailedReview_Block_Adminhtml_Complaint_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'detailedreview';
        $this->_controller = 'adminhtml_complaint';

        $helper = Mage::helper('detailedreview');

        $this->_updateButton('save', 'label', $helper->__('Save Complaint'));
        $this->_updateButton('save', 'id', 'save_button');
        $this->_updateButton('delete', 'label', $helper->__('Delete Complaint'));

        if( $this->getRequest()->getParam($this->_objectId) ) {
            $complaintData = Mage::getModel('detailedreview/complaintType')
                ->load($this->getRequest()->getParam($this->_objectId), $this->_objectId);
            Mage::register('complaint_data', $complaintData);
        }

    }

    public function getHeaderText()
    {
        if( Mage::registry('complaint_data') && Mage::registry('complaint_data')->getId() ) {
            return Mage::helper('detailedreview')->__(
                "Edit Complaint '%s'",
                $this->escapeHtml(Mage::registry('complaint_data')->getTitle())
            );
        } else {
            return Mage::helper('detailedreview')->__('New Complaint');
        }
    }
}
