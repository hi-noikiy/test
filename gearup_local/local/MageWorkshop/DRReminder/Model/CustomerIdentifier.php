<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DRReminder
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

/**
 * Class MageWorkshop_DRReminder_Model_CustomerIdentifier
 *
 * @method string getHash()
 * @method MageWorkshop_DRReminder_Model_CustomerIdentifier setHash(string $hash)
 * @method int getOrderId()
 * @method MageWorkshop_DRReminder_Model_CustomerIdentifier setOrderId(string $orderId)
 * @method int getCustomerId()
 * @method MageWorkshop_DRReminder_Model_CustomerIdentifier setCustomerId(int|null $customerId)
 * @method string getCustomerEmail()
 * @method MageWorkshop_DRReminder_Model_CustomerIdentifier setCustomerEmail(string $customerEmail)
 */
class MageWorkshop_DRReminder_Model_CustomerIdentifier extends Varien_Object
{
    const IDENTIFIER_TYPE_ID    = 'customer_id';
    const IDENTIFIER_TYPE_EMAIL = 'customer_email';
}
