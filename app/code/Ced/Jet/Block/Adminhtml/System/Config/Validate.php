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
namespace Ced\Jet\Block\Adminhtml\System\Config;


class Validate extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * User Id
     *
     * @var string
     */
    public $_apiUser = 'jetconfiguration_jetsetting_user';

    /**
     * Secret Key
     *
     * @var string
     */
    public $_apiSecretkey = 'jetconfiguration_jetsetting_secret_key';

    /**
     * Fulfillment Node ID
     *
     * @var string
     */
    public $_fulfillmentNodeid = 'jetconfiguration_jetsetting_fulfillment_node_id';

    /**
     * Validate Details Button Label
     *
     * @var string
     */
    public $_buttonLabel = 'Validate Details';

    /**
     * Set API User Field Name
     *
     * @param string $apikey
     * @return \Ced\Jet\Block\Adminhtml\System\Config\Validate
     */
   
    public function setApiUser($apikey)
    {
        $this->_apiUser = $apikey;
        return $this;
    }

    /**
     * Get API User Field Name
     *
     * @return string
     */
    public function getApiUser()
    {
        return $this->_apiUser;
    }

    /**
     * Set API Secret Key Field
     *
     * @param string $secretkey
     * @return \Ced\Jet\Block\Adminhtml\System\Config\Validate
     */
    public function setApiSecretKey($secretkey)
    {
        $this->_apiSecretkey = $secretkey;
        return $this;
    }

    /**
     * Get API Secret Key Field
     *
     * @return string
     */
    public function getApiSecretKey()
    {
        return $this->_apiSecretkey;
    }

    /**
     * Set Fulfillment Node Id Field
     *
     * @param string $nodeid
     * @return \Ced\Jet\Block\Adminhtml\System\Config\Validate
     */
    public function setFulfillmentNodeId($nodeid)
    {
        $this->_fulfilmentNodeid = $nodeid;
        return $this;
    }

    /**
     * Get Fulfillment Node Id Field
     *
     * @return string
     */
    public function getFulfillmentNodeId()
    {
        return $this->_fulfillmentNodeid;
    }
    /**
     * Set Validate Details Button Label
     *
     * @param string $buttonLabel
     * @return \Ced\Jet\Block\Adminhtml\System\Config\Validate
     */
    public function setButtonLabel($buttonLabel)
    {
        $this->_buttonLabel = $buttonLabel;
        return $this;
    }

    /**
     * Set template to itself
     *
     * @return \Ced\Jet\Block\Adminhtml\System\Config\Validate
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('system/config/validate.phtml');
        }
        return $this;
    }

    /**
    
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $buttonLabel = !empty($originalData['button_label']) ? $originalData['button_label'] : $this->_vatButtonLabel;
        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('jet/system_config_validate/validate'),
            ]
        );
        return $this->_toHtml();
    }
}
