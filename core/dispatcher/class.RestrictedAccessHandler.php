<?php


namespace core\dispatcher;


use core\restriction\Restriction;
use core\restriction\RestrictionManager;

abstract class RestrictedAccessHandler extends Handler
{
	private $restrictionManager;

	private $preprocessExecuted = false;

	public function __construct()
	{
		$this->restrictionManager = new RestrictionManager;
	}

	protected function Require(Restriction $restriction)
	{
		if(!$this->preprocessExecuted)
		{
			throw new \Exception('Require method must be called before Preprocess stage.');
		}

		$this->restrictionManager->Require($restriction);
	}

	protected function Preprocess()
	{
		$this->preprocessExecuted = true;
		$this->restrictionManager->CheckRequirements();
	}
}