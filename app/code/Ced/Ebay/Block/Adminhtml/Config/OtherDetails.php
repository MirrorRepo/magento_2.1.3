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
namespace Ced\Ebay\Block\Adminhtml\Config;

class OtherDetails extends \Magento\Config\Block\System\Config\Form\Field
{
    
    public $_location = 'ebay_config_ebay_setting_location';

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
     * Set Location
     *
     * @param string $apikey
     * @return \Ced\Ebay\Block\Adminhtml\Config\Details
     */
   
    public function setLocation($location)
    {
        $this->_location = $location;
        return $this;
    }

    /**
     * Get API User Field Name
     *
     * @return string
     */

    public function getLocation()
    {
        return $this->_location;
    }

    /**
     * Set template to itself
     * @return \Ced\Ebay\Block\Adminhtml\Token
     */

    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('config/otherdetails.phtml');
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
        $buttonLabel = $originalData['button_label'];
        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('ebay/config/otherDetails'),
            ]
        );
        return $this->_toHtml();
    }
}
