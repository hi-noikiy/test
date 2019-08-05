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



namespace Mirasvit\Rma\Reports;

use Mirasvit\Report\Model\AbstractReport;
use Mirasvit\Report\Model\Context;
use Mirasvit\Report\Ui\Context as UiContext;

class Attribute extends AbstractReport
{
    /**
     * @var UiContext
     */
    private $uiContext;

    public function __construct(
        UiContext $uiContext,
        Context $context
    ) {
        $this->uiContext = $uiContext;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __('RMA: Report by Attribute');
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'rma_attribute';
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setTable('catalog_product_entity');

        $defaultFilters = [['mst_rma_item|qty_requested', 0, 'gt']];
        try {
            $defaultFilters[] = [$this->uiContext->getActiveDimension(), true, 'notnull'];
        } catch (\Exception $e) {}

        $this->setDefaultFilters($defaultFilters);

        $this->addFastFilters([
            'mst_rma_item|created_at',
        ]);

        $this->setRequiredColumns(['catalog_product_entity|entity_id']);

        $this->setDefaultColumns([
            'catalog_product_entity|attribute',
            'mst_rma_item|total_rma_cnt',
            'mst_rma_item|total_items_cnt',
            'mst_rma_item|item_qty_requested',
            'mst_rma_item|item_qty_returned',
        ]);

        $this->setDefaultDimension('sales_order_item|product_id');

        foreach ($this->context->getProvider()->getSimpleColumns('catalog_product_entity') as $column) {
            $this->addDimensions([$column]);
        }

        $this->addColumns($this->context->getProvider()->getComplexColumns('sales_order_item'));
    }
}