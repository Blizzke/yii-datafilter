<?php
/**
 * Basic component for the data-filter extension
 *
 * @author    Steve Guns <steve@bedezign.com>
 * @package   dataFilter
 * @category  com.bedezign.extensions
 * @copyright 2013 B&E DeZign
 */

/**
 * The DataFilterer class is the hub of the filter support. This class connects the actual filters and the widget.
 *
 * It also allows you to serialize data in between calls (eg if you want to save a filter selection to apply to multiple pages).
 * Keep in mind that because the extension wants to be as expandable as possible, it also saves the actual filters that are added.
 * This way you can dynamically add filters without having to create the entire structure on each page call.
 * It does result in a bit more work as you have to check (when creating the Filterer instance) if it has filters already.
 */
class DataFilterer extends CComponent
{
	protected $_oModel            = NULL;
	protected $_aFilters          = array();
	protected $_sSessionVariable  = NULL;
	protected $_sIdentifier       = NULL;

	/**
	 * Model that can be used by the filters as a base to generate their HTML from, or interpret the incoming data.
	 * @param CModel $oModel
	 * @throws RuntimeException   If the specified model does not implement IFilterable
	 */
	public function setModel($oModel)
	{
		if (!$oModel instanceof IFilterable)
			throw new RuntimeException('The specified model should implement IFilterable');

		$this->_oModel = $oModel;
		if ($this->_sIdentifier === NULL)
			$this->_sIdentifier = get_class($oModel) . 'Filter';
	}
	public function getModel()          { return $this->_oModel; }

	/**
	 * The base name for the form. This is usually the model class name + Filter (set by assigning a model).
	 * @param string $sIdentifier
	 */
	public function setId($sIdentifier) { $this->_sIdentifier = $sIdentifier; }
	public function getId()	            { return $this->_sIdentifier; }

	/**
	 * If you set this to a non-NULL value the filterer will save itself in the session between calls.
	 * This includes the filters and their values
	 * @param string $sSessionVariable
	 */
	public function setSessionVariable($sSessionVariable)
	{
		$this->_sSessionVariable = $sSessionVariable;
		// Make sure we get the opportunity to save ourselves
		Yii::app()->attachEventHandler('onEndRequest', array($this, 'saveToSession'));
	}

	public function getSessionVariable()
	{
		return $this->_sSessionVariable;
	}

	/**
	 * @param DataFilter          $oFilter
	 * @param bool                $bReplace
	 * @return DataFilter|NULL    The filter instance (if it was added)
	 */
	public function addFilter($oFilter, $bReplace = FALSE)
	{
		$oFilter->filterer = $this;
		if (isset($this->_aFilters[$oFilter->activeId]) && !$bReplace)
			return NULL;

		$this->_aFilters[$oFilter->activeId] = $oFilter;
		$this->onFilterAdd(new CEvent($this, array('filter' => $oFilter)));
		return $oFilter;
	}

	public function removeFilter($sIdentifier)
	{
		if (!isset($this->_aFilters[$sIdentifier]))
			return;

		$this->onFilterRemoved(new CEvent($this, array('filter' => $this->_aFilters[$sIdentifier])));
		unset($this->_aFilters[$sIdentifier]);
	}

	public function getFilters()        { return $this->_aFilters; }
	public function getFilterCount()    { return count($this->_aFilters); }

	/**
	 * Collects the HTML code for all the filters and returns it.
	 * The return format is an array of separate html blocks with the filter-id as key (and sometimes an extra label)
	 * @param CWidget $oWidget
	 * @param bool    $bRenderDisabled
	 * @return string[]
	 */
	public function getHtml($oWidget, $bRenderDisabled = FALSE)
	{
		$aHtml = array();
		foreach ($this->_aFilters as $oFilter)
			if ($oFilter->enabled || $bRenderDisabled)
				$aHtml = array_merge($aHtml, $oFilter->getHtml($oWidget));

		return $aHtml;
	}

	/**
	 * Applies the filters to the given Criteria object.
	 * @param CDbCriteria $oCriteria
	 */
	public function apply($oCriteria)
	{
		foreach ($this->_aFilters as $oFilter)
			if ($oFilter->enabled)
				$oFilter->apply($oCriteria);
	}

	/**
	 * If anything was submitted for the filterer, this function will return it
	 * @return array
	 */
	public function getSubmittedData()
	{
		return Yii::app()->request->getParam($this->id);
	}

	/**
	 * Specifies the user data submitted for the filters (or custom data to initialize)
	 * @param array $aData
	 */
	public function setRequest($aData)
	{
		foreach ($this->_aFilters as $oFilter)
			$oFilter->setRequest($aData);
	}

	/**
	 * Adds all the filter data to the session (if a sessionVariable was specified).
	 * Note that the complete filters are saved. During restore all these filters will be pre-created. Make sure to
	 * not add them again (duplicates).
	 * @return bool
	 */
	public function saveToSession()
	{
		if (!$this->sessionVariable)
			return FALSE;

		$aData = array();
		foreach ($this->_aFilters as $sFilterId => $oFilter)
			$aData[$sFilterId] = $oFilter->save();

		Yii::app()->user->setState($this->sessionVariable, $aData);
		return TRUE;
	}

	/**
	 * @return bool
	 */
	public function loadFromSession()
	{
		if (!$this->sessionVariable)
			return FALSE;

		$aData = Yii::app()->user->getState($this->sessionVariable);
		if (!$aData)
			return FALSE;

		foreach ($aData as $sFilterId => $aFilter)
			if ($oFilter = DataFilter::restore($this, $aFilter))
				$this->_aFilters[$sFilterId] = $oFilter;

		return TRUE;
	}

	public function clearFromSession()
	{
		if ($this->sessionVariable)
			Yii::app()->user->setState($this->sessionVariable, NULL);
	}

	public static function cleanup($sSessionVariable)
	{
		Yii::app()->user->setState($sSessionVariable, NULL);
	}

	/**
	 * Create a new filterer for the given model and loads the data/configuration from the session if needed
	 * @param CModel  $oModel
	 * @param String $sSessionVariable
	 * @return self
	 */
	public static function create($oModel, $sSessionVariable = NULL)
	{
		$oDataFilter = new self;
		$oDataFilter->model = is_string($oModel) ? new $oModel : $oModel;
		if ($sSessionVariable !== NULL)
		{
			$oDataFilter->sessionVariable = $sSessionVariable;
			$oDataFilter->loadFromSession();
		}

		return $oDataFilter;
	}

	public function onFilterAdd($oEvent)
	{
		$this->raiseEvent('onFilterAdd', $oEvent);
	}

	public function onFilterRemoved($oEvent)
	{
		$this->raiseEvent('onFilterRemoved', $oEvent);
	}
}
