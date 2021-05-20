<?php


namespace PhpLib\ORM;


use Exception;
use PhpLib\ORM\interfaces\adapters\ORMAdapter;
use PhpLib\ORM\interfaces\ORMDeleter;
use PhpLib\ORM\interfaces\ORMInserter;
use PhpLib\ORM\interfaces\ORMSelector;
use PhpLib\ORM\interfaces\ORMUpdater;

class ORM implements ORMAdapter {
	protected ORMAdapter $orm;

	/**
	 * @param array $conf
	 * @param string $model
	 *
	 * @throws Exception
	 */
	public function __construct(
		private array $conf,
		private string $model
	) {
		$engine = $this->conf['engine'];
		$className = ucfirst($engine);
		$adapter = "\\PhpLib\\ORM\\adapters\\$className";
		if (!in_array(strtolower($engine), ['mysql', 'pgsql', 'mongodb'])) {
			throw new Exception("adapter $adapter not supported");
		}
		$this->orm = new $adapter($this->conf, $this->model);
	}

	public function getConnector(): mixed {
		return $this->orm->getConnector();
	}

	public function insert(): ORMInserter {
		return $this->orm->insert();
	}

	public function select(string|array $fields = '*'): ORMSelector {
		return $this->orm->select($fields);
	}

	public function delete(): ORMDeleter {
		return $this->orm->delete();
	}

	public function update(): ORMUpdater {
		return $this->orm->update();
	}

	public function createTable(): bool {
		return $this->orm->createTable();
	}
}