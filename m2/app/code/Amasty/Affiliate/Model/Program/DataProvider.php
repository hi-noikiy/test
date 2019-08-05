<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Model\Program;

use Amasty\Affiliate\Model\ResourceModel\Program\Collection;
use Amasty\Affiliate\Model\ResourceModel\Program\CollectionFactory;
use Amasty\Affiliate\Model\Program;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class DataProvider
 * @package Amasty\Affiliate\Model\Program
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /** @var  array $loadedData */
    private $loadedData;

    /** @var DataPersistorInterface $dataPersistor */
    private $dataPersistor;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var Program $program */
        foreach ($items as $program) {
            $this->loadedData[$program->getId()] = $program->getData();
            $this->loadedData[$program->getId()]['rule_url'] = $this->urlBuilder->getUrl(
                'sales_rule/promo_quote/edit'
            );
        }

        $data = $this->dataPersistor->get('amasty_affiliate_program');
        if (!empty($data)) {
            $program = $this->collection->getNewEmptyItem();
            $program->setData($data);
            $this->loadedData[$program->getId()] = $program->getData();
            $this->dataPersistor->clear('amasty_affiliate_program');
        }

        return $this->loadedData;
    }
}
