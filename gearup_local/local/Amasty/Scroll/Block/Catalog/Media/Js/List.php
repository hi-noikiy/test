<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Scroll
 */


class Amasty_Scroll_Block_Catalog_Media_Js_List extends Mage_ConfigurableSwatches_Block_Catalog_Media_Js_List
{
    const MEDIA_EVENT = 'product-media-loaded';

    /**
     * @var int
     */
    private $page = null;

    /**
     * @param int $page
     */
    public function setCurrentPage($page)
    {
        $this->page = $page;
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml() {
        $html = parent::_toHtml();
        if ($this->page && !empty($html)) {
            $html = str_replace(self::MEDIA_EVENT, self::MEDIA_EVENT . "-$this->page", $html);
        }

        return $html;
    }
}
