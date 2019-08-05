<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Plugin\User;

use Amasty\Affiliate\Api\Data\AccountInterface;

class SaveAccount
{
    const AFFILIATE_PARAMS_NAMESPACE = 'affiliate';

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @var \Amasty\Affiliate\Model\AccountFactory
     */
    private $accountFactory;

    /**
     * SaveAccount constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository
     * @param \Amasty\Affiliate\Model\AccountFactory $accountFactory
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        \Amasty\Affiliate\Model\AccountFactory $accountFactory
    ) {
        $this->request = $request;
        $this->accountRepository = $accountRepository;
        $this->accountFactory = $accountFactory;
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterDelete(
        $subject,
        $result
    ) {
        $subjectId = $subject->getId();
        /** @var \Amasty\Affiliate\Model\Account $account */
        $account = $this->accountRepository->getByCustomerId($subjectId);
        $this->accountRepository->delete($account);

        return $result;
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterSave($subject, $result)
    {
        $data = $this->request->getParam(self::AFFILIATE_PARAMS_NAMESPACE);
        $account = $this->accountRepository->getByCustomerId($subject->getId());
        if (!$account->getId()) {
            $account->setCustomerId($subject->getId());
        }

        $receiveNotifications = isset($data[AccountInterface::RECEIVE_NOTIFICATIONS])
            ? $data[AccountInterface::RECEIVE_NOTIFICATIONS] : 0;
        $isAffiliateActive = isset($data[AccountInterface::IS_AFFILIATE_ACTIVE])
            ? $data[AccountInterface::IS_AFFILIATE_ACTIVE] : 0;

        $account->setIsAffiliateActive($isAffiliateActive)
            ->setReceiveNotifications($receiveNotifications)
            ->save();

        return $result;
    }
}
