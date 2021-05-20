<?php


namespace PhpLib\ORM\interfaces;


use PhpLib\ORM\Model;

interface ORMInserter {
	public function setAll(array $fields): ORMInserter;

	public function set(string $field, mixed $value): ORMInserter;

	public function build(): bool|Model;
}