<?php
/**
 * This filter renders itself as a dropdown
 *
 * @author    Steve Guns <steve@bedezign.com>
 * @package   dataFilter
 * @category  com.bedezign.extensions
 * @copyright 2013 B&E DeZign
 */


/**
 * For correct display, any model using this filter should at the very least return a "listData" item in the array
 * when requested for the options.
 * Recognized options are:
 * - listData
 * - label
 * - selected
 * - html
 */
class DropDownDataFilter extends DataFilter
{
	public function getHtml($oWidget)
	{
		$aOptions = $this->_getOptions();

		$aList = array();
		if (isset($aOptions['listData']))
		{
			$aList = $aOptions['listData'];
			unset($aOptions['listData']);
		}

		$sId = $this->id;
		$aHtml = array();
		if (isset($aOptions['label']))
			$aHtml[$sId . '_label'] = CHtml::label($aOptions['label'], $this->htmlName);

		$sValue = !$this->isEmpty ? $this->value : (isset($aOptions['selected']) ? $aOptions['selected'] : '');

		$aHtml[$sId] = CHtml::dropDownList($this->htmlName, $sValue, $aList, $aOptions['html']);

		return $aHtml;
	}
}
