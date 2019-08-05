<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Plugin\App\Action;

use Magento\Framework\App\RequestInterface;

class ContextPlugin
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @var \Amasty\Affiliate\Model\AccountRepository
     */
    private $accountRepository;

    /**
     * ContextPlugin constructor.
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Amasty\Affiliate\Model\AccountRepository $accountRepository
     */
    public function __construct(
        \Magento\Framework\App\Http\Context $httpContext,
        \Amasty\Affiliate\Model\AccountRepository $accountRepository
    ) {
        $this->httpContext = $httpContext;
        $this->accountRepository = $accountRepository;
    }

    public function beforeDispatch(\Magento\Framework\App\ActionInterface $subject, RequestInterface $request)
    {
        $this->httpContext->setValue(
            'amasty_affiliate_account',
            $this->accountRepository->isAffiliate(),
            false
        );
    }
}
