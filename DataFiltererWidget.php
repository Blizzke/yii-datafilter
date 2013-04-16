<?php
/**
 *
 *
 * @author    Steve Guns <steve@bedezign.com>
 * @package   dataFilter
 * @category  com.bedezign.extensions
 * @copyright 2013 B&E DeZign
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

		$sHtml = $this->filterer->getHtml($this);

		if ($this->renderSubmit)
		{
			$sLabel = isset($this->submitOptions['label']) ? $this->submitOptions['label'] : 'Submit';
			unset($this->submitOptions['label']);
			$sHtml .= CHtml::openTag('div', array('class' => 'actions')) .
				CHtml::submitButton($sLabel, $this->submitOptions) . CHtml::closeTag('div');
		}

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

}
