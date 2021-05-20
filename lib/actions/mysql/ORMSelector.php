<?php


namespace PhpLib\ORM\actions\mysql;


use PDO;
use PhpLib\ORM\Model;

class ORMSelector implements \PhpLib\ORM\interfaces\ORMSelector {
	protected string $table;
	protected array $where = [];
	protected ?int $limit = null;
	protected ?string $order_by = null;
	protected ?string $sens = null;

	protected array $results = [];

	public const EQUAL = '=';
	public const SUP = '>';
	public const INF = '<';
	public const SUP_EQUAL = '>=';
	public const INF_EQUAL = '<=';
	public const DIF = '!=';

	public const ASC = 'ASC';
	public const DESC = 'DESC';
	public const START_BY = 'start_by';
	public const END_BY = 'end_by';
	public const CONTAINS = 'contains';

	public function __construct(
		private PDO $pdo,
		private string $fields,
		private string $model
	) {
		$this->from($model::getTable());
	}

	public function from(string $table): \PhpLib\ORM\interfaces\ORMSelector {
		$this->table = $table;
		return $this;
	}

	public function where(array $where): \PhpLib\ORM\interfaces\ORMSelector {
		$this->where[] = $where;
		return $this;
	}

	public function and(): \PhpLib\ORM\interfaces\ORMSelector {
		$this->where[count($this->where) - 1]['binary_operation'] = 'AND';
		return $this;
	}

	public function or(): \PhpLib\ORM\interfaces\ORMSelector {
		$this->where[count($this->where) - 1]['binary_operation'] = 'OR';
		return $this;
	}

	public function limit( int $limit ): \PhpLib\ORM\interfaces\ORMSelector {
		$this->limit = $limit;
		return $this;
	}

	public function order( string $order_by ): ORMSelector {
		$this->order_by = $order_by;
		return $this;
	}

	public function asc(): \PhpLib\ORM\interfaces\ORMSelector {
		$this->sens = 'ASC';
		return $this;
	}

	public function desc(): \PhpLib\ORM\interfaces\ORMSelector {
		$this->sens = 'DESC';
		return $this;
	}

	public function count(): int {
		if (empty($this->results)) {
			$this->get();
		}

		return count($this->results);
	}

	public function get(): array {
		/** @var Model $model */
		$model = $this->model;
		$request = "SELECT {$this->fields} FROM `{$this->table}`";

		if (!empty($this->where)) {
			foreach ( $this->where as $i => $where ) {
				if ( isset( $model::getFields()[ $where['field'] ]['joint'] ) ) {
					$request .= " INNER JOIN `{$model::getFields()[ $where['field'] ]['joint']['model']::getTable()}` ON `{$model::getTable()}`.`{$where['field']}` = `{$model::getFields()[ $where['field'] ]['joint']['model']::getTable()}`.`{$model::getFields()[ $where['field'] ]['joint']['field']}`";
					unset($this->where[$i]);
				}
			}

			if (!empty($this->where)) {
				$request .= " WHERE";
				foreach ( $this->where as $where ) {
					if ( ! empty( $where ) ) {
						$request .= " `{$model::getTable()}`.`{$where['field']}` ";

						if ( empty( $where['operation'] ) ) {
							$where['operation'] = self::EQUAL;
						}

						if ( $where['operation'] === self::START_BY ) {
							$request .= "LIKE {$where['value']}%";
						} elseif ( $where['operation'] === self::END_BY ) {
							$request .= "LIKE %{$where['value']}";
						} elseif ( $where['operation'] === self::CONTAINS ) {
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
		}

		if (!is_null($this->order_by)) {
			$request .= " ORDER BY {$this->order_by}";
		}

		if (!is_null($this->sens)) {
			$request .= " {$this->sens}";
		}

		if (!is_null($this->limit)) {
			$request .= " LIMIT {$this->limit}";
		}

		$query = $this->pdo->query($request);
		$result = $query->fetchAll(PDO::FETCH_ASSOC);

		$this->results = array_map(static function(array $c) use($model) {
			/** @var Model $modelObj */
			$modelObj = new $model();
			foreach ($c as $field => $value) {
				$modelObj->$field = $value;
			}
			return $modelObj;
		}, $result);

		return $this->results;
	}
}