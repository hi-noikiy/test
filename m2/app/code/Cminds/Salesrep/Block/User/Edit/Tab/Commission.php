<?php
namespace Cminds\Salesrep\Block\User\Edit\Tab;

class Commission extends \Magento\Backend\Block\Widget\Form\Generic
{
    private $adminUsers;

    private $authSession;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\User\Model\ResourceModel\User\Collection $adminUsers,
        \Magento\Backend\Model\Auth\Session $authSession
    ) {
        $this->adminUsers = $adminUsers;
        $this->authSession = $authSession;
        parent::__construct($context, $registry, $formFactory);
    }

    protected function _prepareForm()
    {
        $canEditSalesRepCommission = $this->authSession->isAllowed(
            'Cminds_Salesrep::edit_sales_rep_commission_rates'
        );
        $canEditManagerCommission = $this->authSession->isAllowed(
            'Cminds_Salesrep::edit_manager_commission_rates'
        );

        $model = $this->_coreRegistry->registry('permissions_user');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        if ($canEditManagerCommission || $canEditSalesRepCommission) {
            $commissionRatesFieldset = $form->addFieldset(
                'commission_rates',
                ['legend' => __('Commission Rates')]
            );

            if ($canEditSalesRepCommission) {
                $commissionRatesFieldset->addField(
                    'salesrep_rep_commission_rate',
                    'text',
                    [
                        'name' => 'salesrep_rep_commission_rate',
                        'label' => __('Commission Rate as Sales Representative'),
                        'id' => 'salesrep_rep_commission_rate',
                        'title' => __('Commission Rate as Sales Representative'),
                        'required' => false,
                        'note' => __('Specify the commission rate that this user should earn on orders for which they are the *primary* sales representative. If left blank, the default commission rate will be used (specified under System -> Config -> Sales Representative).')
                    ]
                );
            }

            if ($canEditManagerCommission) {
                $commissionRatesFieldset->addField(
                    'salesrep_manager_commission_rate',
                    'text',
                    [
                        'name' => 'salesrep_manager_commission_rate',
                        'label' => __('Commission Rate as Manager	'),
                        'id' => 'salesrep_manager_commission_rate',
                        'title' => __('Commission Rate as Manager'),
                        'required' => false,
                        'note' => __('Specify the commission rate that this user should earn on orders submitted by any sales representatives they manage. If this user does not manage any other users, values will be ignored.')
                    ]
                );
            }
        }

        if ($canEditManagerCommission) {
            $managersFieldset = $form->addFieldset(
                'sales_teams_and_managers',
                ['legend' => __('Sales Teams and Managers')]
            );

            $managersFieldset->addField(
                'salesrep_manager_id',
                'select',
                [
                    'name' => 'salesrep_manager_id',
                    'label' => __('Manager'),
                    'id' => 'salesrep_manager_id',
                    'title' => __('Manager'),
                    'values' => $this->getAdmins(),
                    'required' => false,
                    'note' => __('Specify a manager who should be credited for this user\'s orders. If this user does not have a manager or is himself a top-level manager, you should select "No Manager".')
                ]
            );
        }

        $data = $model->getData();
        $form->setValues($data);

        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function getAdmins()
    {
        $current_user = $this->_coreRegistry->registry('permissions_user');
        $collection = $this->adminUsers->setOrder('firstname', 'asc')->load();

        $result = [];
        $result[] = ['value' => "", 'label' => "No Manager"];

        foreach ($collection as $admin) {
            if ($current_user->getId() != $admin->getId()) {
                $result[] = [
                    'value' => $admin->getId(),
                    'label' => $admin->getFirstname() . ' ' . $admin->getLastname() . ' (' . $admin->getUsername() . ')'
                ];
            }
        }
        return $result;
    }
}
