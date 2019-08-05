<?php
namespace Cminds\Salesrep\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Area;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $adminUsers;

    protected $registry;

    protected $subordinateIds;

    protected $authSession;

    protected $scopeConfig;

    protected $transportBuilder;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\User\Model\ResourceModel\User\Collection $adminUsers,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    ) {
        $this->adminUsers = $adminUsers;
        $this->registry = $registry;
        $this->authSession = $authSession;
        $this->scopeConfig = $context->getScopeConfig();
        $this->transportBuilder = $transportBuilder;
        parent::__construct($context);
    }

    /**
     * Check current user permission on resource and privilege
     *
     * @param   string $resource
     * @param   string $privilege
     *
     * @return  boolean
     */
    public function isAllowed($user, $resource, $privilege = null)
    {
        $acl = $this->authSession->getAcl();

        if ($user && $acl) {
            try {
                return $acl->isAllowed(
                    $user->getAclRole(),
                    $resource,
                    $privilege
                );
            } catch (\Exception $e) {
                try {
                    if (!$acl->has($resource)) {
                        return $acl->isAllowed(
                            $user->getAclRole(),
                            null,
                            $privilege
                        );
                    }
                } catch (\Exception $e) {
                }
            }
        }
        return false;
    }

    /**
     * Return array with all admin users
     *
     * @return array
     */
    public function getAdmins()
    {
        $collection = $this->adminUsers->setOrder('firstname', 'asc')->load();

        $result = [];

        $result[] = ['value' => "", 'label' => __("No Manager")];

        foreach ($collection as $admin) {
            $result[] = [
                'value' => $admin->getId(),
                'label' => $admin->getFirstname() . ' ' . $admin->getLastname() . ' (' . $admin->getUsername() . ')'
            ];
        }
        return $result;
    }

    /**
     * Return true if module enabled
     *
     * @return boolean
     */
    public function isModuleEnabled()
    {
        return $this->scopeConfig->getValue(
            'cminds_salesrep_configuration/module_status/enabled',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }

    /**
     * Return array with all admin users for reports
     *
     * @return array
     */
    public function getAdminsForReport()
    {
        $collection = $this->adminUsers->setOrder('firstname', 'asc')->load();

        $result = [];

//        $result[] = ['value' => "0", 'label' => __("No Salesrep")];

        foreach ($collection as $admin) {
            $result[] = [
                'value' => $admin->getId(),
                'label' => $admin->getFirstname() . ' ' . $admin->getLastname()
            ];
        }
        return $result;
    }

    /**
     * Return array with all admin users for reports
     *
     * @return array
     */
    public function getAdminsForFrontend()
    {
        $collection = $this->adminUsers->setOrder('firstname', 'asc')->load();

        $adminsConfig = array_values(
            explode(
                ",",
                $this->scopeConfig->getValue('cminds_salesrep_configuration/checkout/sales_rep_list')
            )
        );

        $result = [];


        foreach ($collection as $admin) {
            if (in_array($admin->getId(), $adminsConfig)) {
                $result[] = [
                    'value' => $admin->getId(),
                    'label' => $admin->getFirstname() . ' ' . $admin->getLastname()
                ];
            }
        }
        return $result;
    }

    /**
     * Return array with all admin users for reports
     *
     * @return array
     */
    public function getAdminsForBackend()
    {
        $collection = $this->adminUsers->setOrder('firstname', 'asc')->load();

        $adminsConfig = array_values(
            explode(
                ",",
                $this->scopeConfig->getValue('cminds_salesrep_configuration/checkout/sales_rep_list')
            )
        );

        $result = [];

        $result[] = ['value' => "0", 'label' => __("No Salesrep")];

        foreach ($collection as $admin) {
            if (in_array($admin->getId(), $adminsConfig)) {
                $result[] = [
                    'value' => $admin->getId(),
                    'label' => $admin->getFirstname() . ' ' . $admin->getLastname()
                ];
            }
        }
        return $result;
    }

    /**
     * Return status list for orders
     *
     * @return array
     */
    public function getStatusList()
    {
        $result = [];
        $result[] = ['value' => 'Unpaid', 'label' => __('Unpaid')];
        $result[] = ['value' => 'Paid', 'label' => __('Paid')];
        $result[] = ['value' => 'Ineligible', 'label' => __('Ineligible')];
        $result[] = ['value' => 'Canceled', 'label' => __('Canceled')];
        return $result;
    }

    /**
     * Return array of subordinate salesrep's
     *
     * @param int|null $id user id
     *
     * @return array
     */
    public function getSubordinateIds($id = null)
    {
        $this->subordinateIds = [];

        /** method clear() refreshes collection everytime we request data from database */
        $adminUserCollection = $this->adminUsers
            ->clear()
            ->addFieldToFilter('salesrep_manager_id', $id);

        foreach ($adminUserCollection as $adminUser) {
            $this->subordinateIds[] = $adminUser->getId();
        }

        return $this->subordinateIds;
    }

    /**
     * Send corresponding email template
     *
     * @param string $template configuration path of email template
     * @param string $send_to send to
     * @param array $templateParams
     * @param int|null $storeId
     *
     * @return $this
     */
    public function sendEmailTemplate(
        $template,
        $send_to = [],
        $templateParams = [],
        $storeId = null
    ) {
        $senderName = $this->scopeConfig->getValue(
            'trans_email/ident_general/name',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        $senderEmail = $this->scopeConfig->getValue(
            'trans_email/ident_general/email',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );

        $sender = [
            'name' => $senderName,
            'email' => $senderEmail
        ];

        $transport = $this->transportBuilder
            ->setTemplateIdentifier($template)
            ->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $storeId,
                ]
            )
            ->setTemplateVars($templateParams)
            ->setFrom($sender)
            ->addTo($send_to['email'], $send_to['name'])
            ->getTransport();

        $transport->sendMessage();
        return $this;
    }

    /**
     * Returns default value from configuration of salesrep commission.
     *
     * @return int|null
     */
    public function getConfigDefaultSalesrepComm()
    {
        return $this->scopeConfig->getValue(
            'cminds_salesrep_configuration/'
            . 'commissions/default_sales_rep',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns default value from configuration of manager commission.
     *
     * @return int|null
     */
    public function getConfigDefaultManagerComm()
    {
        return $this->scopeConfig->getValue(
            'cminds_salesrep_configuration/'
            . 'commissions/default_manager',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns config value using which setting manager commission calculation
     * will be based on.
     *
     * @return int|null
     */
    public function getConfigManagerCommBasedOn()
    {
        return $this->scopeConfig->getValue(
            'cminds_salesrep_configuration/'
            . 'commissions/manager_commission_based_on',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is frontend selector visible.
     *
     * @return int|null
     */
    public function showFrontendSelector()
    {
        return $this->scopeConfig->getValue(
            'cminds_salesrep_configuration/'
            . 'checkout/representative_selector_frontend',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }

    /**
     * Returns the label text from config
     *
     * @return string|null
     */
    public function getCheckoutLabel()
    {
        return $this->scopeConfig->getValue(
            'cminds_salesrep_configuration/checkout/header',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }

    /**
     * Returns the note text from config
     *
     * @return string|null
     */
    public function getCheckoutNote()
    {
        return $this->scopeConfig->getValue(
            'cminds_salesrep_configuration/checkout/label',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }

    /**
     * Returns true if reports cron is enabled in config
     *
     * @return int|null
     */
    public function isReportCronEnabled()
    {
        return $this->scopeConfig->getValue(
            'cminds_salesrep_configuration/email_reports/send_reports',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }

    /**
     * Returns the list of all available admins from config
     *
     * @return string|null
     */
    public function getListOfAvailableAdmins()
    {
        return $this->scopeConfig->getValue(
            'cminds_salesrep_configuration/checkout/sales_rep_list',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }

    /**
     * Returns option value to who reports will be sent.
     *
     * @return int|null
     */
    public function getSendReportsTo()
    {
        return $this->scopeConfig->getValue(
            'cminds_salesrep_configuration/email_reports/send_reports_to',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }

    /**
     * Returns email template name.
     *
     * @return int|null
     */
    public function getSalesrepEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            'cminds_salesrep_configuration/email_reports/template',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }

    /**
     * Returns value which status is default
     *
     * @return int|null
     */
    public function getDefaultCommissionStatus()
    {
        return $this->scopeConfig->getValue(
            'cminds_salesrep_configuration/commissions/default_status',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }
}
