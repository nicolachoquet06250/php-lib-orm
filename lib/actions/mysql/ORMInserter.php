<?php


namespace PhpLib\ORM\actions\mysql;


use PDO;
use PhpLib\ORM\Model;
use RuntimeException;

class ORMInserter implements \PhpLib\ORM\interfaces\ORMInserter {
	private array $fields = [];
	private ?Model $modelObj = null;

	public function __construct(
		private PDO $pdo,
		private string $model
	) {}

	public function setModel(Model $model): ORMInserter {
		$this->modelObj = $model;
		return $this;
	}

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

		if (!is_null($this->modelObj)) {
			foreach ($this->modelObj as $k => $v) {
				$this->set($k, $v);
			}
		}

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

			$tmpModelObj = $model::find([
				$primary_key => $last_id
			])[0];

			if (!is_null($tmpModelObj) && !is_null($this->modelObj)) {
				foreach ($tmpModelObj as $k => $v) {
					$this->modelObj->$k = $v;
				}
				return $this->modelObj;
			}
			return $tmpModelObj;
		}
		return false;
	}
}
