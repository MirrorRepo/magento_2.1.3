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
        \Magento\Framework\HTTP\Adapter\Curl $curl
    )
    {
        $this->objectManager = $objectManager;
        $this->_resource = $curl;
        parent::__construct($context);
        $this->messageManager = $manager;
        $this->directoryList = $directoryList;
        $this->json = $json;
        $this->adminSession = $this->objectManager->create('Magento\Backend\Model\Session');
        $this->scopeConfigManager = $this->objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->configResourceModel = $this->objectManager->create('\Magento\Config\Model\ResourceModel\Config');
        $this->_appID = "PankajAs-GeforceI-PRD-2090330f6-233c7a3c";// PankajAs-GeforceI-PRD-2090330f6-233c7a3c,  PankajAs-GeforceI-SBX-345ed6035-93572cfa
        $this->_certID = 'PRD-090330f62955-96aa-4c19-b1df-6bf4'; //PRD-090330f62955-96aa-4c19-b1df-6bf4, SBX-45ed60356c5f-836b-4422-9202-6f4c
        $this->_devID = 'a4d749e7-9b22-441e-8406-d3b65d95d41a'; //a4d749e7-9b22-441e-8406-d3b65d95d41a, a4d749e7-9b22-441e-8406-d3b65d95d41a
        $this->_environment = $this->scopeConfigManager->getValue('ebay_config/ebay_setting/environment');
        $this->_token = $this->scopeConfigManager->getValue('ebay_config/ebay_setting/token');
        $this->_runame = 'Pankaj_Aswal-PankajAs-Geforc-glteowd'; // Pankaj_Aswal-PankajAs-Geforc-glteowd Pankaj_Aswal-PankajAs-Geforc-ldlnmtua
        $this->_siteID = $this->scopeConfigManager->getValue('ebay_config/ebay_setting/location');
        $this->_compatLevel = 989;
    }

    /**
     * @param $requestBody
     * @param $call
     * @return mixed
     */
    public function sendHttpRequest($requestBody, $call, $type, $siteID= null, $env= null)
    {
        $siteID = $siteID != null ? $siteID : $this->_siteID;
        $env = $env != null ? $env : $this->_environment;
        if ($siteID == "") {
          return "Please select the siteId";
        }
        if ($env == "") {
          return "Please select the environment";
        }
        $headers = $this->_buildEbayHeaders($call, $siteID);
        $serverUrl = $this->getUrl($type, $env);

        $connection = curl_init();
        curl_setopt($connection, CURLOPT_URL, $serverUrl);
        curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($connection, CURLOPT_POST, 1);
        curl_setopt($connection, CURLOPT_POSTFIELDS, $requestBody);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
        $responseXml = curl_exec($connection);
        curl_close($connection);
        $response = $this->ParseResponse($responseXml);
        return $response;
    }


    /**
     * Build header for API request
     * @return array
     */
    public function _buildEbayHeaders($call, $siteID)
    {
        $headers = array(
            'X-EBAY-API-COMPATIBILITY-LEVEL: ' . $this->_compatLevel,
            'X-EBAY-API-DEV-NAME: ' . $this->_devID,
            'X-EBAY-API-APP-NAME: ' . $this->_appID,
            'X-EBAY-API-CERT-NAME: ' . $this->_certID,
            'X-EBAY-API-CALL-NAME: ' . $call,
            'X-EBAY-API-SITEID: ' . $siteID,
        );
        return $headers;
    }

    /**
     * Get Session Id for fetch token id
     * @return array 
     */
    
    public function getSessionId($siteID=null, $env=null)
    {
        $result = [];
        $ruName = $this->_runame;
        $compLevel = $this->_compatLevel;
        $variable ="GetSessionID";
        $requestBody = '<?xml version="1.0" encoding="utf-8" ?>';
        $requestBody .= '<GetSessionIDRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
        $requestBody .= "<Version>$compLevel</Version>";
        $requestBody .= "<RuName>$ruName</RuName>";
        $requestBody .= '</GetSessionIDRequest>';

        $response = $this->sendHttpRequest($requestBody, $variable, 'server', $siteID, $env);

        if(isset($response->Ack) && $response->Ack =='Success') {
            $sessionID = $response->SessionID;
            //$currentUrl = $this->objectManager->get('Magento\Framework\UrlInterface')->getBaseUrl();
            $currentUrl = $this->objectManager->get('Magento\Backend\Helper\Data')->getHomePageUrl();
            $currentUrl = str_replace("admin/", "", $currentUrl);
            $url = $currentUrl."ebay/config/token/";            
            $param = array("url" => $url);
            $query = http_build_query($param);
            $sesId = urlencode($sessionID);
            $loginURL = $this->getUrl('login', $env);
            $runame = $this->_runame;
            $sessionId = $this->adminSession->getSessId();
            if (isset($sessionId)) {
                $this->adminSession->unsSessId();
            }
            $this->adminSession->setSessId($response->SessionID);
            $this->adminSession->setSiteId($siteID);
            $this->adminSession->setEnv($env);
            $result['data'] = $loginURL."?SignIn&runame=".$runame."&SessID=".$sesId."&ruparams=".$query;
            $result['msg'] = "success";
        } else {
            $result['data'] = isset($response->Errors->ShortMessage) ? $response->Errors->ShortMessage : "Something went wrong";
            $result['msg'] = "error";
        }
        return $result;
    }

    /**
     * Fetch Token
     * @return bool 
     */

    public function fetchToken()
    {
        $msg = "";
        $sessionId = $this->adminSession->getSessId();
        $siteID = $this->adminSession->getSiteId();
        $env = $this->adminSession->getEnv();
        $variable = "FetchToken";
        $requestBody = '<?xml version="1.0" encoding="utf-8" ?>';
        $requestBody .= '<FetchTokenRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
        $requestBody .= "<SessionID>$sessionId</SessionID>";
        $requestBody .= '</FetchTokenRequest>';

        $response = $this->sendHttpRequest($requestBody, $variable, 'server', $siteID, $env);
        if(isset($response->Ack) && $response->Ack =='Success') {
            $this->configResourceModel->saveConfig('ebay_config/ebay_setting/location', $siteID,'default',0);
            $this->configResourceModel->saveConfig('ebay_config/ebay_setting/environment', $env,'default',"");
            $this->configResourceModel->saveConfig('ebay_config/ebay_setting/expiration_time',$response->HardExpirationTime,'default',0);
            $this->configResourceModel->saveConfig('ebay_config/ebay_setting/created_time', $response->Timestamp,'default',0);
            $this->configResourceModel->saveConfig('ebay_config/ebay_setting/session_id', $sessionId,'default',0);
            $this->configResourceModel->saveConfig('ebay_config/ebay_setting/token', $response->eBayAuthToken,'default',0);
            $this->adminSession->unsSessionId();
            $this->messageManager->addSuccess("Token Successfully Generated");
        } else {
            $this->messageManager->addSuccess("Token Generation Failed");
        }
        return true;
    }

    /**
     * Get Categories
     * @param level
     * @param parentId
     * @return  
     */

    public function getCategories($level=1,$ParentcatID=null)
    {
        $siteID = $this->_siteID;
        $variable = "GetCategories";
        $token = $this->_token;
        $requestBody  = '<?xml version="1.0" encoding="utf-8"?>';
        $requestBody .= '<GetCategoriesRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
        $requestBody .= "<RequesterCredentials><eBayAuthToken>$token</eBayAuthToken></RequesterCredentials>";
        if($ParentcatID){
            $requestBody .= '<CategoryParent>'.$ParentcatID.'</CategoryParent>';
        }
        $requestBody .= '<CategorySiteID>'.$siteID.'</CategorySiteID>';
        $requestBody .= '<LevelLimit>'.$level.'</LevelLimit>';
        $requestBody .= '<DetailLevel>ReturnAll</DetailLevel>';
        $requestBody .= '</GetCategoriesRequest>';
        $response = $this->sendHttpRequest($requestBody, $variable, 'server');
        if(isset($response->Ack) && $response->Ack =='Success'){
            return $response;
        } else {
            return "error";
        }
    }

    /**
     * Get Category Specific Attribute
     * @param $catID
     * @return string 
     */

    public function getCatSpecificAttribute($catID)
    {
        $siteID = $this->_siteID;
        $variable = "GetCategorySpecifics";
        $token = $this->_token;
        $requestBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestBody .= '<GetCategorySpecificsRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
        $requestBody .= '<WarningLevel>High</WarningLevel>';
        $requestBody .= '<CategorySpecific><CategoryID>'.$catID.'</CategoryID></CategorySpecific>';
        $requestBody .= '<RequesterCredentials><eBayAuthToken>'.$token.'</eBayAuthToken></RequesterCredentials>';
        $requestBody .= '</GetCategorySpecificsRequest>';
        $response = $this->sendHttpRequest($requestBody, $variable, 'server');
        if (isset($response->Ack) && $response->Ack =='Success') {
            return $response;
        } else {
            return "error";
        }
    }
    
    /**
     * Get Category Features
     * @param $catID
     * @param $limits
     * @return string
     */

    public function getCategoryFeatures($catID, $limits)
    {
        $siteID = $this->_siteID;
        $variable = "GetCategoryFeatures";
        $token = $this->_token;
        $requestBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestBody .= '<GetCategoryFeaturesRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
        $requestBody .= '<RequesterCredentials><eBayAuthToken>'.$token.'</eBayAuthToken></RequesterCredentials>';
        $requestBody .= '<CategoryID>'.$catID.'</CategoryID>';
        $requestBody .= '<DetailLevel>ReturnAll</DetailLevel>';
        $requestBody .= '<ViewAllNodes>true</ViewAllNodes>';
        if (is_array($limits) && !empty($limits)) {
            foreach ($limits as $limit) {
                $requestBody .= '<FeatureID>'.$limit.'</FeatureID>';
            }
        }
        $requestBody .= '</GetCategoryFeaturesRequest>';
        $response = $this->sendHttpRequest($requestBody, $variable, 'server');
        if (isset($response->Ack) && $response->Ack =='Success') {
            return $response;
        } else {
            return "error";
        }
    }

    /**
     * Get Category Features
     * @return string
     */

    public function getSiteSpecificPaymentMethods()
    {
        $variable = "GetCategoryFeatures";
        $requestBody = '<?xml version="1.0" encoding="utf-8"?>
                    <GetCategoryFeaturesRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                      <RequesterCredentials>
                        <eBayAuthToken>'.$this->_token.'</eBayAuthToken>
                      </RequesterCredentials>
                      <DetailLevel>ReturnAll</DetailLevel>
                      <FeatureID>PaymentMethods</FeatureID>
                    </GetCategoryFeaturesRequest>';
        
        $response = $this->sendHttpRequest($requestBody, $variable, 'server');
        if (isset($response->Ack) && $response->Ack =='Success') {
            $result = json_encode($response->SiteDefaults->PaymentMethod);
        } else {
            $result = 'error';
        }
        return $result;
    }

    /**
     * Get Return Policy
     * @return string
     */

    public function getSiteSpecificReturnPolicy()
    {
        $variable = "GeteBayDetails";
        $requestBody = '<?xml version="1.0" encoding="utf-8"?>
                      <GeteBayDetailsRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                        <RequesterCredentials>
                          <eBayAuthToken>'.$this->_token.'</eBayAuthToken>
                        </RequesterCredentials>
                        <DetailName>ReturnPolicyDetails</DetailName>
                      </GeteBayDetailsRequest>';
        
        $response = $this->sendHttpRequest($requestBody, $variable, 'server');
        if (isset($response->Ack) && $response->Ack =='Success') {
            $result = json_encode($response);
        } else {
            $result = 'error';
        }
        return $result;
    }

    /**
     * Get Shipping Details
     * @return string
     */

    public function getSiteSpecificShippingDetails()
    {
        $variable = "GeteBayDetails";
        $requestBody = '<?xml version="1.0" encoding="utf-8"?> 
                      <GeteBayDetailsRequest xmlns="urn:ebay:apis:eBLBaseComponents"> 
                        <RequesterCredentials> 
                          <eBayAuthToken>'.$this->_token.'</eBayAuthToken> 
                        </RequesterCredentials> 
                        <DetailName>ShippingCarrierDetails</DetailName> 
                        <DetailName>ShippingServiceDetails</DetailName> 
                      </GeteBayDetailsRequest>';
        
        $response = $this->sendHttpRequest($requestBody, $variable, 'server');
        if (isset($response->Ack) && $response->Ack =='Success') {
            $result = json_encode($response);
        } else {
            $result = 'error';
        }
        return $result;
    }

    /**
     * Fetch Orders
     * @return string
     */
    public function getOrderRequestBody()
    {
        $startDate = date('Y-m-d', strtotime('-10 days', time()));
        $orderFrom =  gmdate("Y-m-d\TH:i:s", strtotime($startDate));   
        $orderTo = gmdate("Y-m-d\TH:i:s");
        $variable = "GetOrders";
        $requestBody = '<?xml version="1.0" encoding="utf-8" ?>';
        $requestBody .= '<GetOrdersRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
        $requestBody .= '<DetailLevel>ReturnAll</DetailLevel>';
        $requestBody .= "<CreateTimeFrom>".$orderFrom."</CreateTimeFrom><CreateTimeTo>".$orderTo."</CreateTimeTo>";
        $requestBody .= '<OrderRole>Seller</OrderRole><OrderStatus>All</OrderStatus>';
        $requestBody .= "<RequesterCredentials><eBayAuthToken>".$this->_token."</eBayAuthToken></RequesterCredentials>";
        $requestBody .= '</GetOrdersRequest>';
        $response = $this->sendHttpRequest($requestBody, $variable, 'server');
        
        if (isset($response->Ack) && $response->Ack =='Success') {
            $result = json_encode($response->OrderArray);
        } else {
            $result = 'error';
        }
        return $result;
    }

    /**
     * create shippment order 
     * @param $ebayOrderId
     * @param $trackNumber
     * @param $shippingCarrierUsed
     * @param $deliveryDate
     * @return string
     */
    public function createShipmentOrderBody($ebayOrderId, $trackNumber, $shippingCarrierUsed, $deliverydate, $shipment)
    {
        $variable = "CompleteSale";
        $requestBody = '<?xml version="1.0" encoding="utf-8"?>
                    <CompleteSaleRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                        <RequesterCredentials>
                            <eBayAuthToken>'.$this->_token.'</eBayAuthToken>
                        </RequesterCredentials>
                        <WarningLevel>High</WarningLevel>
                        <OrderID>'.$ebayOrderId.'</OrderID>
                        <Shipment>
                            <ShipmentTrackingDetails>
                              <ShipmentTrackingNumber>'.$trackNumber.'</ShipmentTrackingNumber>
                              <ShippingCarrierUsed>'.$shippingCarrierUsed.'</ShippingCarrierUsed>
                            </ShipmentTrackingDetails>
                            <ShippedTime>'.$deliverydate.'</ShippedTime>
                        </Shipment>
                        <Shipped>'.$shipment.'</Shipped>
                        <TransactionID>0</TransactionID>
                    </CompleteSaleRequest>';
        $response = $this->sendHttpRequest($requestBody, $variable, 'server');
        if (isset($response->Ack) && $response->Ack =='Success') {
            $result = 'Success';
        } else {
            $result = 'error';
        }
        return $result;
    }

    /**
     * Load File
     * @param string $path
     * @param string $code
     * @param string $type
     * @return mixed|string
     */

    public function loadFile($path, $code = '', $type= '')
    {
        if (!empty($code)) {
            $path = $this->directoryList->getPath($code) . "/" . $path;
        }
        if (file_exists($path)) {
            $pathInfo = pathinfo($path);
            if ($pathInfo['extension'] == 'json') {
                $myfile = fopen($path, "r");
                $data = fread($myfile, filesize($path));
                fclose($myfile);
                if (!empty($data)) {
                    $data = empty($type) ? $this->json->jsonDecode($data) : $data;
                    return $data;
                }
            }
        }
        return false;
    }

    /** 
     * @return array
     */

    public function returnPolicyValue()
    {
        $location = $this->objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('ebay_config/ebay_setting/location');
        $locationList = $this->objectManager->get('Ced\Ebay\Model\Config\Location')->toOptionArray();
        foreach ($locationList as $value) {
          if ($value['value'] == $location) {
              $locationName = $value['label'];
          }
        }
        $mediaDirectory = $this->objectManager->get('\Magento\Framework\Filesystem')->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::APP);
        $folderPath = $mediaDirectory->getAbsolutePath('code/Ced/Ebay/Setup/json/');
        $path = $folderPath .$locationName.'/returnPolicy.json';
        if (file_exists($folderPath .$locationName)) {
            $data = $this->loadFile($path, '', '');
        } else {
            $data = [];
        }
        return $data;
    }

    /**
     * @param $type   
     * @return string
     */

    public function getUrl($type, $env=null)
    {
      $env = $env==null ? $this->_environment : $env;
        if ($env == "production") {
            switch ($type) {
                case 'server':
                    return "https://api.ebay.com/ws/api.dll";
                    break;

                case 'login':
                    return "https://signin.ebay.com/ws/eBayISAPI.dll";
                    break;
                
                case 'finding':
                    return "http://svcs.ebay.com/services/search/FindingService/v1";
                    break;

                case 'shipping':
                    return "http://open.api.ebay.com/shopping";
                    break;

                case 'feedback':
                    return "http://feedback.ebay.com/ws/eBayISAPI.dll";
                    break;

                default:
                     return "https://api.ebay.com/ws/api.dll";
                    break;
            }
        } else {
            switch ($type) {
                case 'server':
                    return "https://api.sandbox.ebay.com/ws/api.dll";
                    break;

                case 'login':
                    return "https://signin.sandbox.ebay.com/ws/eBayISAPI.dll";
                    break;
                
                case 'finding':
                    return "http://svcs.sandbox.ebay.com/services/search/FindingService/v1";
                    break;

                case 'shipping':
                    return "http://open.api.sandbox.ebay.com/shopping";
                    break;

                case 'feedback':
                    return "http://feedback.sandbox.ebay.com/ws/eBayISAPI.dll";
                    break;

                default:
                    return "https://api.sandbox.ebay.com/ws/api.dll";
                    break;
            }
        }
    }

    /**
     * function to get All available sites for ebay
     * @param $siteId
     * @return array
     */

    public function getEbaysites($siteId)
    {
        $site = [];
        switch ($siteId) {
            case '0':  $site['name'] = "US"; $site['currency'] = ['USD']; $site['abbreviation'] = "US"; break;
            case '2':  $site['name'] = "Canada"; $site['currency'] = ['CAD','USD']; $site['abbreviation'] = "CA"; break;
            case '3':  $site['name'] = "UK"; $site['currency'] = ['UK']; $site['abbreviation'] = "GBP"; break;
            case '15': $site['name'] = "Australia"; $site['currency'] = ['AUD']; $site['abbreviation'] = "AU"; break;
            case '16': $site['name'] = "Austria"; $site['currency'] = ['EUR']; $site['abbreviation'] = "AT"; break;
            case '23': $site['name'] = "Belgium_French"; $site['currency'] = ['EUR']; $site['abbreviation'] = "BEFR"; break;
            case '71': $site['name'] = "France"; $site['currency'] = ['EUR']; $site['abbreviation'] = "FR"; break;
            case '77': $site['name'] = "Germany"; $site['currency'] = ['EUR']; $site['abbreviation'] = "DE"; break;
            case '101':$site['name'] = "Italy"; $site['currency'] = ['EUR']; $site['abbreviation'] = "IT"; break;
            case '123':$site['name'] = "Belgium_Dutch"; $site['currency'] = ['EUR']; $site['abbreviation'] = "BENL"; break;
            case '146':$site['name'] = "Netherlands"; $site['currency'] = ['EUR']; $site['abbreviation'] = "NL"; break;
            case '186':$site['name'] = "Spain"; $site['currency'] = ['EUR']; $site['abbreviation'] = "ES"; break;
            case '193':$site['name'] = "Switzerland"; $site['currency'] = ['CHF']; $site['abbreviation'] = "CH"; break;
            case '201':$site['name'] = "HongKong"; $site['currency'] = ['HKD']; $site['abbreviation'] = "HK"; break;
            case '203':$site['name'] = "India"; $site['currency'] = ['INR']; $site['abbreviation'] = "IN"; break;
            case '205':$site['name'] = "Ireland"; $site['currency'] = ['EUR']; $site['abbreviation'] = "IE"; break;
            case '207':$site['name'] = "Malaysia"; $site['currency'] = ['MYR']; $site['abbreviation'] = "MY"; break;
            case '210':$site['name'] = "CanadaFrench"; $site['currency'] = ['CAD', 'USD']; $site['abbreviation'] = "CAFR";break;
            case '211':$site['name'] = "Philippines"; $site['currency'] = ['PHP']; $site['abbreviation'] = "PH"; break;
            case '212':$site['name'] = "Poland"; $site['currency'] = ['PLN']; $site['abbreviation'] = "PL"; break;
            case '216':$site['name'] = "Singapore"; $site['currency'] = ['SGD']; $site['abbreviation'] = "SG"; break;
            default:   $site = []; break;
        }
        return $site;
    }

    /**
     * function to parse ebay response
     * @param $responseXml 
     * @return array
     */

    public function ParseResponse($responseXml){
        $sxe = new \SimpleXMLElement ( $responseXml );
        return $res = json_decode(json_encode($sxe));
    }
}