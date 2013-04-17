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
	/** @var array          Any basic options (will be merged with the ones provided by the model). Use "html" for any htmlOptions you want to pass on */
	protected $_aOptions    = array();

	protected static $_gnCounter = 0;

	public function __construct($sFilterId = NULL, $aOptions = array())
	{
		$this->_sFilterId = $sFilterId;
		$this->_aOptions  = $aOptions;
	}

	public function setFilterer($oFilterer)
	{
		$this->_oFilterer = $oFilterer;
	}

	public function getFilterer()             { return $this->_oFilterer; }

	public function setId($sId)               { $this->_sFilterId = $sId; }
	public function getId()                   { return !empty($this->_sFilterId) ? $this->_sFilterId : 'filter' . ++ self::$_gnCounter; }

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
	 * Returns the HTML code for the filter as separate elements with identifiers
	 * Eg: filter "filter" returns array('filter_label' => 'html', 'filter' => 'html');
	 * @param CWidget $oWidget
	 * @return string[]
	 */
	public function getHtml($oWidget)
	{
		return array();
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
	 * @return array
	 */
	public function save()
	{
		return array
		(
			get_class($this),
			array
			(
				'id'      => $this->id,
				'enabled' => $this->enabled,
				'value'   => $this->value,
				'options' => $this->options,
			)
		);
	}

	/**
	 * Create a filter instance based on the given data
	 * @param $oFilterer
	 * @param $aData
	 * @return DataFilter|NULL
	 */
	public static function restore($oFilterer, $aData)
	{
		if (!is_array($aData))
			return NULL;

		if (!class_exists($aData[0]))
			return NULL;

		$oInstance = new $aData[0];
		$oInstance->filterer = $oFilterer;

		foreach ($aData[1] as $sProperty => $value)
			$oInstance->$sProperty = $value;

		return $oInstance;
	}

	protected function _getOptions()
	{
		$aModelOptions = $this->filterer->model->dataFilterGetOptions($this);
		if (!is_array($aModelOptions))
			$aModelOptions = array($aModelOptions);

		$aOptions = CMap::mergeArray($this->_aOptions, $aModelOptions);
		if (!isset($aOptions['html']))
			$aOptions['html'] = array();

		return $aOptions;
	}
}
