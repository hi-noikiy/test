<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_CommentOnReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */
class MageWorkshop_CommentOnReview_Block_Adminhtml_Comment_Edit_Form extends MageWorkshop_DetailedReview_Block_Adminhtml_Review_Edit_Form
{
    /**
     * @inherit
     */
    protected function _prepareForm()
    {
        /** @var MageWorkshop_DetailedReview_Model_Review $review */
        $review = Mage::registry('comment_data');
        /** @var Mage_Review_Model_Review $mainReview */
        $mainReview = Mage::getModel('review/review')->load($review->getEntityPkValue());
        $storeId = $mainReview->getStoreId();
        $store = Mage::app()->getStore($storeId);

        if (!Mage::getStoreConfig(MageWorkshop_CommentOnReview_Helper_Data::COMMENT_XML_PATH_MODULE_ENABLE, $storeId)) {
            Mage::register('review_data', $review);
            return Mage_Adminhtml_Block_Review_Edit_Form::_prepareForm();
        }

        /**@var MageWorkshop_CommentOnReview_Helper_Data $commentOnReviewHelper*/
        $commentOnReviewHelper = Mage::helper('mageworkshop_commentonreview');

        /** @var Mage_Review_Helper_Data $reviewHelper */
        $reviewHelper = Mage::helper('review');

        $productCollection = Mage::getResourceModel('catalog/product_collection');
        $productCollection->setStoreId($storeId)
            ->addFieldToFilter('entity_id', $mainReview->getEntityPkValue());
        /** _addUrlRewrite() of the product collection does not work with the store that was set in the collection */
        $productCollection->addUrlRewrite();
        /** @var Mage_Catalog_Model_Product $product */
        $product = $productCollection->getFirstItem();
        $urlModel = $product->getUrlModel()->getUrlInstance();
        $urlModel->setFragment('rw_' . $review->getEntityPkValue());
        $reviewUrl = $product->getUrlInStore(array('_store' => $store));

        $statuses = Mage::getModel('review/review')
            ->getStatusCollection()
            ->load()
            ->toOptionArray();

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save', array('id' => (int) $this->getRequest()->getParam('id'), 'ret' => Mage::registry('ret'))),
            'method'    => 'post',
            'enctype'	=> 'multipart/form-data'
        ));

        $fieldSet = $form->addFieldset('review_details', array('legend' => $commentOnReviewHelper->__('Comment Details')));

        $fieldSet->addType('datetime', 'MageWorkshop_DetailedReview_Block_Adminhtml_Renderer_Datetime');

        $reviewLink = sprintf('<a href="%s" onclick="this.target=\'blank\'">%s</a>', $reviewUrl, $review->getTitle());

        $fieldSet->addField('product_name', 'note', array(
            'label'     => Mage::helper('review')->__('Review'),
            'text'      => $reviewLink
        ));

        $customer = Mage::getModel('customer/customer');
        if ($review->getCustomerId()) {
            /** @var Mage_Customer_Model_Customer $customer */
            $customer->load($review->getCustomerId());
        }

        if ($customer->getId()) {
            $customerLink = sprintf(
                    '<a href="%s" onclick="this.target=\'blank\'">%s %s</a> <a href="mailto:%4$s">(%s)</a>',
                    $this->getUrl('*/customer/edit', array('id' => $customer->getId(), 'active_tab'=>'review')),
                    $this->escapeHtml($customer->getFirstname()),
                    $this->escapeHtml($customer->getLastname()),
                    $this->escapeHtml($customer->getEmail())
                );
            $customerText = $reviewHelper->__($customerLink);
        } else {
            $customerText = $reviewHelper->__('Guest');
        }

        $fieldSet->addField('customer', 'note', array(
            'label'     => $reviewHelper->__('Posted By'),
            'text'      => $customerText,
        ));

        $fieldSet->addField('status_id', 'select', array(
            'label'     => $reviewHelper->__('Status'),
            'required'  => true,
            'name'      => 'status_id',
            'values'    => $reviewHelper->translateArray($statuses),
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldSet->addField('select_stores', 'multiselect', array(
                'label'     => $reviewHelper->__('Visible In'),
                'required'  => true,
                'name'      => 'stores[]',
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm()
            ));
            $review->setSelectStores($review->getStores());
        }
        else {
            $fieldSet->addField('select_stores', 'hidden', array(
                'name'      => 'stores[]',
                'value'     => Mage::app()->getStore(true)->getId()
            ));
            $review->setSelectStores(Mage::app()->getStore(true)->getId());
        }

        $fieldSet->addField('nickname', 'text', array(
            'label'     => $reviewHelper->__('Nickname'),
            'required'  => true,
            'name'      => 'nickname'
        ));

        $fieldSet->addField('title', 'text', array(
            'label'     => $commentOnReviewHelper->__('Review Title'),
            'required'  => true,
            'name'      => 'title',
        ));

        $fieldSet->addField('detail', 'textarea', array(
            'label'     => $commentOnReviewHelper->__('Overall Comment'),
            'required'  => true,
            'name'      => 'detail',
            'style'     => 'width:700px; height:24em;',
        ));

        if (Mage::getStoreConfig('detailedreview/show_review_info_settings/allow_response', $storeId)) {
            $fieldSet->addField('response', 'textarea', array(
                'label'     => $reviewHelper->__('Administration Response'),
                'name'      => 'response',
                'style'     => 'width:700px; height:24em;',
            ));
        }

        $fieldSet->addField('created_at', 'datetime', array(
            'label'		=> $reviewHelper->__('Created at'),
            'required'	=> false,
            'name'		=> 'created_at',
            'time'		=> true,
            'format' => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'style' => 'width: 140px;'
        ));

        $form->setUseContainer(true);
        $form->setValues($review->getData());
        Mage::dispatchEvent('detailedreview_adminhtml_review_edit_prepare_form', array('form' => $form));
        $this->setForm($form);
        return $this;
    }
}
