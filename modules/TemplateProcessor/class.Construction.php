<?php

namespace modules\TemplateProcessor;

abstract class Construction implements iConstruction
{
	protected $content = array();
	
	//Метод предобработки контента, в котором происходит начальный разбор контента конструкции. Должен быть переопределен в дочернем классе (если требуется побработка)
	public function ProcessContent($content)
	{
		Log::Warning("Метод ProcessContent абстрактного класса Construction не был переопределен в одном из дочерних классов, однако был вызван. Вы уж разберитесь с этим как-то.");
	}
	
	public function AddContent(iConstruction $contentObject)
	{
		$this->content[] = $contentObject;
	}
}