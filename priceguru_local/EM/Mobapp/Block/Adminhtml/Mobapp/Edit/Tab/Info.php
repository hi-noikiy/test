<?php
class EM_Mobapp_Block_Adminhtml_Mobapp_Edit_Tab_Info extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('mobapp_info', array('legend'=>Mage::helper('mobapp')->__('App Infomation')));
		$fieldset_2 = $form->addFieldset('mobapp_design', array('legend'=>Mage::helper('mobapp')->__('App Design')));
		$fieldset_3 = $form->addFieldset('mobapp_appstore', array('legend'=>Mage::helper('mobapp')->__('Appstore Release Information')));

		$generalinfo = array();
		if(Mage::registry('mobapp_generalinfo'))
			$generalinfo = Mage::registry('mobapp_generalinfo');

		$fieldset->addField('name', 'text', array(
			'label'     => Mage::helper('mobapp')->__('App Name'),
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'name',
		));

		$fieldset->addField('platform', 'multiselect', array(
			'label'     => Mage::helper('mobapp')->__('Platform'),
			'name'      => 'platform',
			'required'  => true,
			'values'    => array(
				array(
					'value'     => 'ios',
					'label'     => Mage::helper('mobapp')->__('IOS'),
				),
				array(
					'value'     => 'android',
					'label'     => Mage::helper('mobapp')->__('Android'),
				),
			),
		));

		$websites = Mage::getModel('core/website')->getCollection();
		$allgroups = Mage::getModel('core/store_group')->getCollection();
		$groups = array();
		foreach ($websites as $website) {
			$values = array();
			foreach ($allgroups as $group) {
				if ($group->getWebsiteId() == $website->getId()) {
					$values[] = array('label'=>$group->getName(),'value'=>$group->getId());
				}
			}
			$groups[] = array('label'=>$website->getName(),'value'=>$values);
		}
		$fieldset->addField('store', 'select', array(
			'name'      => 'store',
			'label'     => Mage::helper('mobapp')->__('Store Support'),
			'values'    => $groups,
			'required'  => true,
		))->setAfterElementHtml("<a href='".$generalinfo['link_youtube_choose_store']."' target='_blank'>Click here for instruction</a>");

		$fieldset->addField('api_consumer_key', 'text', array(
			'label'     => Mage::helper('mobapp')->__('API Consumer Key'),
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'api_consumer_key',
		))->setAfterElementHtml("<a href='".$generalinfo['link_youtube_consumer_key']."' target='_blank'>Click here for instruction</a>");

		$fieldset->addField('api_consumer_secret', 'text', array(
			'label'     => Mage::helper('mobapp')->__('API Consumer Secret'),
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'api_consumer_secret',
		))->setAfterElementHtml("<a href='".$generalinfo['link_youtube_consumer_secret']."' target='_blank'>Click here for instruction</a>");

		$fieldset->addField('contact_email', 'text', array(
			'label'     => Mage::helper('mobapp')->__('Contact Email'),
			'class'     => 'required-entry validate-email',
			'required'  => true,
			'name'      => 'contact_email',
		));

		$fieldset->addField('mobile_number', 'text', array(
			'label'     => Mage::helper('mobapp')->__('Mobile Number'),
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'mobile_number',
		));

		$fieldset_2->addType('mobapp_custom_design','EM_Mobapp_Lib_Varien_Data_Form_Element_Design');
        $fieldset_2->addField('custom_design', 'mobapp_custom_design', array(
            'name'          => 'custom_design',
        ));

		$fieldset_3->addField('release', 'radios', array(
			'name'      => 'release',
			'class'		=> 'custom_imput_release',
			'values' => array(
				array('value'=>'1','label'=> Mage::helper('mobapp')->__('Release with your Apple Deverloper Account: Please follow this link: <a href="'.$generalinfo['link_release_appstore'].'" target="_blank" alt="" >'.$generalinfo['link_release_appstore'].'</a>')),
				array('value'=>'2','label'=> Mage::helper('mobapp')->__('Release 2')),
			),
		));
		
		$fieldset_3->addField('app_name', 'text', array(
			'label'     => Mage::helper('mobapp')->__('Appstore Name'),
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'app_name',
		));

		$fieldset_3->addField('description', 'editor', array(
			'name'      => 'description',
			'label'     => Mage::helper('mobapp')->__('Description (max 4000)'),
			'style'     => 'width:400px; height:150px;',
			'wysiwyg'   => false,
			'required'  => false,
		));

		$fieldset_3->addField('keyword', 'text', array(
			'label'     => Mage::helper('mobapp')->__('Keywords (max 100)'),
			'maxlength' => 100,
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'keyword',
		));

		$fieldset_3->addField('sup_url', 'text', array(
			'label'     => Mage::helper('mobapp')->__('Suport URL'),
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'sup_url',
		));

		$fieldset_3->addField('marketing_url', 'text', array(
			'label'     => Mage::helper('mobapp')->__('Marketing URL'),
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'marketing_url',
		));

		$fieldset_3->addField('firstname', 'text', array(
			'label'     => Mage::helper('mobapp')->__('Firstname'),
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'firstname',
		));

		$fieldset_3->addField('lastname', 'text', array(
			'label'     => Mage::helper('mobapp')->__('Lastname'),
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'lastname',
		));
		
		$fieldset_3->addField('app_email', 'text', array(
			'label'     => Mage::helper('mobapp')->__('Appstore Email'),
			'class'     => 'required-entry validate-email',
			'required'  => true,
			'name'      => 'app_email',
		));
		
		$fieldset_3->addField('app_number', 'text', array(
			'label'     => Mage::helper('mobapp')->__('Appstore Mobile Number'),
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'app_number',
		));
		
		$form->addField('register', 'button', array(
			'class'  => 'btn_custom_register',
			'onclick'=> 'editForm.submit();'
        ));

		if ( Mage::getSingleton('adminhtml/session')->getMobappData() ){
			$form->setValues(Mage::getSingleton('adminhtml/session')->getMobappData());
			Mage::getSingleton('adminhtml/session')->setMobappData(null);
		} elseif ( Mage::registry('mobapp_data') ) {
			$form->setValues(Mage::registry('mobapp_data')->getData());
		}

		return parent::_prepareForm();
	}

}