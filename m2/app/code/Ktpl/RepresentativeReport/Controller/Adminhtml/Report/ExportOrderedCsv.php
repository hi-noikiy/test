<?php

namespace Ktpl\RepresentativeReport\Controller\Adminhtml\Report;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportOrderedCsv extends \Magento\Backend\App\Action {

    protected $resultPageFactory;
    protected $fileFactory;
    
    /**
     * 
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,    
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->fileFactory       = $fileFactory;
        parent::__construct($context);
    }

    /**
     * 
     * @return type
     */
    public function execute() {
        $fileName = 'salesreport.csv';
        
        $content = $this->_view->getLayout()->createBlock(
                        \Ktpl\RepresentativeReport\Block\Adminhtml\Salesdata\Grid::class
                )->getCsvFile();

        return $this->fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }

}
