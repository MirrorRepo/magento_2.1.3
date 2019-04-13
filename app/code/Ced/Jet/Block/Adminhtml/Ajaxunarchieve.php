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

class Ajaxunarchieve extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var Magento\Framework\ObjectManagerInterface|\Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param Magento\Framework\ObjectManagerInterface $objectManager
     * @return void
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
        )
    {
        $this->objectManager = $objectManager;
        parent::__construct($context, $data);
        $this->setTemplate('Ced_Jet::ajaxunarchieve.phtml');
    }
    
    /**
     * @return void
     */
    public function totalcount()
    {
        $api_dat = $this->objectManager->create('Magento\Backend\Model\Session')->getProductUndoArcChunks();
        return count($api_dat);
    }  
}
