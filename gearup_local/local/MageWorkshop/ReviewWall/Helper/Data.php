<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_ReviewWall
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

/**
 * Class MageWorkshop_ReviewWall_Helper_Data
 */
class MageWorkshop_ReviewWall_Helper_Data extends Mage_Core_Helper_Abstract
{
    const REVIEWWALL_XML_PATH_SHARE_EMAIL_TEMPLATE        = 'reviewwall/share_review_by_email/template';
    const REVIEWWALL_XML_PATH_BLIND_COPY_TO_FOR_CUSTOMER  = 'reviewwall/share_review_by_email/blind_copy_to';
    const REVIEWWALL_XML_PATH_TEMPLATE_ID  = 'reviewwall/reviewwall_settings/template_id';

    const REVIEWWALL_MODULE_NAME = 'MageWorkshop_ReviewWall';
    const REVIEWWALL_PACKAGE_FILE = 'ReviewWall';
    const REVIEWWALL_UNINSTALL_PATH = 'reviewwall/uninstall';

    const REVIEW_IMAGE_FRONTEND_TEMPLATE = 'mageworkshop/reviewwall/template/image_review.phtml';
    const ALL_REVIEW_FRONTEND_TEMPLATE = 'mageworkshop/reviewwall/template/all_review.phtml';

    const DEFAULT_FRONTEND_TEMPLATE = 'mageworkshop/reviewwall/template/image_review.phtml';
    const REVIEW_IMAGE_JS_TEMPLATE = 'mageworkshop/reviewwall/js_template/image.phtml';
    const ALL_REVIEW_JS_TEMPLATE = 'mageworkshop/reviewwall/js_template/all_review.phtml';


    /**
     * Build Facebook Social Link
     *
     * @param $product Mage_Catalog_Model_Product
     * @param $review MageWorkshop_DetailedReview_Model_Review
     * @return string
     */
    public function buildFbSocialLink($product, $review)
    {
        $shareUrl     = urlencode($product->getProductUrl() . '#rw_' . $review->getId());
        /** @var MageWorkshop_ReviewWall_Block_Widget_Part_SocialShare_Facebook $block */
        $block = Mage::app()->getLayout()->createBlock('reviewwall/widget_part_socialShare_facebook');
        $block
            ->setReview($review)
            ->setShareUrl($shareUrl);

        return $block->toHtml();
    }

    /**
     * Build Twitter Social Link
     *
     * @param $product Mage_Catalog_Model_Product
     * @param $review MageWorkshop_DetailedReview_Model_Review
     * @return string
     */
    public function buildTwitterSocialLink($product, $review)
    {
        $shareUrl = $product->getProductUrl() . '#rw_'.$review->getId();
        $block = Mage::app()->getLayout()->createBlock('reviewwall/widget_part_socialShare_twitter');
        $block
            ->setReview($review)
            ->setShareUrl($shareUrl);

        return $block->toHtml();

    }

    /**
     * Build Votes Block
     *
     * @param $review MageWorkshop_DetailedReview_Model_Review
     * @return string
     */
    public function buildVotesBlock($review)
    {
        $block = Mage::app()->getLayout()->createBlock('reviewwall/widget_part_helpful');
        $block->setReview($review);
        return $block->toHtml();
    }

    /**
     * Build Pinterest Social Link
     *
     * @param $product Mage_Catalog_Model_Product
     * @param $review MageWorkshop_DetailedReview_Model_Review
     * @return string
     */
    public function buildPinterestSocialLink($product, $review)
    {
        $shareUrl = urlencode($product->getProductUrl() . '#rw_'.$review->getId());
        $block = Mage::app()->getLayout()->createBlock('reviewwall/widget_part_socialShare_pinterest');
        $block
            ->setReview($review)
            ->setShareUrl($shareUrl);

        return $block->toHtml();
    }

    /**
     * Build Share Review by email block
     *
     * @param $review MageWorkshop_DetailedReview_Model_Review
     * @return string
     */
    public function buildMailtoSocialLink($review)
    {
        $shareUrl = Mage::getModel('core/url')->sessionUrlVar($review->getProduct()->getProductUrl() . '#rw_' . $review->getId());

        $block = Mage::app()->getLayout()->createBlock('reviewwall/widget_part_socialShare_email');
        $block
            ->setReview($review)
            ->setShareUrl($shareUrl);

        return $block->toHtml();
    }

    public function getTemplate()
    {
        $idTemplate = Mage::getStoreConfig(self::REVIEWWALL_XML_PATH_TEMPLATE_ID);
        $templates = array(
            1 => self::ALL_REVIEW_FRONTEND_TEMPLATE,
            2 => self::REVIEW_IMAGE_FRONTEND_TEMPLATE
        );

        return  array_key_exists($idTemplate, $templates) ? $templates[$idTemplate] : self::DEFAULT_FRONTEND_TEMPLATE;
    }

    public function getJsTemplate()
    {
        $idTemplate = Mage::getStoreConfig(self::REVIEWWALL_XML_PATH_TEMPLATE_ID);
        $templates = array(
            1 => self::ALL_REVIEW_JS_TEMPLATE,
            2 => self::REVIEW_IMAGE_JS_TEMPLATE
        );

        return  array_key_exists($idTemplate, $templates) ? $templates[$idTemplate] : self::ALL_REVIEW_JS_TEMPLATE;
    }

    public function getCountCharsInReview()
    {
        return (int) Mage::getStoreConfig('reviewwall/reviewwall_settings/count_chars_in_review')
            ? (int) Mage::getStoreConfig('reviewwall/reviewwall_settings/count_chars_in_review')
            : 200;
    }

    public function getCssClassName()
    {
        $idTemplate = Mage::getStoreConfig(self::REVIEWWALL_XML_PATH_TEMPLATE_ID);
        $classes = array(
            1 => 'no-image',
            2 => 'image'
        );
        return  array_key_exists($idTemplate, $classes) ? $classes[$idTemplate] : '';
    }

    public function getTimeElapsedString($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        /** @var DateInterval $diff */
        $diff = $now->diff($ago);
        $diff->d -= floor($diff->d / 7) * 7;

        $singular = array(
            'y' => $this->__('year'),
            'm' => $this->__('month'),
            'd' => $this->__('day'),
            'h' => $this->__('hour'),
            'i' => $this->__('minute'),
            's' => $this->__('second'),
        );

        $plural = array(
            'y' => $this->__('years'),
            'm' => $this->__('months'),
            'd' => $this->__('days'),
            'h' => $this->__('hours'),
            'i' => $this->__('minutes'),
            's' => $this->__('seconds'),
        );

        $result = array();

        foreach ($singular as $k => $v) {
            if ($diff->$k) {
                $result[$k] = $diff->$k . ' ' . ($diff->$k > 1 ? $plural[$k] : $singular[$k]);
            }
        }

        if (!$full) $result = array_slice($result, 0, 1);
        return $result ? implode(', ', $result) . $this->__(' ago') : $this->__('just now');
    }

    /**
     * Get average rating of review
     *
     * @param $items
     * @return float|int
     */
    public function getAverageReviewRating($items)
    {
        $result = 0;
        if ($items) {
            $percent = 0;
            /**@var Mage_Rating_Model_Rating $item */
            foreach ($items as $i => $item) {
                $percent += $item->getPercent();
            }
            $result = $percent/count($items);
        }

        return $result;
    }

    /**
     * @return Mage_Review_Model_Mysql4_Review_Collection
     */
    protected function getReviewCollection()
    {
        return Mage::getModel('review/review')->getCollection()
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED);
    }

    /**
     * @return int
     */
    public function getTotalReviewsCount()
    {
        return $this->getReviewCollection()->count();
    }
    
    /**
     * @return float
     */
    public function getAverageRatingByTotalReviews()
    {
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        
        $select = $read->select()
            ->from(array('rating_aggregated' => $resource->getTableName('rating/rating_vote_aggregated')), array('AVG(percent_approved)'))
            ->join(
                array('rating' => $resource->getTableName('rating/rating')),
                'rating_aggregated.rating_id = rating.rating_id',
                null
            )
            ->join(
                array('rating_entity' => $resource->getTableName('rating/rating_entity')),
                'rating.entity_id = rating_entity.entity_id',
                null
            )
            ->where('rating_aggregated.store_id = ?', Mage::app()->getStore()->getId())
            ->where('rating_entity.entity_code = ?', Mage_Rating_Model_Rating::ENTITY_PRODUCT_CODE);
        
        
        return round($read->fetchOne($select), 2);
    }

    /**
     * @param array $params
     * @return array
     */
    public function prepareShareReviewData($params)
    {
        $data = array(
            'sender'          => array(
                'name'  => Mage::getStoreConfig('trans_email/ident_sales/name'),
                'email' => Mage::getStoreConfig('trans_email/ident_sales/email')
            ),
            'recipient_email' => $params['drw-email-share-mail-to'],
            'copy_to_path'    => Mage::getStoreConfig(self::REVIEWWALL_XML_PATH_BLIND_COPY_TO_FOR_CUSTOMER)
                ? self::REVIEWWALL_XML_PATH_BLIND_COPY_TO_FOR_CUSTOMER
                : 'trans_email/ident_support/email',
            'copy_method'     => 'bcc',
            'template_id'     => Mage::getStoreConfig(self::REVIEWWALL_XML_PATH_SHARE_EMAIL_TEMPLATE),
            'template_params' => array(
                'customer_name'      => $params['drw-email-share-customer-name'],
                'customer_email'     => $params['drw-email-share-customer-email'],
                'message'            => $params['drw-email-share-mail-body'],
                'review_link'        => $params['drw-email-share-link'],
                'review_title'       => $params['drw-email-share-subject'],
                'share_review_image' => $params['drw-email-share-image'],
                'img_alt'            => $this->__('Share Image')
            )
        );
        return $data;
    }
    
    /**
     * @param $reviewsCollection MageWorkshop_DetailedReview_Model_Mysql4_Review_Collection
     * @return array
     */
    public function prepareResponseData($reviewsCollection)
    {
        $responseData = array();
        /** @var $drwHelper MageWorkshop_ReviewWall_Helper_Data */
        $drwHelper = Mage::helper('reviewwall');
    
        /** @var $detailedReviewHelper MageWorkshop_DetailedReview_Helper_Data */
        $detailedReviewHelper = Mage::helper('detailedreview');
        
        /** @var $_review MageWorkshop_DetailedReview_Model_Review */
        foreach ($reviewsCollection as $keyReview => $_review) {
            $_review->setDetail($detailedReviewHelper->smartyModifierTruncate(
                $this->escapeHtml($_review->getDetail()),
                $drwHelper->getCountCharsInReview()
            ));
        
            $responseData[$keyReview]            = $_review->getData();
            $responseData[$keyReview]['product'] = $_review->getProduct()->getData();
            $responseData[$keyReview]['product']['drw_url'] = $_review->getProduct()->getUrlInStore(array('_ignore_category' => true));
            $responseData[$keyReview]['fb']      = $drwHelper->buildFbSocialLink($_review->getProduct(), $_review);
            $responseData[$keyReview]['tw']      = $drwHelper->buildTwitterSocialLink($_review->getProduct(), $_review);
            if ($_review->getImage()) {
                $responseData[$keyReview]['pt']      = $drwHelper->buildPinterestSocialLink($_review->getProduct(), $_review);
            }
            $responseData[$keyReview]['vote']    = $drwHelper->buildVotesBlock($_review);
            $responseData[$keyReview]['rating']  = $drwHelper->getAverageReviewRating($_review->getRatingVotes()->getItems());
            $responseData[$keyReview]['reviewedBy']  = $drwHelper->__('Review by ');
        
            if (Mage::getStoreConfig('reviewwall/share_review_by_email/enabled')) {
                $responseData[$keyReview]['mailto'] = $drwHelper->buildMailtoSocialLink($_review);
            }
        }
        
        return $responseData;
    }
}
