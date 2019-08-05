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

class Reasons extends AbstractReport
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __('RMA: Report by Reasons');
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'rma_reasons';
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setTable('mst_rma_item');
        $this->addFastFilters([]);
        $this->setDefaultColumns([
            'mst_rma_item|total_condition_cnt',
            'mst_rma_item|total_reasons_cnt',
            'mst_rma_item|total_resolution_cnt',
        ]);

        $this->addColumns($this->context->getProvider()->getSimpleColumns('mst_rma_item'));

        $this->addFastFilters([
            'mst_rma_item|created_at',
        ]);

        $this->setDefaultDimension('mst_rma_item|created_at__day');

        $this->addDimensions([
            'mst_rma_item|created_at__day',
            'mst_rma_item|created_at__week',
            'mst_rma_item|created_at__month',
            'mst_rma_item|created_at__year',
        ]);

        $this->getChartConfig()
            ->setType('column')
            ->setDefaultColumns([
                'mst_rma_item|total_reasons_cnt',
            ]);
    }
}