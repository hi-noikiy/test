<?php

class MageWorkshop_ImportExportReview_Block_Adminhtml_Sync_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function _prepareForm()
    {
        /** @var MageWorkshop_ImportExportReview_Model_Sync $sync */
        $sync = Mage::registry('current_drie_sync');

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl(
                'adminhtml/drie_sync/edit',
                array(
                    '_current' => true,
                    'continue' => 0,
                )
            ),
            'method' => 'post',
        ));

        $fieldset = $form->addFieldset(
            'general',
            array(
                'legend' => $this->_getHelper()->__('Sync Store Configuration')
            )
        );

        $fieldset->addType('button', 'MageWorkshop_ImportExportReview_Block_Adminhtml_Sync_Edit_Renderer_Button');

        $fieldset->addField('store_name', 'text',
            array(
                'label' => $this->_getHelper()->__('Store Name'),
                'class'    => 'required-entry',
                'required' => true,
                'name'     => 'syncData[store_name]'
            )
        );

        $fieldset->addField('store_identity', 'text',
            array(
                'label' => $this->_getHelper()->__('Store Identity'),
                'class'    => 'required-entry',
                'required' => true,
                'name'     => 'syncData[store_identity]'
            )
        );

        $fieldset->addField('store_url', 'text',
            array(
                'label' => $this->_getHelper()->__('Store Base URL'),
                'class'    => 'required-entry',
                'required' => true,
                'name'     => 'syncData[store_url]'
            )
        );

        $fieldset->addField('api_username', 'text',
            array(
                'label' => $this->_getHelper()->__('Store API Username'),
                'class'    => 'required-entry',
                'required' => true,
                'name'     => 'syncData[api_username]'
            )
        );

        $fieldset->addField('api_key', 'password',
            array(
                'label' => $this->_getHelper()->__('Store API Key'),
                'class'    => 'required-entry',
                'required' => true,
                'name'     => 'syncData[api_key]'
            )
        );

        if ($syncId = $sync->getId()) {
            $syncNowUrl    = $this->getUrl('*/*/syncNow', array('id' => $syncId));
            $syncSkuNowUrl = $this->getUrl('*/*/syncSkuNow', array('id' => $syncId));
            $fieldset->addField('sync_now', 'button', array(
                'label' => $this->_getHelper()->__('Sync Now'),
                'onclick' => 'syncNow()',
                'after_element_html' =>
                    '<script type="text/javascript">' .
                        "var syncNow = function() {
                            window.location = '$syncNowUrl';
                        }" .
                    '</script>',
                'note' => $this->_getHelper()->__('
                    3000 is the max number of reviews that can be pulled from the current sync store. Please check the history after manual sync to make sure that no reviews are left.
                ')
            ));

            $fieldset->addField('sync_sku_list', 'textarea', array(
                'label' => $this->_getHelper()->__('SKU List'),
                'name' => 'sync_sku_list',
                'required' => false,
                'note' => $this->_getHelper()->__('List of SKUs to be pulled comma separated')
            ));

            $fieldset->addField('sync_sku_now', 'button', array(
                'label' => $this->_getHelper()->__('Sync SKU List Now'),
                'onclick' => 'syncSkuNow()',
                'after_element_html' =>
                    '<script type="text/javascript">' .
                        "var syncSkuNow = function() {
                            var url = '$syncSkuNowUrl';
                            var skuList = $('sync_sku_list').getValue();
                            url = url + 'sku_list/' + skuList;
                            window.location = url;
                        }" .
                    '</script>'
            ));
        }

        $data = $sync->getData();
        $form->setValues($data);
        $this->setForm($form);
        $form->setUseContainer(true);

        return parent::_prepareForm();
    }

    /**
     * @return MageWorkshop_ImportExportReview_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('mageworkshop_importexportreview');
    }
}