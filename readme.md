# Microservice library

[![Latest Stable Version](https://poser.pugx.org/itleague/microservice/v)](https://packagist.org/packages/itleague/microservice) 
[![Total Downloads](https://poser.pugx.org/itleague/microservice/downloads)](https://packagist.org/packages/itleague/microservice) 
[![Latest Unstable Version](https://poser.pugx.org/itleague/microservice/v/unstable)](https://packagist.org/packages/itleague/microservice) 
[![License](https://poser.pugx.org/itleague/microservice/license)](https://packagist.org/packages/itleague/microservice)

# Описание

Библиотека упрощает и ускоряет разработку новых микросервисов, особенно при использовании базы postgres. 
В основном состоит из хелперов и абстрактных классов, в которых уже прописана базовая логика большинства 
микросервисов: многоязычность, кэширование ответов, работа с полями типа file, аутентификация пользователя, 
верификация входящих данных, обработка ошибок и формирование ответов.

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
самого файла (см. документацию для сервиса storage). Всё, что необходимо для задания такого типа полей - у сущности 
заполнять свойство file параметрами: permission, sizes, force.

При force равном true не будет происходить подтверждения файла при обновлении значения поля.

# остальной функционал базового класса сущности

# Аутентификация

# Верификация входящих данных

# формирование ответов

# Хелперы для создания миграций

# Базовый абстрактный класс для тестов

 
