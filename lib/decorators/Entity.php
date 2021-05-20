<?php


namespace PhpLib\ORM\decorators;


use Attribute;
use PhpLib\decorators\Attribute as AttributeBase;
use PhpLib\ORM\Model;

#[Attribute(Attribute::TARGET_CLASS)]
class Entity extends AttributeBase {
	public function __construct(
		private string $table
	) {}

	public function process(): void {
		/**
		 * @var Model $target
		 */
		[$target] = [$this->getTarget()];
		$target::setTable($this->table);
	}
}