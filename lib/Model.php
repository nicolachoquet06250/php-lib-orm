<?php


namespace PhpLib\ORM;


use Exception;
use PhpLib\decorators\Attribute;
use PhpLib\ORM\decorators\DBConf as DBConfAttribute;
use PhpLib\ORM\decorators\Entity;
use PhpLib\ORM\decorators\ORMFieldAttribute;
use PhpLib\ORM\interfaces\adapters\ORMAdapter;
use PhpLib\ORM\interfaces\ORMDeleter;
use PhpLib\ORM\interfaces\ORMInserter;
use PhpLib\ORM\interfaces\ORMSelector;
use PhpLib\ORM\interfaces\ORMUpdater;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;

class Model implements ORMAdapter {
	protected static string $table = '';
	protected static array $fields = [];
	protected static string $dbConf = 'default';

	private ORMAdapter $orm;

	/* ** ****************************************** ** */
	/* ** ORM Initializer method ******************* ** */
	/* ** ****************************************** ** */

	/**
	 * @return ORMAdapter
	 * @throws Exception
	 */
	protected static function getORM(): ORMAdapter {
		if (is_null(DBConf::getConf(static::$dbConf))) {
			throw new RuntimeException('database conf "default" is expected !');
		}

		return new ORM(DBConf::getConf(static::$dbConf), static::class);
	}

	/* ** ****************************************** ** */
	/* ** Build generation methods ***************** ** */
	/* ** ****************************************** ** */

	public static function setTable(string $table): void {
		static::$table = $table;
	}

	public static function getTable(): string {
		return static::$table;
	}

	public static function addField(string $field): void {
		static::$fields[$field] = [];
	}

	public static function setFieldKey(string $field, string $key, mixed $value): void {
		if (empty(static::$fields[$field])) {
			static::addField($field);
		}

		static::$fields[$field][$key] = $value;
	}

	public static function getFields(): array {
		return static::$fields;
	}

	public static function setDbConf(string $confName): void {
		static::$dbConf = $confName;
	}

	/**
	 * Genère un tableau static de métadatas sur la classe du model
	 * en fonctions des attributs placés sur chaque propriétés
	 */
	protected static function build(): void {
		if (empty(static::getTable()) && empty(static::getFields())) {
			$rc = new ReflectionClass(static::class);
			if (!empty($rc->getAttributes( DBConfAttribute::class))) {
				$attr = $rc->getAttributes(DBConfAttribute::class)[0];
				/** @var Attribute $entity */
				$entity = $attr->newInstance();
				$entity->setTarget($rc->getName());
				$entity->setMethod('__construct');
				$entity->process();
			}

			if (!empty($rc->getAttributes(Entity::class))) {
				$attr = $rc->getAttributes(Entity::class)[0];
				/** @var Attribute $entity */
				$entity = $attr->newInstance();
				$entity->setTarget($rc->getName());
				$entity->setMethod('__construct');
				$entity->process();
			}

			foreach ($rc->getProperties( ReflectionProperty::IS_PUBLIC ) as $property) {
				$attrs = $property->getAttributes(ORMFieldAttribute::class,ReflectionAttribute::IS_INSTANCEOF);
				if (!empty($attrs)) {
					foreach ($attrs as $attr) {
						/** @var ORMFieldAttribute $entity */
						$entity = $attr->newInstance();
						$entity->setTarget($rc->getName());
						$entity->setProperty($property->getName());
						$entity->process();
					}
				}
			}
		}
	}

	/* ** ****************************************** ** */
	/* ** Generation de la table ******************* ** */
	/* ** ****************************************** ** */

	/**
	 * Crée la table associé au model
	 *
	 * @return bool
	 * @throws Exception
	 */
	public static function create(): bool {
		static::build();
		$orm = static::getORM();

		return $orm->createTable();
	}

	/* ** ****************************************** ** */
	/* ** Selection dans la table ****************** ** */
	/* ** ****************************************** ** */

	/**
	 * @return Model[]
	 * @throws Exception
	 */
	public static function find($from = []): array {
		static::build();
		$orm = static::getORM();

		$selector = $orm->select()->from(static::getTable());

		if (!empty($from)) {
			$cmp = 0;
			foreach ( $from as $k => $v ) {
				$selector->where(field: $k, value: $v);
				if ($cmp < count($from) - 1) {
					$selector->and();
				}
				$cmp++;
			}
		}

        return $selector->get();
	}

	/**
	 * @param int $from
	 *
	 * @return ?Model
	 * @throws Exception
	 */
	public static function findOne(int $from): ?Model {
		static::build();

		$primary_key = array_reduce(
			array_values(static::getFields()),
			static fn (?string $r, array $c) => (isset($c['primary_key']) && $c['primary_key']) ?  $c['field'] : $r,
			null
		);

		$selectedElements = static::find([
			$primary_key => $from
		]);
		if (empty($selectedElements)) {
			return null;
		}

		[$selectedElement] = $selectedElements;
		return $selectedElement;
	}

	/* ** ****************************************** ** */
	/* ** Ajout dans la table ********************** ** */
	/* ** ****************************************** ** */

	/**
	 * Ajoute une ligne à la table associée au model
	 *
	 * @return ORMInserter
	 * @throws Exception
	 */
	public static function add(): ORMInserter {
		$orm = static::getORM();
		return $orm->insert();
	}

	/* ** ****************************************** ** */
	/* ** Interface methods ************************ ** */
	/* ** ****************************************** ** */

	/**
	 * Model constructor.
	 * @throws Exception
	 */
	public function __construct() {
		static::build();
		$this->orm = static::getORM();
	}

	public function getConnector(): mixed {
		throw new RuntimeException('method not supported');
	}

	public function insert(): ORMInserter {
		return $this->orm->insert()->setModel($this);
	}

	public function select(array|string $fields = '*'): ORMSelector {
		return $this->orm->select($fields);
	}

	public function delete(): ORMDeleter {
		return $this->orm->delete()->setModel($this);
	}

	public function update(): ORMUpdater {
		return $this->orm->update()->addObject($this);
	}

	public function createTable(): bool {
		return $this->orm->createTable();
	}
}
