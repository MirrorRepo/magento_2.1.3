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

namespace Ced\Jet\Controller\Adminhtml\Jetproduct;

use Magento\Backend\App\Action;

/**
 * massDelete category Controller
 *
 * @author     CedCommerce Magento Core Team <Ced_MagentoCoreTeam@cedcommerce.com>
 */

class massDelete extends \Magento\Backend\App\Action

{
	/**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Jet::rejected_files';
    
	/**
	 * 
   * @return \Magento\Backend\Model\View\Result\Redirect
   */
	public function execute()
	{
		$data = $this->getRequest()->getParam('id');
		if (isset($data)) {
			$productDeleted = 0;

			foreach ($data as $val) {
				$model = $this->_objectManager->create('Ced\Jet\Model\Errorfile')->load($val)->delete();
				$productDeleted++;
			}
			$this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $productDeleted));
			$this->_redirect('jet/jetproduct/rejected');
		}

	}
}  
