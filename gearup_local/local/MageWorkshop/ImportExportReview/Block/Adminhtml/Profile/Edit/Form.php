<?php

class MageWorkshop_ImportExportReview_Block_Adminhtml_Profile_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl(
                'adminhtml/drie_profile/edit',
                array(
                    '_current' => true,
                    'continue' => 0,
                )
            ),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));

        /** @var MageWorkshop_ImportExportReview_Model_Profile $profile */
        $profile = Mage::registry('current_drie_profile');

        if ($profile->getId()) {
            $form->addField('id', 'hidden', array(
                'name' => 'id',
            ));
            $form->setValues($profile->getData());
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}