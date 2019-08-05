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
namespace Magestore\Affiliateplus\Block\Adminhtml\Payment;

/**
 * Grid Grid
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Edit constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data
    )
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_objectId = 'payment_id';
        $this->_blockGroup = 'Magestore_Affiliateplus';
        $this->_controller = 'adminhtml_payment';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Payment'));
        $this->buttonList->update('delete', 'label', __('Delete'));
        $this->removeButton('reset');
        $this->removeButton('save');

        $data = $this->getRegistryModel();

        if (isset($data['status']) && ($data['status'] == 3 || $data['status'] == 4)) {
            $this->removeButton('delete');
        } else {
            if (!$this->getRequest()->getParam('payment_id')) {
                $this->buttonList->add(
                    'saveandcontinue',
                    [
                        'label' => __('Save and Pay Manually'),
                        'class' => 'save',
                        'data_attribute' => [
                            'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                        ],
                    ],
                    -200
                );
            } else {
                $this->removeButton('delete');
            }

            if (isset($data['status']) && $data['status'] && $data['status'] < 3) {
                $this->buttonList->add(
                    'cancel-payment',
                    [
                        'label' => __('Cancel'),
                        'class' => 'cancel',
                        'data_attribute' => [
                            'mage-init' => [
                                'button' => ['event' => 'cancelPayment', 'target' => '#edit_form'],
                            ],
                        ],
                    ],
                    -200
                );

                $this->buttonList->add(
                    'complete-payment-manually',
                    [
                        'label' => __('Complete Manually'),
                        'class' => 'save',
                        'data_attribute' => [
                            'mage-init' => [
                                'button' => ['event' => 'completePayment', 'target' => '#edit_form'],
                            ],
                        ],
                    ],
                    -150
                );
            }

            $reviewUrl = $this->getUrl('affiliateplusadmin/payment/review',
                [
                    'payment_id' => $this->getRequest()->getParam('payment_id'),
                    'store' => $this->getRequest()->getParam('store')
                ]
            );

            $cancelurl = $this->getUrl('affiliateplusadmin/payment/cancelPayment',
                [
                    'payment_id' => $this->getRequest()->getParam('payment_id'),
                    'store' => $this->getRequest()->getParam('store')
                ]
            );

            $confirmText = __('Are you sure?');

            $this->_formScripts[] = "

               function saveAndPayManual() {

                    require(
                        [
                            'jquery'
                        ],
                         function(jQuery){
                                jQuery('#edit_form').attr('action','$reviewUrl') .attr('method','post')
                         jQuery('#edit_form').submit();
                         });

                }
                function saveAndPayNow() {
                    require(
                    [
                        'jquery'
                    ],
                     function(jQuery){
                     jQuery('#edit_form').attr('action','$reviewUrl'+'masspayout/true') .attr('method','post')
                         jQuery('#edit_form').submit();

                     });
                }
                  require(
                    [
                        'jquery',
                        'prototype'
                    ],
                     function(jQuery){
                     jQuery('#cancel-payment').click(function(){
                         if (confirm('$confirmText')){
                                setLocation('$cancelurl');
                            }
                        });
                     jQuery('#complete-payment-manually').click(function(){
                         if (confirm('$confirmText')){
                               $('status').value = 3;
                               $('edit_form').submit();
                            }
                        });
                     });
                function saveAndContinueEdit(){
                 require(
                    [
                        'jquery'
                    ],
                     function(jQuery){
                     jQuery('#edit_form').attr('action',jQuery('#edit_form').action+'back/edit/') .attr('method','post')
                         jQuery('#edit_form').submit();

                     });
                }

              ";


        }
        $this->_eventManager->dispatch('affiliateplus_adminhtml_payment_edit_form_action',
            [
                'form'  => $this,
                'data'  => $data
            ]
        );

        return;


    }

    /**
     * get registry model.
     *
     * @return \Magento\Framework\Model\AbstractModel|null
     */
    public function getRegistryModel()
    {
        return $this->_coreRegistry->registry('payment_data');
    }

    /**
     * Get edit form container header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->getRegistryModel()->getId()) {
            return __("Edit Withdrawal '%1'", $this->escapeHtml($this->getRegistryModel()->getAccountName()));
        } else {
            return __('Create New Withdrawal');
        }
    }
}
