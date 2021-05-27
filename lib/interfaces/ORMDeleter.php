<?php


namespace PhpLib\ORM\interfaces;


use PhpLib\ORM\actions\mysql\ORMSelector;

interface ORMDeleter {
	public function from(string $table): ORMDeleter;
	public function where(string $field, mixed $value, string $operation = ORMSelector::EQUAL): ORMDeleter;
	public function and(): ORMDeleter;
	public function or(): ORMDeleter;
	public function save(): bool;
}