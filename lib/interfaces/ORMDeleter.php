<?php


namespace PhpLib\ORM\interfaces;

use PhpLib\ORM\Model;

interface ORMDeleter {
	public function setModel(Model $model): ORMDeleter;
	public function from(string $table): ORMDeleter;
	public function where(string $field, mixed $value, string $operation = \PhpLib\ORM\ORMSelector::EQUAL): ORMDeleter;
	public function and(): ORMDeleter;
	public function or(): ORMDeleter;
	public function save(): bool;
}
