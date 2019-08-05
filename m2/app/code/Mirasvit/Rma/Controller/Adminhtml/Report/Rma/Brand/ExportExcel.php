<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rma
 * @version   2.0.18
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Controller\Adminhtml\Report\Rma\Brand;

use Magento\Framework\Controller\ResultFactory;

class ExportExcel extends \Mirasvit\Rma\Controller\Adminhtml\Report\Rma\Brand
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        throw new \Exception('Not implemented');
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $fileName = 'report_rma_brand.xml';
        $grid = $resultPage->getLayout()->createBlock('\Mirasvit\Rma\Block\Adminhtml\Report\Rma\Brand\Grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcel($fileName));
    }
}
