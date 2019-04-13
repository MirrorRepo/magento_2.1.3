<?php
use Magento\Framework\App\Bootstrap;
require __DIR__ . '/app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();
$storeManager = $obj->get('\Magento\Store\Model\StoreManagerInterface');
$product_sku = $_GET['sku'];

$data_helper = $obj->create('Ced\Jet\Helper\Data');
$data = $data_helper->CGetRequest('/merchant-skus/'.rawurldecode($product_sku)); 

echo"<pre>";print_r(json_decode($data, true));
