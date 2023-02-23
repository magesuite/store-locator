<?php
declare(strict_types=1);

namespace MageSuite\StoreLocator\Test\Integration\Model\Import;

class SourceTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\Framework\App\ObjectManager $objectManager;
    protected ?\Magento\InventoryApi\Api\SourceRepositoryInterface $sourceRepository;
    protected ?\Magento\InventorySalesAdminUi\Model\ResourceModel\GetStockIdsBySourceCodes $getStockIdsBySourceCodes;
    protected ?\Magento\Framework\Filesystem $filesystem;
    protected ?\MageSuite\StoreLocator\Model\Import\Source $importModel;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->sourceRepository = $this->objectManager->get(\Magento\InventoryApi\Api\SourceRepositoryInterface::class);
        $this->getStockIdsBySourceCodes = $this->objectManager->get(\Magento\InventorySalesAdminUi\Model\ResourceModel\GetStockIdsBySourceCodes::class);
        $this->filesystem = $this->objectManager->get(\Magento\Framework\Filesystem::class);
        $this->importModel = $this->objectManager->get(\MageSuite\StoreLocator\Model\Import\Source::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento_InventoryApi::Test/_files/stock.php
     */
    public function testItImportInventorySource(): void
    {
        $pathToFile = __DIR__ . '/../../_files/import.csv';
        $directory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => $pathToFile,
                'directory' => $directory
            ]
        );
        $errors = $this->importModel->setSource(
            $source
        )->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                'entity' => \MageSuite\StoreLocator\Model\Import\Source::ENTITY_CODE,
                \Magento\ImportExport\Model\Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR => ','
            ]
        )->validateData();
        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->importModel->importData();

        $source = $this->sourceRepository->get('dummy_source_code');
        $this->assertEquals('Dummy name', $source->getName());
        $this->assertEquals('Dummy description', $source->getDescription());
        $this->assertEquals(48.206627, $source->getLatitude());
        $this->assertEquals(16.369188, $source->getLongitude());
        $this->assertEquals('US', $source->getCountryId());
        $this->assertEquals('Los Angeles', $source->getCity());
        $this->assertEquals('Street', $source->getStreet());
        $this->assertEquals(1, $source->getPostcode());
        $this->assertEquals('https://example.com/', $source->getExtensionAttributes()->getUrl());

        $stockIds = $this->getStockIdsBySourceCodes->execute(['dummy_source_code']);
        $this->assertTrue(in_array(10, $stockIds));
    }
}
