<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Setup;

use Aheadworks\StoreCredit\Model\Source\Transaction\EntityType;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Aheadworks\StoreCredit\Model\Source\TransactionType;
use Magento\Sales\Api\OrderRepositoryInterface;
use Aheadworks\StoreCredit\Model\Comment\CommentPoolInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\State;
use Magento\Sales\Api\CreditmemoRepositoryInterface;

/**
 * Class UpgradeSchema
 *
 * @package Aheadworks\StoreCredit\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var CommentPoolInterface
     */
    private $commentPool;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    /**
     * @param State $appState
     * @param CommentPoolInterface $commentPool
     * @param OrderRepositoryInterface $orderRepository
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     */
    public function __construct(
        State $appState,
        CommentPoolInterface $commentPool,
        OrderRepositoryInterface $orderRepository,
        CreditmemoRepositoryInterface $creditmemoRepository
    ) {
        try {
            if (!$appState->getAreaCode()) {
                $appState->setAreaCode('adminhtml');
            }
        } catch (LocalizedException $e) {
            $appState->setAreaCode('adminhtml');
        }
        $this->commentPool = $commentPool;
        $this->orderRepository = $orderRepository;
        $this->creditmemoRepository = $creditmemoRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if ($context->getVersion() && version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->addColumnsToTransactionTable($setup);
            $this->addTransactionEntityTable($setup);
            $this->updateTransactionData($setup);
        }
    }

    /**
     * Add transaction entity table
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    private function addTransactionEntityTable(SchemaSetupInterface $installer)
    {
        /**
         * Create table 'aw_sc_transaction_entity'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_sc_transaction_entity'))
            ->addColumn(
                'transaction_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Transaction Id'
            )->addColumn(
                'entity_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'unsigned' => true, 'primary' => true],
                'Entity Type'
            )->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true, 'primary' => true],
                'Entity Id'
            )->addColumn(
                'entity_label',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                ['nullable' => true],
                'Entity Label'
            )->addIndex(
                $installer->getIdxName('aw_sc_transaction_entity', ['transaction_id', 'entity_type', 'entity_id']),
                ['transaction_id', 'entity_type', 'entity_id']
            )->addForeignKey(
                $installer->getFkName(
                    'aw_sc_transaction_entity',
                    'transaction_id',
                    'aw_sc_transaction',
                    'transaction_id'
                ),
                'transaction_id',
                $installer->getTable('aw_sc_transaction'),
                'transaction_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Aheadworks Store Credit Transaction Entity');
        $installer->getConnection()->createTable($table);
    }

    /**
     * Add columns to transaction table
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    private function addColumnsToTransactionTable(SchemaSetupInterface $installer)
    {
        $connection = $installer->getConnection();
        $connection->addColumn(
            $installer->getTable('aw_sc_transaction'),
            'comment_to_customer_placeholder',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'length' => 255,
                'after' => 'comment_to_customer',
                'comment'  => 'Comment To Customer Placeholder'
            ]
        );
        $connection->addColumn(
            $installer->getTable('aw_sc_transaction'),
            'created_by',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => true,
                'unsigned' => true,
                'comment' => 'Created By'
            ]
        );
    }

    /**
     * Update transaction data
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    private function updateTransactionData(SchemaSetupInterface $installer)
    {
        $connection = $installer->getConnection();

        $oldComments = [
            TransactionType::STORE_CREDIT_USED_IN_ORDER => [
                'parse' => 1,
                'comment' => 'spent_for_order'
            ],
            TransactionType::ORDER_CANCELED => [
                'parse' => 1,
                'comment' => 'reimbursed_spent_sс_on_order_cancel'
            ],
            TransactionType::REFUND_BY_STORE_CREDIT => [
                'parse' => 2,
                'comment' => 'refund_to_store_credit'
            ],
            TransactionType::REIMBURSE_OF_SPENT_STORE_CREDIT => [
                'parse' => 2,
                'comment' => 'reimbursed_spent_store_credit'
            ]
        ];
        $select = $connection->select()
            ->from($installer->getTable('aw_sc_transaction'));
        $transactions = $connection->fetchAssoc($select);

        // Convert comment
        foreach ($transactions as $transaction) {
            $updateParams = [];
            if (isset($oldComments[$transaction['type']])) {
                $commentArguments = [];
                $param = $oldComments[$transaction['type']];

                switch ($param['parse']) {
                    case 1:
                        $orderId = str_replace($param['comment'] . '_', '', $transaction['comment_to_customer']);
                        try {
                            $order = $this->orderRepository->get($orderId);
                        } catch (NoSuchEntityException $e) {
                            continue;
                        }
                        $bind = [
                            [
                                'transaction_id' => $transaction['transaction_id'],
                                'entity_id'    => $order->getEntityId(),
                                'entity_type'  => EntityType::ORDER_ID,
                                'entity_label' => $order->getIncrementId()
                            ]
                        ];
                        $commentArguments = [
                            EntityType::ORDER_ID => [
                                'entity_id' => $order->getEntityId(),
                                'entity_label' => $order->getIncrementId()
                            ]
                        ];
                        break;
                    case 2:
                        $data = explode(
                            '_',
                            str_replace($param['comment'] . '_', '', $transaction['comment_to_customer'])
                        );
                        try {
                            $order = $this->orderRepository->get($data[0]);
                        } catch (NoSuchEntityException $e) {
                            continue;
                        }
                        try {
                            $creditMemo = $this->creditmemoRepository->get($data[1]);
                        } catch (NoSuchEntityException $e) {
                            continue;
                        }
                        $bind = [
                            [
                                'transaction_id' => $transaction['transaction_id'],
                                'entity_id'    => $order->getEntityId(),
                                'entity_type'  => EntityType::ORDER_ID,
                                'entity_label' => $order->getIncrementId()
                            ],
                            [
                                'transaction_id' => $transaction['transaction_id'],
                                'entity_id'    => $creditMemo->getEntityId(),
                                'entity_type'  => EntityType::CREDIT_MEMO_ID,
                                'entity_label' => $creditMemo->getIncrementId()
                            ]
                        ];
                        $commentArguments = [
                            EntityType::ORDER_ID => [
                                'entity_id' => $order->getEntityId(),
                                'entity_label' => $order->getIncrementId()
                            ],
                            EntityType::CREDIT_MEMO_ID => [
                                'entity_id' => $creditMemo->getEntityId(),
                                'entity_label' => $creditMemo->getIncrementId()
                            ]
                        ];
                        break;
                }
                $connection->insertMultiple($installer->getTable('aw_sc_transaction_entity'), $bind);

                $commentInstance = $this->commentPool->get($transaction['type']);
                $updateParams['comment_to_customer'] = $commentInstance->renderComment($commentArguments);
                $updateParams['comment_to_customer_placeholder'] = $commentInstance->getLabel();

                $connection->update(
                    $installer->getTable('aw_sc_transaction'),
                    $updateParams,
                    'transaction_id = ' . $transaction['transaction_id']
                );
            }
        }
    }
}
