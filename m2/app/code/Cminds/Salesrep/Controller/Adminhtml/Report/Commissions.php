<?php
namespace Cminds\Salesrep\Controller\Adminhtml\Report;

use Cminds\Salesrep\Model\Salesrep;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Reports\Controller\Adminhtml\Report\AbstractReport;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\Flag;

class Commissions extends AbstractReport
{
    /**
     * @var Salesrep
     */
    protected $salesrep;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param TimezoneInterface $timezone
     * @param Salesrep $salesrep
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        TimezoneInterface $timezone,
        Salesrep $salesrep
    ) {
        $this->salesrep = $salesrep;
        parent::__construct($context, $fileFactory, $dateFilter, $timezone);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $request = $this->getRequest();
        try {
            if ($ordersToChange = $request->getParam('orders_to_change')) {
                $salesRepId = $request->getParam('sales_rep_id');
                $status = $request->getParam('status');
                $collection = $this->salesrep->getCollection()
                    ->addFieldToFilter('rep_id', $salesRepId)
                    ->addFieldToFilter('order_id', ['in' => $ordersToChange]);
                foreach ($collection as $entity) {
                    $entity->setManagerCommissionStatus($status);
                }
                $collection->walk('save');
                $this->messageManager->addSuccessMessage(__('Statuses updated successfully.'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something gone wrong during status change process.'));

        }
        $this->_showLastExecutionTime(Flag::REPORT_ORDER_FLAG_CODE, 'sales');

        $this->_initAction()->_setActiveMenu(
            'Cminds_Salesrep::report_salesrep'
        )->_addBreadcrumb(
            __('Sales Representative Report'),
            __('Sales Representative Report')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            __('Sales Representative Report')
        );
        $gridBlock = $this->_view->getLayout()->getBlock(
            'adminhtml_reports_commissions.grid'
        );
        $filterFormBlock = $this->_view->getLayout()->getBlock('grid.filter.form.commissions');

        $this->_initReportAction([$gridBlock, $filterFormBlock]);

        $this->_view->renderLayout();
    }

    /**
     * Report action init operations
     *
     * @param array|\Magento\Framework\DataObject $blocks
     * @return $this
     */
    public function _initReportAction($blocks)
    {
        if (!is_array($blocks)) {
            $blocks = [$blocks];
        }

        $requestData = $this->_objectManager->get(
            'Magento\Backend\Helper\Data'
        )->prepareFilterString(
            $this->getRequest()->getParam('filter')
        );
        $inputFilter = new \Zend_Filter_Input(
            ['from' => $this->_dateFilter, 'to' => $this->_dateFilter],
            [],
            $requestData
        );
        $requestData = $inputFilter->getUnescaped();
        $requestData['store_ids'] = $this->getRequest()->getParam('store_ids');
        $params = new \Magento\Framework\DataObject();

        foreach ($requestData as $key => $value) {
            if (!empty($value)) {
                $params->setData($key, $value);
            }
        }

        foreach ($blocks as $block) {
            if ($block) {
                $block->setPeriodType($params->getData('period_type'));
                $block->setFilterData($params);
            }
        }

        return $this;
    }
}
