<?php


namespace PhpLib\ORM\decorators\types;


use Attribute;
use PhpLib\ORM\decorators\ORMFieldAttribute;
use PhpLib\ORM\Model;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Integer extends ORMFieldAttribute {
	public function __construct(
		private int $size = 11
	) {}
	public function process(): void {
		/** @var Model $target */
		[$target, $property] = [$this->getTarget(), $this->getProperty()];

		$target::setFieldKey($property, 'type', [
			'name' => 'integer',
			'size' => $this->size
		]);
	}
}