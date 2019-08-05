<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Export;

use Magento\ImportExport\Model\Export\AbstractEntity;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\CollectionFactory;
use Magento\ImportExport\Model\Export as ModelExport;

abstract class AbstractExport extends AbstractEntity
{
    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Collection
     */
    protected $collection;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var array
     */
    protected $exportAttributeCodes = [];

    /**
     * @var array
     */
    protected $_productsOnTotalStock = [];

    /**
     * @return \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Collection
     */
    protected function _getEntityCollection()
    {
        if ($this->collection === null) {
            $this->collection = $this->collectionFactory->create();
            $this->addAttributesToCollection();
        }

        return $this->collection;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $item
     */
    public function exportItem($item)
    {
        $row = $item->toArray();

        if ($row['source_code'] === "Total Stock") {
            $row['source_code'] = "default";
        }

        if (!in_array($row['sku'], $this->_productsOnTotalStock)
            && $row['source_code'] === 'default'
        ) {
            $row['quantity'] = 0;
        }

        $this->getWriter()->writeRow($row);
    }

    /**
     * @return string
     */
    public function export()
    {
        $writer = $this->getWriter();
        $writer->setHeaderCols($this->_getHeaderColumns());
        $this->_exportCollectionByPages($this->_getEntityCollection());

        return $writer->getContents();
    }

    /**
     * @return array
     */
    protected function _getExportAttributeCodes()
    {
        if (!$this->exportAttributeCodes) {
            $skipAttr = $this->_parameters[ModelExport::FILTER_ELEMENT_SKIP];
            foreach (static::COLUMNS as $column) {
                if (array_search($column, $skipAttr) === false) {
                    $this->exportAttributeCodes[] = $column;
                }
            }
        }

        return $this->exportAttributeCodes;
    }

    /**
     * @return array
     */
    protected function _getHeaderColumns()
    {
        return $this->_getExportAttributeCodes();
    }
}
