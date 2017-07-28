<?php

namespace core;
use core\exceptions\SidePanelException;
use modules\TemplateProcessor\TemplateProcessor;

/**
 * Боковая панель.
 * @package core
 */
abstract class SidePanel
{
	/**
	 * @var string Название шаблона.
	 */
	private $templateName;

	/**
	 * @var array Переменные.
	 */
	private $vars = [];

	public function __construct($templateName)
	{
		$this->templateName = $templateName;
	}

	/**
	 * Добавляет переданные переменные боковой панели.
	 * @param array $vars Массив переменных.
	 */
	public function Assign(array $vars)
	{
		$this->vars += $vars;
	}

	/**
	 * Генерирует код боковой панели.
	 * @param Page $page Объект страницы, для которой генерируется панель.
	 * @return string Код боковой панели.
	 * @throws SidePanelException
	 */
	public function Generate(Page $page)
	{
		$this->Assign(['gl_images_path' => IMAGES_PATH]);

		$this->Execute($page);

		if(!isset($this->templateName))
		{
			throw new SidePanelException("Не задано имя шаблона.");
		}

		return TemplateProcessor::ProcessTemplate($this->templateName, $this->vars);
	}

	protected abstract function Execute(Page $page);
}