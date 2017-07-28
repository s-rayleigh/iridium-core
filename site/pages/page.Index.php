<?php

namespace site\pages;

use core\Page;
use site\sidepanels\DefaultSidePanel;

class IndexPage extends Page
{
	protected function Preprocess()
	{
		$this->SetSidePanel(new DefaultSidePanel());
	}

	protected function Process()
	{
		$this->SetTemplateName('page.index.tpl');

		$this->AddCss('control-elements');
		$this->AddCss('popup');
		$this->AddCss('episodes');
		$this->AddCss('index');

		$this->AddJs('popup');
		$this->AddJs('episode_dom_object');
		$this->AddJs('data_list');
		$this->AddJs('index');
	}
}
