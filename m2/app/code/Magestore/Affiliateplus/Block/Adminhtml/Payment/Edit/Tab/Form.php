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
namespace Magestore\Affiliateplus\Block\Adminhtml\Payment\Edit\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Form extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{
    /**
     * @var \Magestore\Affiliateplus\Helper\Payment
     */
    protected $_helperPayment;

    /**
     * @var \Magestore\Affiliateplus\Helper\Payment\Tax
     */
    protected $_helperTaxPayment;
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_sessionQuote;
    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magestore\Affiliateplus\Helper\Payment $helperPayment
     * @param \Magestore\Affiliateplus\Helper\Payment\Tax $helperTaxPayment
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magestore\Affiliateplus\Helper\Payment $helperPayment,
        \Magestore\Affiliateplus\Helper\Payment\Tax $helperTaxPayment,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        PriceCurrencyInterface $priceCurrency,
        array $data = array()
    )
    {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_helperPayment = $helperPayment;
        $this->_helperTaxPayment = $helperTaxPayment;
        $this->_sessionQuote = $sessionQuote;
        $this->_priceCurrency = $priceCurrency;
    }

    /**
     * Retrieve store model object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_getSession()->getStore();
    }
    /**
     * Retrieve quote session object
     *
     * @return \Magento\Backend\Model\Session\Quote
     */
    protected function _getSession()
    {
        return $this->_sessionQuote;
    }
    /**
     * @param $value
     * @return mixed
     */
    public function formatPrice($value)
    {
        return $this->_priceCurrency->format(
            $value,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getStore()
        );
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
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('General information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('General information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
    /**
     * @return \Magento\Framework\Phrase
     */
    public function getPageTitle()
    {
        return $this->getRegistryModel()->getId()
            ? __("Edit Payment of '%1'", $this->escapeHtml($this->getRegistryModel()->getData('account_name'))) : __('Add New Payment');
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('page.title')->setPageTitle($this->getPageTitle());
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();


        if ($this->_session->getPaymentData()){
            $data = $this->_session->getPaymentData();

            if (!isset($data['payment_method']) || !$data['payment_method']) {
                $data['payment_method'] = 'paypal';
            }

            $data['temp_payment_method'] = $data['payment_method'];
            $data['affiliate_account'] = $data['account_email'];
            $this->_session->setPaymentData(null);
        } elseif ($this->getRegistryModel()) {
            $data = $this->getRegistryModel()->getData();

            if (!isset($data['payment_method']) || !$data['payment_method']) {
                $data['payment_method'] = 'paypal';
            }

            $data['temp_payment_method'] = $data['payment_method'];
            $data['affiliate_account'] = $data['account_email'];
        }

        if (!isset($data['payment_method']) || !$data['payment_method']) {
            $data['payment_method'] = 'paypal';
        }

        $form->setPaymentData($data);
        $form->setFormValues($data);


        $fieldset = $form->addFieldset('payment_data', ['legend' => __('Withdrawal Information')]);

        $fieldset->addField('account_id', 'hidden', [
            'name' => 'account_id',
        ]
        );

        $fieldset->addField('account_email', 'hidden', [
            'name' => 'account_email',
        ]
        );



        $fieldset->addField(
            'affiliate_account',
            'link',
            [
                'name' => 'affiliate_account',
                'label' => __('Affiliate Account'),
                'title' => __('Affiliate Account'),
                'href' => $this->getUrl('affiliateplusadmin/account/edit', ['_current' => true, 'account_id' => $data['account_id']]),
                'title' => __('View Affiliate Account Details'),
            ]
        );

        $fieldset->addField('account_name', 'hidden', [
            'name' => 'account_name',
        ]);
        $storeId = $this->getRequest()->getParam('store');
        if (isset($data['account_balance'])) {
            $fieldset->addField(
                'account_balance',
                'note',
                [
                    'label' => __('Balance'),
                    'text' => $this->formatPrice($data['account_balance'])
                ]
            );
        }

        $whoPayFees = $this->_helperPayment->getConfig('affiliateplus/payment/who_pay_fees', $storeId);
        if ($whoPayFees == 'payer'){
            $note = __('Not including fee');
        }else{
            $note = __('Including fee');
        }

        $js = '';
        if (isset($data['account_balance'])) {
            $js .='<script type="text/javascript">
                var request_amount_max =' . round($data['account_balance'], 2) . ';
                  if(request_amount_max == "1e-12"){
                    request_amount_max=0;
                }
                function checkAmountBalance(el){
                    el.value = parseFloat(el.value);
                    if (el.value < 0) el.value = 0;
                    else if (el.value > request_amount_max || el.value == \'NaN\') el.value = request_amount_max;
                }
                </script>';
        }

        $params = [
            'label' => __('Withdrawal Amount'),
            'name' => 'amount',
            'class' => 'required-entry',
            'required' => true,
            'onchange' => 'checkAmountBalance(this)',
            'note' => $note,
            'after_element_html' => $js,
        ];

        if (isset($data['payment_method']) && $data['payment_method'] == 'credit') {
            unset($params['note']);
        }

        if ($this->getRequest()->getParam('id'))
            $params['readonly'] = 'readonly';

        if (isset($data['tax_amount']) && $data['tax_amount']) {
            $taxParams = $params;
            unset($taxParams['after_element_html']);
            if (isset($taxParams['note']))
                unset($taxParams['note']);

            $taxParams['name'] = 'amount_incl_tax';
            $fieldset->addField('amount_incl_tax', 'text', $taxParams);

            $taxParams['name'] = 'tax_amount';
            $taxParams['label'] = __('Tax');
            $fieldset->addField('tax_amount', 'text', $taxParams);

            $params['label'] = __('Amount (Excl. Tax)');
        }

        if (isset($data['affiliateplus_account']) && $data['affiliateplus_account']) {
            $rate = $this->_helperTaxPayment->getTaxRate($data['affiliateplus_account']);
            if ($rate > 0) {
                $taxParams = $params;

                $taxParams['name'] = 'amount_incl_tax';
                $taxParams['note'] = __('Including %1 tax', round($rate, 2) . '%');
                $taxParams['after_element_html'] = '
                    <script type="text/javascript">
                        var request_amount_max =' . round($data['account_balance'], 2) . ';
                        function checkAmountBalance(el){
                            el.value = parseFloat(el.value);
                            if (el.value < 0) el.value = 0;
                            else if (el.value > request_amount_max || el.value == \'NaN\') el.value = request_amount_max;
                            var taxRate = ' . $rate . ';
                            var taxAmount = el.value * taxRate / (100 + taxRate);
                            taxAmount = Math.round(taxAmount * 100) / 100;
                            $(\'amount\').value = el.value - taxAmount;
                        }
                        function changeRealAmount(el) {
                            var taxRate = ' . $rate . ';
                            var maxRequestAmount = request_amount_max * taxRate / (100 + taxRate);
                            maxRequestAmount = Math.round(maxRequestAmount * 100) / 100;

                            el.value = parseFloat(el.value);
                            if (el.value < 0) el.value = 0;
                            else if (el.value > maxRequestAmount || el.value == \'NaN\') el.value = maxRequestAmount;

                            var taxAmount = el.value * taxRate / 100;
                            var totalAmount = parseFloat(el.value) + parseFloat(taxAmount);
                            totalAmount = Math.round(totalAmount * 100) / 100;
                            $(\'amount_incl_tax\').value = totalAmount;
                        }
                    </script>
                ';
                $fieldset->addField('amount_incl_tax', 'text', $taxParams);
                $params['label'] = __('Amount (Excl. Tax)');
                $params['onchange'] = 'changeRealAmount(this)';
                unset($params['after_element_html']);
            }
        }
        $fieldset->addField('amount', 'text', $params);

        $paymentMethods = $this->_helperPayment->getAvailablePayment();

        if (isset($data['payment_method']) && $data['payment_method'] == 'credit') {
            $fieldset->addField(
                'credit_refund_amount',
                'text',
                [
                    'name' => 'credit_refund_amount',
                    'label' => __('Refunded'),
                    'readonly' => true,
                ]
            );

            $fieldset->addField(
                'order_increment_id',
                'note',
                [
                    'label' => __('Pay for Order'),
                    'text' => '<a title="' . __('View Order')
                        . '" href="' . $this->getUrl('sales/order/view', ['order_id' => $data['credit_order_id']])
                        . '">#'
                        . $data['credit_order_increment_id'] . '</a>'
                ]
            );
        } else if (!$this->_isActivePaymentPlugin()) {
            $fieldset->addField(
                'payment_method',
                'hidden',
                [
                    'name' => 'payment_method',
                    'value' => 'paypal',
                ]
            );

            $feeParams = array(
                'label' => __('Fee'),
                'name' => 'fee',
            );
            if (isset($data['status']) && $data['status'] >= 3) {
                $feeParams['disabled'] = true;
            }
            $fieldset->addField('fee', 'text', $feeParams);

            $fieldset->addField(
                'paypal_email',
                'text',
                [
                    'label' => __('Paypal Email'),
                    'name' => 'paypal_email',
                    'readonly' => 'readonly',
                    'class' => 'required-entry',
                    'required' => true,
                ]
            );
            if (isset($data['status']) && $data['status'] < 3) {
                $fieldset->addField(
                    'pay_now',
                    'note',
                    [
                        'text' => '<button type="button" class="scalable save" onclick="saveAndPayNow()"><span>' . __('Pay Now') . '</span></button>',
                        'note' => __('Automatically pay out for Affiliate through the paygate')
                    ]
                );
            }

            if ((isset($data['transaction_id']) && $data['transaction_id']) || (isset($data['status']) && $data['status'] < 3)) {
                $fieldset->addField(
                    'transaction_id',
                    'text',
                    [
                        'label' => __('Transaction ID'),
                        'name' => 'transaction_id',
                    ]
                );
            }
        }else {
            $params = [
                'label' => __('Payment Method'),
                'name' => 'payment_method',
                'required' => true,
                'values' => $this->_helperPayment->getPaymentOption(),
                'onclick' => 'changePaymentMethod(this);',
            ];
            $id = $this->getRequest()->getParam('id', null);
            $params = [
                'label' => __('Payment Method'),
                'name' => 'payment_method',
                'required' => true,
                'values' => $this->_helperPayment->getPaymentOption($id),
                'onclick' => 'changePaymentMethod(this);',
            ];

            if ((isset($data['status']) && $data['status'] >= 3) || (isset($data['is_request']) && $data['is_request'] == 1)) {
                $params['disabled'] = true;
                $fieldset->addField('temp_payment_method', 'select', $params);

                $fieldset->addField('payment_method', 'hidden', [
                    'name' => 'payment_method',
                ]
                );
            } else {
                if ($this->getRequest()->getParam('id')) {
                    $params['disabled'] = true;
                    $fieldset->addField('temp_payment_method', 'select', $params);

                    $fieldset->addField('payment_method', 'hidden', [
                        'name' => 'payment_method',
                    ]
                    );
                } else {
                    $fieldset->addField('payment_method', 'select', $params);
                }
            }

            if (isset($data['status']) && $data['status'] < 3) {
                $fieldset->addField(
                    'pay_now',
                    'note',
                    [
                        'text' => '<button type="button" class="scalable save" onclick="saveAndPayNow()"><span>' . __('Pay Now') . '</span></button>',
                        'note' => __('Automatic Payout for Affiliate through Paygate')
                    ]
                );
            }

            $feeParams = array(
                'label' => __('Fee'),
                'name' => 'fee',
            );
            if (isset($data['status']) && $data['status'] >= 3) {
                $feeParams['disabled'] = true;
            }
            $fieldset->addField('fee', 'text', $feeParams);

            $form->addFieldset('payment_method_data', array('legend' => __('Payment Method Information')));

            foreach ($paymentMethods as $code => $paymentMethod) {
                $paymentFieldset = $form->addFieldset("payment_fieldset_$code", array());

                $this->_eventManager->dispatch("affiliateplus_adminhtml_payment_method_form_$code",
                    [
                        'form' => $form,
                        'fieldset' => $paymentFieldset,
                    ]
                );

                if ($code == 'paypal') {
                    $readOnly = (isset($data['status']) && $data['status'] >= 3);
                    $paymentFieldset->addField(
                        'paypal_email',
                        'text',
                        [
                            'label' => __('Paypal Email'),
                            'name' => 'paypal_email',
                            'readonly' => 'readonly',
                            'class' => 'required-entry',
                            'required' => true,
                            'note' => $readOnly ? null : __('You can change this email address on the Edit Account page.'),
                        ]
                    );

                    $params = [
                        'label' => __('Transaction ID'),
                        'name' => 'transaction_id',
                    ];
                    if ($readOnly)
                        $params['readonly'] = 'readonly';
                    if (!$readOnly || (isset($data['paypal_transaction_id']) && $data['paypal_transaction_id'])) {
                        $paymentFieldset->addField('paypal_transaction_id', 'text', $params);
                    }
                }
            }
            $fieldset->addField(
                'javascript',
                'hidden',
                [
                    'after_element_html' => '
					<script type="text/javascript">
						function changePaymentMethod(el){
						 require(["prototype"], function(){
						    var payment_fieldset = "payment_fieldset_" + el.value;
							$$("div.fieldset").each(function(e){
								if (e.id.startsWith("payment_fieldset_")){
									e.hide();
									var i = 0;
									while(e.down(".required-entry",i) != undefined)
										e.down(".required-entry",i++).disabled = true;
								}if (e.id == payment_fieldset){
									var i = 0;
									while(e.down(".required-entry",i) != undefined)
										e.down(".required-entry",i++).disabled = false;
									e.show();
								}
							});
                            if (el.value == "paypal" || el.value == "moneybooker") {
                                $("pay_now").parentNode.parentNode.show();
                            } else {
                                $("pay_now").parentNode.parentNode.hide();
                            }
						 });
						}

                    	require(["prototype",
                            ], function  () {
                              Event.observe(window, "load", function(){
                              changePaymentMethod($("payment_method"))
                              });

                    });
					</script>
					',
                ]
            );
        }

        //event to add more field
        $this->_eventManager->dispatch('affiliateplus_adminhtml_add_field_payment_form', array('fieldset' => $fieldset, 'form' => $form));

        $status = \Magestore\Affiliateplus\Model\Payment::getPaymentStatus();

        $id = $this->getRequest()->getParam('payment_id');

        if ($id) {
            $fieldset->addField('status_note', 'note', [
                'label' => __('Status'),
                'text' => '<strong>' . $status[$data['status']] . '</strong>'
            ]
        );
            $fieldset->addField('status', 'hidden', [
                'name' => 'status',
            ]
        );

            $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
            $timeFormat = $this->_localeDate->getTimeFormat(\IntlDateFormatter::SHORT);
            $fieldset->addField('request_time', 'note', [
                'label' => __('Requested time'),
                'title' => __('Requested time'),
                'text' =>   $this->formatDate($data['request_time'], \IntlDateFormatter::MEDIUM),
                'date_format' => $dateFormat,
                    $this->_localeDate->date(new \DateTime($data['request_time'])),
                    \IntlDateFormatter::MEDIUM,
                    true
                ]
            );
        }

        $form->setValues($data);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * @return bool
     */
    protected function _isActivePaymentPlugin() {
        if($this->_helperPayment->getConfig('affiliateplus_payment/recurring/enable')) {
            return true;
        }
        return false;
    }
}