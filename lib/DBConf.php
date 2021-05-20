<?php


namespace PhpLib\ORM;


class DBConf {
	private static array $conf = [];

	public function use(string $engine, string $host, string $dbname, string $username, string $password, string $saveName = 'default'): DBConf {
		static::$conf[$saveName] = [
			'engine' => $engine,
			'host' => $host,
			'dbname' => $dbname,
			'username' => $username,
			'password' => $password
		];
		return $this;
	}

	public static function getConf(string $confName = 'default'): ?array {
		return static::$conf[ $confName ] ?? null;
	}
}