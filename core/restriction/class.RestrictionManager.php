<?php

namespace core\restriction;

use core\exceptions\RestrictionException;

class RestrictionManager
{
	/**
	 * @var Restriction[] List of the restrictions.
	 */
	private $restrictions = [];

	public function __construct() { }

	public function Require(Restriction $restriction)
	{
		if(empty($restriction))
		{
			throw new \InvalidArgumentException("Argument 'restriction' should not be null.");
		}

		$this->restrictions[] = $restriction;
	}

	public function ClearRequirements()
	{
		$this->restrictions = [];
	}

	public function CheckRequirements()
	{
		foreach($this->restrictions as $restr)
		{
			if($restr->Check())
			{
				$restr->SuccessCheckAction();
			}
			else
			{
				$restr->FailedCheckAction();
				throw new RestrictionException($restr);
			}
		}
	}
}