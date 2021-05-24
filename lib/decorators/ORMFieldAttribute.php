<?php


namespace PhpLib\ORM\decorators;


use PhpLib\decorators\Attribute as AttributeBase;

abstract class ORMFieldAttribute extends AttributeBase {
	protected string $property;

	public function setProperty(string $property): ORMFieldAttribute {
		$this->property = $property;
		return $this;
	}

	public function getProperty(): string {
		return $this->property;
	}
}