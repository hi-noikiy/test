<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SecurityAuth
 */


namespace Amasty\SecurityAuth\Plugin;

class UserSave
{
    /**
     * @var \Amasty\SecurityAuth\Model\AuthRepository
     */
    protected $authRepository;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session;

    /**
     * @var \Amasty\SecurityAuth\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\User\Model\User
     */
    protected $userModel;

    /**
     * UserSave constructor.
     * @param \Amasty\SecurityAuth\Model\AuthRepository $authRepository
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Backend\Model\Session $session
     * @param \Amasty\SecurityAuth\Helper\Data $helper
     * @param \Magento\User\Model\User $userModel
     */
    public function __construct(
        \Amasty\SecurityAuth\Model\AuthRepository $authRepository,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Backend\Model\Session $session,
        \Amasty\SecurityAuth\Helper\Data $helper,
        \Magento\User\Model\User $userModel
    ) {
        $this->authRepository = $authRepository;
        $this->messageManager = $messageManager;
        $this->session = $session;
        $this->helper = $helper;
        $this->userModel = $userModel;
    }

    public function afterExecute(\Magento\User\Controller\Adminhtml\User\Save\Interceptor $subject)
    {
        if ($this->helper->isActive()) {
            $request = $subject->getRequest();
            $user = $this->userModel->loadByUsername($request->getParam('username'));
            if ($userId = $user->getId()) {
                if (!(count($this->messageManager->getMessages()->getErrors()) > 0)) {
                    $userAuth = $this->authRepository->getByUserId($userId);
                    $userAuth->setUserId($userId);
                    $userAuth->setEnable($request->getParam('securityauth_active'));
                    $userAuth->setTwoFactorToken($request->getParam('securityauth_secret'));
                    $this->authRepository->save($userAuth);
                } else {
                    $this->session->setUserIdTwoAuth($userId);
                }
            }
        }
    }
}
