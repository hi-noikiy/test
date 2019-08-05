<?php
/**
 * Copyright © 2015 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_Salesforce extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package  Magenest_Salesforce
 * @author   ThaoPV
 */
namespace Magenest\Salesforce\Controller\Adminhtml\Report;

use Magenest\Salesforce\Controller\Adminhtml\Report as ReportController;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Controller\ResultFactory;
use Magenest\Salesforce\Model\ReportFactory as ReportFactory;
use Magenest\Salesforce\Model\ResourceModel\Report\CollectionFactory as ReportCollectionFactory;

/**
 * Class Index
 *
 * @package Magenest\Salesforce\Controller\Adminhtml\Report
 */
class MassDelete extends ReportController
{
    /**
     * Mass Action Filter
     *
     * @var Filter
     */
    protected $_filter;

    /**
     * Report Collection
     *
     * @var ReportCollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Report Model
     *
     * @var ReportFactory $_reportFactory
     */
    protected $_reportFactory;

    /**
     * @param Context                 $context
     * @param ReportFactory           $reportFactory
     * @param LayoutFactory           $layoutFactory
     * @param PageFactory             $resultPageFactory
     * @param Filter                  $filter
     * @param ForwardFactory          $resultForwardFactory
     * @param ReportCollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        ReportFactory $reportFactory,
        LayoutFactory $layoutFactory,
        PageFactory $resultPageFactory,
        Filter $filter,
        ForwardFactory $resultForwardFactory,
        ReportCollectionFactory $collectionFactory
    ) {
        $this->_reportFactory = $reportFactory;
        $this->_filter            = $filter;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $reportFactory, $layoutFactory, $resultPageFactory, $resultForwardFactory);
    }

    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collectionFilter = $this->_filter->getCollection($this->_collectionFactory->create());
        /** @var \Magenest\Salesforce\Model\Report $reportModel */
        $reportModel = $this->_reportFactory->create();
        $collectionSize = $collectionFilter->getSize();
        /** @var \Magenest\Salesforce\Model\ResourceModel\Report\Collection $collectionReport */
        $collectionReport = $this->_collectionFactory->create();
        $collectionArr = [];
        $maxRecord = 5000;
        $count = 0;
        $lastItemId = $collectionReport->getLastItem()->getId();
        foreach ($collectionFilter as $item) {
            $count++;
            /** @var \Magenest\Salesforce\Model\Map $item */
            $collectionArr[] = $item->getId();
            if ($count >= $maxRecord || $item->getId() == $lastItemId) {
                $reportModel->deleteMultiReports($collectionArr);
                $collectionArr = [];
                $count = 0;
            }
        }

        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $collectionSize));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }


    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_Salesforce::salesforce_report');
    }
}
