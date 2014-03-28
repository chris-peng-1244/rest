<?php
use Phalcon\Mvc\Model,
	Phalcon\Mvc\Model\Message,
	Phalcon\Mvc\Model\Validator\InclusionIn,
	Phalcon\Mvc\Model\Validator\Uniqueness;

class Robots extends Model
{
	public function validation()
	{
		$this->validate(new InclusionIn(
			array(
				'field' => 'type',
				'domain' => array('droid', 'mechanical', 'virtual')
			)
		));

		$this->validate(new Uniqueness(
			array(
				'field' => 'name',
				'message' => 'The robot name must be unique'
			)
		));

		if ($this->year < 0) {
			$this->appendMessage(new Message('The year cannot be less than zero'));
		}

		if ($this->validationHasFailed() == true) {
			return false;
		}
	}
}
