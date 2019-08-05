<?php

namespace ShippyPro\ShippyPro\Block;

class ShippyPro extends \Magento\Framework\View\Element\Template
{
    public function getAccessPointsUrl(){
        return $this->getBaseUrl() . "shippypro";
    }
}