<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplus\Block\Adminhtml\Payment\Review;

/**
 * Grid Grid
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_objectId = 'payment_id';
        $this->_blockGroup = 'Magestore_Affiliateplus';
        $this->_controller = 'adminhtml_payment_review';

        parent::_construct();

        $this->removeButton('reset');
        $this->removeButton('delete');

        $this->buttonList->update('save', 'label', __('Pay'));
        $backUrl = $this->getUrl('affiliateplusadmin/payment/cancelReview', [
            'payment_id' => $this->getRequest()->getParam('payment_id'),
            'store' => $this->getRequest()->getParam('store')
        ]
    );
            $this->_formScripts[] = "
                 function backToEdit(){
                editForm.submit('$backUrl');
               }

              ";
    }

}
