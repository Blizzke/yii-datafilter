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
	/** @var string         CSS file to use. Will use assets/dataFilter.css if none was specified */
	public $cssFile         = NULL;
	/** @var DataFilterer   The associated DataFilterer instance */
	public $filterer        = NULL;

	/** @var string         If !NULL, the entire filter will be wrapped in a div with this as CSS class */
	public $filterClass     = 'dataFilter';

	/** @var bool           Set to TRUE to also render form tags */
	public $renderForm      = TRUE;
	/** @var array          Any html options for the form and "action" and "method" */
	public $formOptions     = array('method' => 'get');

	/** @var bool           If FALSE, the filter related labels will be rendered separately. In separator mode, both label and
	 *                      "input" get a separator, in token mode they are available as {filter_label} and {filter}. If TRUE
	 *                      they will be merged into one {filter}-token */
	public $mergeLabels     = TRUE;
	/** @var string         Default separator between an input+label */
	public $separator       = '<br />';
	/** @var string         If this has a value, "token mode" will be used. The string should contain {name} tokens that
	 *                      will be replaced by the html content. If you want separate control over the labels, disable
	 *                      "mergeLabels" and you have separate label tokens {name_label} */
	public $template        = NULL;

	/** @var bool           TRUE to render a submit button */
	public $renderSubmit    = TRUE;
	/** @var array          Any extra html options for the submit and optionally a "label" */
	public $submitOptions   = array('label' => 'Submit');

	public function init()
	{
		if (!$this->filterer)
			throw new RuntimeException('A filterer instance is required');

		if (!$this->cssFile)
			$this->cssFile = Yii::app()->assetManager->publish(dirname(__FILE__) . '/assets/dataFilter.css');

		parent::init();
	}

	public function run()
	{
		Yii::app()->clientScript->registerCssFile($this->cssFile);

		$aHtml = $this->filterer->getHtml($this);

		if ($this->renderSubmit)
		{
			$sLabel = isset($this->submitOptions['label']) ? $this->submitOptions['label'] : 'Submit';
			unset($this->submitOptions['label']);
			$aHtml['submit'] = CHtml::openTag('div', array('class' => 'actions')) .
				CHtml::submitButton($sLabel, $this->submitOptions) . CHtml::closeTag('div');
		}

		if ($this->mergeLabels)
		{
			$aHtmlNew = array();
			// If we have to merge labels and their input, search and replace
			foreach ($aHtml as $sItem => $sCode)
				if (substr($sItem, -6) != '_label')
					$aHtmlNew[$sItem] = (isset($aHtml[$sItem . '_label']) ? $aHtml[$sItem . '_label'] : '') . $sCode;
			$aHtml = $aHtmlNew;
		}

		$sHtml = $this->_renderBody($aHtml);

		if ($this->renderForm)
		{
			$sAction = isset($this->formOptions['action']) ? $this->formOptions['action'] : Yii::app()->request->url;
			$sMethod = isset($this->formOptions['method']) ? $this->formOptions['method'] : 'get';
			unset($this->formOptions['action'], $this->formOptions['method']);
			$sHtml = CHtml::beginForm($sAction, $sMethod, $this->formOptions) . $sHtml . CHtml::endForm();
		}

		if ($this->filterClass)
			$sHtml = CHtml::tag('div', array('class' => $this->filterClass), $sHtml);

		echo $sHtml;
	}

	protected function _renderBody($aHtml)
	{
		if (!empty($this->template))
		{
			// Render in "token mode"
			$aTokens = array();
			foreach ($aHtml as $sToken => $sCode)
				$aTokens['{' . $sToken . '}'] = $sCode;
			// Replace all tokens with their values
			$sHtml = strtr($this->template, $aTokens);
			// Get rid of all the other tokens
			return preg_replace('/{[\w_-]+?}/', '', $sHtml);
		}

		return implode($this->separator, $aHtml);
	}

}
