# Microservice library

[![Latest Stable Version](https://poser.pugx.org/itleague/microservice/v)](https://packagist.org/packages/itleague/microservice) 
[![Total Downloads](https://poser.pugx.org/itleague/microservice/downloads)](https://packagist.org/packages/itleague/microservice) 
[![Latest Unstable Version](https://poser.pugx.org/itleague/microservice/v/unstable)](https://packagist.org/packages/itleague/microservice) 
[![License](https://poser.pugx.org/itleague/microservice/license)](https://packagist.org/packages/itleague/microservice)

# Описание

Библиотека упрощает и ускоряет разработку новых микросервисов, особенно при использовании базы postgres. 
В основном состоит из хелперов и абстрактных классов, в которых уже прописана базовая логика большинства 
микросервисов: многоязычность, кэширование ответов, работа с полями типа file, аутентификация пользователя, 
валидация входящих данных, обработка ошибок и формирование ответов.

# Установка

`composer require itleague/microservice`

### Включает следующие библиотеки

* [laravel/lumen-framework](https://github.com/laravel/lumen-framework)
* [illuminate/redis](https://github.com/illuminate/redis)
* [predis/predis](https://github.com/predis/predis)
* [guzzlehttp/guzzle](https://github.com/guzzle/guzzle)
* [flipbox/lumen-generator](https://github.com/flipboxstudio/lumen-generator): дополнительные команды artisan для Lumen. Очень помогает в разработке
* [opis/closure](https://github.com/opis/closure): библиотека для сериализации значений типа Closure. Используется в функционале кэширования моделей. Также модели сериализуются для запуска job.
* [barryvdh/laravel-ide-helper](https://github.com/barryvdh/laravel-ide-helper): библиотека формирует метафайлы для использования IDE. Также может формировать phpDoc для моделей. Помогает в разработке и описании моделей
* [phabloraylan/lumen-middleware-trim-or-convert-strings](https://github.com/phabloraylan/lumen-middleware-trim-or-convert-strings): включает 2 middleware для триминга строк из запросов и превращения пустых строк в null

# Многоязычность

Полностью готовый функционал для работы с т. н. переводимыми полями. В первую очередь необходимо создать таблицу для 
хранения списка используемых языков:

`php artisan microservice:languages-table`

Будет создана новая миграция для таблицы. Один из языков определяется как язык по умолчанию.

Далее, для любой сущности можно определить некий набор полей, значения которых отличаются для разных языков, 
вынести их в отдельную таблицу. Значения на запрашиваемом языке будут автоматически подтягиваться в 
поле translation базовой сущности. При отсутствии необходимых переводов подтягиваются значения на языке по умолчанию.

Язык определяется заголовком `Accept-Language`.

Заполнение переводимых значений происходит прямой передачей всего набора полей в базовую сущность. 
Необходимо только определить fillable поля моделей.

# Кэширование ответов

Для кэширования ответов используется паттерн Repository с декоратором. Ответы для всех get-запросов кэшируются. 
Кэш сбрасывается при обновлении/добавлении/удалении/восстановлении сущности. Есть возможность указать время кэширования 
и доп. сущности, кэш которых будет сбрасываться вместе с текущим.

Для правильной работы необходимо для каждой сущности создать интерфейс, класс репозитория и класс декоратора Caching. 
Наследоваться можно от обычного интерфейса `ITLeague\Microservice\Repositories\Interfaces\RepositoryInterface` или 
его Restorable версии, которая включает в себя ещё и метод `restore` для восстановления удалённой сущности.

Также уже готов абстрактный класс `ITLeague\Microservice\Http\Controllers\ResourceController` (и Restorable версия), 
в котором есть 5 методов для CRUD api.

# Работа с полями типа file

В базе поля типа file хранятся в виде UUID файла в хранилище. В ответах такие поля возвращаются в виде метаданных 
самого файла (см. документацию для сервиса storage). Всё, что необходимо для задания такого типа полей - у модели 
заполнять свойство file параметрами: permission, sizes, force.

При force равном true не будет происходить подтверждения файла при обновлении значения поля.

# Остальной функционал базового класса сущности

### Использование метода `getUnfilledAttributes`

При создании/обновлении каждой сущности все значения полей, которые не входят в набор fillable-полей, помещаются в 
массив unfilled. Его можно получить методом `getUnfilledAttributes` и использовать, например, в событии `saved` для 
заполнения связанных сущностей. Именно так, кстати, заполняются переводимые поля сущности.

### Создание дополнительных фильтров и сортировок сущности

По умолчанию любую сущность можно фильтровать по её идентификатору. В конструкторе модели можно задать дополнительные 
фильтры и сортировки. Для этого необходимо заполнить свойства модели filters и sorts соответственно. Ключами являются
названия полей, значениями - closure с описанием логики фильтра и сортировки.

Также необходимо добавить новые фильтры и сортировки в правила валидации сущности (см. далее).

### Запрос из базы только необходимых связей сущности

Для всех запрашиваемых сущностей есть возможность указать только необходимые в ответе поля. И если из таблицы сущности
в базе выбираются все поля, то связанные сущности из других таблиц запрашивать необязательно, если они не требуются 
в ответе. Для этого у сущности существует свойство `eagerLoad`, в котором перечисляются все отношения сущности. Если 
какое-то из этих отношений не требуется в get-запросе, то оно не будет доставаться из базы.

# Аутентификация

Для каждого запроса обрабатываются заголовки `x-authenticated-userid` и `x-authenticated-scope`. 
В результате запрос либо остаётся неавторизованным, либо создаётся экземпляр класса `ITLeague\Microservice\Models\User`,
унаследованный от `GenericUser`. В дальнейшем аутентификацию можно проверять фасадом `Auth`.

Также добавлен полный доступ ко всем эндпоинтам для супер-админа.

# Валидация входящих данных

Идея в том, чтобы валидировать все входящие данные до передачи их в бд. Для этого у каждой сущности есть свойство 
`rules` со следующими ключами:
* *store* - набор правил валидации для сохранения сущности. Включает в себя переводимые поля и остальные поля,
которые попадают в unfilled.
* *update* - набор правил для обновления сущности. Обычно отличается от store отсутствием правила для первичного ключа
и отсутствием правила `required`.
* *filter* - набор правил валидации фильтра, который используется при запросе списка сущностей. Фильтр по первичному 
ключу уже задан для всех сущностей, но для использования его необходимо также указать в правилах. Для удобства в 
валидатор добавлены правила `array_or_integer` и `array_or_string:length`.
* *sort* - правило, ограничивающее список полей, по которым возможно сортировать список сущностей. Для удобства
есть правило `sort_in:field_1,field_2...`.

Также есть валидация параметра `fields`. Никакие правила специально задавать не нужно, используется набор ключей 
соответствующего ресурса сущности. Необходимо только указать для ресурса трейт 
`ITLeague\Microservice\Traits\FilterableResource` и обернуть возвращаемый методом `toArray` массив в метод 
`fields`.

# Формирование ответов

Для единообразного формирования ответов используется трейт `ITLeague\Microservice\Traits\ApiResponse`, который
можно подключить для любого класса, возвращающего ответы. В основной это контроллеры и хендлер обработки
ошибок.

### Обработка ошибок

Для обработки всех исключений уже подключен `ITLeague\Microservice\Exceptions\Handler`. Пока нет возможности добавлять 
в него свои типы исключений. 

Если переменная `APP_DEBUG` установлена в `true`, то используется штатный рендер ошибок Lumen. Иначе ошибки выводятся в
виде json-объекта. 

# Хелперы

### Файловое хранилище

На данный момент в `ITLeague\Microservice\Http\Helpers\Storage` реализованы только методы confirm, delete и info.
За доп. информацией по ним см. документацию сервиса storage

### Миграции

Есть 3 дополнительных метода класса `Blueprint`:
* *softDeletesWithUserAttributes* - добавляет к таблице поля `deleted_at` и `deleted_by`. Для работы с этими полями 
к модели надо подключить трейт `ITLeague\Microservice\Traits\SofDeletes`
* *timestampsWithUserAttributes* - добавляет к таблице поля `created_at`, `created_by`, `updated_at` и `updated_by`. 
К модели подключать трейт `ITLeague\Microservice\Traits\WithUserAttributes`. Подразумевается, что добавлять и
редактировать записи в таблице могут только авторизованные пользователи. Иначе необходимые поля к таблице 
добавляются руками.
* *foreignLanguageId* - добавляет к таблице с переводимыми полями сущности поле `language_id` со ссылкой 
на таблицу с языками. Предполагается, что сама таблица с языками уже должна быть создана (см. Многоязычность)

Также есть большой класс-хелпер для добавления триггеров и функций к базе postgres `ITLeague\Microservice\Http\Helpers\DB`. 

В первой миграции проекта желательно создать три функции: `createOnInsertFunction`, `createOnUpdateFunction` 
(если будет использоваться  трейт `ITLeague\Microservice\Traits\WithUserAttributes`) и `createOnDeleteFunction` 
(если будет использоваться трейт `ITLeague\Microservice\Traits\SofDeletes`). В дальнейшем для каждой 
таблицы с сущностью необходимо добавлять триггеры `createOnUpdateTrigger`, `createOnInsertTrigger` 
и `createOnDeleteTrigger`. также для каждой сущности рекомендуется добавлять запретить изменять 
первичный ключ методом `setImmutablePrimary`.

Для таблиц с переводимыми полями, справочниками и промежуточными таблицами создаётся сначала функция методом 
`createOnUpdateOrInsertRelationshipFunction`, а затем триггер `createOnUpdateOrInsertRelationshipTrigger`.
Это необходимо для изменения `created`/`updated` полей родительской сущности. Для таблиц с переводимыми полями 
рекомендуется создать триггер `createOnDeleteTrigger` для предотвращения удаления записей.

Удалять в миграциях есть смысл только функции. Триггеры удаляются вместе с таблицей. Все соответствующие методы 
в хелпере есть.

# Базовый абстрактный класс для тестов

 К базовому классу `TestCase` от Lumen добавлены свойства `Faker\Factory` faker, user/admin/superAdmin` (экземпляры класса 
 `ITLeague\Microservice\Models\User` с соответствующими правами). Также повешен постоянный 204 ответ на обращения
 к файловому хранилищу. Ещё есть стандартная структура возвращаемых ошибок.
