<?php
declare(strict_types=1);


namespace Zaahed\AutoInvoice\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const ENABLE = 'sales/auto_invoice/enable';
    const PAYMENT_METHODS = 'sales/auto_invoice/payment_methods';

    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Is Auto Invoice enabled.
     *
     * @return bool
     */
    public function isEnabled():bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::ENABLE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get payment methods.
     *
     * @return array
     */
    public function getPaymentMethods(): array
    {
        $values = $this->scopeConfig->getValue(
            self::PAYMENT_METHODS,
            ScopeInterface::SCOPE_WEBSITE
        );

        return explode(',', $values);
    }

    /**
     * Is payment method supported.
     *
     * @return bool
     */
    public function isPaymentSupported(string $method)
    {
        return in_array(
            $method,
            $this->getPaymentMethods()
        );
    }
}
