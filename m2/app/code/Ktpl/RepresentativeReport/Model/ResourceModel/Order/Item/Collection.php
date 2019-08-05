<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ktpl\RepresentativeReport\Model\ResourceModel\Order\Item;

/**
 * Flat sales order payment collection
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Order\Item\Collection {

    public function getSize() {
        return sizeof($this->getAllIds());
    }

}
