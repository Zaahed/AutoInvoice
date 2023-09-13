<?php
declare(strict_types=1);


namespace Zaahed\AutoInvoice\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Payment\Helper\Data;

class OfflinePaymentMethods implements OptionSourceInterface
{
    const OFFLINE_GROUP = 'offline';
    private Data $paymentMethodHelper;

    /**
     * @param Data $paymentMethodHelper
     */
    public function __construct(Data $paymentMethodHelper)
    {
        $this->paymentMethodHelper = $paymentMethodHelper;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $result = [];

        foreach ($this->paymentMethodHelper->getPaymentMethods() as $code => $data) {
            if (!isset($data['group'])) {
                continue;
            }
            if ($data['group'] !== self::OFFLINE_GROUP) {
                continue;
            }

            $result[] = [
                'value' => $code,
                'label' => $data['title']
            ];
        }

        return $result;
    }
}
