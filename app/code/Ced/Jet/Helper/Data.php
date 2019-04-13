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
namespace Ced\Jet\Helper;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    public $_curl;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;
    /**
     * @var mixed
     */
    public $scopeConfigManager;
    /**
     * @var mixed
     */
    public $configValueManager;
    /**
     * @var mixed
     */
    public $adminSession;
    /**
     * @var string
     */
    public $apiHost = '';
    /**
     * @var \Magento\Framework\Message\Manager
     */
    public $messageManager;
    /**
     * @var string
     */
    public $user = '';
    /**
     * @var string
     */
    public $pass = '';
    /**
     * @var array
     */
    public $batcherror = [];
    /**
     * @var \Magento\Framework\HTTP\Adapter\Curl
     */
    public $_resource;

    protected $_productAttributeRepository;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Message\Manager $manager
     * @param \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoaderFactory $creditmemoLoaderFactory
     * @param \Magento\Framework\HTTP\Adapter\Curl $curl
     * @param \Magento\Framework\Json\Helper\Data $_jdecode
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Message\Manager $manager,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoaderFactory $creditmemoLoaderFactory,
        \Magento\Framework\HTTP\Adapter\Curl $curl,
        \Magento\Framework\Json\Helper\Data $_jdecode,
        \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository
    )
    {
        $this->objectManager = $objectManager;
        $this->_resource = $curl;
        $this->_jdecode = $_jdecode;
        $this->creditmemoLoaderFactory = $creditmemoLoaderFactory;
        parent::__construct($context);
        $this->messageManager = $manager;
        $this->adminSession = $this->objectManager->create('Magento\Backend\Model\Session');
        $this->scopeConfigManager = $this->objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->configValueManager = $this->objectManager->get('Magento\Framework\App\Config\ValueInterface');
        $this->apiHost = $this->scopeConfigManager->getValue('jetconfiguration/jetsetting/api_url');
        $this->user = $this->scopeConfigManager->getValue('jetconfiguration/jetsetting/user');
        $this->pass = $this->scopeConfigManager->getValue('jetconfiguration/jetsetting/secret_key');
        $this->JrequestTokenCurl();
        $this->_productAttributeRepository = $productAttributeRepository;
    }

    public function initBatcherror()
    {
        $batcherror = [];
        $this->adminSession->setBatcherror($batcherror);
    }

    /**
     * @param string $pid
     * @return int
     */

    public function getBatchIdFromProductId($pid = "")
    {
        $batch_id = 0;
        if ($pid) {
            $batchcoll = $this->objectManager->create('Ced\Jet\Model\Batcherror')->getCollection();
            foreach ($batchcoll as $bat) {
                if ($bat->getData('product_id') == $pid) {
                    $batch_id = $bat->getData('id');
                    return $batch_id;
                }
            }
        }
        return $batch_id;
    }

    /**
     * @param $model
     * @param int $index
     * @return void
     */

    public function initBatchErrorForProduct($model, $index = 0)
    {
        $batcherror = [];
        $batcherror = $this->adminSession->getBatcherror();
        $batch_id = 0;
        $batchmod = '';
        $pid = $model->getId();
        $batch_id = $this->getBatchIdFromProductId($pid);
        if ($batch_id) {
            $batchmod = $this->objectManager->create('Ced\Jet\Model\Batcherror')->load($batch_id);
            $batchmod->setData("batch_num", $index);
            $batchmod->setData("is_write_mode", '1');
            $batchmod->setData("error", '');
            $batchmod->save();
        } else {
            $batchmod = $this->objectManager->create('Ced\Jet\Model\Batcherror');
            $batchmod->setData('product_id', $pid);
            $batchmod->setData('product_sku', $model->getSku());
            $batchmod->setData("is_write_mode", '1');
            $batchmod->setData("error", '');
            $batchmod->setData("batch_num", $index);
            $batchmod->save();
        }
        $batcherror[$pid]['error'] = "";
        $batcherror[$pid]['sku'] = $model->getSku();
        $batcherror[$pid]['batch_num'] = $index;
        $this->adminSession->setBatcherror($batcherror);
    }

    /**
     * @param null $secretKey
     * @param null $userId
     * @return bool|mixed
     */

    public function JrequestTokenCurl($secretKey = null, $userId = null)
    {
        $url = $this->apiHost . '/Token';
        if (isset($secretKey) && isset($userId)) {
            $postFields = '{"user":"' . $userId . '","pass":"' . $secretKey . '"}';
        } else {
            $postFields = '{"user":"' . $this->user . '","pass":"' . $this->pass . '"}';
        }
        $headers = ["Content-Type:application/json;"];
        $this->_resource->setOptions([CURLOPT_HEADER => 1,
            CURLOPT_POST => 1, CURLOPT_SSL_VERIFYPEER => 0, CURLOPT_RETURNTRANSFER => 'true']);
        $this->_resource->write("POST", $url, '1.1', $headers, $postFields);
        $server_output = $this->_resource->read();
        $header_size = $this->_resource->getInfo(CURLINFO_HEADER_SIZE);
        $header = substr($server_output, 0, $header_size);
        $body = substr($server_output, $header_size);
        $this->_resource->close();
        $token_data = json_decode($body);
        if (is_object($token_data) && isset($token_data->id_token)) {
            $data = $this->objectManager->create('Magento\Config\Model\ResourceModel\Config');
            $data->saveConfig('jetcom/token', $body, 'default', 0);
            return json_decode($body);
        } else {
            return false;
        }
    }


    /**
     * @param $method
     * @param $postFields
     * @return string
     */

    public function CPostRequest($method, $postFields)
    {
        $url = $this->apiHost . $method;
        $tObject = $this->Authorise_token();
        $headers = [];
        $headers[] = "Content-Type: application/json";
        $headers[] = "Authorization: Bearer $tObject->id_token";
        $this->_resource->setOptions([CURLOPT_HEADER => 1, CURLOPT_SSL_VERIFYPEER => 0, CURLOPT_RETURNTRANSFER => 'true']);
        $this->_resource->write("POST", $url, '1.1', $headers, $postFields);
        $server_output = $this->_resource->read();
        $header_size = $this->_resource->getInfo(CURLINFO_HEADER_SIZE);
        $header = substr($server_output, 0, $header_size);
        $body = substr($server_output, $header_size);
        $this->_resource->close();
        return $body;
    }

    /**
     * @param $method
     * @param $postFields
     * @return string
     */

    public function CPutRequest($method, $postFields)
    {
        $call = "PUT";
        if (strpos($method, "inventory")) {
            $call = 'PATCH';
        }
        $url = $this->apiHost . $method;
        $tObject = $this->Authorise_token();
        $token = isset($tObject->id_token) ? $tObject->id_token : "";
        $headers = [];
        $headers[] = "Content-Type: application/json";
        $headers[] = "Authorization: Bearer $token";
        $this->_resource->setOptions([
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_HEADER => 1,
            CURLOPT_CUSTOMREQUEST => $call,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => 'true']);
        $this->_resource->write($call, $url, '1.1', $headers, $postFields);
        $server_output = $this->_resource->read();
        $header_size = $this->_resource->getInfo(CURLINFO_HEADER_SIZE);
        $header = substr($server_output, 0, $header_size);
        $body = substr($server_output, $header_size);
        $this->_resource->close();
        return $body;
    }

    /**
     * @return bool|mixed
     */

    public function Authorise_token()
    {
        $Jtoken = json_decode($this->scopeConfigManager->getValue('jetcom/token'));
        $refresh_token = false;
        if (is_object($Jtoken) && $Jtoken != null) {
            $url = $this->apiHost . '/authcheck';
            $headers = [];
            $headers[] = "Content-Type: application/json";
            $headers[] = "Authorization: Bearer $Jtoken->id_token";
            $this->_resource->setOptions([CURLOPT_URL => $url,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_HEADER => 1,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_RETURNTRANSFER => 'true']);
            $server_output = $this->_resource->read();
            $header_size = $this->_resource->getInfo(CURLINFO_HEADER_SIZE);
            $header = substr($server_output, 0, $header_size);
            $body = substr($server_output, $header_size);
            $this->_resource->close();
            $bjson = json_decode($body);

            if (is_object($bjson) && $bjson->Message != '' && $bjson->Message == 'Authorization has been denied for this request.') {
                $refresh_token = true;
            }
        } else {
            $refresh_token = true;
        }
        if ($refresh_token) {
            $token_data = $this->JrequestTokenCurl();
            if ($token_data != false) {
                return $token_data;
            } else {
                $this->messageManager->addError(__('API user & API password either or Invalid.Please set API user & API pass from jet configuration.'));
            }
        } else {
            return $Jtoken;
        }
    }

    /**
     * @param $localfile
     * @param $url
     */

    public function uploadFile($localfile, $url)
    {
        $headers = [];
        $headers[] = "x-ms-blob-type:BlockBlob";
        $fp = fopen($localfile, 'r');
        $this->_resource->setOptions([
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_HEADER => 1,
            CURLOPT_PUT => 1,
            CURLOPT_SSL_VERIFYPEER => 'false',
            CURLOPT_RETURNTRANSFER => 'true',
            CURLOPT_INFILE => $fp,
            CURLOPT_INFILESIZE => filesize($localfile)]);
        $this->_resource->write("PUT", $url, '1.1', $headers, "");
        $server_output = $this->_resource->read();
        $error = $this->_resource->getError();
        $http_code = $this->_resource->getInfo(CURLINFO_HTTP_CODE);
        $this->_resource->close();
        fclose($fp);
    }

    /**
     * @param $method
     * @return string
     */

    public function CGetRequest($method)
    {
        $tObject = $this->Authorise_token();
        $token = isset($tObject->id_token) ? $tObject->id_token : "";
        $url = $this->apiHost . $method;

        $headers = [];
        $headers[] = "Content-Type: application/json";
        $headers[] = "Authorization: Bearer $token";
        $this->_resource->setOptions([CURLOPT_HEADER => 1,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => 'true']);
        $this->_resource->write("GET", $url, '1.1', $headers, "");
        $server_output = $this->_resource->read();
        $header_size = $this->_resource->getInfo(CURLINFO_HEADER_SIZE);
        $header = substr($server_output, 0, $header_size);
        $body = substr($server_output, $header_size);
        $this->_resource->close();
        return $body;
    }

    /**
     * @param $type
     * @param $data
     * @return string
     */

    public function createJsonFile($type, $data)
    {
        $t = time() + rand(2, 5);
        $finalskujson = json_encode($data);
        if ($type == 'MerchantSKUs') {
            $newJsondata = $this->ConvertNodeInt($finalskujson);
        } else {
            $newJsondata = $finalskujson;
        }
        $file_path = $this->objectManager->get(
                'Magento\Framework\Filesystem\DirectoryList')->getPath(
                'var') . "/jetupload" . "/" . $type . $t . ".json";
        $file_type = $type;
        $file_name = $type . $t . ".json";
        $myfile = fopen($file_path, "w");
        fwrite($myfile, $newJsondata);
        fclose($myfile);
        if (file_exists($file_path . ".gz") == false) {
            $this->gzCompressFile($file_path, 9);
        }
        return $file_path;
    }

    /**
     * @param $json
     * @return mixed
     */

    public function ConvertNodeInt($json)
    {
        $pattern = '/"jet_browse_node_id":"(\d+)"/i';
        $replacement = '"jet_browse_node_id":$1';
        $json_replaced_node = preg_replace($pattern, $replacement, $json);

        $pattern1 = '"mjattr';
        $replacement1 = '';
        $json_replaced_node = str_replace($pattern1, $replacement1, $json_replaced_node);
        $pattern1 = 'mjattr"';
        $json_replaced_node = str_replace($pattern1, $replacement1, $json_replaced_node);

        $pattern1 = '/"attribute_id":"(\d+)"/i';
        $replacement1 = '"attribute_id":$1';
        return preg_replace($pattern1, $replacement1, $json_replaced_node);
    }

    /**
     * @param $json
     * @param $count
     * @return mixed
     */

    public function Varitionfix($json, $count)
    {
        $patt = '';
        $replacement = '';
        for ($i = 1; $i <= $count; $i++) {
            $patt = $patt . '"(\d+)",';
            $replacement = $replacement . '$' . $i . ',';
        }
        $patt = rtrim($patt, ',');
        $replacement = rtrim($replacement, ',');

        $pattern = '/"variation_refinements":\[' . $patt . '\]/i';
        $replacement = '"variation_refinements":[' . $replacement . ']';

        return preg_replace($pattern, $replacement, $json);
    }

    /**
     * @param $source
     * @param int $level
     * @return bool|string
     */

    public function gzCompressFile($source, $level = 9)
    {
        $dest = $source . '.gz';
        $mode = 'wb' . $level;
        $error = false;
        if ($fp_out = gzopen($dest, $mode)) {
            if ($fp_in = fopen($source, 'rb')) {
                while (!feof($fp_in))
                    gzwrite($fp_out, fread($fp_in, 1024 * 512));
                fclose($fp_in);
            } else {
                $error = true;
            }
            gzclose($fp_out);
        } else {
            $error = true;
        }
        if ($error) {
            return false;
        } else {
            return $dest;
        }
    }

    /**
     * @return mixed
     */

    public function getFulfillmentNode()
    {
        $fullfillmentnodeid = $this->scopeConfigManager->getValue(
            'jetconfiguration/jetsetting/fulfillment_node_id');
        return $fullfillmentnodeid;
    }

    /**
     * @param $attrcode
     * @return bool|string
     */

    public function getattributeType($attrcode)
    {
        try {
            $load_attr = $this->objectManager->create(
                'Magento\Catalog\Model\ResourceModel\Eav\Attribute')->loadByCode(
                'catalog_product', $attrcode);
            if (!$load_attr->getId()) {
                return false;
            } else {
                return $load_attr->getFrontendInput();
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return bool|string
     */

    public function getStandardOffsetUTC()
    {
        $timezone = date_default_timezone_get();
        if ($timezone == 'UTC') {
            return '';
        } else {
            $timezone = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
            $timezone = $timezone->getConfigTimezone();
            $transitions = array_slice($timezone->getTransitions(), -3, null, true);
            foreach (array_reverse($transitions, true) as $transition) {
                if ($transition['isdst'] == 1) {
                    continue;
                }
                return sprintf('UTC %+03d:%02u', $transition['offset'] / 3600,
                    abs($transition['offset']) % 3600 / 60);
            }
            return false;
        }
    }

    /**
     * @param $product
     * @return array
     */

    public function moreProductAttributesData($product)
    {
        $more = [];
        $jetAttrColl = $this->objectManager->create(
            'Ced\Jet\Model\OptionalAttribute')->getCollection()->addFieldToFilter(
            'used', ['eq' => 1]);
        foreach ($jetAttrColl as $jet_attr) {
            $jetCode = $jet_attr->getJetCode();
            $mappedCode = $jet_attr->getMapAttributeCode();
            if ($product->getData($mappedCode) != "") {
                $attributeData = $this->AttributeData($jetCode, $mappedCode, $product);
                if (!empty($attributeData)) {
                    $more [$jetCode] = $attributeData;
                }
                // text case
                $arrayText = ['amazon_item_type_keyword',
                    'type_of_unit_for_price_per_unit', 'category_path',
                    'map_implementation', 'product_tax_code'];

                if (in_array($jetCode, $arrayText)) {
                    $more[$jetCode] = $product->getData($mappedCode);
                }
                // float case
                $arrayFloat = ['number_units_for_price_per_unit',
                    'shipping_weight_pounds', 'package_length_inches',
                    'package_width_inches', 'package_height_inches',
                    'display_length_inches', 'display_width_inches',
                    'display_height_inches', 'map_price', 'msrp',
                    'no_return_fee_adjustment'];

                if (in_array($jetCode, $arrayFloat)) {
                    if (is_numeric($product->getData($mappedCode))) {
                        $more[$jetCode] = (float)$product->getData($mappedCode);
                    }
                }
                // int case
                $arrayInt = ['fulfillment_time'];
                if (in_array($jetCode, $arrayInt)) {
                    if (is_numeric($product->getData($mappedCode))) {
                        $more[$jetCode] = (int)$product->getData($mappedCode);
                    }
                }
            }
            // boolean case
            $arrayBool = ['ships_alone', 'exclude_from_fee_adjustments', 'prop_65'];
            if (in_array($jetCode, $arrayBool)) {
                $more[$jetCode] = $product->getData($mappedCode) ? true : false;
            }
        }
        return $more;
    }


    /**
     * @param $jetCode
     * @param $mappedCode
     * @param $product
     * @return array|string
     */

    public function AttributeData($jetCode, $mappedCode, $product)
    {
        $stringObj = $this->objectManager->get(
            '\Magento\Framework\Stdlib\StringUtils');
        switch ($jetCode) {
            case 'bullets':
                $more = $this->ForBullets($mappedCode, $product);
                break;
            case 'legal_disclaimer_description':
                $string = trim($product->getData($mappedCode));
                $len = $stringObj->strlen($string);
                if ($len <= 500 && $len > 0) {
                    $more = $string;
                }
                break;
            case 'cpsia_cautionary_statements':
                $string = $product->getData($mappedCode);
                $arr = explode(',', $string);
                $more = $this->GetString($arr);
                break;
            case 'safety_warning':
                $string = trim($product->getData($mappedCode));
                if ($string !== "") {
                    $more = $stringObj->substr($string, 0, 1999);
                }
                break;
            case 'start_selling_date':
                $more = $this->ForSellingDate($mappedCode, $product);
                break;
            case 'country_of_origin':
                $countryManufact = substr($product->getData($mappedCode), 0, 50);
                $more = $countryManufact;
                break;
            default:
                $more = '';
                break;
        }
        return $more;
    }

    /**
     * @param $arr
     * @return string
     */

    public function GetString($arr)
    {
        if (!empty($arr)) {
            if ($arr[0] == 'no warning applicable')
                array_shift($arr);
            $more = $arr;
        } else {
            $more = '';
        }
        return $more;
    }

    /**
     * @param $mappedCode
     * @param $product
     * @return array
     */

    public function ForBullets($mappedCode, $product)
    {
        $more = [];
        if ($mappedCode == 'bullets' && $product->getData('bullets') != "") {
            $bullet_data = [];
            $bullets = $product->getData('bullets');
            preg_match_all("/\{(.*?)\}/", $bullets, $matches);
            $new_bullets = [];
            $new_bullets = $matches[1];
            $j = 0;
            for ($i = 0; $i < count($new_bullets); $i++) {
                $string = trim($new_bullets[$i]);
                if (strlen($string) <= 500 && strlen($string) > 0) {
                    $bullet_data[$j] = $string;
                    $j++;
                }
                if ($j > 4) {
                    break;
                }
            }
            if (!empty($bullet_data)) {
                $more = $bullet_data;
            }
        } else {
            $buldata = $product->getData($mappedCode);
            if ($buldata != "") {
                $explode_data = explode(",", $buldata);
                if (!empty($explode_data)) {
                    $more = $explode_data;
                }
            }
        }
        return $more;
    }

    /**
     * @param $mappedCode
     * @param $product
     * @return string
     */

    public function ForSellingDate($mappedCode, $product)
    {
        $more = '';
        $string = $product->getData($mappedCode);
        $offset_end = $this->getStandardOffsetUTC();
        if (empty($offset_end) || trim($offset_end) == '') {
            $offset = '.0000000-00:00';
        } else {
            $offset = '.0000000' . trim($offset_end);
        }
        $shipTodatetime = "";
        $shipTodatetime = strtotime($string);
        $Ship_todate = "";
        $Ship_todate = date("Y-m-d", $shipTodatetime) . 'T' . date("H:i:s", $shipTodatetime) . $offset;
        $more = $Ship_todate;
        return $more;
    }

    /**
     * @param $code
     * @param $barcode
     * @param $barcodeValue
     * @return string
     */

    public function checkAttrValue($code, $barcode, $barcodeValue)
    {
        switch ($code) {
            case 'checkAsinBarcode':
                if ($barcode == 'asin') {
                    $cValue = $barcodeValue ? $barcodeValue : "";
                } else {
                    $cValue = '';
                }
                break;
            case 'CheckBarcode':
                $cValue = $this->CheckBarcode($barcode, $barcodeValue);
                break;
            default:
                $cValue = '';
                break;
        }
        return $cValue;
    }

    /**
     * @param $brandAttrType
     * @param $product
     * @param $configBrand
     * @return string
     */
    public function getBrandValue($brandAttrType, $product, $configBrand)
    {
        $brand = '';
        if ($brandAttrType != false) {
            $brand = $this->GetBrand(
                $brandAttrType, $product, $configBrand);
        } else {
            $brand = $product->getData('jet_brand');
        }
        return $brand;
    }

    /**
     * @param $productId
     * @return array|bool|mixed
     */

    public function createProductOnJet($product, $parentImage, $parentCategory)
    {
        $msg = [];
        $fullfillmentnodeid = $this->getFulfillmentNode();
        $configBrand = $this->scopeConfigManager->getValue(
            'jetconfiguration/productinfo_map/jet_brand');
        $configMfpn = $this->scopeConfigManager->getValue(
            'jetconfiguration/productinfo_map/jet_manufacturer_part_number');
        $configTitle = $this->scopeConfigManager->getValue(
            'jetconfiguration/productinfo_map/jet_title');
        $configDescp = $this->scopeConfigManager->getValue(
            'jetconfiguration/productinfo_map/jet_description');
        $configMultipack = $this->scopeConfigManager->getValue(
            'jetconfiguration/productinfo_map/jet_multipack_quantity');

        $val_multipack = $this->getattributeType($configMultipack);
        $brandAttrType = $this->getattributeType($configBrand);
        if (is_numeric($product)) {
            $product = $this->objectManager->create(
                'Magento\Catalog\Model\Product')->load($product);
        }
        $productType = $product->getTypeId();

        if ($productType == 'configurable') {
            $configData = $this->reviewConfigProduct($product, $brandAttrType, $configBrand);
            return $configData;
        }
        
        /*   get all the child products , configurable product image/description
             recursively call createProductonJet with parent image/description
        */
        if (isset($product) && $product->getId()) {
            $attributeArray = [];
            $barcode = $product->getData('barcode');
            $barcodeValue = (trim($product->getData('barcode_value')) != "") ? $product->getData('barcode_value') : "";
            $asin = $this->checkAttrValue('checkAsinBarcode', $barcode, $barcodeValue);
            $_uniquedata = $this->checkAttrValue('CheckBarcode', $barcode, $barcodeValue);
            $brand = $this->getBrandValue($brandAttrType, $product, $configBrand);
            $valMfpn = $this->getattributeType($configMfpn);
            if ($valMfpn != false) {
                $manuPartNumber = $this->Manupartnumber($product, $configMfpn, $valMfpn);
            }
            $mfrpExist = false;
            if ($manuPartNumber != null) {
                $mfrpExist = true;
            }
            $errMsg = $this->testValidation($brand, $product, $_uniquedata, $asin, $mfrpExist);
            $validate = ($errMsg != "") ? false : true;
            $cats = $product->getCategoryIds();
            try {
                $nodeId = $this->browserNodeId($cats);
                if (!empty($parentCategory)) {
                    $prd_browser_nodeid = $parentCategory;
                } else if ((!empty($nodeId))) {
                    $prd_browser_nodeid = $nodeId;
                } else {
                    $validate = false;
                    $err_msg = 'SKU: <b>' . $product->getSku() .
                        ' </b></br> Rejected : Product(s) assigned category is not mapped with any Jet category';
                }
            } catch (\Exception $e) {
                $validate = false;
                $errMsg = $e->getMessage();
            }

            if ($validate == false) {
                $msg ['msg'] = [
                    'type' => 'error',
                    'data' => $errMsg
                ];
                return $msg;
            } else {
                $proAttr = [
                    'uniquedata' => $_uniquedata,
                    'config_title' => $configTitle,
                    'config_descp' => $configDescp,
                    'brand' => $brand,
                    'asin' => $asin,
                    'mfrp_exist' => $mfrpExist,
                    'manu_part_number' => $manuPartNumber,
                    'prd_browser_nodeid' => $prd_browser_nodeid,
                    'val_multipack' => $val_multipack,
                    'config_multipack' => $configMultipack
                ];

                // for setting the jet attributes data in product
                $proContent = $this->prepareData($product, $proAttr, $parentImage);
                
                if (!$proContent['status']) {
                    return $proContent;
                }
                $skuArray = $proContent['SKU_Array'];
                if (!empty($skuArray)) {
                    $returnData = $this->setAttributeData($skuArray, $fullfillmentnodeid, $product,
                        $proContent['sku'], $prd_browser_nodeid);
                    $msg = !empty($returnData["msg"]) ? $returnData['msg'] : $returnData["prodData"];
                    return $msg;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * @param $product
     * @param $brandAttrType
     * @param $configBrand
     * @return array
     */
    public function reviewConfigProduct($product, $brandAttrType, $configBrand)
    {
        $msg = [];
        $parentCategory = $product->getCategoryIds();
        try {
            $nodeId = $this->browserNodeId($parentCategory);
            if (!empty($nodeId)) {
                $prd_browser_nodeid = $nodeId;
            } else {               
                $msg['msg'] = [
                    'type' => 'error',
                    'data' => 'SKU: <b>' . $product->getSku() .' </b></br> Rejected : Product is not mapped with any Jet category'
                ];
                return $msg;
            }
        } catch (\Exception $e) {
            $msg['msg'] = [
                'type' => 'error',
                'data' => $e->getMessage()
            ];
            return $msg;        
        }
        $parentBrand = $this->getBrandName($product, $brandAttrType, $configBrand);
        if (empty($parentBrand)) {
            $msg['msg'] = [
                'type' => 'error',
                'data' => 'Brand Name missing from configurable product (id : ' . $product->getId() . ') . Please fill the Brand Name .'
            ];
            return $msg;
        }

        $parent_image = $this->objectManager->create('Magento\Catalog\Model\Product\Media\Config')->getMediaUrl($product->getImage());
        $image = explode("/", $parent_image);
        $parent_image = (end($image) != '') ? $parent_image : '';

        $confAttributes = [];
        $variation = [];
        $variantAtrribute = $product->getTypeInstance()->getConfigurableAttributes($product)->getData();

        foreach ($variantAtrribute as $attribute) {
            $attrCode = $this->objectManager->get('Magento\Eav\Model\Entity\Attribute')->load($attribute['attribute_id'])->getAttributeCode();
            $confAttributes['variationcode'][] = $attrCode;
            $attributeMapped = $this->isAttributeNotMapped($attrCode);
            if (!$attributeMapped) {
                $msg['msg'] = [
                    'type' => 'error',
                    'data' => 'Configurable Product attribute(s) of id ' . $product->getId() . ' is/are not mapped to Jet Attribute . Please map all attributes .'
                ];
                return $msg;
            } else {
                //$msg = ['type'=>'sucess'];     //addeed code by me
                $variation[] = $attributeMapped[0]['jet_attribute_id'];
            }
        }
        $parentPass = false; // set parent product
        $parent = '';
        $result = [];
        $price = [];
        $inventory = [];
        $childSkuForJet = [];
        $childrenProducts = $product->getTypeInstance()->getUsedProducts($product);
        $scount = 0;
        foreach ($childrenProducts as $childrenProduct) {
            $childProduct = $this->objectManager->create(
                'Magento\Catalog\Model\Product')->load($childrenProduct->getId());
            $childBrand = $this->getBrandName($childProduct, $brandAttrType, $configBrand);
            $childSku = $childProduct->getSku();
            /* check if parent and child product has same brand namr or not */
            if ($parentBrand != $childBrand) {
                $msg = $this->setErrorMessage($msg, $childrenProduct, 'brand name mismatch in child product sku =' . $childSku . ' ');
                continue;
            }

            $resultData = $this->createProductOnJet($childProduct, $parent_image, $prd_browser_nodeid);
            if ($resultData['msg']['type'] != 'error') {
                $result = array_merge($result, $resultData['merchantsku']);
                $price = array_merge($price, $resultData['price']);
                $inventory = array_merge($inventory, $resultData['inventory']);

                if (!$parentPass) {
                    $parent = $childSku;
                    $parentPass = true;
                } else {
                    $childSkuForJet[] = $childSku;
                }
            } else {
                $scount++;
                $msg = $this->setErrorMessage($msg, $childrenProduct, $resultData['msg']['data']);
            }
        }


        $relationship[$parent]['relationship'] = "Variation";
        $relationship[$parent]['variation_refinements'] = $variation;
        $relationship[$parent]['children_skus'] = $childSkuForJet;

        $prodData = [];
        $prodData['merchantsku'] = $result;
        $prodData['price'] = $price;
        $prodData['inventory'] = $inventory;
        $prodData['msg'] = $msg;
        if ($scount == count($childrenProducts)) {
            $prodData['show_config_error'] = 1;
        }

        if (count($relationship) > 0) {
            $prodData['relationship'] = $relationship;
        }
        return $prodData;

    }

    /**
     * @param $msg
     * @param $childrenProduct
     * @param $cause
     * @return array
     */
    public function setErrorMessage($msg, $childrenProduct, $cause)
    {
        if (empty($msg)) {
            $msg = [
                'type' => 'error',
                'sub-type' => 'product_skip',
                'cause' => $cause,
                'data' => $childrenProduct->getSku()
            ];
        } else {
            $data = $msg['data'] . ', ' . $childrenProduct->getSku();
            $cause = $msg['cause'] . ', ' . $cause;
            $msg = [
                'type' => 'error',
                'sub-type' => 'product_skip',
                'cause' => $cause,
                'data' => $data
            ];
        }
        return $msg;
    }

    /**
     * @param $product
     * @param $brandAttrType
     * @param $configBrand
     * @return mixed
     */
    public function getBrandName($product, $brandAttrType, $configBrand)
    {
        if ($brandAttrType != false) {
            if ($brandAttrType == 'select') {
                $brandName = $product->getAttributeText($configBrand);
            } else {
                $brandName = $product->getData($configBrand);
            }
        } else {
            $brandName = $product->getData('jet_brand');
        }
        return $brandName;
    }

    /**
     * @param $product
     * @return mixed
     */
    public function getMainProductSku($product)
    {
        $_children = $product->getTypeInstance()->getUsedProductIds($product);
        return $_children;
    }

    /**
     * @param $attrCode
     * @return bool
     */
    public function isAttributeNotMapped($attrCode)
    {
        $data = $this->objectManager->create('Ced\Jet\Model\Jetattributes')->getCollection()->addFieldToFilter('magento_attribute_code', $attrCode)->getData();
        return empty($data) ? false : $data;
    }

    /**
     * @param $product
     * @param $proAttr
     * @param $parentImage
     * @return array
     */
    public function prepareData($product, $proAttr, $parentImage)
    {
        $skuArray = [];
        $more = $this->moreProductAttributesData($product);
        $skuArray = array_merge($skuArray, $more);
        $sku = $product->getSku();
        $skuArray['product_title'] = $this->SkuProductTitle(
            $product, $proAttr['config_title']);
        $content = [];

        if (strlen($skuArray ['product_title']) < 5) {
            $content['status'] = false;
            $content['msg'] = [
                'type' => 'error',
                'data' => 'product title length must be equal or greater than 5'
            ];
            return $content;
        }
        $description = $this->getDescription($product, $proAttr['config_descp']);
        if (strlen($description) == 0) {
            $content['status'] = false;
            $content['msg'] = [
                'type' => 'error',
                'data' => 'product description not found'
            ];
            return $content;
        }
        $skuArray ['product_description'] = $description;
        $skuArray ['brand'] = substr($proAttr['brand'], 0, 100);
        if ($proAttr['asin']) {
            $skuArray ['ASIN'] = $proAttr['asin'];
        }
        if ($proAttr ['uniquedata']['type']) {
            $txt ['standard_product_code'] = $proAttr ['uniquedata']['value'];
            $txt ['standard_product_code_type'] = $proAttr ['uniquedata']['type'];
            $skuArray ['standard_product_codes'] [] = $txt;
        }
        if (!isset($skuArray['ASIN']) && empty($skuArray ['standard_product_codes'])) {
            $content['status'] = false ;
            $content['msg'] = [
                'type' => 'error',
                'data' => 'Please Set Barcode Value'
            ];
            return $content;
        }
        if ($proAttr['mfrp_exist']) {
            $skuArray ['mfr_part_number'] = substr($proAttr['manu_part_number'], 0, 50);
        }
        $skuArray ['jet_browse_node_id'] = $proAttr['prd_browser_nodeid'];
        $skuArray ['multipack_quantity'] = $this->MultipackQuantity(
            $proAttr['val_multipack'], $product, $proAttr['config_multipack']);

        $image = $this->objectManager->create('Magento\Catalog\Model\Product\Media\Config')->getMediaUrl($product->getImage());
        $image1 = explode("/", $image);
        if (end($image1) != '') {
            $skuArray ['main_image_url'] = $image;
        } else if ($parentImage != "") {
            $skuArray ['main_image_url'] = $parentImage;
        } else {
            $content['status'] = false;
            $content['msg'] = [
                'type' => 'error',
                'data' => 'product image not found'
            ];
            return $content;
        }
        $skuArray ['alternate_images'] = $this->alternateImage(
            $product, $image);
        $content['status'] = true;
        $content['SKU_Array'] = $skuArray;
        $content['sku'] = rawurlencode($sku);

        return $content;
    }

    /**
     * @param $product
     * @param $image
     * @return array
     */

    public function alternateImage($product, $image)
    {
        $_images = $product->getMediaGalleryImages();
        $jet_image_slot = 1;
        $altImage = [];
        foreach ($_images as $alternat_image) {
            if ($alternat_image->getUrl() != '' && $alternat_image->getUrl() != $image) {
                $altImage [] = [
                    'image_slot_id' => $jet_image_slot,
                    'image_url' => $alternat_image->getUrl()
                ];
                $jet_image_slot++;
                if ($jet_image_slot > 7) {
                    break;
                }
            }
        }
        return $altImage;
    }

    /**
     * @param $SKU_Array
     * @param $fullfillmentnodeid
     * @param $product
     * @param $sku
     * @return array
     */

    public function setAttributeData($SKU_Array, $fullfillmentnodeid, $product, $sku, $prd_browser_nodeid)
    {
        $attributeArray = $this->getAllCategoryAttributes($prd_browser_nodeid, $product);
        $msg = [];
        if(count($attributeArray) > 0){
            $SKU_Array ['attributes_node_specific'] = $attributeArray ;
        }
        $result [$sku] = $SKU_Array;
        $product_price = $this->objectManager->create(
            'Ced\Jet\Helper\Jet')->getJetPrice($product);

        $price [$sku] ['price'] = $product_price;
        $price [$sku] ['fulfillment_nodes'] [] = [
            'fulfillment_node_id' => "$fullfillmentnodeid",
            'fulfillment_node_price' => $product_price
        ];
        $stockItem = $this->objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
        $stock = $stockItem->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $qty = $stock->getQty();
        //$qty = $product->getQuantityAndStockStatus()['qty'];
        if ($qty <= 0) {
            $msg ["msg"] = [
                'type' => 'error',
                'data' => 'product is out of stock'
            ];
        }
        $inventory [$sku] ['fulfillment_nodes'] [] = [
            'fulfillment_node_id' => "$fullfillmentnodeid",
            'quantity' => $qty
        ];
        $prodData = [];
        $prodData ['merchantsku'] = $result;
        $prodData ['price'] = $price;
        $prodData ['inventory'] = $inventory;
        $prodData ['msg']['type'] = 'success';
        $data = ["prodData" => $prodData, "msg" => $msg];
        return $data;
    }

    /**
     * @param $categoryId
     * @param $product
     * @return array
     */

    public function getAllCategoryAttributes($categoryId, $product)
    {   
        /*$pp_id = $this->objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable')
                ->getParentIdsByChild($product->getId());
        if(count($pp_id)!=0)
        {
            $arr = [];
            $rr = $this->objectManager->create('Magento\Catalog\Model\Product')->load($pp_id[0]);
            $productAttributeOptions = $rr->getTypeInstance(true)->getConfigurableAttributesAsArray($rr);
            foreach ($productAttributeOptions as $key => $value) {
                $data_attr = $this->objectManager->create('Ced\Jet\Model\Jetattributes')->getCollection()
                        ->addFieldToFilter('magento_attribute_id',$value['attribute_id'])
                        ->getFirstItem();
                $arr[] = explode(",", $data_attr->getJetAttributeId());
            }
        }*/

        $jetCateroryData = $this->objectManager->create('Ced\Jet\Model\Categories')->getCollection()->addFieldToFilter('cat_id', $categoryId)->getFirstItem();
        $arr = explode(",", $jetCateroryData->getData('attribute_ids'));
        $attributeArray = [];
        foreach ($arr as $key => $ar) {
            $attribute = $this->objectManager->create('Ced\Jet\Model\Jetattributes')->getCollection()->addFieldToFilter('jet_attribute_id', $ar)->getFirstItem();
            if ($attribute && !empty($attribute->getJetAttributeId())) {
                $magentoattributeid = $attribute->getData('magento_attribute_id');
                $data = $this->_productAttributeRepository->get($magentoattributeid)->getData();
                if (isset($data['attribute_id']) && $data['attribute_id'] != null) {
                    $code = $data['attribute_code'];
                    $attr_type = $data['frontend_input'];

                    if (isset($attr_type) && $attr_type == 'select') {
                        $codevalue = (string)$product->getAttributeText($code);
                    } else {
                        $codevalue = (string)$product->getData($code);
                    }

                    /* check free text value first it is 0 , 1 or 2 */
                    $free_text = $attribute->getFreeText();

                    if (isset($free_text) && $free_text != null && trim($codevalue) != '') {
                        if ($free_text == 0 || $free_text == 1) {

                            $attributeArray[] = [
                                'attribute_id' => $attribute->getJetAttributeId(),
                                'attribute_value' => $codevalue
                            ];

                        } else {

                            $units_exp = explode(",", $attribute->getUnit());

                            if ($attribute->getUnit() && $attribute->getUnit() != null && count($units_exp) > 0) {

                                $code_before_space = explode(" ", $codevalue);
                                array_pop($code_before_space);
                                $first_half = trim($code_before_space[0]);

                                $getUnit_value = end(explode(" ", $codevalue));
                                $getUnit_value = trim($getUnit_value);

                                if (isset($first_half) && $first_half != '') {

                                    if (count($units_exp) > 0) {
                                        if (!empty($getUnit_value) || $getUnit_value != '') {

                                            if (in_array($getUnit_value, $units_exp)) {

                                                $attributeArray[] = [
                                                    'attribute_id' => $attribute->getJetAttributeId(),
                                                    'attribute_value' => $first_half,
                                                    'attribute_value_unit' => $getUnit_value];
                                            } else {
                                                $emsg = 'Unit value is required for this attribute ' . $data['attribute_code'] . ' from one of these comma seperated values: ' . $attribute->getUnit() . ' for example : ' . $code_before_space[0] . '{space}' . $units_exp[0] . ' ie. ' . $code_before_space[0] . ' ' . $units_exp[0];

                                                $batcherror = array();
                                                $batcherror = $this->adminSession->getBatcherror();
                                                $err_msg = "";
                                                $err_msg = $emsg . ' for product ' . $product->getName() . ' So this is skipped from upload.';
                                                if (count($batcherror) > 0) {
                                                    $msg['type'] = 'error';
                                                    $msg['data'] = $err_msg;
                                                    return $msg;
                                                }
                                            }
                                        }
                                    }
                                }

                            } else {
                                $attributeArray[] = [
                                    'attribute_id' => $attribute->getJetAttributeId(),
                                    'attribute_value' => $codevalue];
                            }
                        }
                    }
                }
            }
        }
        return $attributeArray;
    }

    /**
     * @param $valMultipack
     * @param $product
     * @param $configMultipack
     * @return int
     */

    public function MultipackQuantity($valMultipack, $product, $configMultipack)
    {
        $multipack = 1;
        if ($valMultipack != false) {
            if ($valMultipack == 'select') {
                $multipack = $product->getAttributeText($configMultipack);
            } else {
                $multipack = $product->getData($configMultipack);
            }
            if (is_numeric($multipack) && $multipack > 0 && $multipack < 129) {
                $multipack = (int)$multipack;
            }
        } else {
            $multipack = 1;
        }
        return $multipack;
    }

    /**
     * @param $cats
     * @return string
     */

    public function browserNodeId($cats)
    {
        $nodeId = '';
        $JetcatResult = [];
        if (count($cats) > 0) {
            foreach ($cats as $catArray) {
                $catCollection = $this->objectManager->create(
                'Ced\Jet\Model\Categories')->getCollection()->addFieldToFilter(
                ['magento_cat_id'], [[ 'like' => "%".$catArray.",%" ]])->getData();
                foreach ($catCollection as $value) {
                    $catExplode = explode(',', $value['magento_cat_id']);
                    foreach ($catExplode as $valCat) {
                        if ($catArray == $valCat) {
                            $nodeId = $value['cat_id'];
                            break;
                        }
                    }
                }
            }
        }
        return $nodeId;
    }

    /**
     * @param $product
     * @param $configmfpn
     * @param $valmfpn
     * @return mixed
     */

    public function Manupartnumber($product, $configmfpn, $valmfpn)
    {
        if ($valmfpn == 'select') {
            $manupartnumber = $product->getAttributeText($configmfpn);
        } else {
            $manupartnumber = $product->getData($configmfpn);
        }
        return $manupartnumber;
    }

    /**
     * @param $product
     * @param $configTitle
     * @return string
     */

    public function SkuProductTitle($product, $configTitle)
    {
        $pruductTitle = '';
        if (trim($configTitle) != "" && $product->getData($configTitle) != "") {
            $pruductTitle = substr($product->getData($configTitle)
                , 0, 500);
        } else {
            $pruductTitle = substr($product->getName(), 0, 500);
        }
        return $pruductTitle;
    }

    /**
     * @param $product
     * @param $configDescp
     * @return string
     */

    public function getDescription($product, $configDescp)
    {
        $description = '';
        if (trim($configDescp) && $product->getData($configDescp) != "") {
            $description = $product->getData($configDescp);
        } else {
            $description = $product->getDescription();
        }
        $description = (strlen($description) > 1999) ? substr($description, 0, 1999) : $description;
        $description = strip_tags($description);

        return $description;
    }

    /**
     * @param $brandattrtype
     * @param $product
     * @param $configbrand
     * @return mixed
     */

    public function GetBrand($brandattrtype, $product, $configbrand)
    {
        if ($brandattrtype == 'select') {
            $brand = $product->getAttributeText($configbrand);
        } else {
            $brand = $product->getData($configbrand);
        }
        return $brand;
    }

    /**
     * @param $brand
     * @param $product
     * @param $_uniquedata
     * @param $asin
     * @param $mfrpExist
     * @return string
     */

    public function testValidation($brand, $product, $_uniquedata, $asin, $mfrpExist)
    {
        $errmsg = '';
        if ($brand == null || trim($brand) == '') {
            $errmsg = "Error in Product: " . $product->getName() .
                " Brand information Required";
        } else if ($_uniquedata ['type'] == false && $asin == null &&
            $mfrpExist == false
        ) {
            $errmsg = "Error in Product: " . $product->getName() .
                " One of these values(UPC, EAN,GTIN-14,ISBN-13,ISBN-10) OR ASIN OR
        Manufacturer Part Number are Required";
        } else if ($_uniquedata ['type'] != false) {
            if ($_uniquedata ['allow'] == 0) {
                $errmsg = "Error in Product: " . $product->getName() .
                    " " . $_uniquedata ['type'] . " must be of " .
                    $_uniquedata ['size'] . " digits";
            }
        } else if ($asin != null) {
            if (strlen($asin) != 10 && $_uniquedata ['type'] == false) {
                $errmsg = "Error in Product: " . $product->getName() .
                    " ASIN must be of 10 digits";
            }
        }
        $qty = isset($product->getQuantityAndStockStatus()['qty']) ? $product->getQuantityAndStockStatus()['qty'] : 0;
        if ($qty <= 0) {
            $errmsg = 'product is out of stock';
        }
        return $errmsg;
    }

    /**
     * @param $barcode
     * @param $barcodeValue
     * @return array
     */

    public function CheckBarcode($barcode, $barcodeValue)
    {
        $_uniquedata = [];
        switch ($barcode) {
            case 'upc':
                $_uniquedata = ["type" => "UPC", "value" => $barcodeValue,
                    "allow" => (strlen($barcodeValue) == 12) ? 1 : 0, "size" => 12];
                break;
            case 'ean':
                $_uniquedata = ["type" => "EAN", "value" => $barcodeValue,
                    "allow" => (strlen($barcodeValue) == 13) ? 1 : 0, "size" => 13];
                break;
            case 'isbn-10':
                $_uniquedata = ["type" => "ISBN-10", "value" => $barcodeValue,
                    "allow" => (strlen($barcodeValue) == 10) ? 1 : 0, "size" => 10];
                break;
            case 'isbn-13':
                $_uniquedata = ["type" => "ISBN-13", "value" => $barcodeValue,
                    "allow" => (strlen($barcodeValue) == 13) ? 1 : 0, "size" => 13];
                break;
            case 'gtin-14':
                $_uniquedata = ["type" => "GTIN-14", "value" => $barcodeValue,
                    "allow" => (strlen($barcodeValue) == 14) ? 1 : 0, "size" => 14];
                break;
            default:
                $_uniquedata = ["type" => false];
        }
        return $_uniquedata;
    }

    /**
     * @param string $index
     */

    public function saveBatchData($index = "")
    {
        $date = date('Y-m-d H:i:s');
        $batcherror = [];
        $batcherror = $this->adminSession->getBatcherror();
        foreach ($batcherror as $key => $val) {
            $id = $key;
            if ($val['error'] != "") {
                $batch_id = 0;
                $batch_id = $this->getBatchIdFromProductId($id);
                if ($batch_id) {
                    $batchmod = $this->objectManager->create('Ced\Jet\Model\Batcherror')->load($batch_id);
                    $batchmod->setData("batch_num", $index);
                    $batchmod->setData("is_write_mode", '0');
                    $batchmod->setData("error", $val['error']);
                    $batchmod->setData('product_sku', $val['sku']);
                    $batchmod->setData("date_added", $date);
                    $batchmod->save();
                } else {
                    $batchmod = $this->objectManager->create('Ced\Jet\Model\Batcherror');
                    $batchmod->setData('product_id', $id);
                    $batchmod->setData('product_sku', $val['sku']);
                    $batchmod->setData("is_write_mode", '0');
                    $batchmod->setData("error", $val['error']);
                    $batchmod->setData("batch_num", $index);
                    $batchmod->setData("date_added", $date);
                    $batchmod->save();
                }
            } else {
                $batch_id = 0;
                $batch_id = $this->getBatchIdFromProductId($id);
                if ($batch_id) {
                    $batchmod = $this->objectManager->create('Ced\Jet\Model\Batcherror')->load($batch_id);
                    $batchmod->setData("batch_num", $index);
                    $batchmod->setData("is_write_mode", '0');
                    $batchmod->setData("error", '');
                    $batchmod->setData('product_sku', $val['sku']);
                    $batchmod->setData("date_added", $date);
                    $batchmod->save();
                }
            }
        }
        $this->adminSession->unsBatcherror();
    }

    /**
     * @param string $order
     * @param string $item_sku
     * @return array
     */
    public function getRefundedQtyInfo($order = "", $item_sku = "")
    {
        $item_sku = trim($item_sku);
        $check = [];
        $check['error'] = 1;
        if ($order == "") {
            $check['error_msg'] = "Order not found for current item.";
            return $check;
        }
        if ($item_sku == "") {
            $check['error_msg'] = "Item Sku not found for current item.";
            return $check;
        }

        if ($order instanceof \Magento\Sales\Model\Order) {
            $qty_ordered = 0;
            $qty_refunded = 0;

            foreach ($order->getAllItems() as $items) {
                if ($item_sku == $items->getSku()) {

                    $qty_ordered = intval($items->getQtyOrdered());
                    $qty_refunded = intval($items->getQtyRefunded());
                }
            }
            $available_to_refund_qty = 0;
            $available_to_refund_qty = intval($qty_ordered - $qty_refunded);

            $check['error'] = 0;
            $check['qty_already_refunded'] = $qty_refunded;
            $check['available_to_refund_qty'] = $available_to_refund_qty;
            $check['qty_ordered'] = $qty_ordered;
            return $check;
        }
    }

    /**
     * @return array
     */

    public function feedbackOptArray()
    {
        return [
            [
                'value' => '', 'label' => __('Please Select an Option')
            ],
            [
                'value' => 'Item is missing parts/accessories', 'label' => __('Item is missing parts/accessories')
            ],
            [
                'value' => 'Wrong Item', 'label' => __('Wrong Item')
            ],
            [
                'value' => 'Item damaged', 'label' => __('Item damaged')
            ],
            [
                'value' => 'Returned outside window', 'label' => __('Returned outside window')
            ],
            [
                'value' => 'Restocking fee', 'label' => __('Restocking fee')
            ],
            [
                'value' => 'Not shipped in original packaging', 'label' => __('Not shipped in original packaging')
            ],
            [
                'value' => 'Rerouting fee', 'label' => __('Rerouting fee')
            ]
        ];
    }


    /**
     * @return array
     */
    public function refundreasonOptionArr()
    {
        return [
            [
                'value' => '', 'label' => __('Please Select an Option')
            ],
            [
                'value' => 'No longer want this item', 'label' => __('No longer want this item')
            ],
            [
                'value' => 'Received the wrong item', 'label' => __('Received the wrong item')
            ],
            [
                'value' => 'Website description is inaccurate', 'label' => __('Website description is inaccurate')
            ],
            [
                'value' => 'Product is defective / does not work', 'label' => __('Product is defective / does not work')
            ],
            [
                'value' => 'Item arrived damaged - box intact', 'label' => __('Item arrived damaged - box intact')
            ],
            [
                'value' => 'Item arrived damaged - box damaged', 'label' => __('Item arrived damaged - box damaged')
            ],
            [
                'value' => 'Package never arrived', 'label' => __('Package never arrived')
            ],
            [
                'value' => 'Package arrived late', 'label' => __('Package arrived late')
            ],
            [
                'value' => 'Wrong quantity received', 'label' => __('Wrong quantity received')
            ],
            [
                'value' => 'Better price found elsewhere', 'label' => __('Better price found elsewhere')
            ],
            [
                'value' => 'Unwanted gift', 'label' => __('Unwanted gift')
            ],
            [
                'value' => 'Accidental order', 'label' => __('Accidental order')
            ],
            [
                'value' => 'Unauthorized purchase', 'label' => __('Unauthorized purchase')
            ],
            [
                'value' => 'Item is missing parts / accessories', 'label' => __('Item is missing parts / accessories')
            ],
            [
                'value' => 'Return to Sender - damaged, undeliverable, refused', 'label' => __('Return to Sender - damaged, undeliverable, refused')
            ],
            [
                'value' => 'Return to Sender - lost in transit only', 'label' => __('Return to Sender - lost in transit only')
            ],
            [
                'value' => 'Item is refurbished', 'label' => __('Item is refurbished')
            ],
            [
                'value' => 'Item is expired', 'label' => __('Item is expired')
            ],
            [
                'value' => 'Package arrived after estimated delivery date', 'label' => __('Package arrived after estimated delivery date')
            ]
        ];
    }

    /**
     * @param null $merchant_order_id
     * @param null $order_reference_id
     * @return array|int
     */

    public function getMagentoIncrementOrderId($merchant_order_id = null, $order_reference_id = null)
    {
        $merchant_order_id = trim($merchant_order_id);
        $order_reference_id = trim($order_reference_id);
        if ($merchant_order_id == null && $order_reference_id == null) {
            return 0;
        }
        try {
            $collection = $this->objectManager->get('Ced\Jet\Model\JetOrders')->getCollection()->addFieldToSelect('magento_order_id')->addFieldToFilter('merchant_order_id', $merchant_order_id);
            if ($order_reference_id)
                $reference_order = $this->objectManager->get('Ced\Jet\Model\JetOrders')->getCollection()->addFieldToSelect('magento_order_id')->addFieldToFilter('reference_order_id', $order_reference_id);
            if ($collection->getSize() > 0) {
                return $this->getMagentoOrderId($collection);
            } elseif ($reference_order->getSize() > 0) {
                return $this->getMagentoOrderId($reference_order);
            }
            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * @param $collection
     * @return mixed
     */
    public function getMagentoOrderId($collection)
    {
        foreach ($collection as $coll) {
            $mageOrderId = $coll->getData('magento_order_id');
            return $mageOrderId;
        }
    }

    /**
     * @param string $details_after_saved
     * @return bool
     */

    public function generateCreditMemoForRefund($details_after_saved = '')
    {
        if (!empty($details_after_saved)) {
            $sku_details = "";
            $sku_details = $details_after_saved['sku_details'];
            $item_details = [];
            $merchant_order_id = "";
            $merchant_order_id = $details_after_saved['merchant_order_id'];
            $shipping_amount = 0;
            $adjustment_positive = 0;
            foreach ($sku_details as $detail) {
                if ($this->checkifTrue($detail)) {
                    $item_details[] = ['sku' => $detail['merchant_sku'], 'refund_qty' => $detail['refund_quantity']];
                    $return_shipping_cost = 0;
                    $return_shipping_tax = 0;
                    $return_tax = 0;
                    $return_shipping_cost = (float)trim(isset($detail['return_shipping_cost']) ? $detail['return_shipping_cost'] : 0);
                    $return_tax = (float)trim(isset($detail['return_tax']) ? $detail['return_tax'] : 0);
                    $return_shipping_tax = (float)trim(isset($detail['return_shipping_tax']) ? $detail['return_shipping_tax'] : 0);
                    $shipping_amount = $shipping_amount + $return_shipping_cost +
                        $return_shipping_tax;
                    $adjustment_positive = $adjustment_positive + $return_tax;
                }
            }
            $collection = "";
            $mageOrderId = '';
            $collection = $this->objectManager->create(
                'Ced\Jet\Model\JetOrders')->getCollection()->addFieldToSelect('magento_order_id')->addFieldToFilter(
                'merchant_order_id', $merchant_order_id);

            if ($collection->getSize() > 0) {
                foreach ($collection as $coll) {
                    $mageOrderId = $coll->getData('magento_order_id');
                    break;
                }
            }

            if ($mageOrderId != '') {
                try {
                    $order = "";
                    $order = $this->objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($mageOrderId);
                    $data = [];
                    $shipping_amount = 1; // enable credit memo
                    $data['shipping_amount'] = 0;
                    $data['adjustment_positive'] = 0;

                    ($shipping_amount > 0) ? $data['shipping_amount'] = $shipping_amount : false;
                    ($adjustment_positive > 0) ? $data['adjustment_positive'] = $adjustment_positive : false;

                    foreach ($item_details as $key => $value) {
                        $orderItem = "";
                        $orderItem = $order->getItemsCollection()->getItemByColumnValue('sku', $value['sku']);
                        $data['qtys'][$orderItem->getId()] = $value['refund_qty'];
                    }

                    if (!array_key_exists("qtys", $data)) {
                        $this->messageManager->addError("Problem in Credit Memo Data Preparation.Can't generate Credit Memo.");
                        return false;
                    }

                    ($data['shipping_amount'] == 0) ? $this->messageManager->addError("Amount is 0 .So Credit Memo Cannot be generated.") : $this->generateCreditMemo($merchant_order_id, $data);

                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage() . ".Can't generate Credit Memo.");
                    return false;
                }
            } else {
                $this->messageManager->addError("Order not found.Can't generate Credit Memo.");
                return false;
            }
        } else {
            $this->messageManager->addError("Can't generate Credit Memo.");
            return false;
        }
    }

    /**
     * @param $detail
     * @return bool
     */
    public function checkifTrue($detail)
    {
        if ($detail['refund_quantity'] > 0
            && $detail['return_quantity'] >= $detail['refund_quantity']
            && $detail['refund_quantity'] <= $detail['available_to_refund_qty']
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $magento_order_id
     * @param $data
     * @return bool
     */

    public function generateCreditMemo($mageOrderId, $data)
    {
        $creditmemo_api = "";
        $creditmemo_id = "";
        $comment = "";
        $comment = "Credit memo generated from Jet.com refund functionality.";
        $order = $this->objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($mageOrderId);
        if ($order->canCreditmemo()) {
            $this->creditmemoLoader = $this->creditmemoLoaderFactory->create();
            $this->creditmemoLoader->setOrderId(intval($mageOrderId));
            $this->creditmemoLoader->setCreditmemo($data['qtys']);
            $creditmemo = $this->creditmemoLoader->load();
            $creditmemoManagement = $this->objectManager->create(
                'Magento\Sales\Api\CreditmemoManagementInterface');
            if ($creditmemo) {
                $creditmemoManagement->refund($creditmemo, (bool)$data);
                $this->messageManager->addSuccess("Credit Memo " . $creditmemo_id . " is Successfully generated for Order :" . $mageOrderId . ".");
                return true;
            }
        }
    }

    /**
     * @param string $merchant_order_id
     * @return bool
     */

    public function checkOrderInRefund($merchant_order_id = "")
    {
        $merchant_order_id = trim($merchant_order_id);
        try {
            $collection = "";
            $collection = $this->objectManager->create('Ced\Jet\Model\Refund')->getCollection()->addFieldToFilter('refund_orderid', $merchant_order_id);
            if ($collection->getSize() > 0) {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param string $details_saved_after
     * @param string $id
     * @return array
     */
    public function prepareDataAfterSubmitReturn($detailSavedAfter = "", $id = "")
    {
        $skus = $detailSavedAfter['sku_details'];
        $returnModel = $this->objectManager->get('Ced\Jet\Model\OrderReturn')->load($id);
        $returnSerData = $returnModel->getData('return_details');
        $returnData = unserialize($returnSerData);
        $mageOrderId = $this->getMagentoIncrementOrderId($returnData->merchant_order_id);
        $order = $this->objectManager->get('Magento\Sales\Model\Order')->loadByIncrementId($mageOrderId);
        $result = [];
        $result['id'] = $returnModel->getData('id');
        $result['returnid'] = $returnModel->getData('returnid');
        $result['merchant_order_id'] = $returnData->merchant_order_id;
        $result['agreeto_return'] = $detailSavedAfter['agreeto_return'];
        $i = 0;
        foreach ($returnData->return_merchant_SKUs as $sku_detail) {
            $check = [];
            $check = $this->getRefundedQtyInfo($order, $sku_detail->merchant_sku);
            if ($check['error'] == '1') {
                continue;
            }
            $flag = false;
            foreach ($skus as $key => $detail) {
                if ($sku_detail->merchant_sku == $detail['merchant_sku'] && $detail['want_to_return'] == '1') {
                    $result['sku_details']["sku$i"]['refund_quantity'] = trim($detail['refund_quantity']);
                    $result['sku_details']["sku$i"]['return_refundfeedback'] = trim($detail['return_refundfeedback']);
                    $result['sku_details']["sku$i"]['return_actual_principal'] = trim($detail['return_actual_principal']);
                    $result['sku_details']["sku$i"]['want_to_return'] = $detail['want_to_return'];
                    $result['sku_details']["sku$i"]['changes_made'] = 0;
                    $result['sku_details']["sku$i"]['qty_already_refunded'] = isset($detail['qty_already_refunded']) ? $detail['qty_already_refunded'] : 0;
                    $result['sku_details']["sku$i"]['available_to_refund_qty'] = isset($detail['available_to_refund_qty']) ? $detail['available_to_refund_qty'] : 0;
                    $result['sku_details']["sku$i"]['qty_ordered'] = isset($detail['qty_ordered']) ? $detail['qty_ordered'] : 0;
                    $result['sku_details']["sku$i"]['order_item_id'] = $detail['order_item_id'];
                    $result['sku_details']["sku$i"]['return_quantity'] = $detail['return_quantity'];
                    $result['sku_details']["sku$i"]['merchant_sku'] = $detail['merchant_sku'];
                    $result['sku_details']["sku$i"]['return_principal'] = trim($detail['return_principal']);
                    $result['sku_details']["sku$i"]['return_tax'] = trim($detail['return_tax']);
                    $result['sku_details']["sku$i"]['return_shipping_cost'] = trim($detail['return_shipping_cost']);
                    $result['sku_details']["sku$i"]['return_shipping_tax'] = trim($detail['return_shipping_tax']);
                    $flag = true;
                    break;
                }
            }
            if ($flag) {
                $i++;
                continue;
            }
            $result['sku_details']["sku$i"]['refund_quantity'] = 0;
            $result['sku_details']["sku$i"]['return_refundfeedback'] = "";
            $result['sku_details']["sku$i"]['return_actual_principal'] = $sku_detail->requested_refund_amount->principal;
            $result['sku_details']["sku$i"]['want_to_return'] = 0;
            $result['sku_details']["sku$i"]['changes_made'] = 0;
            $result['sku_details']["sku$i"]['qty_already_refunded'] = $check['qty_already_refunded'];
            $result['sku_details']["sku$i"]['available_to_refund_qty'] = $check['available_to_refund_qty'];
            $result['sku_details']["sku$i"]['qty_ordered'] = $check['qty_ordered'];
            $result['sku_details']["sku$i"]['order_item_id'] = $sku_detail->order_item_id;
            $result['sku_details']["sku$i"]['return_quantity'] = $sku_detail->return_quantity;
            $result['sku_details']["sku$i"]['merchant_sku'] = $sku_detail->merchant_sku;
            $result['sku_details']["sku$i"]['return_principal'] = $sku_detail->requested_refund_amount->principal;
            $result['sku_details']["sku$i"]['return_tax'] = $sku_detail->requested_refund_amount->tax;
            $result['sku_details']["sku$i"]['return_shipping_cost'] = $sku_detail->requested_refund_amount->shipping_cost;
            $result['sku_details']["sku$i"]['return_shipping_tax'] = $sku_detail->requested_refund_amount->shipping_tax;
            $i++;
        }
        return $result;
    }

    /**
     * @param string $detailSavedAfter
     * @return string
     */

    public function saveChangesMadeValue($detailSavedAfter = "")
    {
        $skus = "";
        $skus = $detailSavedAfter['sku_details'];
        foreach ($skus as $key => $detail) {
            if ($detail['want_to_return'] == '1') {
                $detailSavedAfter['sku_details'][$key]['changes_made'] = 1;
            }
        }
        return $detailSavedAfter;
    }

    /**
     * @param $merchantNode
     * @param $commp_ids
     * @param $fileid
     * @param $tokenurl
     * @param $model
     * @param $helper
     * @param $action
     * @return mixed
     */

    public function arcUnarcJetProduct($merchantNode, $commp_ids, $fileid, $tokenurl, $model, $helper, $action)
    {
        $merchantSkuPath = $helper->createJsonFile($action, $merchantNode);
        $path_data = explode('/', $merchantSkuPath);
        $sku_file_name = end($path_data);
        $merchantSkuPath = $merchantSkuPath . '.gz';
        $text = ['magento_batch_info' => $commp_ids,
            'jet_file_id' => $fileid,
            'token_url' => $tokenurl,
            'file_name' => $sku_file_name,
            'file_type' => "Archive",
            'status' => 'unprocessed'
        ];
        $model->addData($text)->save();
        $helper->uploadFile($merchantSkuPath, $tokenurl);

        $postFields = '{"url":"' . $tokenurl . '","file_type":"Archive","file_name":"' . $sku_file_name . '"}';
        $response = $helper->CPostRequest('/files/uploaded', $postFields);
        return $response;
    }

    /**
     * @param string $detailSavedAfter
     * @return bool
     */

    public function checkViewCaseForReturn($detailSavedAfter = "")
    {
        $skus = "";
        $skus = $detailSavedAfter['sku_details'];
        $count = 0;
        $count = count($skus);
        $i = 0;
        foreach ($skus as $key => $detail) {
            if ($detail['changes_made'] == '1') {
                $i++;
            }
        }
        if ($count > 0 && $count == $i) {
            return true;
        }
        return false;
    }

    /**
     * @return array|string
     */

    public function getReturnLocation()
    {
        $address1 = $this->scopeConfigManager->getValue('jetconfiguration/return_location/first_add');
        $address2 = $this->scopeConfigManager->getValue('jetconfiguration/return_location/second_add');
        $city = trim($this->scopeConfigManager->getValue('jetconfiguration/return_location/city'));
        $state = trim($this->scopeConfigManager->getValue('jetconfiguration/return_location/state'));
        $zip = trim($this->scopeConfigManager->getValue('jetconfiguration/return_location/zip_code'));
        if ($zip == "") {
            return "kindly set zip code from system configuration";
        }
        if ($state == "") {
            return "kindly set state from system configuration";
        }
        if ($city == "") {
            return "kindly set city from system configuration";
        }
        if ($address1 == "") {
            return "kindly set address from system configuration";
        }
        // create Retrun location Array
        $returnLoc = ['address1' => $address1,
            'address2' => $address2,
            'city' => $city,
            'state' => $state,
            'zip_code' => $zip];
        return $returnLoc;
    }
}
