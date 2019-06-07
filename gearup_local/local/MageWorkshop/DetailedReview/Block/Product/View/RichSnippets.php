<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */
class MageWorkshop_DetailedReview_Block_Product_View_RichSnippets extends Mage_Review_Block_Product_View
{
    public function getJsonRichSnippets()
    {
        $config = array();

        /** @var Mage_Catalog_Model_Product $product */
        $product = $this->getProduct();
        /** @var Mage_Review_Model_Resource_Review_Collection $reviewCollection */
        $reviewCollection = $this->getReviewsCollection();

        if ((int) $count = Mage::getStoreConfig(MageWorkshop_DetailedReview_Helper_Config::DETAILEDREVIEW_XML_PATH_SEO_REVIEW_COUNT)) {
            $reviewCollection->getSelect()->limit($count);
        }
        $reviewCollection->addRateVotes();

        if ($product && $reviewCollection) {
            $aggregate = array();
            if ($reviewCollection->getSize()) {
                $aggregate = array(
                    '@type'             => 'AggregateRating',
                    'ratingValue'       => ((int) $product->getRatingSummary()->getData('rating_summary') * 5) / 100,
                    'reviewCount'       => $product->getRatingSummary()->getData('reviews_count')
                );
            }
            $config = array(
                '@context'          => 'http://schema.org',
                '@type'             => 'Product',
                'aggregateRating'   => $aggregate,
                'description'       => $product->getData('description'),
                'name'              => $product->getName(),
                'image'             => $product->getImageUrl(),
                'offers'            => array(
                    '@type'             => 'Offer',
                    'availability'      => $product->isInStock() ? 'http://schema.org/InStock' : 'http://schema.org/OutOfStock',
                    'price'             => $product->getPrice(),
                    'priceCurrency'     => Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol()
                ),
                'review'            => array()
            );

            $reviewModel = Mage::getModel('review/review');
            $reviewIds = $reviewCollection->getAllIds();
            $repliesCollection = $reviewModel->getCollection();
            $repliesCollection
                ->addFieldToFilter('entity_pk_value', array('in' => $reviewIds))
                ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                ->addFieldToFilter('entity_id', $reviewModel->getEntityIdByCode('review'));

            $replies = array();
            foreach ($repliesCollection as $reply) {
                $replies[$reply->getEntityPkValue()][] = $reply;
            }

            foreach ($reviewCollection as $review) {
                $comments = isset($replies[$review->getId()]) ? $replies[$review->getId()] : array();
                $config['review'][] = $this->prepareReview($review, $comments);
            }
        }

        return Mage::helper('core')->jsonEncode($config);
    }


    /**
     * @param MageWorkshop_DetailedReview_Model_Review $review
     * @param array $comments
     * @return array
     */
    protected function prepareReview($review, $comments)
    {
        $reviewRating = array();
        $ratingCount = 0;
        $ratingSum = 0;
        $ratingVotes = $review->getData('rating_votes');
        /** @var Mage_Rating_Model_Rating_Option $rating */
        foreach ($ratingVotes as $rating) {
            $ratingCount = $ratingCount + 1;
            $ratingSum = $ratingSum + $rating->getData('value');
        }

        if ($ratingSum && $ratingCount) {
            $avgRating = $ratingSum / $ratingCount;
            $reviewRating = array(
                '@type'         => 'Rating',
                'bestRating'    => '5',
                'ratingValue'   => round($avgRating, 2),
                'worstRating'   => '1'
            );
        }

        $date = new Zend_Date($review->getCreatedAt());
        $timestampWithOffset = $date->get() - Mage::getSingleton('core/date')->getGmtOffset();

        return array(
            '@type'             => 'Review',
            'author'            => $review->getNickname(),
            'datePublished'     => date('Y-m-d', $timestampWithOffset),
            'description'       => $review->getDetail(),
            'name'              => $review->getTitle(),
            'reviewRating'      => $reviewRating,
            'comment'           => $this->prepareComment($comments)
        );
    }

    /**
     * @param array $comments
     * @return array
     */
    protected function prepareComment($comments)
    {
        $result = array();
        /** @var MageWorkshop_DetailedReview_Model_Review $reply */
        foreach ($comments as $reply) {
            $dateReply = new Zend_Date($reply->getCreatedAt());
            $replyTimestampWithOffset = $dateReply->get() - Mage::getSingleton('core/date')->getGmtOffset();

            $result[] = array(
                '@type'             => 'Comment',
                'author'            => $reply->getNickname(),
                'datePublished'     => date('Y-m-d', $replyTimestampWithOffset),
                'description'       => $reply->getDetail()
            );
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function isCrawler()
    {
        $crawlers = array(
            'Bot'               => 'bot',
            'Crawl'             => 'crawl',
            'Slurp'             => 'slurp',
            'Spider'            => 'spider',
            'Google'            => 'google',
            'MSN'               => 'msnbot',
            'Rambler'           => 'rambler',
            'Yahoo'             => 'yahoo',
            'AbachoBOT'         => 'abachobot',
            'accoona'           => 'accoona',
            'AcoiRobot'         => 'acoirobot',
            'ASPSeek'           => 'aspseek',
            'CrocCrawler'       => 'croccrawler',
            'Dumbot'            => 'dumbot',
            'FAST-WebCrawler'   => 'fast-webcrawler',
            'GeonaBot'          => 'geonabot',
            'Gigabot'           => 'gigabot',
            'Lycos spider'      => 'lycos',
            'MSRBOT'            => 'msrbot',
            'Altavista robot'   => 'scooter',
            'AltaVista robot'   => 'altavista',
            'ID-Search Bot'     => 'idbot',
            'eStyle Bot'        => 'estyle',
            'Scrubby robot'     => 'scrubby',
            'Facebook'          => 'facebookexternalhit',
        );

        $crawlers_agents = '/(' . implode('|', $crawlers) .')/';

        return !preg_match($crawlers_agents, strtolower($_SERVER['HTTP_USER_AGENT'])) === false;
    }

}
