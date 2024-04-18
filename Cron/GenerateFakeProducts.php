<?php
namespace Hgati\SomeoneRecentlyBought\Cron;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Data\Collection;

class GenerateFakeProducts
{
    const CONFIG_PATH = 'hgati_someone_recently_bought/general/product_ids';
    const CRON_PATH = 'hgati_someone_recently_bought/general/fakecron';
    const LIMIT_PATH = 'hgati_someone_recently_bought/general/limit';

    protected $_logger;
    protected $_scopeConfig;
    protected $productRepository;
    protected $productCollectionFactory;
    protected $configWriter;    

    public function __construct(
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        ProductRepositoryInterface $productRepository,
        CollectionFactory $productCollectionFactory,
        WriterInterface $configWriter
    ) {
        $this->_logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->configWriter = $configWriter;
    }

    public function execute()
    {
        try {
            $this->_logger->info('Hgati_SomeoneRecentlyBought:: Start fake products');

            $_cron = $this->_scopeConfig->getValue(self::CRON_PATH);
            $_product_ids = $this->_scopeConfig->getValue(self::CONFIG_PATH);
            if(!empty($_product_ids) && empty($_cron)) {
                $this->_logger->info('Hgati_SomeoneRecentlyBought:: product_ids are manually set, so pass on this.');
                return;
            }

            $limit = $this->_scopeConfig->getValue(self::LIMIT_PATH);

            $randomProductIds = [];
            $productCollection = $this->productCollectionFactory->create();
            //$productCollection->addAttributeToFilter('ox_featured', 1);
            $productCollection->addAttributeToFilter('goods_type', 8684); // Albums
            $productCollection->setVisibility([Visibility::VISIBILITY_BOTH, Visibility::VISIBILITY_IN_CATALOG]);
            $productCollection->addAttributeToSort('entity_id', Collection::SORT_ORDER_DESC);
            $productCollection->setPageSize(200)->getSelect();
            $stockStatusTable = $productCollection->getTable('cataloginventory_stock_status');
            $productCollection->getSelect()->join(
                ['stock_status' => $stockStatusTable],
                'e.entity_id = stock_status.product_id AND stock_status.stock_status = 1',
                []
            );
            $productIds = []; foreach($productCollection as $product) $productIds[] = $product->getId();
            shuffle($productIds);
            $randomProductIds = array_slice($productIds, 0, $limit);

            if(!empty($randomProductIds)) {
                $values = implode(',', $randomProductIds);
                $this->configWriter->save(self::CONFIG_PATH, $values);
                $this->_logger->info("Hgati_SomeoneRecentlyBought:: Fake ProductIds:: $values");
            }

            $this->_logger->info('Hgati_SomeoneRecentlyBought:: finished fake products');
        } catch (\Exception $e) {
            $this->_logger->error('Error in cron job: ' . $e->getMessage());
        }
    }

}
