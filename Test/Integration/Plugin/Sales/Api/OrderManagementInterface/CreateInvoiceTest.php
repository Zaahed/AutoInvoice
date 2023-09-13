<?php
declare(strict_types=1);


namespace Zaahed\AutoInvoice\Test\Integration\Plugin\Sales\Api\OrderManagementInterface;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceSearchResultInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Zaahed\AutoInvoice\Plugin\Sales\Api\OrderManagementInterface\CreateInvoice;

/**
 * @magentoDataFixture Magento/Sales/_files/order.php
 */
class CreateInvoiceTest extends TestCase
{
    public function setUp(): void
    {
        $this->objectManager = ObjectManager::getInstance();
        $this->createInvoice = $this->objectManager->create(CreateInvoice::class);
        $this->orderRepository = $this->objectManager->create(OrderRepositoryInterface::class);
        $this->searchCriteriaBuilder = $this->objectManager->create(SearchCriteriaBuilder::class);
        $this->orderManagement = $this->objectManager->create(OrderManagementInterface::class);
        $this->invoiceRepository = $this->objectManager->create(InvoiceRepositoryInterface::class);
    }

    /**
     * Test auto invoice creation.
     *
     * @magentoConfigFixture current_website sales/auto_invoice/enable 1
     * @magentoConfigFixture current_website sales/auto_invoice/payment_methods checkmo
     * @return void
     */
    public function testAfterPlace()
    {
        $this->createInvoice->afterPlace(
            $this->orderManagement,
            $this->getOrder()
        );

        $invoices = $this->getInvoices();
        $this->assertEquals(
            1,
            $this->getInvoices()->getTotalCount()
        );

    }

    /**
     * Test that no invoice is created if a different payment method is configured.
     *
     * @magentoConfigFixture current_website sales/auto_invoice/enable 1
     * @magentoConfigFixture current_website sales/auto_invoice/payment_methods banktransfer
     * @return void
     */
    public function testAfterPlaceNoInvoice()
    {
        $this->createInvoice->afterPlace(
            $this->orderManagement,
            $this->getOrder()
        );

        $this->assertEquals(
            0,
            $this->getInvoices()->getTotalCount()
        );
    }

    /**
     * Get order invoices.
     *
     * @return InvoiceSearchResultInterface
     */
    private function getInvoices(): InvoiceSearchResultInterface
    {
        $orderId = $this->getOrder()->getId();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_id', $orderId)
            ->create();

        $invoices = $this->invoiceRepository->getList($searchCriteria);
        return $invoices;
    }

    /**
     * Get test order.
     *
     * @return OrderInterface
     */
    private function getOrder(): OrderInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', '100000001')
            ->create();

        $orders = $this->orderRepository->getList($searchCriteria)->getItems();
        return reset($orders);
    }
}
