<?php


namespace core\restriction;


abstract class Restriction
{
	public function __construct() { }

	public abstract function Check() : bool;

	public abstract function GetFailedCheckMessage() : string;

	public abstract function GetCode() : string;

	public function FailedCheckAction() { }

	public function SuccessCheckAction() { }
}