<?php


namespace PhpLib\ORM\decorators;

use Attribute;
use PhpLib\ORM\Model;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DefaultValue extends ORMFieldAttribute {
	public function __construct(
		private mixed $value
	) {}

	public function process(): void {
		/** @var Model $target */
		[$target, $property] = [$this->getTarget(), $this->getProperty()];

		$target::setFieldKey($property, 'default', $this->value);
	}
}