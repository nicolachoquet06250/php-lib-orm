<?php


namespace PhpLib\ORM\decorators\types;

use Attribute;
use PhpLib\ORM\decorators\ORMFieldAttribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Model extends ORMFieldAttribute {
	public function __construct(
		private string $model,
		private string $joinedField = 'id'
	) {}

	public function process(): void {
		/** @var \PhpLib\ORM\Model $target */
		[$target, $property] = [$this->getTarget(), $this->getProperty()];

		$target::setFieldKey($property, 'joint', [
			'model' => $this->model,
			'field' => $this->joinedField
		]);
	}
}