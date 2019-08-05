<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CollinsHarper\CanadaPost\Helper;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;



class DataFactory
{

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;


    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create data helper
     *
     * @param string $area
     * @return \CollinsHarper\CanadaPost\Helper\*
     * @throws LocalizedException
     */
    public function create($helper)
    {
        return $this->objectManager->get($helper);
    }




}