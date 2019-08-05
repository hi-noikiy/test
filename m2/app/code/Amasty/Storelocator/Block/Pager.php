<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Block;

use Magento\Framework\View\Element\Template;

class Pager extends \Magento\Theme\Block\Html\Pager
{
    /**
     * Pager constructor.
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * Return correct URL for ajax request
     *
     * @param array $params
     * @return string
     */
    public function getPagerUrl($params = [])
    {
        if ($query = $this->getRequest()->getParam('query')) {
            $params['query'] = $query;
        }

        return $this->_urlBuilder->getUrl('amlocator/index/ajax', $params);
    }
}
