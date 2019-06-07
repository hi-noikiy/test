<?php

class Gearup_Blog_Block_Last extends AW_Blog_Block_Last
{
    public function getCategoryName($id)
    {       
        $cats = Mage::getModel('blog/cat')->getCollection()
                ->addPostFilter($id)
                ->addStoreFilter(Mage::app()->getStore()->getId());

        foreach ($cats as $cat) {
            $catName[] = $cat->getTitle();
        } 

        return $catName;
    }

    public function getEventUrl($id,$identifier)
    {       
        $cats = Mage::getModel('blog/cat')->getCollection()
                ->addPostFilter($id)
                ->addStoreFilter(Mage::app()->getStore()->getId());
        $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        $catIdentifier = array();
        $url = '';
        foreach ($cats as $cat) {
            $catIdentifier[] = $cat->getIdentifier();
        } 
        if(isset($catIdentifier[0])){
            $url = $baseUrl.'news/cat/'.$catIdentifier[0].'/post/'.$identifier;
        }
        return $url;
    }

    public function getRating($id) {

        if (!$this->hasData('postRating' . $id)) {
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $query = 'SELECT percent FROM rating_option_vote_aggregated as blog_rating WHERE rating_id = ' .
                    new Zend_Db_Expr('(SELECT rating_id FROM  rating WHERE entity_id = ' .
                    new Zend_Db_Expr('(SELECT entity_id FROM rating_entity WHERE  entity_code like "' .
                    Gearup_Blog_Model_RateAggregate::BLOG_RATING_ENTITY . '") ) AND blog_rating.entity_pk_value=' . $id)
            );
            $this->setData('postRating' . $id,$readConnection->fetchOne($query));
           
        }

        return $this->getData('postRating' . $id);
    }

    public function getComment($id)
    {
        $collection = Mage::getModel('blog/comment')->getCollection()
                    ->addPostFilter($id)
                    ->addApproveFilter(2);
                      
        return count($collection);
    }
}
