<?php


namespace PhpLib\ORM\interfaces;


use PhpLib\ORM\Model;

interface ORMSelector {
	public function from(string $table): ORMSelector;
	public function where(array $where): ORMSelector;
	public function and(): ORMSelector;
	public function or(): ORMSelector;
	public function limit(int $limit): ORMSelector;
	public function order(string $order_by): ORMSelector;
	public function asc(): ORMSelector;
	public function desc(): ORMSelector;
	public function count(): int;
	public function get(): Model|array;
}