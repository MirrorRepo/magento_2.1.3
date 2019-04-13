<?php

$file = fopen('var/import/new.csv', 'r', '"'); // set path to the CSV file
if ($file !== false) {

    require __DIR__ . '/app/bootstrap.php';
    $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);

    $objectManager = $bootstrap->getObjectManager();

    $state = $objectManager->get('Magento\Framework\App\State');
    $state->setAreaCode('adminhtml');

    // used for updating product stock - and it's important that it's inside the while loop
    $stockRegistry = $objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface');

    // add logging capability
    $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/import-new.log');
    $logger = new \Zend\Log\Logger();
    $logger->addWriter($writer);

    $header = fgetcsv($file); // get data headers and skip 1st row

    // enter the min number of data fields you require that the new product will have (only if you want to standardize the import)
    $required_data_fields = 3;
    $i=0;
    while ( $row = fgetcsv($file, 3000, ",") ) {

        $data_count = count($row);
        if ($data_count < 1) {
            continue;
        }

        // used for setting the new product data
        $product = $objectManager->create('Magento\Catalog\Model\Product');
        $data = array();
        $data = array_combine($header, $row);

        $sku = $data['sku'];
        if ($data_count < $required_data_fields) {
            $logger->info("Skipping product sku " . $sku . ", not all required fields are present to create the product.");
            continue;
        }

        $name = $data['name'];
        $description = $data['description'];
        $shortDescription = $data['short_description'];
        $qty = trim($data['qty']);
        $price = trim($data['price']);
        $base_image = "/"."amazon/".trim($data['image']);
        $base_small_image = "/"."amazon/".trim($data['small_image']);
        $thumbnail_image ="/"."amazon/".trim($data['thumbnail_image']);
        $additional_images =  "/"."amazon/".trim($data['additional_images']);
        $manage_stock  = trim($data['manage_stock']);
        $is_in_stock  = trim($data['is_in_stock']);
        $max_sale_qty  = trim($data['max_sale_qty']);
        $use_config_manage_stock  = trim($data['use_config_manage_stock']);
        $use_config_max_sale_qty  = trim($data['use_config_max_sale_qty']);
        $status  = trim($data['status']);




        try {
            $product->setTypeId('simple') // type of product you're importing
                    ->setStatus(1) // 1 = enabled
                    ->setAttributeSetId(4) // In Magento 2.2 attribute set id 4 is the Default attribute set (this may vary in other versions)
                    ->setName($name)
                    ->setSku($sku)
                    ->setPrice($price)
                    ->setTaxClassId(0) // 0 = None
                    ->setCategoryIds(array(2, 3)) // array of category IDs, 2 = Default Category
                    ->setDescription($description)
                    ->setShortDescription($shortDescription)
                    ->setWebsiteIds(array(1)) // Default Website ID
                    ->setStoreId(0) // Default store ID
                    ->setVisibility(4) // 4 = Catalog & Search
                    ->setImage($base_image)
                    ->setSmallImage($base_small_image)
                    ->setThumbnail($thumbnail_image)
                    ->setStockData(array(
                        'use_config_manage_stock' => 0, //'Use config settings' checkbox
                        'manage_stock' => $is_in_stock, //manage stock
                        'min_sale_qty' => 1, //Minimum Qty Allowed in Shopping Cart
                        'max_sale_qty' => $max_sale_qty, //Maximum Qty Allowed in Shopping Cart
                        'is_in_stock' => 1, //Stock Availability
                        'qty' => 100 //qty
                        )
                      ->setStatus($status);
                    ->save();
                    				weight_typ	price	weight	qty	upc	size	count_sise	jet_brand	barcode_value	visibility	store_view_code	product_websites	max_dispatch_time	packs	listing_duration	listing_type	news_to_date	country_of_manufactu	categories	tax_class_name	additional_attributes	url_key	product_type	attribute_set_code

        } catch (\Exception $e) {
            $logger->info('Error importing product sku: '.$sku.'. '.$e->getMessage());
            continue;
        }

        try {
            $stockItem = $stockRegistry->getStockItemBySku($sku);

            if ($stockItem->getQty() != $qty) {
                $stockItem->setQty($qty);
                if ($qty > 0) {
                    $stockItem->setIsInStock(1);
                }
                $stockRegistry->updateStockItemBySku($sku, $stockItem);

                echo $i++." Record insert Sucessfully";
            }
        } catch (\Exception $e) {
            $logger->info('Error importing stock for product sku: '.$sku.'. '.$e->getMessage());
            continue;
        }
        unset($product);
    }
    fclose($file);
}


?>
