<?php
namespace MageSuite\StoreLocator\Helper;

class Configuration
{
    const META_TITLE_CONFIG_PATH = 'store_locator/page/meta_title';
    const META_DESCRIPTION_CONFIG_PATH = 'store_locator/page/meta_description';
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigInterface;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface)
    {
        $this->scopeConfigInterface = $scopeConfigInterface;
    }

    public function getMetaTitle()
    {
        return $this->scopeConfigInterface->getValue(self::META_TITLE_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getMetaDescription()
    {
        return $this->scopeConfigInterface->getValue(self::META_DESCRIPTION_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}