<?php


namespace PhpLib\ORM\interfaces;


use PhpLib\ORM\Model;

interface ORMUpdater {
	public function from(string $table): ORMUpdater;
	public function where(string $field, mixed $value, string $operation = \PhpLib\ORM\ORMSelector::EQUAL): ORMUpdater;
	public function and(): ORMUpdater;
	public function or(): ORMUpdater;
	public function set(string $field, mixed $value): ORMUpdater;
	public function addObject(Model $object): ORMUpdater;
	public function save(): bool|Model;
}