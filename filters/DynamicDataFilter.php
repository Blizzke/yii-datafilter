<?php
/**
 * Acts as a collection bag for filters that can be dynamically added. It renders as a dropdown
 *
 * @author    Steve Guns <steve@bedezign.com>
 * @package   dataFilter
 * @category  com.bedezign.extensions
 * @copyright 2013 B&E DeZign
 */


/**
 * @TODO add support for actually activating the filters
 * label
 *
 *
 */
class DynamicDataFilter extends DropDownDataFilter
{
	protected $_aFilters = array();
	protected $_aOptions = array('label' => 'Add filter');

	public function __construct($sFilterId = NULL, $aOptions = array())
	{
		parent::__construct($sFilterId, $aOptions);
	}

	/**
	 * Register a new filter for use
	 * @param string        $sTitle   Title of the filter (to display when it needs to be added)
	 * @param DataFilter    $oFilter  The actual filter instance
	 * @param int           $nLimit   How many times this filter can be added before it disappears from the list (0 = unlimited)
	 */
	public function registerFilter($sTitle, $oFilter, $nLimit = 1)
	{
		$oFilter->filterer = $this->filterer;
		// title, visible in dropdown list, limit, filter
		$this->_aFilters[$oFilter->id] = array($sTitle, TRUE, $nLimit, $oFilter->save());
	}

	/**
	 * Add a new filter to the filterer
	 * @param $sIdentifier
	 * @return DataFilter|FALSE
	 */
	public function activateFilter($sIdentifier)
	{
		if (!isset($this->_aFilters[$sIdentifier]))
			return FALSE;

		list($nFound, $nMaxId) = $this->_getFilterStats($sIdentifier);

		if (!$this->_aFilters[$sIdentifier][1])
			// Filter is disabled (hidden), cannot be added anymore
			return FALSE;

		if ($this->_aFilters[$sIdentifier][2] && $nFound >= $this->_aFilters[$sIdentifier][2])
		{
			// Imposed limit and over it
			$this->_aFilters[$sIdentifier][1] = FALSE;
			return FALSE;
		}

		// Still here, think we can add the filter
		$oFilter = DataFilter::restore($this->filterer, $this->_aFilters[$sIdentifier][3]);
		$oFilter->id = $sIdentifier . ($nMaxId ? '_' . ($nMaxId + 1) : '');
		$nFound ++;
		if ($this->_aFilters[$sIdentifier][2] && $nFound >= $this->_aFilters[$sIdentifier][2])
			$this->_aFilters[$sIdentifier][1] = FALSE;

		return $this->filterer->addFilter($oFilter);
	}

	public function setFilters($aFilters) { $this->_aFilters = $aFilters; }

	protected function _getOptions()
	{
		$aOptions = parent::_getOptions();

		$aList = array();
		foreach ($this->_aFilters as $sIdentifier => $aFilter)
			if ($aFilter[1])
				$aList[$sIdentifier] = $aFilter[0];

		$aOptions['listData'] = $aList;
		$aOptions['html']['prompt'] = '';
		return $aOptions;
	}


	public function save()
	{
		$aSave = parent::save();
		$aSave[1]['filters'] = $this->_aFilters;
		return $aSave;
	}

	protected function _getFilterStats($sIdentifier)
	{
		$nFound = 0;
		$nMaxId = 0;
		foreach ($this->filterer->filters as $sFilter => $oFilter)
			if (preg_match('/' . $sIdentifier . '(?:_(\d+)){0,1}/', $sFilter, $aMatches))
			{
				$nFound ++;
				$nMaxId = max($nMaxId, isset($aMatches[1]) ? intval($aMatches[1]) : 1);
			}

		return array($nFound, $nMaxId);
	}
}
