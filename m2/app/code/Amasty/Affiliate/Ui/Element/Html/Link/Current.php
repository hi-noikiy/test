<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Ui\Element\Html\Link;

use Amasty\Affiliate\Controller\Router;

class Current extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * @var \Amasty\Affiliate\Model\Account
     */
    private $account;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @var array
     */
    protected $availableLayouts = [
        'amasty-affiliate-account-navigation-program'
    ];

    /**
     * @var \Amasty\Affiliate\Model\Url
     */
    private $url;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Amasty\Affiliate\Model\Account $account,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        \Amasty\Affiliate\Model\Url $url,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->account = $account;
        $this->accountRepository = $accountRepository;
        $this->url = $url;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (in_array($this->getNameInLayout(), $this->availableLayouts)) {
            return parent::_toHtml();
        }

        if ($this->accountRepository->isAffiliate()
            && $this->accountRepository->getCurrentAccount()->getIsAffiliateActive()) {
            return parent::_toHtml();
        }

        return '';
    }

    public function getHref()
    {
        return $this->getUrl($this->url->getPath($this->getPath()));
    }

    public function isCurrent()
    {
        return $this->getUrl(Router::AMASTY_AFFILIATE_URL_STANDARD_PREFIX . '/' . $this->getPath())
            == $this->getUrl($this->getMca());
    }

    /**
     * Copy-paste from parent class just to be able to override isCurrent function
     *
     * @return string
     */
    private function getMca()
    {
        $routeParts = [
            'module' => $this->_request->getModuleName(),
            'controller' => $this->_request->getControllerName(),
            'action' => $this->_request->getActionName(),
        ];

        $parts = [];
        foreach ($routeParts as $key => $value) {
            if (!empty($value) && $value != $this->_defaultPath->getPart($key)) {
                $parts[] = $value;
            }
        }
        return implode('/', $parts);
    }
}
