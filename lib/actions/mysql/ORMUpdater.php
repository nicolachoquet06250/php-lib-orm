<?php


namespace PhpLib\ORM\actions\mysql;


use PDO;
use PhpLib\ORM\Model;

class ORMUpdater implements \PhpLib\ORM\interfaces\ORMUpdater {
	private string $table;
	private array $where = [];
	private array $updatedFields = [];
	private ?Model $modelObj = null;

	public function __construct(
		private PDO $pdo,
		private string $model
	) {
		$this->from($model::getTable());
	}

	public function from( string $table ): \PhpLib\ORM\interfaces\ORMUpdater {
		$this->table = $table;
		return $this;
	}

	public function where( string $field, mixed $value, string $operation = ORMSelector::EQUAL ): \PhpLib\ORM\interfaces\ORMUpdater {
		$this->where[] = [
			'field' => $field,
			'value' => $value,
			'operation' => $operation
		];
		return $this;
	}

	public function and(): \PhpLib\ORM\interfaces\ORMUpdater {
		$this->where[count($this->where) - 1]['binary_operation'] = 'AND';
		return $this;
	}

	public function or(): \PhpLib\ORM\interfaces\ORMUpdater {
		$this->where[count($this->where) - 1]['binary_operation'] = 'OR';
		return $this;
	}

	public function set( string $field, mixed $value ): \PhpLib\ORM\interfaces\ORMUpdater {
		$this->updatedFields[$field] = $value;
		return $this;
	}

	public function save(): bool|Model {
		/** @var Model $model */
		$model = $this->model;
		$request = "UPDATE `{$this->table}`";
		if (!empty($this->updatedFields)) {
			$request .= " SET ";
		}

		$updatedFields = [];
		foreach ($this->updatedFields as $field => $value) {
			$updatedFields[] = "`{$this->table}`.{$field} = ";
			if (is_string($value)) {
				$updatedFields[count($updatedFields) - 1] .= "'{$value}'";
			} else {
				$updatedFields[count($updatedFields) - 1] .= $value;
			}
		}

		$request .= implode(', ', $updatedFields);

		if (!is_null($this->modelObj)) {
			$primary_key = array_reduce(
				array_values($model::getFields()),
				static fn (?string $r, array $c) => (isset($c['primary_key']) && $c['primary_key']) ?  $c['field'] : $r,
				null
			);

			$this->where = [
				[
					'field' => $primary_key,
					'value' => $this->modelObj->$primary_key,
					'operation' => ORMSelector::EQUAL
				]
			];
		}
		if (!empty($this->where)) {
			$request .= " WHERE";
			foreach ( $this->where as $where ) {
				if ( ! empty( $where ) ) {
					$request .= " `{$this->table}`.{$where['field']} ";

					if ( empty( $where['operation'] ) ) {
						$where['operation'] = ORMSelector::EQUAL;
					}

					if ( $where['operation'] === ORMSelector::START_BY ) {
						$request .= "LIKE {$where['value']}%";
					} elseif ( $where['operation'] === ORMSelector::END_BY ) {
						$request .= "LIKE %{$where['value']}";
					} elseif ( $where['operation'] === ORMSelector::CONTAINS ) {
						$request .= "LIKE %{$where['value']}%";
					} else {
						$request .= "{$where['operation']} ";
						if ( is_string( $where['value'] ) ) {
							$request .= "'{$where['value']}'";
						} else {
							$request .= $where['value'];
						}
					}

					if ( isset( $where['binary_operation'] ) ) {
						$request .= " {$where['binary_operation']}";
					}
				}
			}
		}

		if ($this->pdo->exec($request) !== false) {
			foreach ($this->updatedFields as $field => $value) {
				$this->modelObj->$field = $value;
			}
			return $this->modelObj;
		}
		return false;
	}

	public function addObject( Model $object ): \PhpLib\ORM\interfaces\ORMUpdater {
		$this->modelObj = $object;
		return $this;
	}
}