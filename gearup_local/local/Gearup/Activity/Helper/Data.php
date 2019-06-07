<?php

/**
 * Helper
 */

class Gearup_Activity_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getSellActivity($category) {
        $products = Mage::getModel('catalog/category')->load($category->getEntityId())->getProductCollection();
        $pids = array();
        $haveitems = array();
        $realP = array();
        foreach ($products as $product) {
            $pids[] = $product->getId();
        }
        $allcount = round((count($products) * 20) / 100);
        $realcount = round((count($products) * 10) / 100);
        $specificcount = round((count($products) * 5) / 100);
        $weekBeginning = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(strtotime('last sunday')));
        $weekEnd = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(strtotime('next saturday')));
        $orders = Mage::getResourceModel('sales/order_item_collection');
        $orders->addAttributeToFilter('product_id', array('in' => $pids));
        $orders->addAttributeToFilter('created_at', array('from' => $weekBeginning));
        $orders->addAttributeToFilter('created_at', array('to' => $weekEnd));
        $orders->getSelect()->group('product_id');
        $orders->getSelect()->limit($realcount);

        if ($orders->getSize()) {
            if ($orders->getSize() < $realcount) {
                $remaincount = $allcount - $orders->getSize();
            } else {
                $remaincount = $realcount;
            }

            foreach ($orders as $order) {
                $haveitems[] = $order->getProductId();
                $realP[] = $order->getProductId();
            }
        } else {
            $remaincount = $realcount;
        }

        if ($category->getCategoryManufacturerActivity()) {
            $products = Mage::getModel('catalog/product')->getCollection();
            $products->addCategoryFilter($category);
            $products->addAttributeToFilter('manufacturer', array('eq'=>$category->getCategoryManufacturerActivity()));
            $products->addAttributeToFilter('status', array('eq'=>1));
            if ($products->getSize()) {
                $sp = 0;
                foreach ($products as $product) {
                    if ($sp >= $specificcount) {
                        break;
                    }
                    if (!in_array($product->getId(), $haveitems)) {
                        $haveitems[] = $product->getId();
                        $sp++;
                    }
                }
                $remaincount = $remaincount - $sp;
            } else {
                $remaincount = $remaincount;
            }
        } else {
            $remaincount = $remaincount;
        }

        if ($category->getCategorySellActivity()) {
            $specifyProducts = explode(',', $category->getCategorySellActivity());
            foreach ($specifyProducts as $specifyProduct) {
                $productSpec = Mage::getModel('catalog/product')->loadByAttribute('sku', $specifyProduct);
                if (!in_array($productSpec->getId(), $haveitems)) {
                    $haveitems[] = $productSpec->getId();
                }
            }
            $remaincount = $remaincount - count($specifyProducts);
        } else {
            $remaincount = $remaincount;
        }
        $i = 0;
        $previousRandom = $this->getrecordRandom($category->getEntityId());
        if (!$previousRandom) {
            shuffle($pids);
            $randomids = $pids;
        } else {
            $randomids = $previousRandom;
        }
        foreach ($randomids as $pid) {
            if ($i >= $remaincount) {
                break;
            }
            if (!in_array($pid, $haveitems)) {
                $haveitems[] = $pid;
                $i++;
            }
        }
        $record = implode(',', $haveitems);
        $this->recordRandom($category->getEntityId(), $record);

        $items = array('pid'=>$haveitems, 'real'=>$realP);
        return $items;
    }

    public function getRandomwordS($product, $real) {
        $record = $this->recordActivity($product, Gearup_Activity_Model_Record::SELL_TYPE);
        if ($real) {
            $weekBeginning = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(strtotime('last sunday')));
            $weekEnd = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(strtotime('next saturday')));
            $orders = Mage::getResourceModel('sales/order_item_collection');
            $orders->addAttributeToFilter('product_id', array('eq' => $product->getId()));
            $orders->addAttributeToFilter('created_at', array('from' => $weekBeginning));
            $orders->addAttributeToFilter('created_at', array('to' => $weekEnd));
            if ($orders->getSize()) {
                $number = $record->getNumberCount() + $orders->getSize();
                $msg = 'Purchased by '.$number.' customers this week';
                return $msg;
            } else {
                $number = $record->getNumberCount();
                $msg = 'Purchased by '.$number.' customers this week';
                return $msg;
            }
        } else {
            $number = $record->getNumberCount();
            $msg = 'Purchased by '.$number.' customers this week';
            return $msg;
        }
    }

    public function getRandomwordV($product) {
        $record = $this->recordActivity($product, Gearup_Activity_Model_Record::VIEWED_TYPE);
        $weekBeginning = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(strtotime('last sunday')));
        $weekEnd = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(strtotime('next saturday')));
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('report_viewed_product_index');
        $searchquery = "SELECT * FROM `{$table}` WHERE `product_id` = ".$product->getId()." AND `added_at` >= '".$weekBeginning."' AND `added_at` < '".$weekEnd."';";
        $searchs = $readConnection->fetchAll($searchquery);

        if ($searchs) {
            $number = $record->getNumberCount() + count($searchs);
            $msg = $number.' Customers are viewed in this week';
            return $msg;
        } else {
            $number = $record->getNumberCount();
             $msg = $number.' Customers are viewed in this week';
            return $msg;
        }
    }

    public function getRandomwordW($product) {
        $record = $this->recordActivity($product, Gearup_Activity_Model_Record::WISHLIST_TYPE);
        $weekBeginning = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(strtotime('last sunday')));
        $weekEnd = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(strtotime('next saturday')));
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('wishlist_item');
        $searchquery = "SELECT * FROM `{$table}` WHERE `product_id` = ".$product->getId()." AND `added_at` >= '".$weekBeginning."' AND `added_at` < '".$weekEnd."';";
        $searchs = $readConnection->fetchAll($searchquery);

        if ($searchs) {
            $number = $record->getNumberCount() + count($searchs);
            $msg = $number.' Customers added to the wishlist in this week';
            return $msg;
        } else {
            $number = $record->getNumberCount();
             $msg = $number.' Customers added to the wishlist in this week';
            return $msg;
        }
    }

    public function checkExpireDate() {
        $weekEnd = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(strtotime('next saturday')));
        $now = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(strtotime('now')));
        $expiresHave = Mage::getModel('gearup_activity/expire')->getCollection();
        if ($expiresHave->getSize()) {
            $expires = Mage::getModel('gearup_activity/expire')->getCollection();
            $expires->addFieldToFilter('expire_at', array('lt' => $now));
            if ($expires->getSize()) {
                foreach ($expires as $expire) {
                    $expire->delete();
                }
                $recordMs = Mage::getModel('gearup_activity/record')->getCollection();
                foreach ($recordMs as $recordM) {
                    $recordM->delete();
                }
                $recordRandoms = Mage::getModel('gearup_activity/random')->getCollection();
                foreach ($recordRandoms as $recordRandom) {
                    $recordRandom->delete();
                }
                $expiresM = Mage::getModel('gearup_activity/expire');
                $expiresM->setExpireAt($weekEnd);
                $expiresM->save();
            }
        } else {
            $expiresM = Mage::getModel('gearup_activity/expire');
            $expiresM->setExpireAt($weekEnd);
            $expiresM->save();
        }
    }

    public function recordActivity($product, $type) {
        $weekEnd = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(strtotime('next saturday')));
        $now = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(strtotime('now')));
        $recordCollection = Mage::getModel('gearup_activity/record')->getCollection();
        $recordCollection->addFieldToFilter('product_id', array('eq'=>$product->getId()));
        $recordCollection->addFieldToFilter('type', array('eq'=>$type));
        $recordCollection->addFieldToFilter('created_at', array('lt'=>$weekEnd));
        if ($recordCollection->getSize()) {
            return $recordCollection->getFirstItem();
        }
        $rand = rand(5, 15);
        $recordModel = Mage::getModel('gearup_activity/record');
        $recordModel->setProductId($product->getId());
        $recordModel->setNumberCount($rand);
        $recordModel->setType($type);
        $recordModel->setCreatedAt($now);
        $recordModel->save();

        return $recordModel;
    }

    public function recordRandom($categoryId, $record) {
        $expiresHave = Mage::getModel('gearup_activity/random')->getCollection();
        $expiresHave->addFieldToFilter('category_id', array('eq' => $categoryId));
        if (!$expiresHave->getSize()) {
            $expire = Mage::getModel('gearup_activity/random');
            $expire->setCategoryId($categoryId);
            $expire->setRandomNumber($record);
            $expire->save();
        }
    }

    public function getrecordRandom($categoryId) {
        if (!$categoryId) {
            return false;
        }
        $expiresHave = Mage::getModel('gearup_activity/random')->getCollection();
        $expiresHave->addFieldToFilter('category_id', array('eq' => $categoryId));
        if ($expiresHave->getSize()) {
            $item = $expiresHave->getFirstItem();
            return explode(',', $item->getRandomNumber());
        } else {
            return false;
        }
    }

    public function getSellDaily($product) {
        $number = 0;
        $today = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(strtotime('today') - (3600 * 4)));
        $now = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(strtotime('now')));
        $orders = Mage::getResourceModel('sales/order_item_collection');
        $orders->addAttributeToFilter('product_id', array('eq' => $product->getId()));
        $orders->addAttributeToFilter('created_at', array('from' => $today));
        $orders->addAttributeToFilter('created_at', array('to' => $now));
        if ($orders->getSize()) {
            $number = $orders->getSize();
        }

        $msg = 'Purchased by '.$number.' customers this day';
        return $msg;
    }

    public function getViewedDaily($product) {
        $number = 0;
        $today = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(strtotime('today') - (3600 * 4)));
        $now = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(strtotime('now')));
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('report_viewed_product_index');
        $searchquery = "SELECT * FROM `{$table}` WHERE `product_id` = ".$product->getId()." AND `added_at` >= '".$today."' AND `added_at` < '".$now."';";
        $searchs = $readConnection->fetchAll($searchquery);

        if ($searchs) {
            $number = count($searchs);
        }

        $msg = $number.' Customers are viewed in this day';
        return $msg;
    }

    public function getWishlistDaily($product) {
        $number = 0;
        $today = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(strtotime('today') - (3600 * 4)));
        $now = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(strtotime('now')));
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('wishlist_item');
        $searchquery = "SELECT * FROM `{$table}` WHERE `product_id` = ".$product->getId()." AND `added_at` >= '".$today."' AND `added_at` < '".$now."';";
        $searchs = $readConnection->fetchAll($searchquery);

        if ($searchs) {
            $number = count($searchs);
        }

        $msg = $number.' Customers added to the wishlist in this day';
        return $msg;
    }

    public function getrecordRandoms($categoryIds) {
        if (!$categoryIds) {
            return false;
        }
        $expiresHave = Mage::getModel('gearup_activity/random')->getCollection();
        $expiresHave->addFieldToFilter('category_id', array('in' => $categoryIds));
        if ($expiresHave->getSize()) {
            $item = $expiresHave->getFirstItem();
            return explode(',', $item->getRandomNumber());
        } else {
            return false;
        }
    }
}
