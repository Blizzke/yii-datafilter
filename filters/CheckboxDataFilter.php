<?php
/**
 * Checkbox filter.
 *
 * @author    Steve Guns <steve@bedezign.com>
 * @package   dataFilter
 * @category  com.bedezign.extensions
 * @copyright 2013 B&E DeZign
 */

/**
 * value = the value to assign to the checkbox (default = 1)
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
			$aHtml[$sId . '_label'] = CHtml::label($aOptions['label'], $this->htmlName);

		// Not checked by default, unless the value was set already before, in which case we mirror that
		$bChecked = FALSE;
		if (!$this->isEmpty)
			$bChecked = $this->value;

		$aHtml[$sId] = CHtml::checkBox($this->htmlName, $bChecked,  $aOptions['html']);

		return $aHtml;
	}

	public function getIsEmpty()
	{
		return !$this->_mValue;
	}

	public function setValue($mValue)
	{
		// If we receive a boolean, that is our checked status
		if (is_bool($mValue))
			$this->_mValue = $mValue;
		else
			// Otherwise the value needs to match our value
			$this->_mValue = $mValue == $this->_aOptions['value'];
	}

	public function getValue()
	{
		// If we are checked, return the regular value, otherwise return null
		if ($this->_mValue)
			return $this->_aOptions['value'];
		return NULL;
	}
}
