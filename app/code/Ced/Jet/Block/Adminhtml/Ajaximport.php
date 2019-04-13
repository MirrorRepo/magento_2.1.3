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

namespace Ced\Jet\Block\Adminhtml;

class Ajaximport extends \Magento\Backend\Block\Widget\Container
{
    public $objectManager;

    /**
     * Ajaximport constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
        )
    {
        $this->objectManager = $objectManager;    
        parent::__construct($context, $data);
        $this->setTemplate('Ced_Jet::ajaximport.phtml');
    }

    /**
     * @return int|void
     */
    
    public function totalcount()
    {
        $api_dat = $this->objectManager->create('Magento\Backend\Model\Session')->getProductChunks();
        return count($api_dat);
    } 
}
