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
 * @package     Magestore_Affiliateplusprogram
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplusprogram\Model\ResourceModel;

/**
 * Class Joined
 * @package Magestore\Affiliateplusprogram\Model\ResourceModel
 */
class Joined extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(\Magestore\Affiliateplusprogram\Setup\InstallSchema::SCHEMA_PROGRAM_JOINED,'id');
    }

    /**
     * @param null $programId
     * @param null $accountId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateJoinedDatabase($programId = null, $accountId = null) {
        $adapter = $this->getConnection();
        $selectSQL = $adapter->select()->reset()
            ->from(array('a' => $this->getTable(\Magestore\Affiliateplusprogram\Setup\InstallSchema::SCHEMA_PROGRAM_ACCOUNT)), array())
            ->columns(array('program_id', 'account_id'));
        if ($programId) {
            $selectSQL->where('program_id = ?', $programId);
        }
        if ($accountId) {
            $selectSQL->where('account_id = ?', $accountId);
        }
        $insertSQL = $selectSQL->insertFromSelect($this->getMainTable(),
            array('program_id', 'account_id'),
            true
        );
        $adapter->query($insertSQL);
        return $this;
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function insertJoinedDatabase(\Magento\Framework\Model\AbstractModel $object) {
        $adapter = $this->getConnection();

        $select = $adapter->select()
            ->from($this->getMainTable(), array($this->getIdFieldName()))
            ->where('program_id = ?', $object->getData('program_id'))
            ->where('account_id = ?', $object->getData('account_id'));
        if ($adapter->fetchOne($select) === false) {
            $object->setId(null);
            $this->save($object);
        }
        return $this;
    }
}
