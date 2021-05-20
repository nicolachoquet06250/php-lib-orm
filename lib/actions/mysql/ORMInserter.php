<?php


namespace PhpLib\ORM\actions\mysql;


use PDO;
use PhpLib\ORM\Model;
use RuntimeException;

class ORMInserter implements \PhpLib\ORM\interfaces\ORMInserter {
	private array $fields = [];

	public function __construct(
		private PDO $pdo,
		private string $model
	) {}

	public function setAll(array $fields): ORMInserter {
		$this->fields = array_merge($this->fields, $fields);
		return $this;
	}

	public function set(string $field, mixed $value): ORMInserter {
		$this->fields[$field] = $value;
		return $this;
	}

	public function build(): bool|Model {
		/** @var Model $model */
		$model = $this->model;

		$primary_key = array_reduce(
			array_values($model::getFields()),
			static fn (?string $r, array $c) => (isset($c['primary_key']) && $c['primary_key']) ?  $c['field'] : $r,
			null
		);
		if (is_null($primary_key)) {
			throw new RuntimeException("une clée primaire doit être définie dans la table {$model::getTable()}");
		}

		$request = "INSERT INTO `{$model::getTable()}` SET ";
		$requestSets = [];

		foreach ($this->fields as $field => $value) {
			$requestSets[] = "`{$field}`=";
			if (is_string($value)) {
				$requestSets[count($requestSets) - 1] .= "'{$value}'";
			} else {
				$requestSets[count($requestSets) - 1] .= $value;
			}
		}

		$request .= implode(', ', $requestSets);

		$success = $this->pdo->exec($request) !== false;

		if ($success) {
			$last_id = $this->pdo->lastInsertId();

			return $model::find([
				$primary_key => $last_id
			])[0];
		}
		return false;
	}
}