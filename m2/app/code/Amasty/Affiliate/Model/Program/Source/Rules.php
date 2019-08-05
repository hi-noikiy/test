<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Model\Program\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Rules implements OptionSourceInterface
{
    /**
     * @var \Amasty\Affiliate\Model\Program
     */
    private $program;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory
     */
    private $ruleCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * Rules constructor.
     * @param \Amasty\Affiliate\Model\Program $program
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
     */
    public function __construct(
        \Amasty\Affiliate\Model\Program $program,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->program = $program;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        /** @var  $ruleCollection */
        $ruleCollection = $this->ruleCollectionFactory->create();
        $ruleCollection
            ->addFieldToFilter(
                'coupon_type',
                ['eq' => \Magento\SalesRule\Model\Rule::COUPON_TYPE_SPECIFIC]
            )->addFieldToFilter('use_auto_generation', ['eq' => 1]);

        $options = [];
        foreach ($ruleCollection as $rule) {
            $options[] = [
                'label' => $rule->getName(),
                'value' => $rule->getRuleId()
            ];
        }

        if ($ruleCollection->count() <= 0) {
            $this->coreRegistry->register('affiliate_rules_are_empty', true, true);
        }

        return $options;
    }
}
