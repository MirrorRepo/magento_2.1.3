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

namespace Ced\Jet\Model\System\Message;

class JetNotifications implements \Magento\Framework\Notification\MessageInterface
{
	
	/**
	* @return string
	*/
	public function getIdentity() {

	        return 'identity';
	}
	
	/**
	* @return boolean
	*/
	public function isDisplayed() {

		return true;
	}

	/**
	* @return string
	*/

	public function getText() {

		return 'Jet New Messages goes here';
	}
	
	/**
	* @return string
	*/

	public function getSeverity() {
		 // Possible values: SEVERITY_CRITICAL, SEVERITY_MAJOR, SEVERITY_MINOR, SEVERITY_NOTICE
	        return self::SEVERITY_MAJOR;
	}
}
