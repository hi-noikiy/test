<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Block\Account;

class Program extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'account/program.phtml';

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Amasty\Affiliate\Model\ResourceModel\Program\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Amasty\Affiliate\Model\Transaction
     */
    protected $transaction;

    /**
     * @var \Amasty\Affiliate\Model\Program
     */
    private $program;

    /**
     * Program constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Amasty\Affiliate\Model\ResourceModel\Program\CollectionFactory $collectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Amasty\Affiliate\Model\Transaction $transaction
     * @param \Amasty\Affiliate\Model\Program $program
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amasty\Affiliate\Model\ResourceModel\Program\CollectionFactory $collectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Amasty\Affiliate\Model\Transaction $transaction,
        \Amasty\Affiliate\Model\Program $program,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->customerSession = $customerSession;
        $this->transaction = $transaction;
        $this->program = $program;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Affiliate Programs'));
    }

    public function getPrograms()
    {
        if (!$this->customerSession->getCustomerId()) {
            return false;
        }
        $programs = $this->collectionFactory->create();
        $programs->addActiveFilter();

        return $programs;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getPrograms()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'amasty.affiliate.program.pager'
            )->setCollection(
                $this->getPrograms()
            );
            $this->setChild('pager', $pager);
            $this->getPrograms()->load();
        }
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    public function prepareWithdrawalType($type)
    {
        $availableTypes = $this->transaction->getAvailableTypes();

        $type = $availableTypes[$type];

        return $type;
    }

    public function prepareDiscountType($type)
    {
        $availableTypes = $this->program->getAvailableDiscountTypes();

        $type = $availableTypes[$type];

        return $type;
    }
}
