<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Model\Filters\Transaction;

use Aheadworks\StoreCredit\Api\Data\TransactionInterface;

/**
 * Class Aheadworks\StoreCredit\Model\Filters\Transaction\CustomerSelection
 */
class CustomerSelection implements \Zend_Filter_Interface
{
    /**#@+
     * Constant for default field name for customer selection
     */
    const DEFAULT_FIELD_NAME = 'customer_selections';
    /**#@-*/

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @param string $fieldName
     */
    public function __construct($fieldName = null)
    {
        if ($fieldName == null) {
            $this->fieldName = self::DEFAULT_FIELD_NAME;
        } else {
            $this->fieldName = $fieldName;
        }
    }

    /**
     *  {@inheritDoc}
     */
    public function filter($value)
    {
        $result = [];
        if (is_array($value)
            && isset($value[$this->fieldName])
            && is_array($value[$this->fieldName])
        ) {
            foreach ($value[$this->fieldName] as $customerSelection) {
                $websiteId = $this->get($value, TransactionInterface::WEBSITE_ID);
                if ($customerSelection['website_id'][0] != $websiteId) {
                    continue;
                }
                $result[] = [
                    TransactionInterface::CUSTOMER_ID => $this->get(
                        $customerSelection,
                        TransactionInterface::CUSTOMER_ID
                    ),
                    TransactionInterface::CUSTOMER_NAME => $this->get(
                        $customerSelection,
                        TransactionInterface::CUSTOMER_NAME
                    ),
                    TransactionInterface::CUSTOMER_EMAIL => $this->get(
                        $customerSelection,
                        TransactionInterface::CUSTOMER_EMAIL
                    ),
                    TransactionInterface::COMMENT_TO_CUSTOMER => $this->get(
                        $value,
                        TransactionInterface::COMMENT_TO_CUSTOMER
                    ),
                    TransactionInterface::COMMENT_TO_ADMIN => $this->get(
                        $value,
                        TransactionInterface::COMMENT_TO_ADMIN
                    ),
                    TransactionInterface::BALANCE => $this->get(
                        $value,
                        TransactionInterface::BALANCE
                    ),
                    TransactionInterface::WEBSITE_ID => $websiteId
                ];
            }
        }
        return $result;
    }

    /**
     * Get data from array
     *
     * @param array $data
     * @param string $field
     * @return string
     */
    private function get($data, $field)
    {
        return (is_array($data) && isset($data[$field])) ? $data[$field] : null;
    }
}
