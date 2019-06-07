<?php

class Admitad_Tracking_Block_Adminhtml_Settings_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $userId = Mage::getSingleton('admin/session')->getUser()->getId();
        $user = Mage::getModel('admin/user')
            ->load($userId);
        $user->unsetData('password');

        $clientId = Mage::getStoreConfig(
            'admitadtracking/general/client_id',
            Mage::app()->getStore()
        );

        $clientSecret = Mage::getStoreConfig(
            'admitadtracking/general/client_secret',
            Mage::app()->getStore()
        );

        $campaignCode = Mage::getStoreConfig(
            'admitadtracking/general/campaign_code',
            Mage::app()->getStore()
        );

        $postbackKey = Mage::getStoreConfig(
            'admitadtracking/general/postback_key',
            Mage::app()->getStore()
        );

        $paramName = Mage::getStoreConfig(
            'admitadtracking/general/param_name',
            Mage::app()->getStore()
        );

        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('account_info', array('legend' => Mage::helper('tracking')->__('Account Information')));

        $fieldset->addField(
            'param_name', 'text', array(
                'name'     => 'param_name',
                'label'    => Mage::helper('tracking')->__('Param Name'),
                'title'    => Mage::helper('tracking')->__('Param Name'),
                'required' => true,
                'value'    => $paramName,
            )
        );

        if (!$clientId && !$clientSecret) {
            $fieldset->addField(
                'client_id', 'text', array(
                    'name'     => 'client_id',
                    'label'    => Mage::helper('tracking')->__('Client ID'),
                    'title'    => Mage::helper('tracking')->__('Client ID'),
                    'required' => true,
                )
            );
            $fieldset->addField(
                'client_secret', 'text', array(
                    'name'     => 'client_secret',
                    'label'    => Mage::helper('tracking')->__('Client Secret'),
                    'title'    => Mage::helper('tracking')->__('Client Secret'),
                    'required' => true,
                )
            );
            $form->setValues(
                array(
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                )
            );
        } else {
            /** @var Admitad_Tracking_Helper_Api $api */
            $api = Mage::helper('tracking/api');
            $accessToken = Mage::getStoreConfig(
                'admitadtracking/general/access_token',
                Mage::app()->getStore()
            );

            $api->setAccessToken($accessToken);

            $fieldset->addField(
                'campaign_code', 'text', array(
                    'name'     => 'campaign_code',
                    'label'    => Mage::helper('tracking')->__('Campaign Code'),
                    'title'    => Mage::helper('tracking')->__('Campaign Code'),
                    'disabled' => true,
                )
            );
            $fieldset->addField(
                'postback_key', 'text', array(
                    'name'     => 'postback_key',
                    'label'    => Mage::helper('tracking')->__('Postback Key'),
                    'title'    => Mage::helper('tracking')->__('Postback Key'),
                    'disabled' => true,
                )
            );

            $configuration = json_decode(
                Mage::getStoreConfig(
                    'admitadtracking/general/configuration',
                    Mage::app()->getStore()
                ), true
            );

            $info = $api->getAdvertiserInfo();

            if (!empty($info['actions'])) {
                foreach ($info['actions'] as $action) {
                    if (empty($action['tariffs'])) {
                        continue;
                    }

                    $fieldset = $form->addFieldset('action_' . $action['action_id'], array('legend' => $action['action_name']));

                    $actionConfig = isset($configuration[1][$action['action_code']]) ? $configuration[1][$action['action_code']] : array(
                        'type'    => null,
                        'tariffs' => array(),
                    );

                    $fieldset->addField(
                        'actions[1][' . $action['action_code'] . '][type]', 'select', array(
                        'label'  => Mage::helper('tracking')->__('Action'),
                        'name'   => 'actions[1][' . $action['action_code'] . '][type]',
                        'values' => array(
                            0 => Mage::helper('tracking')->__('Inactive'),
                            1 => Mage::helper('tracking')->__('Sale'),
                        ),
                        'value'  => $actionConfig['type'],
                        )
                    );

                    foreach ($action['tariffs'] as $tariff) {
                        $tariffConfig = isset($actionConfig['tariffs'][$tariff['tariff_code']]) ? $actionConfig['tariffs'][$tariff['tariff_code']] : array(
                            'categories' => array(),
                        );

                        $fieldset->addField(
                            'actions[1][' . $action['action_code'] . '][tariffs][' . $tariff['tariff_code'] . '][categories]', 'multiselect', array(
                            'label'  => $tariff['tariff_name'],
                            'name'   => 'actions[1][' . $action['action_code'] . '][tariffs][' . $tariff['tariff_code'] . '][categories]',
                            'values' => $this->getCategoriesTree(),
                            'value'  => array_values($tariffConfig['categories']),
                            )
                        );
                    }
                }
            }

            $form->addValues(
                array(
                'campaign_code' => $campaignCode,
                'postback_key'  => $postbackKey,
                )
            );
        }


        $form->setAction($this->getUrl('*/admitad_settings/save'));
        $form->setMethod('post');
        $form->setUseContainer(true);
        $form->setId('edit_form');

        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function getCategoriesTree()
    {
        // Get category collection
        $categories = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSort('path', 'asc')
            ->addFieldToFilter('is_active', array('eq' => '1'))
            ->load()
            ->toArray();

        // Arrange categories in required array
        $categoryList = array();
        foreach ($categories as $catId => $category) {
            if (isset($category['name'])) {
                $categoryList[] = array(
                    'label' => str_repeat(' . ', $category['level'] - 1) . $category['name'],
                    'level' => $category['level'],
                    'value' => $catId,
                );
            }
        }

        return $categoryList;
    }

}