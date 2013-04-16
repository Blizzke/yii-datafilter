<?php
/**
 *
 *
 * @author    Steve Guns <steve@bedezign.com>
 * @package   dataFilter
 * @category  com.bedezign.extensions
 * @copyright 2013 B&E DeZign
 */

interface IFilterable
{
	/**
	 * Depending on the filter this may vary. It should return the required options/configuration to correctly render the filter.
	 * Please refer to the actual filter for concrete details
	 * @param DataFilter $oFilter
	 * @return array
	 */
	public function dataFilterGetOptions($oFilter);

	/**
	 * Called on the model to apply whatever values were given to the filter to the criteria
	 * @param DataFilter    $oFilter
	 * @param CDbCriteria   $oCriteria
	 * @return bool
	 */
	public function dataFilterApply($oFilter, $oCriteria);
}
