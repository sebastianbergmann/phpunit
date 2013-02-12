<?php
class PHPUnit_Framework_Constraint_ExceptionThrown extends PHPUnit_Framework_Constraint {

	private $_exceptionName;
	private $_exceptionThrown = null;

	public function __construct($exceptionName)
	{
		$this->_exceptionName = $exceptionName;
	}

	/**
	 * Evaluates the constraint for parameter $other. Returns TRUE if the
	 * constraint is met, FALSE otherwise.
	 *
	 * @param mixed $other Value or object to evaluate.
	 * @return bool
	 */
	public function evaluate($code)
	{
		try {
			$code();
		} catch (\Exception $e) {
			$this->_exceptionThrown = $e;
		}

		if (!$this->_exceptionThrown) {
			return false;
		}

		if (!$this->_exceptionThrown instanceof \Exception) {
			return false;
		}

		if (!$this->_exceptionThrown instanceof $this->_exceptionName) {
			return false;
		}

		return true;
	}

	public function customFailureDescription($other, $description, $not)
	{
		if(!$this->_exceptionThrown || !$this->_exceptionThrown instanceof \Exception) {
			$failureDescription = sprintf("Expected exception <%s> but no exception was thrown.", $this->_exceptionName);
		}  else {
			$failureDescription = sprintf("Expected exception: <%s>\nCaught Exception: <%s>.", $this->_exceptionName, get_class($this->_exceptionThrown));
		}
		return $failureDescription;
	}
	
	public function toString()
	{
	}

}