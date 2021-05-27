<?php


namespace PhpLib\ORM\actions\mysql;


use PDO;
use PhpLib\ORM\Model;

class ORMDeleter implements \PhpLib\ORM\interfaces\ORMDeleter {
	private string $table;
	private array $where = [];

	public function __construct(
		private PDO $pdo,
		private string $model
	) {
		$this->from($model::getTable());
	}

	public function from(string $table): \PhpLib\ORM\interfaces\ORMDeleter {
		$this->table = $table;
		return $this;
	}

	public function where(string $field, mixed $value, string $operation = ORMSelector::EQUAL): \PhpLib\ORM\interfaces\ORMDeleter {
		$this->where[] = [
			'field' => $field,
			'value' => $value,
			'operation' => $operation
		];
		return $this;
	}

	public function and(): \PhpLib\ORM\interfaces\ORMDeleter {
		$this->where[count($this->where) - 1]['binary_operation'] = 'AND';
		return $this;
	}

	public function or(): \PhpLib\ORM\interfaces\ORMDeleter {
		$this->where[count($this->where) - 1]['binary_operation'] = 'OR';
		return $this;
	}

	public function save(): bool {
		/** @var Model $model */
		$model = $this->model;
		$request = "DELETE FROM `{$model::getTable()}`";

		if (!empty($this->where)) {
			$request .= " WHERE";
			foreach ( $this->where as $where ) {
				if ( ! empty( $where ) ) {
					$request .= " `{$model::getTable()}`.`{$where['field']}` ";

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

		return $this->pdo->exec($request) !== false;
	}
}