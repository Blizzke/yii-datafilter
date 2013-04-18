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
 * Rendering:
 * - either specify a separator (in which case a <label> <input> will be outputted)
 * - option to combine label & input into a single token (easier for template)
 * - Specify a full template with {id} that will be replaced by the actual tokens
 */
class DataFiltererWidget extends CWidget
{
	/** @var string         CSS file to include. FALSE to disable or anything "empty" to use "assets/dataFilter.css" */
	public $cssFile         = NULL;
	/** @var DataFilterer   The associated DataFilterer instance */
	public $filterer        = NULL;

	/** @var string         If !NULL, the entire filter will be wrapped in a div with this as CSS class */
	public $filterClass     = 'dataFilter';

	/** @var bool           Set to TRUE to also render form tags */
	public $renderForm      = TRUE;
	/** @var array          Any html options for the form and "action" and "method" */
	public $formOptions     = array('method' => 'post');

	/** @var bool           If FALSE, the filter related labels will be rendered separately. In separator mode, both label and
	 *                      "input" get a separator, in token mode they are available as {filter_label} and {filter}. If TRUE
	 *                      they will be merged into one {filter}-token */
	public $mergeLabels     = TRUE;
	/** @var string         Default separator between an input+label */
	public $separator       = '<br />';
	/** @var string         If this has a value, "token mode" will be used. The string should contain {filterName} tokens that
	 *                      will be replaced by their html content. If you want separate control over the labels, disable
	 *                      "mergeLabels" and you have separate label tokens {filterName_label}.
	 *                      There are some special tokens:
	 *                      {filters}   - The complete filters, generated with separators & obeying "mergeLabels"
	 *                      {formBegin} - Form open tag (if renderForm is true)
	 *                      {formEnd}   - For close tag   "
	 *                      {submit}    - Submit button (encapsulated in a div with class "actions", if renderSubmit is true)
	 *                      {form}      - {formBegin}{filters}{submit}{formEnd} */
	public $template        = NULL;
	/** @var bool           TRUE to render a submit button */
	public $renderSubmit    = TRUE;
	/** @var array          Any extra html options for the submit and optionally a "label" */
	public $submitOptions   = array('label' => 'Submit');

	public function init()
	{
		if (!$this->filterer)
			throw new RuntimeException('A filterer instance is required');

		if ($this->cssFile === NULL)
			$this->cssFile = Yii::app()->assetManager->publish(dirname(__FILE__) . '/assets/dataFilter.css');

		parent::init();
	}

	public function run()
	{
		Yii::app()->clientScript->registerCssFile($this->cssFile);

		$aHtml = $this->filterer->getHtml($this);

		if ($this->mergeLabels)
		{
			$aHtmlNew = array();
			// If we have to merge labels and their input, search and replace
			foreach ($aHtml as $sItem => $sCode)
				if (substr($sItem, -6) != '_label')
					$aHtmlNew[$sItem] = (isset($aHtml[$sItem . '_label']) ? $aHtml[$sItem . '_label'] : '') . $sCode;
			$aHtml = $aHtmlNew;
		}

		if ($this->renderSubmit)
		{
			$sLabel = isset($this->submitOptions['label']) ? $this->submitOptions['label'] : 'Submit';
			unset($this->submitOptions['label']);
			$aHtml['submit'] = CHtml::openTag('div', array('class' => 'actions')) .
				CHtml::submitButton($sLabel, $this->submitOptions) . CHtml::closeTag('div');
		}

		if ($this->renderForm)
		{
			$sAction = isset($this->formOptions['action']) ? $this->formOptions['action'] : Yii::app()->request->url;
			$sMethod = isset($this->formOptions['method']) ? $this->formOptions['method'] : 'get';
			unset($this->formOptions['action'], $this->formOptions['method']);

			$aHtml['formBegin'] = CHtml::beginForm($sAction, $sMethod, $this->formOptions);
			$aHtml['formEnd'] = CHtml::endForm();
		}

		$sHtml = $this->_renderBody($aHtml);

		if ($this->filterClass)
			$sHtml = CHtml::tag('div', array('class' => $this->filterClass), $sHtml);

		echo $sHtml;
	}

	protected function _renderBody($aHtml)
	{
		$sTemplate = empty($this->template) ? '{form}' : $this->template;
		// Replace the form token with its components (easier on the html array)
		$sTemplate = str_replace('{form}', '{formBegin}{filters}{submit}{formEnd}', $sTemplate);

		// Wrap all variables in curly brackets
		$aTokens = array();
		foreach ($aHtml as $sToken => $sCode)
			$aTokens['{' . $sToken . '}'] = $sCode;

		unset($aHtml['formBegin'], $aHtml['formEnd'], $aHtml['submit']);
		$aTokens['{filters}'] = implode($this->separator, $aHtml);

		// Replace all tokens with their values
		$sHtml = strtr($sTemplate, $aTokens);

		// Get rid of all the other tokens
		return preg_replace('/{[\w_-]+?}/', '', $sHtml);
	}

}
