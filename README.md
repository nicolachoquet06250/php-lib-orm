# php-lib-orm
module d'orm de la bibliothèque nicolachoquet06250/php-lib

## DOCUMENTATION

- Créer un Model :

```php
<?php

use PhpLib\ORM\Model;
use PhpLib\ORM\decorators\{
    Entity, Column, AutoIncrement, 
    PrimaryKey, DefaultValue,
    types\Integer, types\DateTime, 
    types\NotNull
};

#[Entity('table-name')]
class MaTable extends Model {
    #[
        Column('id'),
        Integer(11),
        AutoIncrement(),
        PrimaryKey()
    ]
    public int $id; 
    
    #[
        Column(),
        DateTime(),
        DefaultValue('NOW()'),
        NotNull()
    ]
    public string $created_at;
}
```