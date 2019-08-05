<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Block\Adminhtml\Customer\Tab;

use Magento\Customer\Controller\RegistryConstants;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Config\Model\Config\Source\YesnoFactory
     */
    private $yesnoFactory;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory
     * @param \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        array $data = []
    ) {
        $this->yesnoFactory = $yesnoFactory;
        $this->accountRepository = $accountRepository;
        parent::__construct($context, $registry, $formFactory, $data);
    }


    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        /** @var \Amasty\Affiliate\Model\Account $account */
        $account = $this->accountRepository->getByCustomerId($this->getCustomerId());

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setUseContainer(true);
        $form->setHtmlIdPrefix('user_');
        $fieldset = $form->addFieldset('amasty_affiliate_account_fieldset', ['legend' => __('Affiliate Account')]);
        $yesno = $this->yesnoFactory->create()->toOptionArray();

        $fieldset->addField(
            'is_affiliate_active',
            'select',
            [
                'name' => 'affiliate[is_affiliate_active]',
                'label' => __('Enabled'),
                'title' => __('Enabled'),
                'values' => $yesno,
                'data-form-part' => 'customer_form'
            ]
        );

        $fieldset->addField(
            'receive_notifications',
            'select',
            [
                'name' => 'affiliate[receive_notifications]',
                'label' => __('Receive Notifications'),
                'title' => __('Receive Notifications'),
                'values' => $yesno,
                'data-form-part' => 'customer_form'
            ]
        );

        $form->setValues($account->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }
}
