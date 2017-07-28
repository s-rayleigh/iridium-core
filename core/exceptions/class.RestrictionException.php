<?php


namespace core\exceptions;


use core\restriction\Restriction;

class RestrictionException extends \Exception
{
	/**
	 * @var Restriction Restriction.
	 */
	private $restriction;

	public function __construct(Restriction $restriction)
	{
		$this->restriction = $restriction;
		parent::__construct($restriction->GetFailedCheckMessage());
	}
}