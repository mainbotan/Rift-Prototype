<?php

namespace App\Core\Testing\Sources;

use Rift\Core\Database\Models\Model;
use Rift\Core\ORM\Table;
use Rift\Core\Database\Models\Types;

class ClientsModel extends Model {
    const NAME = 'users';
    const VERSION = '1.0.0';

    protected function schema(): void {
        $this->table->create('id')
            ->type(Types::id('uuid'))
            ->comment('Уникальный идентификатор пользователя')
            ->affirm();

        $this->table->create('username')
            ->type(Types::varchar(50))
            ->nullable(false)
            ->comment('Логин пользователя')
            ->affirm();

        $this->table->create('password_hash')
            ->type(Types::varchar(255))
            ->nullable(false)
            ->comment('Хэш пароля')
            ->affirm();

        $this->table->create('email')
            ->type(Types::varchar(255))
            ->nullable(false)
            ->comment('Основной email')
            ->affirm();

        $this->table->create('backup_email')
            ->type(Types::varchar(255))
            ->nullable(true)
            ->comment('Резервный email')
            ->affirm();

        $this->table->create('tier_level')
            ->type(Types::SMALLINT)
            ->defaultValue(1)
            ->comment('Уровень доступа (1-10)')
            ->affirm();

        $this->table->create('balance')
            ->type(Types::money())
            ->defaultValue(0)
            ->comment('Баланс счета')
            ->affirm();

        $this->table->create('metadata')
            ->type(Types::JSONB)
            ->nullable(true)
            ->comment('Дополнительные метаданные')
            ->affirm();

        $this->table->create('last_login_ip')
            ->type(Types::ipAddress())
            ->nullable(true)
            ->comment('IP последнего входа')
            ->affirm();

        $this->table->create('login_history')
            ->type(Types::JSON_ARRAY)
            ->nullable(true)
            ->comment('История входов')
            ->affirm();

        $this->table->create('created_at')
            ->type(Types::CREATED_AT)
            ->comment('Дата создания')
            ->affirm();

        // 2. Индексы
        $this->table->uniqueIndex(['username'])
            ->comment('Уникальный индекс для логина')
            ->affirm();

        $this->table->uniqueIndex(['email'])
            ->comment('Уникальный индекс для email')
            ->affirm();

        $this->table->addIndex(['tier_level'])
            ->comment('Композитный индекс для статуса и уровня')
            ->affirm();

        // 4. Настройки таблицы
        $this->table->engine('InnoDB')
            ->charset('utf8mb4')
            ->collation('utf8mb4_unicode_ci')
            ->commentTable('Таблица пользователей корпоративной системы');
    }
}