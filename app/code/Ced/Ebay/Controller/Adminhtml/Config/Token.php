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
namespace Ced\Ebay\Controller\Adminhtml\Config;

use Magento\Framework\Controller\Result\JsonFactory;

class Token extends \Magento\Backend\App\Action
{
    /**
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Cache\TypeListInterface $cache
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cache = $cache;
    }

    /**
     * Check whether vat is valid
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $helper = $this->_objectManager->get('Ced\Ebay\Helper\Data');
        $data = $this->getRequest()->getParams();
        if (isset($data['status'])) {
            if ($data['status'] == "success") {
                $helper->fetchToken();
                $this->_redirect('adminhtml/system_config/edit/section/ebay_config');
            }
        } else {
            if (isset($data['location']) && isset($data['env'])) {                
                $response = $helper->getSessionId($data['location'], $data['env']);
                if ($response['msg'] == "success") {
                    $cacheType = ['translate','config','block_html','config_integration','reflection','db_ddl','layout','eav','config_integration_api','full_page','collections','config_webservice'];
                    foreach ($cacheType as $cache) {
                        $this->cache->cleanType($cache);
                    }
                }
            }
            
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($response);
        }

    }
}
