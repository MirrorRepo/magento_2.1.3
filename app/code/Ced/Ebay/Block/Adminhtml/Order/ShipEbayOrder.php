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
namespace Ced\Ebay\Block\Adminhtml\Order;


class ShipEbayOrder extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Order Ship By Jet tab
     */

    /**
     * Get ObjectManager instance
     *
     * @return \Magento\Framework\App\ObjectManager
     */
    public function getObjectManager()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager;
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * Retrieve Helper instance
     *
     * @return \Magento\Sales\Model\Order
     */

    public function getHelper($helper)
    {

        $helper = $this->getObjectManager()->get("Ced\Ebay\Helper".$helper);
        return $helper;
    }

    /**
     * Retrieve JetOrders model instance
     *
     * @return \Ced\Jet\Model\JetOrders
     */
    public function getModel()
    {
        $Incrementid = $this->getOrder()->getIncrementId();
        $resultdata = $this->getObjectManager()->get('Ced\Ebay\Model\Orders')->getCollection()->addFieldToFilter('magento_order_id', $Incrementid)->getFirstItem();

        return $resultdata;
    }


    /**
     * Retrieve JetOrders model instance
     *
     * @return \Ced\Jet\Model\JetOrders
     */

    public function setOrderResult($resultdata)
    {
        return $this->_coreRegistry->register('current_jet_order',$resultdata);
    }


    /**
     * ######################## TAB settings #################################
     */

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Ship eBay Order');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Ship eBay Order');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        $data = $this->getModel();
        if (!empty($data->getData())) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        $data = $this->getModel();
        if (!empty($data->getData())) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @return string
     */

    public function getEbayShipCarrier()
    {
        $s_carriers = $this->getObjectManager()->get('Ced\Ebay\Model\Config\ShippingService')->toOptionArray();
        $selectData =  '<select id="carrier" class="admin__control-select" name="carrier" >';
        foreach($s_carriers as $s_carrier) {
            $selectData = $selectData.'<option value ="'.$s_carrier['value'].'">'.$s_carrier['label'].'</option>'; 
        }
        $selectData = $selectData.'</select>';

        

        return $selectData;
    }

    /**
     * @return string
     */

    public function getJetShipMethod()
    {
        $methods = array("A1", "ABF", "ADuiePyle", "APEX", "Averitt", "A&MTrucking", "CEVA", "DHLEasyReturnPlus", "DHLExpress12", "DHLExpress9", "DHLExpressEnvelope", "DHLExpressWorldwide", "DHLeCommerce", "DHLSmartmailFlatsGround", "DHLSmartmailParcelGround", "DHLSmartmailParcelPlusGround", "DynamexSameDay", "EasternConnectionExpeditedMail", "EasternConnectionGround", "EasternConnectionPriority", "EasternConnectionSameDay", "EnsendaHome", "EnsendaNextDay", "EnsendaSameDay", "EnsendaTwoMan", "Estes", "Fedex2Day", "FedExExpeditedFreight", "FedexExpressSaver", "FedexFirstOvernight", "FedexFreight", "FedExGround", "FedExHome", "FedexPriorityOvernight", "FedexSameDay", "FedExSmartPost", "FedExSmartPostReturns", "FedexStandardOvernight", "GSOGround", "LandAirExpress", "LasershipSameDay", "LaserShipNextDay", "LaserShipGlobalPriority", "Prestige", "LSO2ndDay", "LSOEarlyNexyDay", "LSOEconomyNextDay", "LSOGround", "LSOPriorityNextDay", "LSOSaturday", "Meyer", "NewPenn", "OnTracDirectPost", "OnTracGround", "OnTracPalletizedFreight", "OnTracSaturdayDelivery", "OnTracSunrise", "OnTracSunriseGold", "Other", "Pilot", "RBF", "Reddaway", "RLCarriers", "RoadRunner", "SAIAFreight", "SoutheasternFreight", "SpeeDee", "UDSNextDay", "UDSSameDay", "UES", "UPSSurepost", "UPS2ndDayAir", "UPS2ndDayAirAM", "UPS2ndDayAirFreight", "UPS2ndDayAirFreightNGS", "UPS3DayFreight", "UPS3DayFreightNGS", "UPS3DaySelect", "UPSExpressCritical", "UPSFreight", "UPSGround", "UPSGroundFreightPricing", "UPSHundredweightService", "UPSMailInnovations", "UPSNextDayAir", "UPSNextDayAirEarly", "UPSNextDayAirFreight", "UPSNextDayAirFreightNGS", "UPSNextDayAirSaver", "UPSStandard", "USPSFirstClassMail", "USPSMediaMail", "USPSPriorityMail", "USPSPriorityMailExpress", "USPSRetailGround", "YRC", "DHLEasyReturnLight", "DHLEasyReturnGround", "DHLSmartmailFlatsExpedited", "DHLSmartmailParcelExpedited", "DHLSmartmailParcelPlusExpedited", "GSOPriority", "GSOFreight");        
        $selectData =  '<select id="method" style="width: 192px !important" class="admin__control-select" name="method" >';
        foreach($methods as $method){
                    $selectData = $selectData.'<option value ="'.$method.'">'.$method.'</option>'; 
                }
        $selectData = $selectData.'</select>';
        return $selectData;
    }
}
