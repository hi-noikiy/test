<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_BannersLite
 */


namespace Amasty\BannersLite\Model\ResourceModel\Rule;

class Collection extends \Magento\SalesRule\Model\ResourceModel\Rule\Collection
{

    /**
     * @param string $linkField
     * @param array $ruleIds
     *
     * @return array
     */
    public function getActiveRuleIds($linkField, $ruleIds)
    {
        $this->prepareSqlConditions($linkField, $ruleIds);

        return $this->getRuleIds($linkField);
    }

    /**
     * @param string $linkField
     * @param array $ruleIds
     */
    private function prepareSqlConditions($linkField, $ruleIds)
    {
        $allowedActions = [
            'ampromo_product',
            'ampromo_items',
            'ampromo_cart',
            'ampromo_spent',
            'ampromo_eachn'
        ];

        if (class_exists(\Amasty\Rules\Helper\Data::class, false)) {
            $allowedActions += array_keys(\Amasty\Rules\Helper\Data::staticGetDiscountTypes());
        }

        $this->addFieldToFilter($linkField, ['in' => $ruleIds]);
        $this->addFieldToFilter(\Magento\SalesRule\Model\Data\Rule::KEY_SIMPLE_ACTION, ['in' => $allowedActions]);
        $this->addIsActiveFilter();
    }

    /**
     * @param string $linkField
     *
     * @return array
     */
    private function getRuleIds($linkField)
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $idsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);

        $idsSelect->columns($linkField, 'main_table');

        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }
}
