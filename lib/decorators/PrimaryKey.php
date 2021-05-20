<?php


namespace PhpLib\ORM\decorators;

use Attribute;
use PhpLib\ORM\Model;

#[Attribute(Attribute::TARGET_PROPERTY)]
class PrimaryKey extends ORMFieldAttribute {
	public function process(): void {
		/** @var Model $target */
		[$target, $property] = [$this->getTarget(), $this->getProperty()];

		$target::setFieldKey($property, 'primary_key', true);
	}
}