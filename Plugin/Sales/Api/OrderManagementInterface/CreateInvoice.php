<?php
declare(strict_types=1);


namespace Zaahed\AutoInvoice\Plugin\Sales\Api\OrderManagementInterface;

use Magento\Framework\DB\TransactionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Service\InvoiceService;
use Psr\Log\LoggerInterface;
use Zaahed\AutoInvoice\Model\Config;

class CreateInvoice
{
    private InvoiceService $invoiceService;
    private TransactionFactory $transactionFactory;
    private Config $config;
    private LoggerInterface $logger;
    private OrderStatusHistoryInterfaceFactory $orderStatusHistoryFactory;

    /**
     * @param InvoiceService $invoiceService
     * @param TransactionFactory $transactionFactory
     * @param Config $config
     * @param LoggerInterface $logger
     * @param OrderStatusHistoryInterfaceFactory $orderStatusHistoryFactory
     */
    public function __construct(
        InvoiceService $invoiceService,
        TransactionFactory $transactionFactory,
        Config $config,
        LoggerInterface $logger,
        OrderStatusHistoryInterfaceFactory $orderStatusHistoryFactory
    ) {
        $this->invoiceService = $invoiceService;
        $this->transactionFactory = $transactionFactory;
        $this->config = $config;
        $this->logger = $logger;
        $this->orderStatusHistoryFactory = $orderStatusHistoryFactory;
    }

    /**
     * Create an invoice automatically after order placement.
     *
     * @param OrderManagementInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function afterPlace(OrderManagementInterface $subject, OrderInterface $order)
    {
        $paymentMethod = $order->getPayment()->getMethod();
        if (!$this->config->isEnabled() ||
            !$this->config->isPaymentSupported($paymentMethod)) {
            return $order;
        }

        try {
            /** @var Order $order */
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->register();

            $this->addAutoInvoiceComment($order);

            $transaction = $this->transactionFactory->create();
            $transaction->addObject($invoice);
            $transaction->addObject($order);
            $transaction->save();
        } catch (\Exception $e) {
            $this->logger->error(
                'Could not create invoice: ' . $e->getMessage()
            );
        }

        return $order;
    }

    /**
     * Add comment to order status history.
     *
     * @param OrderInterface $order
     * @return void
     */
    private function addAutoInvoiceComment(OrderInterface $order): void
    {
        $statusHistories = $order->getStatusHistories() ?? [];

        $statusHistory = $this->orderStatusHistoryFactory->create();
        $statusHistory->setStatus($order->getStatus());
        $statusHistory->setEntityName(Order::ENTITY);
        $statusHistory->setComment('Invoice automatically created.');
        $statusHistory->setIsVisibleOnFront(false);

        $statusHistories[] = $statusHistory;
        $order->setStatusHistories($statusHistories);
    }
}
