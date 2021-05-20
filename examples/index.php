<?php

use PhpLib\ORM\DBConf;

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/User.php';

$conf = new DBConf();
$conf->use(
	engine: 'mysql',
	host: 'localhost',
	dbname: 'database',
	username: 'root',
	password: 'root'
);

User::create();
Message::create();

User::find([
	'email' => 'nchoquet@gmail.com'
]);

Message::find([
	'user_id' => 1,
	'message' => 'toto'
]);

/*$addUser = User::add()
	->set('firstname', 'Nicolas')
	->set('lastname', 'Choquet')
	->set('email', 'nchoquet@norsys.fr')
	->set('password', 'monpasswordvalidé')
	->set('created_at', $now = date('Y-m-d\TH:i:s.uP'))
	->build();

dump($addUser);*/

/*dump(User::findOne(10));

$supTo10 = (new User())->select()->where(['field' => 'id', 'operation' => ORMSelector::SUP, 'value' => 10])->get();
dump($supTo10);

$deleted = (new User())->delete()
	->where('id', 10, '>')
	->save();
dump('deleted', $deleted);*/
/** @var User $updatedUser */
$updatedUser = User::findOne(10)->update()
	->set('firstname', 'Nicolas')
	->set('lastname', 'Choquet')
	->set('updated_at', $now = date('Y-m-d\TH:i:s.uP'))
	->save();

dump($updatedUser);