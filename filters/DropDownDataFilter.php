<?php
/**
 *
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
		$aOptions = CMap::mergeArray($this->_aOptions, $this->filterer->model->dataFilterGetOptions($this));
		if (!count($aOptions))
			return '';

		$aList = array();
		if (isset($aOptions['listData']))
		{
			$aList = $aOptions['listData'];
			unset($aOptions['listData']);
		}

		$sHtml = '';
		if (isset($aOptions['label']))
		{
			$sHtml = CHtml::tag('label', array('for' => $this->htmlName), $aOptions['label']);
			unset($aOptions['label']);
		}
		$sValue = !$this->isEmpty ? $this->value : (isset($aOptions['selected']) ? $aOptions['selected'] : '');

		$sHtml .= CHtml::dropDownList($this->htmlName, $sValue, $aList, isset($aOptions['html']) ? $aOptions['html'] : array());

		return $sHtml;
	}
}
