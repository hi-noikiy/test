<?php

class Hatimeria_Elastic_Model_Worker extends Mage_Core_Model_Abstract
{
    /**
     * Page Size
     * @var int
     */
    const PAGE_SIZE = 1000;

    /**
     * Log file
     */
    const LOG_FILE = 'elastic_update_attributes.log';

    /**
     * Run Cron Job
     */
    public function run()
    {
        $this->prepare();
        $collection = $this->getNewCollection();
        $collection->setPageSize(self::PAGE_SIZE);
        $lastPage = $collection->getLastPageNumber();

        for ($i=1; $i<=$lastPage; $i++) {

            unset($collection);
            $collection = $this->getNewCollection();
            $collection->addAttributeToSelect(array(
                'sales_count',
                'sales_count_force',
                'view_count',
                'view_count_force',
                'rate_count',
                'rate_count_force'
            ));
            $collection->setPageSize(self::PAGE_SIZE);
            $collection->setCurPage($i);
            $collection->load();

            foreach ($collection as $product) {
                try {
                    $this->updateProduct($product);
                } catch (Exception $e) {
                    Mage::log(sprintf("Error in product: %d, message: %s", $product->getId(), $e->getMessage()), Zend_Log::ERR, self::LOG_FILE);
                }
            }
        }
    }

    /**
     * Read attribute from Index
     * @param $attrName
     * @param $productId
     * @return array
     */
    public function getAttributeValue($attrName, $productId)
    {
        $sql = "SELECT * FROM am_sorting_{$attrName} WHERE id = :product";
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $results = $connection->fetchAssoc($sql, array(
            'product' => $productId
        ));
        foreach ($results as $row) {
            return array('value' => (int)$row[$attrName], 'store_id' => (int)$row['store_id']);
        }
    }

    /**
     * Prepare
     */
    protected function prepare()
    {
        $logFile = sprintf('%s/var/log/%s', Mage::getBaseDir(), self::LOG_FILE);
        if (file_exists($logFile)) {
            unlink($logFile);
        }
    }

    /**
     * Update Product
     * @param Mage_Catalog_Model_Product $product
     */
    protected function updateProduct(Mage_Catalog_Model_Product $product)
    {
        $defaults = array('value' => 0, 'store_id' => 0);
        $wished = $defaults;
        $mostviewed = $defaults;
        $bestseller = $defaults;

        if ((int)$product->getSalesCountForce() != 1) {
            $bestseller = $this->getAttributeValue('bestsellers', $product->getId());
            $product->addAttributeUpdate('sales_count', (int)$bestseller['value'], (int)$bestseller['store_id']);
        }

        if ((int)$product->getViewCountForce() != 1) {
            $mostviewed = $this->getAttributeValue('most_viewed', $product->getId());
            $product->addAttributeUpdate('view_count', (int)$mostviewed['value'], (int)$mostviewed['store_id']);
        }

        if ((int)$product->getRateCountForce() != 1) {
            //$wished = $this->getAttributeValue('toprated', $product->getId());
            //$product->addAttributeUpdate('rate_count', (int)$wished['value'], (int)$wished['store_id']);
            $summaryData = Mage::getModel('review/review_summary')->setStoreId(1)->load($product->getId());
            $product->addAttributeUpdate('rate_count', (int)$summaryData["rating_summary"], (int)$summaryData["store_id"]);
        }

        $msg = sprintf("ID: %d\tViews: %d\tBestsellers: %d\tWished: %d",
            $product->getId(),
            $mostviewed['value'],
            $bestseller['value'],
            $wished['value']
        );

        //Mage::log($msg, Zend_Log::INFO, self::LOG_FILE);
    }

    /**
     * New Collection
     * @return mixed
     */
    protected function getNewCollection()
    {
        return Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
            ->addAttributeToFilter('status', 1);

    }
}