<?php

namespace Cminds\Salesrep\Block\Adminhtml\Reports\Commissions;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Grid extends \Magento\Reports\Block\Adminhtml\Grid\AbstractGrid
{
    /**
     * GROUP BY criteria
     *
     * @var string
     */
    protected $_columnGroupBy = 'period';

    protected $salesrep;

    protected $salesModel;

    protected $coreResource;

    protected $dateTime;

    protected $authSession;

    protected $salesrepHelper;

    protected $priceCurrency;

    protected $currencyHelper;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Reports\Model\ResourceModel\Report\Collection\Factory $resourceFactory,
        \Magento\Reports\Model\Grouped\CollectionFactory $collectionFactory,
        \Magento\Reports\Helper\Data $reportsData,
        \Cminds\Salesrep\Model\Salesrep $salesrep,
        \Magento\Sales\Model\Order $salesModel,
        \Magento\Framework\App\ResourceConnection $coreResource,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Cminds\Salesrep\Helper\Data $salesrepHelper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Pricing\Helper\Data $currencyHelper
    ) {
        parent::__construct(
            $context,
            $backendHelper,
            $resourceFactory,
            $collectionFactory,
            $reportsData
        );
        $this->salesrep = $salesrep;
        $this->salesModel = $salesModel;
        $this->coreResource = $coreResource;
        $this->dateTime = $dateTime;
        $this->authSession = $authSession;
        $this->salesrepHelper = $salesrepHelper;
        $this->priceCurrency = $priceCurrency;
        $this->currencyHelper = $currencyHelper;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Cminds_Salesrep::report/commissions/grid.phtml');
        $this->setCountTotals(true);
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceCollectionName()
    {
        return 'Cminds\Salesrep\Model\ResourceModel\Salesrep\Collection';
    }

    /**
     * @return $this|\Magento\Backend\Block\Widget\Grid
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _prepareCollection()
    {
        $filterData = $this->getFilterData();
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
    }

    public function getBaseCollection()
    {

        $collection = $this->salesModel->getCollection();

        $salesrepTable = $this->coreResource
            ->getTableName("salesrep");

        $collection->getSelect()
            ->joinLeft(
                ['s' => $salesrepTable],
                's.order_id = main_table.entity_id'
            );
        return $collection;
    }

    public function getFilteredCollection()
    {
        $filters = $this->getFilterData()->getData();

        if (empty($filters)) {
            $orderStatuses = $this->_scopeConfig->getValue(
                'cminds_salesrep_configuration/report_defaults/order_statuses',
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            );
            $orderAdmins = $this->_scopeConfig->getValue(
                'cminds_salesrep_configuration/report_defaults/sales_rep',
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            );
            $includeCommission = false;
            $commissionStatuses = false;
            $from = false;
            $to = false;
        } else {
            $orderStatuses = $filters['order_statuses'][0];
            $orderAdmins = $filters['sales_rep'];
            if (isset($filters['include_commission_status'])) {
                $includeCommission = $filters['include_commission_status'];
            } else {
                $includeCommission = 0;
            }
            if (isset($filters['commission_statuses'])) {
                $commissionStatuses = $filters['commission_statuses'];
            } else {
                $commissionStatuses = 0;
            }
            $from = $this->dateTime->gmtDate(
                null,
                strtotime($filters['from'] . ' 00:00:00')
            );
            $to = $this->dateTime->gmtDate(
                null,
                strtotime($filters['to'] . ' 23:59:59')
            );
        }


        $collection = $this->getBaseCollection();

        if (isset($orderStatuses)) {
//            $orderStatuses = 'canceled,closed,complete,fraud,holded,payment_review,paypal_canceled_reversal,paypal_reversed,processing,pending';
            $collection->addAttributeToFilter(
                'main_table.status',
                [
                    'in' => explode(
                        ",",
                        $orderStatuses
                    )
                ]
            );
        }

        if (isset($orderAdmins) && is_array($orderAdmins)) {
            $cond = [];
            $cond[] = ['in' => explode(',', $orderAdmins[0])];

            $collection->addAttributeToFilter('s.rep_id', $cond);
        }

        if ($includeCommission
            && $commissionStatuses
            && isset($includeCommission)
            && $includeCommission == 1
            && isset($commissionStatuses)
        ) {
            $collection->addAttributeToFilter(
                's.rep_commission_status',
                ['eq' => $commissionStatuses]
            );
        }

        if ($from && $to) {
            $collection->addAttributeToFilter(
                'created_at',
                ['from' => $from, 'to' => $to]
            );
        }
        return $collection;
    }

    public function currentAdminUser()
    {
        return $this->authSession->getUser();
    }

    /**
     * @return string
     */
    public function getStatusFormAction()
    {
        return $this->getUrl('salesrep/report/commissions', ['filter' => $this->getRequest()->getParam('filter')]);
    }

    public function getSubordinateIds()
    {
        return $this->salesrepHelper->getSubordinateIds(
            $this->currentAdminUser()->getUserId()
        );
    }

    public function canSeeRepName($row, $isManager = false)
    {
        $showName = false;
        if ($isManager) {
            $salesrepId = $row->getManagerId();
        } else {
            $salesrepId = $row->getRepId();
        }

        $view_rep_name_all = $this->authSession
            ->isAllowed(
                'Cminds_Salesrep::'
                . 'sales_and_commission_reports_view_order_list_and_representative_name_all_orders'
            );
        $view_rep_name_sub = $this->authSession
            ->isAllowed(
                'Cminds_Salesrep::'
                . 'sales_and_commission_reports_view_order_list_and_representative_name_subordinate'
            );
        $view_rep_name_own = $this->authSession
            ->isAllowed(
                'Cminds_Salesrep::'
                . 'sales_and_commission_reports_view_order_list_and_representative_name_only_own'
            );

        if ($view_rep_name_all
            || ($view_rep_name_sub && in_array(
                    $row->getRepId(),
                    $this->getSubordinateIds()
                ))
            || ($view_rep_name_own
                && $this->currentAdminUser()->getUserId() == $salesrepId)
        ) {
            $showName = true;
        }

        return $showName;
    }

    public function canSeeRepCommission($row, $isManager = false)
    {
        $showCommission = false;
        if ($isManager) {
            $salesrepId = $row->getManagerId();
        } else {
            $salesrepId = $row->getRepId();
        }

        $view_comm_all = $this->authSession
            ->isAllowed(
                'Cminds_Salesrep::'
                . 'sales_and_commission_reports_view_commission_amount_all_orders'
            );
        $view_comm_sub = $this->authSession
            ->isAllowed(
                'Cminds_Salesrep::'
                . 'sales_and_commission_reports_view_commission_amount_subordinate'
            );
        $view_comm_own = $this->authSession
            ->isAllowed(
                'Cminds_Salesrep::'
                . 'sales_and_commission_reports_view_commission_amount_only_own'
            );

        if ($view_comm_all
            || ($view_comm_sub && in_array(
                    $row->getRepId(),
                    $this->getSubordinateIds()
                ))
            || ($view_comm_own
                && $this->currentAdminUser()->getUserId() == $salesrepId)
        ) {
            $showCommission = true;
        }
        return $showCommission;
    }

    public function canSeeRepCommissionStatus($row, $isManager = false)
    {
        $showCommissionStatus = false;
        if ($isManager) {
            $salesrepId = $row->getManagerId();
        } else {
            $salesrepId = $row->getRepId();
        }

        $view_status_all = $this->authSession
            ->isAllowed(
                'Cminds_Salesrep::'
                . 'sales_and_commission_reports_view_commission_amount_all_orders'
            );
        $view_status_sub = $this->authSession
            ->isAllowed(
                'Cminds_Salesrep::'
                . 'sales_and_commission_reports_view_commission_amount_subordinate'
            );
        $view_status_own = $this->authSession
            ->isAllowed(
                'Cminds_Salesrep::'
                . 'sales_and_commission_reports_view_commission_amount_only_own'
            );

        if ($view_status_all
            || ($view_status_sub && in_array(
                    $row->getRepId(),
                    $this->getSubordinateIds()
                ))
            || ($view_status_own
                && $this->currentAdminUser()->getUserId() == $salesrepId)
        ) {
            $showCommissionStatus = true;
        }
        return $showCommissionStatus;
    }

    public function getDate($format = null, $value = null)
    {
        return $this->dateTime->date(null, $value);
    }

    public function prepareReportData()
    {
        $data = [];
        $collection = $this->getFilteredCollection();
        foreach ($collection as $row) {
            $showName = $this->canSeeRepName($row, false);
            $showComm = $this->canSeeRepCommission($row, false);
            $showCommStatus = $this->canSeeRepCommissionStatus($row, false);
            if ($showName) {
                $rep_name = ($row->getRepName() == "")
                    ? "No Sales Rep."
                    : $row->getRepName();

                if (!array_key_exists($rep_name, $data)) {
                    $data[$rep_name] = [];
                }

                // Total earned for user
                if (!isset($data[$rep_name]['paid_total'])) {
                    $data[$rep_name]['paid_total'] = 0;
                }

                if (!isset($data[$rep_name]['unpaid_total'])) {
                    $data[$rep_name]['unpaid_total'] = 0;
                }

                if (strtolower($row->getRepCommissionStatus()) == "paid") {
                    $data[$rep_name]['paid_total'] += round(
                        $row->getRepCommissionEarned(),
                        2
                    );
                } else {
                    if (strtolower($row->getRepCommissionStatus()) == "unpaid") {
                        $data[$rep_name]['unpaid_total'] += round(
                            $row->getRepCommissionEarned(),
                            2
                        );
                    }
                }

                if (!isset($data[$rep_name]['orders'])) {
                    $data[$rep_name]['orders'] = [];
                }

                $data[$rep_name]['orders'][] = [
                    'value' => $showComm
                        ? $row->getRepCommissionEarned()
                        : '',
                    'status' => strtolower($row->getRepCommissionStatus()),
                    'show_status' => $showCommStatus,
                    'created_at' => $this->getDate(
                        null,
                        strtotime($row->getData('created_at'))
                    ),
                    'order_id' => $row->getId(),
                    'order_increment_id' => $row->getIncrementId(),
                    'order_status' => $row->getStatus(),
                    'is_manager' => false,
                ];

                $data[$rep_name]['rep_id'] = $row->getRepId();
                $data[$rep_name]['show_comm'] = $showComm;
            }

            // Manager
            $filters = $this->getFilterData()->getData();

            if (empty($filters)) {
                $orderAdminsArray = [];
            } else {
                $orderAdmins = $filters['sales_rep'];
                $orderAdminsArray = explode(',', $orderAdmins[0]);
            }


            if ($row->getManagerId() && !empty($orderAdmins) && in_array($row->getManagerId(), $orderAdminsArray)) {
                $showName = $this->canSeeRepName($row, true);
                $showComm = $this->canSeeRepCommission($row, true);
                $showCommStatus = $this->canSeeRepCommissionStatus($row, true);


                if ($showName && ($rep_name = $row->getManagerName()) != '') {
                    if (!array_key_exists($rep_name, $data)) {
                        $data[$rep_name] = [];
                    }

                    // Total earned for user
                    if (!isset($data[$rep_name]['paid_total'])) {
                        $data[$rep_name]['paid_total'] = 0;
                    }

                    if (!isset($data[$rep_name]['unpaid_total'])) {
                        $data[$rep_name]['unpaid_total'] = 0;
                    }

                    if (strtolower($row->getManagerCommissionStatus()) == "paid") {
                        $data[$rep_name]['paid_total'] += round(
                            $row->getManagerCommissionEarned(),
                            2
                        );
                    } else {
                        if (strtolower($row->getManagerCommissionStatus()) == "unpaid") {
                            $data[$rep_name]['unpaid_total'] += round(
                                $row->getManagerCommissionEarned(),
                                2
                            );
                        }
                    }

                    if (!isset($data[$rep_name]['orders'])) {
                        $data[$rep_name]['orders'] = [];
                    }

                    $data[$rep_name]['orders'][] = [
                        'value' => $showComm
                            ? $row->getManagerCommissionEarned()
                            : '',
                        'status' => strtolower(
                            $row->getManagerCommissionStatus()
                        ),
                        'show_status' => $showCommStatus,
                        'created_at' => $this->getDate(
                            null,
                            strtotime($row->getData('created_at'))
                        ),
                        'order_id' => $row->getId(),
                        'order_increment_id' => $row->getIncrementId(),
                        'order_status' => $row->getStatus(),
                        'is_manager' => true,
                    ];

                    $data[$rep_name]['rep_id'] = $row->getManagerId();
                    $data[$rep_name]['show_comm'] = $showComm;
                }
            }
        }
        return $data;
    }

    public function sortReportData($data)
    {
        if (isset($data['No Sales Rep.'])) {
            $_tmp = $data['No Sales Rep.'];
            unset($data['No Sales Rep.']);

            ksort($data);

            $data['No Sales Rep.'] = $_tmp;
            unset($_tmp);
        } else {
            ksort($data);
        }
        return $data;
    }

    public function getFilterDate($date)
    {
        return $this->dateTime->gmtDate(null, $date);
    }

    public function getCurrencySymbol()
    {
        return $this->priceCurrency->getCurrencySymbol();
    }

    public function getCurrency($price)
    {
        return $this->currencyHelper->currency($price, true, false);
    }

    public function isAllowedForUser($acl)
    {
        return $this->authSession->isAllowed($acl);
    }

    public function getStatusList()
    {
        return $this->salesrepHelper->getStatusList();
    }

    public function getReportDatesRange(
        $to_date,
        $from_date,
        $reportType,
        $_report_end_date,
        $_report_start_date
    ) {

        if ($_report_end_date != null) {
            if ($reportType == "week"
                || $reportType == "month"
                || $reportType == "year"
            ) {
                $_report_start_date = mktime(
                    0,
                    0,
                    0,
                    date("m", $_report_end_date),
                    date("d", $_report_end_date) + 1,
                    date("Y", $_report_end_date)
                );
            } else {
                $_report_start_date = $_report_end_date;

                $_report_start_date = mktime(
                    0,
                    0,
                    0,
                    date("m", $_report_end_date),
                    date("d", $_report_end_date) + 1,
                    date("Y", $_report_end_date)
                );
            }
        } else {
            $_report_start_date = strtotime(
                $this->getFilterDate(
                    strtotime($from_date)
                )
            );
        }

        if ($_report_start_date > strtotime(
                $this->getFilterDate(
                    strtotime($to_date)
                )
            )
        ) {
            return false;
        }

        // calculate end date
        switch ($reportType) {
            case 'year':
                $_report_end_date = mktime(
                    23,
                    59,
                    59,
                    12,
                    31,
                    date("Y", $_report_start_date)
                );
                break;
            case 'month':
                $_report_end_date = mktime(
                    23,
                    59,
                    59,
                    date("m", $_report_start_date) + 1,
                    0,
                    date("Y", $_report_start_date)
                );
                break;
            case 'week':
                $_report_end_date = mktime(
                    23,
                    59,
                    59,
                    date("m", $_report_start_date),
                    date("d", $_report_start_date) + 6,
                    date("Y", $_report_start_date)
                );
                break;
            case 'day':
                $_report_end_date = mktime(
                    23,
                    59,
                    59,
                    date("m", $_report_start_date),
                    date("d", $_report_start_date),
                    date("Y", $_report_start_date)
                );
                break;
        }

        // make date label
        if ($reportType == 'day') {
            $_report_date_label = date(
                'm/d/Y',
                $_report_start_date
            );
        } else {
            $_report_date_label = date(
                    'm/d/Y',
                    $_report_start_date
                ) . ' - ' . date(
                    'm/d/Y',
                    $_report_end_date
                );
        }

        return [
            'report_start_date' => $_report_start_date,
            'report_end_date' => $_report_end_date,
            'report_date_label' => $_report_date_label
        ];
    }
}
