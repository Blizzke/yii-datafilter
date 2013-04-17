<?php
/**
 *
 *
 * @author    Steve Guns <steve@bedezign.com>
 * @package   9Maand.com
 * @category
 * @copyright 2013 B&E DeZign
 */

/**
 * value = 1 (the value to assign to the checkbox (can also be added to 'html'
 * label
 */
class CheckboxDataFilter extends DataFilter
{
	protected $_aOptions    = array('value' => 1);

	public function getHtml($oWidget)
	{
		$aOptions = $this->_getOptions();

		$sId = $this->id;
		$aHtml = array();
		if (isset($aOptions['label']))
			$aHtml[$sId . '_label'] = CHtml::tag('label', array('for' => $this->htmlName), $aOptions['label']);

		$bChecked = isset($aOptions['checked']) ? $aOptions['checked'] : FALSE;
		if (isset($aOptions['value']))
			$aOptions['html']['value'] = $aOptions['value'];

		if (!$bChecked && !$this->isEmpty && isset($aOptions['html']['value']))
			// If our current value = the specified value, we should be checked
			if ($this->value == $aOptions['html']['value'])
				$bChecked = TRUE;

		$aHtml[$sId] = CHtml::checkBox($this->htmlName, $bChecked,  $aOptions['html']);

		return $aHtml;
	}
}
