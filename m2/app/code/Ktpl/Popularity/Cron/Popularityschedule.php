<?php
namespace Ktpl\Popularity\Cron;

class Popularityschedule
{
    public function execute()
    {
        try {
            $objectManager = $obj = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();

            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/popularitylog.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);

            $eventTable = $resource->getTableName('report_event'); 
            $cpevTable = 'catalog_product_entity_varchar';

            $productCollection = $obj->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
            $productCollection->addAttributeToSelect('entity_id');
            $attributeId = $obj->create('Magento\Eav\Model\ResourceModel\Entity\Attribute')->getIdByCode('catalog_product', 'popularity_multiplication');

            $productCollection->getSelect()->join(
                    array('cpev' =>$cpevTable),
                    'cpev.entity_id = e.entity_id AND cpev.attribute_id = '.$attributeId,
                    array()
                );
             $productCollection->getSelect()->join(
                    array('eventTable' =>$eventTable),
                    '`e`.`entity_id`= eventTable.object_id AND eventTable.logged_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)',
                    array('views'=>'COUNT(eventTable.event_id)')
                )->group('e.entity_id');

            foreach ($productCollection as $key => $productValue) {
                    $productId = $productValue->getId();
                    $ratevalue = 0;
                    $ratevalue = $productValue->getViews();

                if ($productId != "") {
                    $product = $obj->create('Magento\Catalog\Model\Product')->load($productId);
                    $product->setPopularity($ratevalue);
                    $product->save();
                    $logger->info("Id=>  ". $product->getEntityId().", popularity==>". $ratevalue );
                }
            }
        } catch(\Exception $e){
            $logger->info("error=>  ".$e->getMessage());
        }
    }
    
}