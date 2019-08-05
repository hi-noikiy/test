<?php

/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ktpl\Guestabandoned\Controller\Adminhtml\Guestabandoned;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportCsv extends \Ktpl\Guestabandoned\Controller\Adminhtml\Guestabandoned {

    /**
     * Export products report grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute() {
        $fileName = 'guest_abandoned.csv';
        
        $content = $this->_view->getLayout()->createBlock(
                        \Ktpl\Guestabandoned\Block\Adminhtml\Guestabandoned\Grid::class
                )->getCsvFile();

        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }

}
