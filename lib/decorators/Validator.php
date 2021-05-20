<?php


namespace PhpLib\ORM\decorators;

use Attribute;
use PhpLib\ORM\Model;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Validator extends ORMFieldAttribute {
	public function __construct(
		private string $validationRegex
	) {}

	public function process(): void {
		/** @var Model $target */
		[$target, $property] = [$this->getTarget(), $this->getProperty()];

		$target::setFieldKey($property, 'validator', $this->validationRegex);
	}
}