<?php


namespace PhpLib\ORM\decorators;

use Attribute;
use PhpLib\decorators\Attribute as AttributeBase;
use PhpLib\ORM\Model;

#[Attribute(Attribute::TARGET_CLASS)]
class DBConf extends AttributeBase {
	public function __construct(
		private string $confName = 'default'
	) {}

	public function process(): void {
		/** @var Model $model */
		$model = $this->getTarget();
		$model::setDbConf($this->confName);
	}
}