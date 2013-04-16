<?php
/**
 * Base class for a single data filter
 *
 * @author    Steve Guns <steve@bedezign.com>
 * @package   dataFilter
 * @category  com.bedezign.extensions
 * @copyright 2013 B&E DeZign
 */

abstract class DataFilter extends CComponent
{
	/** @var DataFilterer */
	protected $_oFilterer   = NULL;
	/** @var string         ID of the filter for usage in the form */
	protected $_sFilterId   = '';
	/** @var bool           A filter can be disabled, in which case it will not be asked to provide conditions for the search */
	protected $_bEnabled    = TRUE;
	/** @var mixed          Last assigned value for this filter */
	protected $_mValue      = NULL;
	/** @var array          Any basic options (will be included with the ones from the model). Use "html" for any html options you wish to pass on */
	protected $_aOptions    = array();

	public function __construct($sFilterId = NULL, $aOptions = array())
	{
		$this->_sFilterId = $sFilterId;
		$this->_aOptions  = $aOptions;
	}

	public function setFilterer($oFilterer)   { $this->_oFilterer = $oFilterer; }
	public function getFilterer()             { return $this->_oFilterer; }

	public function setId($sId)               { $this->_sFilterId = $sId; }
	public function getId()                   { return !empty($this->_sFilterId) ? $this->_sFilterId : 'dropdown'; }

	public function setEnabled($bEnabled)     { $this->_bEnabled = $bEnabled; }
	public function getEnabled()              { return $this->_bEnabled; }

	public function setAttribute($sAttribute) { if (!$this->_sAttribute) $this->_sAttribute = $sAttribute;}
	public function getAttribute()            { return $this->_sAttribute; }

	public function setValue($mValue)         { $this->_mValue = $mValue; }
	public function getValue()                { return $this->_mValue; }

	public function setOptions($aOptions)     { $this->_aOptions = $aOptions; }
	public function getOptions()              { return $this->_aOptions; }

	public function getHtmlName()             { return $this->filterer->id . '[' . $this->id . ']'; }
	public function getActiveId()             { return $this->filterer->id . '_' . $this->id; }

	/**
	 * Returns the HTML code for the filter
	 */
	public function getHtml($oWidget)
	{
		return '';
	}

	/**
	 * Called to apply the conditions on the given criteria object so that the data can be searched.
	 * Note: Filters marked as disabled will not receive this call.
	 * @param $oCriteria
	 */
	public function apply($oCriteria)
	{
		if ($this->enabled)
			$this->filterer->model->dataFilterApply($this, $oCriteria);
	}

	/**
	 * Returns whether or not this filter has a value
	 * @return bool
	 */
	public function getIsEmpty()
	{
		return $this->_mValue === NULL;
	}

	/**
	 * Specifies all data that came in from the user, the filter should pick everything that applies and store it.
	 * If no applicable was found (nothing posted/submitted), the filter should reset its value to NULL.
	 * @param $aData
	 */
	public function setRequest($aData)
	{
		$this->value = isset($aData[$this->id]) ? $aData[$this->id] : NULL;
	}

	/**
	 * Returns whatever data is needed to save this filter.
	 */
	public function save()
	{
		return array
		(
			'id'      => $this->id,
			'enabled' => $this->enabled,
			'value'   => $this->value,
			'options' => $this->options,
		);
	}

	/**
	 * Sets properties for the current filter based on the given data. This is called when the filter is restored
	 * from the session and can contain any configuration setting
	 * Note: If you save using correct property names there's no need to override this function
	 * @param array $data
	 * @return bool
	 */
	public function restore($data)
	{
		if (!is_array($data))
			return FALSE;

		foreach ($data as $sProperty => $value)
			$this->$sProperty = $value;

		return TRUE;
	}
}
