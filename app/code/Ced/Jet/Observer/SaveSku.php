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
 * @category    Ced
 * @package     Ced_Jet
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Jet\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\App\RequestInterface;

class SaveSku implements ObserverInterface {
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $_objectManager;

    /**
     * SaveSku constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager, RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->_objectManager = $objectManager;
    }

    /**
     * Producr event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Framework\Event\Observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $id = $this->request->getParam('id');
        $getSku = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($id)->getSku();
        $registeryObj =  $this->_objectManager->get('Magento\Framework\Registry');
        if ($registeryObj->registry('prev_sku')) {
            $registeryObj->unregister('prev_sku');
        }
        $registeryObj->register('prev_sku',$getSku);
        return $observer;
    }
}
