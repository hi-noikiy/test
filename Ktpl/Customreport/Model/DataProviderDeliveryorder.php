<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace  Ktpl\Customreport\Model;

use Ktpl\Customreport\Model\ResourceModel\Deliveryorder\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;


class DataProviderDeliveryorder extends \Magento\Ui\DataProvider\AbstractDataProvider {

    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $pageCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $pageCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $pageCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
       
    }


    public function getData() {

        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();

        foreach ($items as $customRow) {
            $brand = $customRow->getData();
            $this->loadedData[$customRow->getId()] = $brand;
        }

            
        return $this->loadedData;
    }

}
