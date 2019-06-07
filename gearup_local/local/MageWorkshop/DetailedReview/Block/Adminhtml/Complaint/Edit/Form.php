<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */
class MageWorkshop_DetailedReview_Block_Adminhtml_Complaint_Edit_Form extends Mage_Adminhtml_Block_Review_Edit_Form
{
    /**
     * @inherit
     */
    protected function _prepareForm()
    {
        $helper = Mage::helper('detailedreview');

        /* @var $model MageWorkshop_DetailedReview_Model_ComplaintType $complaint*/
        $complaint = Mage::registry('complaint_data');


        if(!empty($complaint)) {
            $complaint = Mage::getModel('detailedreview/complaintType')->load($complaint->getEntityId(), 'entity_id');
        }

        $statuses = Mage::getModel('review/review')
            ->getStatusCollection()
            ->toOptionArray();

        $form   = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'method'    => 'post'
        ));

        $fieldSet = $form->addFieldset('complaint_details', array('legend' => $helper->__('Complaint Details')));


        $fieldSet->addField('status_id', 'select', array(
            'label'     => $helper->__('Status'),
            'required'  => true,
            'name'      => 'status_id',
            'values'    => Mage::getModel('detailedreview/source_common_status')->toOptionArray()
        ));

        $fieldSet->addField('title', 'text', array(
            'label'     => $helper->__('Complaint Title'),
            'required'  => true,
            'name'      => 'title',
        ));

        if(!empty($complaint)) {
            $form->setAction($this->getUrl(
                '*/*/save',
                array(
                    'entity_id' => (int) $this->getRequest()->getParam('entity_id'),
                    'ret'       => Mage::registry('ret')
                )
            ));
            $form->setValues($complaint->getData());
        } else {
            $form->setAction($this->getUrl('*/*/save'));
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return $this;
    }
}
