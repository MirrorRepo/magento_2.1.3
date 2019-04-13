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

namespace Ced\Jet\Controller\Adminhtml\Jetattribute;

class Fetchattrval extends \Magento\Backend\App\Action
{
	public function execute()
	{
		$name = $this->getRequest()->getPost('mag_att_code');
		$attributeInfo = $this->_objectManager->create('\Magento\Eav\Model\ResourceModel\Entity\Attribute')->getIdByCode('customer', $name);
		$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
		$attributeOptions = $attribute ->getSource()->getAllOptions(false);
		$mjetattr_id = $this->getRequest()->getPost('jet_id');

		$units_or_options = array();
		$csv = new Varien_File_Csv();
		$file = Mage::getBaseDir("var") . DS . "jetcsv" . DS . "Jet_Taxonomy_attribute.csv";

		if (!file_exists($file)) { ?>

			<label><strong>Note:</strong></label>
			<p>Jet Extension Csv missing please check "Jet_Taxonomy_attribute.csv" exist at "var/jetcsv" location.</p>

			<?php return;
		}

		$taxonomy = $csv->getData($file);
		unset($taxonomy[0]);

		$save_attr_id = false;
		$save_attr_unitType = false;

		foreach ($taxonomy as $txt) {
			if (number_format($txt[0], 0, '', '') == $mjetattr_id) {

				$save_attr_id = number_format($txt[0], 0, '', '');
				$save_attr_unitType = $txt[6];
				break;
			}
		}

		if ($save_attr_id == false) { ?>
			<label><strong>Note:</strong></label>
			<p>Jet Atrribute id: <?php echo $mjetattr_id ?> which you trying to map is not available  in jet.com. </p>
			<?php return;
		}

		//test code end
		$csv = new Varien_File_Csv();
		$file = Mage::getBaseDir("var") . DS . "jetcsv" . DS . "Jet_Taxonomy_attribute_value.csv";


		if (!file_exists($file)) {?>
			<label><strong>Note:</strong></label>
			<p>Jet Extension Csv missing please check "Jet_Taxonomy_attribute_value.csv" exist at "var/jetcsv" location.</p>
			<?php  return;
		}
		$taxonomy = $csv->getData($file);


		unset($taxonomy[0]);
		try {
			if ($save_attr_unitType == 2) {
				foreach ($taxonomy as $txt) {
					$numberfomat_id = number_format($txt[0], 0, '', '');

					if ($mjetattr_id == $numberfomat_id) {

						$units_or_options[] = $txt[2];
					}
				}
			} else if ($save_attr_unitType == 0) {

				foreach ($taxonomy as $txt) {
					if ($mjetattr_id == number_format($txt[1], 0, '', '')) {
						$units_or_options[] = $txt[2];
					}
				}

			}
		} catch (Exception $e) { ?>

			<label><strong>Note:</strong></label>
			<p> <?php $e->getMessage() ?> </p>
			<?php  return;
		}
		//test s

		$options_array = $units_or_options;?>
		<div class="hor-scroll">

			<?php if ($save_attr_unitType == 2) { ?>

				<label><strong>Note:</strong></label>
				<p>Jet Atrribute id: <?php echo $mjetattr_id ?> which you trying to map is a <b>UNIT</b> type attribute in jet.com. You need to Add or Create new options based on these values in your Drop down options </p>
				<p>Options: <b><?php echo $row['unit'] ?></b></p>
				<label>Example: <strong>"Your value"{space}"UNIT"</strong></label>
				<p>We have taken <b>10</b> as Value for example.</p>
				<select>
					<?php foreach ($options_array as $data) { ?>
						<option value="<?php echo '10 '.$data; ?>"><?php echo '10 '.$data; ?></option>
					<?php } ?>
				</select>

				<?php
			}
			else if ($save_attr_unitType == 0) {  ?>
			<?php if (count($options_array)>0) { ?>
			<label><strong>Note:</strong></label>
			<p>Jet Atrribute id: <?php echo $mjetattr_id ?> which you trying to map is a <b>Dropdown</b> type attribute  & the options of this attribute is fixed on jet.com You need to Add or Create new options under Manage Label / Options tab based on these values </p>
			<p>Options: <b><?php foreach ($options_array as $data) { ?>
						<?php echo $data; ?>,
					<?php } ?> </b></p>
			<label>Magento Attribute Value
				<select id ="mag_attr_vals">
					<option value="">Please select Options</option>
					<?php foreach ($attributeOptions as $data) {
						if ($data['label']!='Admin' && $data['label']!='Main Website')
						{?>
							<option value="<?php echo $data['label']; ?>"><?php echo $data['label']; ?></option>
						<?php }
					} ?>
				</select> Mapped with this Jet Attribute Value
				<select id ="jet_attr_vals">
					<option value="">Please select Options</option>
					<?php foreach ($options_array as $data) { ?>
						<option value="<?php echo $data; ?>"><?php echo $data; ?></option>
					<?php } ?>
				</select>

				<button style="" onclick ="saveMapping()" id ="save_map" class="scalable " type="button" title="Save Mapping"><span><span><span>Save Mapping</span></span></span></button>

				<?php }}

				else if ($save_attr_unitType == 1) { ?>

					<label><strong>Note:</strong></label>
					<p>Jet Atrribute id: <?php echo $mjetattr_id ?> which you trying to map is a <b>Free Text </b> type attribute  & the options of this attribute you can use anything which you want.</p>
				<?php }

				else { ?>
					<label><strong>Note:</strong></label>
					<p>Jet Atrribute id: <?php echo $mjetattr_id ?> which you trying to map is not available  in jet.com. </p>
				<?php }

				?>


		</div>
		<?php
	}
}
