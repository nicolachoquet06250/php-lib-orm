<?php


namespace PhpLib\ORM\interfaces;


interface ORMDeleter {
	public function from(string $table): ORMDeleter;
	public function where(string $field, mixed $value, string $operation = \PhpLib\ORM\ORMSelector::EQUAL): ORMDeleter;
	public function and(): ORMDeleter;
	public function or(): ORMDeleter;
	public function save(): bool;
}