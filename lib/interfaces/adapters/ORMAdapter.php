<?php


namespace PhpLib\ORM\interfaces\adapters;

use PhpLib\ORM\interfaces\{ORMInserter, ORMSelector, ORMDeleter, ORMUpdater};

interface ORMAdapter {
	public function getConnector(): mixed;

	public function insert(): ORMInserter;

	public function select(string|array $fields = '*'): ORMSelector;

	public function delete(): ORMDeleter;

	public function update(): ORMUpdater;

	public function createTable(): bool;
}