<?php

namespace MageSuite\StoreLocator\Model\PickupLocation\Mapper\PreProcessor;

class FrontendName implements \Magento\InventoryInStorePickupApi\Model\Mapper\PreProcessorInterface
{
    public function process(\Magento\InventoryApi\Api\Data\SourceInterface $source, $value): string
    {
        if (empty($value)) {
            $value = $source->getName();
        }

        return $value;
    }
}
