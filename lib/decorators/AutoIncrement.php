<?php


namespace PhpLib\ORM\decorators;


use Attribute;
use PhpLib\ORM\Model;

#[Attribute(Attribute::TARGET_PROPERTY)]
class AutoIncrement extends ORMFieldAttribute {
	public function process(): void {
		/** @var Model $target */
		[$target, $property] = [$this->getTarget(), $this->getProperty()];

		$target::setFieldKey($property, 'autoincrement', true);
	}
}