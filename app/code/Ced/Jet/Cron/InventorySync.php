<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Jet
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Jet\Cron;

class InventorySync
{
    public $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->objectManager = $objectManager;
        $this->logger = $logger;
        $this->stockRegistry = $this->objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface');
        $this->helper = $this->objectManager->get('Ced\Jet\Helper\Data');
        $this->jethelper = $this->objectManager->get('Ced\Jet\Helper\Jet');
    }

    /**
     * @return void
     */
    public function execute()
    {
        $Inventory = [];
        $Price = [];
        try {
            $fullfillmentnodeid = $this->helper->getFulfillmentNode();
            if (empty($fullfillmentnodeid)) {
                $this->logger->info('InventorySync: Error - fullfillment node id missing');
                return true;
            }
            $product_skus = $this->helper->CGetRequest('/merchant-skus/');
            $productskus = json_decode($product_skus, true);
            //$product_ids = $this->jethelper->getAllJetUploadableProduct(true);
            foreach ($productskus['sku_urls'] as $sku) {
                $productLoad = $this->objectManager->create('Magento\Catalog\Model\Product')->loadByAttribute('sku', substr($sku, 14));
                if (!empty($productLoad)) {
                    if ($productLoad->getTypeId() == 'configurable') {
                        $childrenProducts = $productLoad->getTypeInstance()->getUsedProducts($productLoad);
                        foreach ($childrenProducts as $childrenProduct) {
                            $childProduct = $this->objectManager->create('Magento\Catalog\Model\Product')->load($childrenProduct->getId());
                            $inventoryNode = [];
                            $stock = $this->stockRegistry->getStockItem(
                                $childProduct->getId(),
                                $childProduct->getStore()->getWebsiteId()
                            );
                            $qty = $stock->getQty();

                            $inventoryNode['fulfillment_node_id'] = $fullfillmentnodeid;
                            $inventoryNode['quantity'] = $qty;
                            $Inventory[$childProduct->getSku()]['fulfillment_nodes'][] = $inventoryNode;
                            
                            $product_price = 0;
                            $product_price = $this->jethelper->getJetPrice($childProduct);
                            $Price[$childProduct->getSku()]['price'] = $product_price;
                        }
                    } else {
                        $stock = $this->stockRegistry->getStockItem(
                            $productLoad->getId(),
                            $productLoad->getStore()->getWebsiteId()
                        );
                        $qty = $stock->getQty();
                            
                        $inventoryNode = [];
                        $inventoryNode['fulfillment_node_id'] = $fullfillmentnodeid;
                        $inventoryNode['quantity'] = $qty;
                        $Inventory[$productLoad->getSku()]['fulfillment_nodes'][] = $inventoryNode;
                        
                        $product_price = 0;
                        $product_price = $this->jethelper->getJetPrice($productLoad);
                        $Price[$productLoad->getSku()]['price'] = $product_price;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('InventorySync: Error - '.$e->getMessage());
            return true;
        }
        $this->SendFile($Inventory, $Price);
        return true;
    }

    public function SendFile($Inventory, $Price)
    {
        if (!empty($Inventory)) {
            $inventoryPath = "";
            $inventoryPath = $this->helper->createJsonFile("SyncInventory", $Inventory);
            $path_data = explode('/', $inventoryPath);
            $inventory_file_name =  end($path_data);
            $inventoryPath = $inventoryPath.'.gz';
            
            $tokenresponse = $this->helper->CGetRequest('/files/uploadToken');
            $tokendata = json_decode($tokenresponse);
              
            $this->helper->uploadFile($inventoryPath,$tokendata->url);
              
            $postFields ='{"url":"'.$tokendata->url.'","file_type":"Inventory","file_name":"'.$inventory_file_name.'"}';
            $responseInv = $this->helper->CPostRequest('/files/uploaded',$postFields);
            $data2 = json_decode($responseInv);
            if (isset($data2) && $data2->status == 'Acknowledged') {
                $this->logger->info('InventorySync: Success');
            } else {
                $this->logger->info('InventorySync: Error - while uploading files');
            }
        }

        if (!empty($Price)) {
            $price_Path = "";
            $price_file_name = "";
            $price_Path = $this->helper->createJsonFile("SyncPrice", $Price);
            $path_data = explode('/', $price_Path);
            $price_file_name =  end($path_data);
            $price_Path = $price_Path.'.gz';
          
            $tokenresponse2 = $this->helper->CGetRequest('/files/uploadToken');
            $tokendata2 = json_decode($tokenresponse2);
          
            $this->helper->uploadFile($price_Path, $tokendata2->url);
            $postFields = '{"url":"'.$tokendata2->url.'","file_type":"Price","file_name":"'.$price_file_name.'"}';
            $responsePrice = $this->helper->CPostRequest('/files/uploaded', $postFields);
            $pricedata = json_decode($responsePrice);
            if (isset($pricedata) && $pricedata->status == 'Acknowledged') {
                $this->logger->info('InventorySync: Success');
            } else {
                $this->logger->info('InventorySync: Error - while uploading files');
            }
        }
        return true;
    }
}
