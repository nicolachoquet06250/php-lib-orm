<?php


namespace PhpLib\ORM\decorators;


use Attribute;
use PhpLib\ORM\Model;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Column extends ORMFieldAttribute {
	public function __construct(
		private ?string $field = null
	) {}

	public function process(): void {
		/** @var Model $target */
		[$target, $property, $settedField] = [$this->getTarget(), $this->getProperty(), $this->field];

		$target::setFieldKey($property, 'field', (is_null($settedField) ? $property : $settedField));
	}
}