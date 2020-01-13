<?php

namespace MageSuite\StoreLocator\Plugin\InventoryApi\SourceRepository;

class LoadStoreLocatorOnGetListPlugin
{
    /**
     * @var \MageSuite\StoreLocator\Model\Source\InitStoreLocatorExtensionAttributes
     */
    protected $setExtensionAttributes;

    /**
     * @param \MageSuite\StoreLocator\Model\Source\InitStoreLocatorExtensionAttributes $setExtensionAttributes
     */
    public function __construct(
        \MageSuite\StoreLocator\Model\Source\InitStoreLocatorExtensionAttributes $setExtensionAttributes
    ) {
        $this->setExtensionAttributes = $setExtensionAttributes;
    }

    public function afterGetList(
        \Magento\InventoryApi\Api\SourceRepositoryInterface $subject,
        \Magento\InventoryApi\Api\Data\SourceSearchResultsInterface $sourceSearchResults
    ) {
        $items = $sourceSearchResults->getItems();
        array_walk(
            $items,
            [$this->setExtensionAttributes, 'execute']
        );

        return $sourceSearchResults;
    }
}
