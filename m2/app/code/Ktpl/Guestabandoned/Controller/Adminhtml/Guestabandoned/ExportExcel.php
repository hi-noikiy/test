<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ktpl\Guestabandoned\Controller\Adminhtml\Guestabandoned;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportExcel extends \Ktpl\Guestabandoned\Controller\Adminhtml\Guestabandoned
{
    
    
    /**
     * Export products report to Excel XML format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'guest_abandoned.xml';
        $content = $this->_view->getLayout()->createBlock(
             \Ktpl\Guestabandoned\Controller\Block\Guestabandoned\Grid::class
        )->getExcelFile(
            $fileName
        );

        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
