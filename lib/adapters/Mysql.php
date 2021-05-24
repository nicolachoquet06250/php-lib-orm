<?php


namespace PhpLib\ORM\adapters;


use Exception;
use JetBrains\PhpStorm\Pure;
use PDO;
use PDOException;
use PhpLib\ORM\interfaces\{ORMInserter, ORMSelector, ORMUpdater, ORMDeleter, adapters\ORMAdapter};
use PhpLib\ORM\Model;
use PhpLib\ORM\ORM;

class Mysql implements ORMAdapter {
	protected PDO $connector;

	/**
	 * @param array{host:string, dbname:string, username:string, password:string} $config
	 */
	public function __construct(
		protected array $config,
		protected string $model
	) { $this->connect(); }

	/**
	 * @throws PDOException
	 */
	private function connect(): void {
		$charset = $this->config['charset'] ?? 'utf8';
		$this->connector = new PDO(
			"mysql:host={$this->config['host']};dbname={$this->config['dbname']};charset=$charset",
			username: $this->config['username'],
			password: $this->config['password']
		);
	}

	public function getConnector(): mixed {
		return $this->connector;
	}

	#[Pure]
	public function insert(): ORMInserter {
		return new \PhpLib\ORM\actions\mysql\ORMInserter($this->connector, $this->model);
	}

	public function select(string|array $fields = '*'): ORMSelector {
		return new \PhpLib\ORM\actions\mysql\ORMSelector($this->connector, $fields, $this->model);
	}

	public function delete(): ORMDeleter {
		return new \PhpLib\ORM\actions\mysql\ORMDeleter($this->connector, $this->model);
	}

	public function update(): ORMUpdater {
		return new \PhpLib\ORM\actions\mysql\ORMUpdater($this->connector, $this->model);
	}

	/**
	 * Crée la table associé au model
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function createTable(): bool {
		/** @var Model $modelClass */
		$modelClass = $this->model;

		$table = $modelClass::getTable();
		$fieldList = [];
		foreach ($modelClass::getFields() as $field => $fieldData) {
			$createField = $fieldData['field'];
			if (!empty($fieldData['type'])) {
				$type = strtoupper($fieldData['type']['name']);
				$size = isset($fieldData['type']['size']) ? "({$fieldData['type']['size']})" : '';
				$createField .= " {$type}{$size}";
			} elseif (!empty($fieldData['joint'])) {
				/** @var Model $model */
				$model = $fieldData['joint']['model'];
				$modelField = $fieldData['joint']['field'];

				$type = strtoupper($model::getFields()[$modelField]['type']['name']);
				$size = isset($model::getFields()[$modelField]['type']['size']) ? "({$model::getFields()[$modelField]['type']['size']})" : '';

				$createField .= " {$type}{$size}";
			}
			$createField .= isset($fieldData['default']) && $fieldData['default'] ? " DEFAULT {$fieldData['default']}" : '';
			$createField .= isset($fieldData['not_null']) && $fieldData['not_null'] ? " NOT NULL" : '';
			$createField .= isset($fieldData['autoincrement']) && $fieldData['autoincrement'] ? ' AUTO_INCREMENT' : '';
			$createField .= isset($fieldData['primary_key']) && $fieldData['primary_key'] ? ' PRIMARY KEY' : '';
			$fieldList[] = $createField;
		}
		$fieldList = implode(",\n", $fieldList);
		$request = "CREATE TABLE IF NOT EXISTS `{$table}` (
	        {$fieldList}
		)";

		if ($this->connector->exec($request)) {
			return true;
		}
		return false;
	}
}