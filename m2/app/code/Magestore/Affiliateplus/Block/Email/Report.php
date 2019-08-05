<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplus\Block\Email;

use Magestore\Affiliateplus\Model\Transaction;

class Report extends \Magestore\Affiliateplus\Block\AbstractTemplate
{
    /**
     * @param $statistic
     * @return array
     */
    public function prepareStatistic($statistic){
        $sales = 0;
        $transaction = 0;
        $commission = 0;
        foreach ($statistic as $sta){
            $sales += isset($sta['sales']) ? $sta['sales'] : 0;
            $transaction += isset($sta['transactions']) ? $sta['transactions'] : 0;
            $commission += isset($sta['transactions']) ? $sta['commissions'] : 0;
        }
        return array('sales' => $sales, 'transaction' => $transaction, 'commission' => $commission);
    }

    /**
     * @return array
     */
    public function getOptionLabels(){
        return array(
            'complete'	=> __('Complete'),
            'pending'	=> __('Pending'),
            'cancel'	=> __('Canceled'),
            'onhold'	=> __('On Hold'),
        );
    }
}