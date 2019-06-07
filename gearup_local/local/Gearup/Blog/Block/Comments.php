<?php

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Blog
 * @version    tip
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */
class Gearup_Blog_Block_Comments extends Gearup_Blog_Block_Post {

    const DEFAULT_COMMENT_SORT_ORDER = 'created_time';
    const DEFAULT_COMMENT_SORT_DIR = 'desc';

    protected function _prepareLayout() {
        $head = $this->getLayout()->getBlock('head');
        if ($head) {
            $head->unsetChild('aw_blog_og');
        }
    }

    public function getComment() {
        $postId = $this->getRequest()->getParam('postId');


        if (!$this->hasData('commentCollection1')) {
            $sortOrder = $this->getRequest()->getParam('order', self::DEFAULT_COMMENT_SORT_ORDER);
            $sortDirection = $this->getRequest()->getParam('dir', self::DEFAULT_COMMENT_SORT_DIR);
            $collection = Mage::getModel('blog/comment')
                    ->getCollection()
                    ->addPostFilter($postId)
                    ->addApproveFilter(2)
            ;

            $collection->setOrder($collection->getConnection()->quote($sortOrder), $sortDirection);
            $this->setData('commentCollection1', $collection);
        }
        return $this->getData('commentCollection1');
    }

}
