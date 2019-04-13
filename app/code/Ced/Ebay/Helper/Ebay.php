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
 * @package   Ced_Ebay
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Ebay\Helper;

class Ebay extends \Magento\Framework\App\Helper\AbstractHelper
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
    public $adminSession;
    /**
     * @var \Magento\Framework\Message\Manager
     */
    public $messageManager;
    /**
     * DirectoryList
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    public $directoryList;
    /**
     * Json Parser
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $json;
    /**
     * @var \Magento\Framework\HTTP\Adapter\Curl
     */
    public $_resource;

    public $_compatLevel;

    public $_siteID;

    public $_appID;

    public $_certID;

    public $_devID;

    public $_environment;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Message\Manager $manager
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Json\Helper\Data $json
     * @param \Magento\Framework\HTTP\Adapter\Curl $curl
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Message\Manager $manager,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Json\Helper\Data $json,
        \Magento\Directory\Model\CountryFactory $country,
        \Magento\Framework\HTTP\Adapter\Curl $curl
    )
    {
        $this->objectManager = $objectManager;
        $this->_resource = $curl;
        parent::__construct($context);
        $this->messageManager = $manager;
        $this->directoryList = $directoryList;
        $this->json = $json;
        $this->_country = $country;
        $this->adminSession = $this->objectManager->create('Magento\Backend\Model\Session');
        $this->scopeConfigManager = $this->objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->configResourceModel = $this->objectManager->create('\Magento\Config\Model\ResourceModel\Config');
        $this->_appID = "PankajAs-GeforceI-SBX-345ed6035-93572cfa";
        $this->_certID = 'SBX-45ed60356c5f-836b-4422-9202-6f4c';
        $this->_devID = 'a4d749e7-9b22-441e-8406-d3b65d95d41a';
        $this->_environment = $this->scopeConfigManager->getValue('ebay_config/ebay_setting/environment');
        $this->_token = $this->scopeConfigManager->getValue('ebay_config/ebay_setting/token');
        $this->_runame = 'Pankaj_Aswal-PankajAs-Geforc-ldlnmtua';
        $this->_siteID = $this->scopeConfigManager->getValue('ebay_config/ebay_setting/location');
        $this->_compatLevel = 989;
    }

    public function prepareData($productId)
    {
        $item = [];
        $paymentMethods = $this->scopeConfigManager->getValue('ebay_config/payment_detail/payment_methods');
        $paymentMethods = explode(',', $paymentMethods);
        $paypalEmail = $this->scopeConfigManager->getValue('ebay_config/payment_detail/paypal_email');
        $serviceType = $this->scopeConfigManager->getValue('ebay_config/shipping_details/service_type');
        $shippingService = $this->scopeConfigManager->getValue('ebay_config/shipping_details/shipping_service');
        $shippingCost = $this->scopeConfigManager->getValue('ebay_config/shipping_details/shipping_cost');
        $shipAddCost = $this->scopeConfigManager->getValue('ebay_config/shipping_details/ship_add_cost');
        $postalCode = $this->scopeConfigManager->getValue('ebay_config/shipping_details/postal_code');
        $refundType = $this->scopeConfigManager->getValue('ebay_config/return_policy/refund_type');
        $returnDays = $this->scopeConfigManager->getValue('ebay_config/return_policy/return_days');
        $returnAccepted = $this->scopeConfigManager->getValue('ebay_config/return_policy/return_accepted');
        $returnDescription = $this->scopeConfigManager->getValue('ebay_config/return_policy/return_description');
        $shipCostPaidby = $this->scopeConfigManager->getValue('ebay_config/return_policy/ship_cost_paidby');
        $restockingFeeValue = $this->scopeConfigManager->getValue('ebay_config/return_policy/restocking_fee_value');

       if (empty($serviceType) || empty($paymentMethods) || empty($shippingService) || empty($shippingCost) || empty($shipAddCost) || empty($postalCode) || empty($refundType) || empty($returnDays) || empty($returnDescription) || empty($returnAccepted) || empty($shipCostPaidby) || empty($restockingFeeValue)) {
            $content = [
                'type' => 'error',
                'data' => 'Please fill the configuration section for eBay.'
            ];
            return $content;
        }

        $xmlArray = [];
        $returnArray = [];
        $finalXml = "";
        $counter = 1;
        $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($productId);
        $productType = $product->getTypeId();
        $sku = $product->getSku();
        $countryDetails = $this->objectManager->get('Ced\Ebay\Helper\Data')->getEbaysites($this->_siteID);
        $country = $countryDetails['name'];
        $currency = $countryDetails['currency'][0];
        $profileId = $this->objectManager->get('Ced\Ebay\Model\Profileproducts')->loadByField('product_id', $productId);
        $profileData = $this->objectManager->get('Ced\Ebay\Model\Profile')->load($profileId->getProfileId());
        $image = $this->objectManager->get('Magento\Catalog\Model\Product\Media\Config')->getMediaUrl($product->getImage());
        $image1 = explode("/", $image);
        if (end($image1) != '') {
            $pictureUrl = $image;
        } else {
            $content = [
                'type' => 'error',
                'data' => 'product image not found'
            ];
            return $content;
        }

        $mpn = $product->getData('mfr_part_number');
        /*$isbn = $product->getData('isbn');
        $ean = $product->getData('ean');
        $upc = $product->getData('upc');*/
        $isbn = $ean = "";
        if (empty($mpn)) {
            $content = [
                'type' => 'error',
                'data' => 'M. P. N. is a required field'
            ];
            return $content;
        }
        $upc = $product->getBarcodeValue();
        if (empty($isbn) && empty($ean) && empty($upc)) {
            $content = [
                'type' => 'error',
                'data' => 'Please fill atleast one of them (UPC, ISBN, EAN)'
            ];
            return $content;
        }

        $longDescription = $product->getDescription();
        $title = substr($product->getName(), 0, 65);
        $catJson = $profileData->getProfileCategory();
        $primarycatId = "";
        if ($catJson) {
            $catArray = array_reverse(json_decode($catJson, true));
            foreach ($catArray as $value) {
                if ($value != "") {
                    $primarycatId = $value;
                    break;
                }
            }
        }
        $nameValueList = "";
        $listingDuration = $product->getData('listing_duration');
        $listingType = $product->getData('listing_type');
        $dispatchTime = $product->getData('max_dispatch_time');
         if (empty($listingDuration) || empty($listingType) || empty($dispatchTime)) {
            $content = [
                'type' => 'error',
                'data' => 'Listing Duration, Type, dispatch time are required fields'
            ];
            return $content;
        }
        $price = $product->getFinalPrice() != '' ? (float)$product->getFinalPrice() : 0;
        $amount = isset($product->getQuantityAndStockStatus()['qty'])? $product->getQuantityAndStockStatus()['qty'] : 0;
        if($amount == 0){
            $content = [
                'type' => 'error',
                'data' => "Product Inventory/ Quantity cann't be 0"
            ];
            return $content;
        }
        if($price == 0){
            $content = [
                'type' => 'error',
                'data' => "Product price can not be 0"
             ];
        return $content;
        }
        $configAttrlist = $profileData->getProfileCatAttribute();
        $catSpecifics = json_decode($configAttrlist, true);
        if(is_array($catSpecifics) && !empty($catSpecifics)){
            foreach ($catSpecifics as $key => $specific) {
                $catValue = $product->getData($specific['magento_attribute_code']);
                if (empty($catValue)) {
                    $content = [
                            'type' => 'error',
                            'data' => "please fill all category dependent attribute's value"
                         ];
                    return $content;
                }
                if ($specific['magento_attribute_code'] == 'country_of_manufacture' && !empty($catValue)) {
                    $catValue = $this->_country->create()->loadByCode($catValue)->getName();
                }
                $nameValueList .= '<NameValueList>
                                  <Name>'.$key.'</Name>
                                  <Value>'.$catValue.'</Value>
                                </NameValueList>';
            }
        }

        $reqOptAttr = $profileData->getProfileReqOptAttribute();
        //print_r(json_decode($reqOptAttr, true));die;
        //$this->reqOptAttributeData($product, $reqOptAttr);
        //echo "<pre>"; print_r($nameValueList);die;
        $conditionID = $profileData->getProfileCatFeature();
        
        $item['Title'] = $title;
        $item['SKU'] = $sku;
        $item['Description'] = $longDescription;
        if($nameValueList != "" && $nameValueList!= null){
            $nameValueList = '<ItemSpecifics>'.$nameValueList.'</ItemSpecifics>';
            $item['ItemSpecifics'] = "ced";
        }
        $item['PrimaryCategory']['CategoryID'] = $primarycatId;
        
        if($ean != ""){
            $item['ProductListingDetails']['EAN'] = $ean;
        }
        if($isbn !=""){
            $item['ProductListingDetails']['ISBN'] = $isbn;
        }
        if($upc !=""){
            $item['ProductListingDetails']['UPC'] = $upc;
        }
        $item['CategoryMappingAllowed'] = true;
        $item['ConditionID'] = $conditionID;
        $item['ShippingDetails']['ShippingType']= $serviceType;
        $item['ShippingDetails']['ShippingServiceOptions']['ShippingService'] = $shippingService;
        $item['ShippingDetails']['ShippingServiceOptions']['ShippingServicePriority'] = 1;
        $item['ShippingDetails']['ShippingServiceOptions']['ShippingServiceAdditionalCost'] = $shipAddCost;
        $item['ShippingDetails']['ShippingServiceOptions']['ShippingServiceCost'] = $shippingCost;
        $item['ReturnPolicy']['ReturnsAcceptedOption'] = $returnAccepted;
        $item['ReturnPolicy']['RefundOption'] = $refundType ;
        $item['ReturnPolicy']['ReturnsWithinOption'] = $returnDays;
        $item['ReturnPolicy']['ShippingCostPaidByOption'] = $shipCostPaidby;
        $item['HitCounter'] = 'HiddenStyle';
        $item['Site'] = $country;
        $item['Quantity'] = $amount;
        $item['StartPrice'] = $price;
        $item['ListingDuration'] = $listingDuration;
        $item['ListingType'] = $listingType;
        $item['DispatchTimeMax'] = $dispatchTime;
        $item['Country'] = $country;
        $item['Currency'] = $currency;
        $item['PostalCode'] = $postalCode;
        $item['PaymentMethods'] = 'ced';
        $paymethod  = '';
        foreach ($paymentMethods as $paymentmethod) {
            $paymethod .= '<PaymentMethods>'.$paymentmethod.'</PaymentMethods>'; 
        }
        if($paypalEmail) {
            $item['PayPalEmailAddress'] = $paypalEmail;
        }
        $item['PictureDetails']['PictureURL'] = $pictureUrl;
        
        $xmlArray['AddItemRequestContainer']['MessageID'] = $productId;
        $xmlArray['AddItemRequestContainer']['Item'] = $item;
        $rootElement = "AddItemRequestContainer";
        $xml = new \SimpleXMLElement("<$rootElement/>");
        $this->array2XML($xml, $xmlArray['AddItemRequestContainer']);
        $finalXml = $xml->asXML();
        if(strpos($finalXml, '<ItemSpecifics>ced</ItemSpecifics>') !== false){
            $finalXml = str_replace('<ItemSpecifics>ced</ItemSpecifics>',$nameValueList, $finalXml);
        }
        if(strpos($finalXml, '<PaymentMethods>ced</PaymentMethods>') !== false){
            $finalXml = str_replace('<PaymentMethods>ced</PaymentMethods>',$paymethod, $finalXml);
        }
        $content = [
                'type' => 'success',
                'data' => $finalXml
            ];
        return $content;
    }

    /**
     * @param $mappedCode
     * @param $product
     * @return array
     */

    public function getBullets($mappedCode, $product)
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

    function reqOptAttributeData() {

    }

    function array2XML($xml_obj, $array) {
            foreach ($array as $key => $value) {
                if(is_numeric($key)) {
                    $key = $key;
                }
                if (is_array($value)) {
                    $node = $xml_obj->addChild($key);
                    $this->array2XML($node, $value);
                }
                else {
                    $xml_obj->addChild($key, htmlspecialchars($value));
                }
            }
        }
}