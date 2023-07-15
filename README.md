# Описание и пояснения

Написан `RateClientInterface` интерфейс и его реализация для ЦБ РФ (`CbrClient`);
В файле `.env` можно настроить дефолтный клиент (`RATE_CLIENT_CLASS`) для работы сервиса, например, чтобы получать данные с другого ресурса.
Так как использование сервиса производится через написанный фасад, ServiceProvider может прокидывать в экземпляр сервиса экземпляр нужного клиента из конфига.

Все запросы в классе `CbrClient` кэшируются с помощью Redis (выбран как `CACHE_DRIVER` в `.env`)

Задача про получение данных за полгода могла выполняться 2 способами:
* Цикличное обращение к ЦБ РФ и запрос данных на каждую дату за 180 предыдущих дней
* Запрос к специальному методу в ЦБ РФ где мы получаем данные в разрезе "от и до"

Я выбрал второй вариант, чтобы не отправлять кучу запросов, а все расчеты уже выполняю на своей стороне.

Так же в первом варианте есть проблема дублирования данных, которую нужно было учесть при разработке.
Например, если запросить данные на _10.07.2023_, то мы получим данные за _08.07.2023_, так как 9 и 10 не являлись торговыми днями
В свою очередь запрос на _09.07.2023_ также вернет данные за _08.07.2023_.
Обработать такой сценарий можно, но тогда получается что эти 2 запроса попросту лишние.

Также в этой задаче необходимо было _"продемонстрировать навыки работы с брокерами сообщений"_.
Как таковой, Redis сложно назвать брокером сообщений, для этой задачи больше подойдет RabbitMQ.
Но я попробовал реализовать хранение ответов после запроса в Redis, и, вроде как, у меня получилось.

В качестве очередей я использовал БД, так как есть некоторые сложности с запуском job-ов в Redis очередях, 
в частности это обработка `failed_jobs`, так как в ванильном варианте они пытаются сохраниться в БД, даже при том,
что в проекте установлено значение `redis` в параметре `QUEUE_CONNECTION`. Redis драйвер попросту не знает,
что uuid job-ы нужно установить в поле uuid таблицы. Да, можно кастовать таблицу или придумать что-то еще, но это уже вопрос времени и желания)
Также можно установить `laravel horizon`, так как он способен работать с очередями полностью на стороне Redis клиента.

По сути условие использования очередей в задаче формальное.
При такой реализации как сейчас в проекте, очереди не нужны,
так как запрос выполняется очень быстро (за счет выбора второго подхода к решению задачи).

# Установка и запуск проекта

### 1. Разворачиваем `.example` файлы
#### Windows:
```powershell
copy .env.example .env
copy docker-compose.yml.example docker-compose.yml
copy .\docker\app\php-ini-overrides.ini.example .\docker\app\php-ini-overrides.ini
copy .\docker\nginx\sites-available\default.conf.example .\docker\nginx\sites-available\default.conf
```
#### Linux/Mac:
```
cp .env.example "$(basename .env.example .example)"
cp docker-compose.yml.example "$(basename docker-compose.yml.example .example)"
cp ./docker/app/php-ini-overrides.ini.example ./docker/app/"$(basename php-ini-overrides.ini.example .example)"
cp ./docker/nginx/sites-available/default.conf.example ./docker/nginx/sites-available/"$(basename default.conf.example .example)"
```

### 2. Собираем `Docker` контейнеры
```
cd .\docker\; docker-compose build; cd ..
```

### 3. Запустим контейнеры
```
docker-compose up -d
```

### 4. Запуск команд внутри контейнера
#### Установка зависимостей

```
docker exec cbr_app sh -c 'composer install; php artisan key:generate; php artisan config:cache; php artisan config:clear'
```

#### Создаём БД
```
docker exec -it cbr_pg createdb -U postgres cbr
```

#### Выполняем миграции
```
docker exec -it cbr_app php artisan migrate
```

# Описание API
## GET /api/comparison-with-yesterday
Этот API-эндпоинт позволяет получить сравнение курса валюты с предыдущим днем.
### Параметры запроса
1. `date` (обязательный): Дата в формате "дд.мм.гггг" (например, "01.01.2020").
2. `currency_code` (обязательный): Строковое значение кода валюты (например, "USD").
3. `base_currency_code` (необязательный): Строковое значение кода базовой валюты (по умолчанию "RUR").

### Примеры
#### Запрос `GET localhost:88/api/comparison-with-yesterday?date=13.07.2005&currency_code=EUR`
#### Пример ответа (успешный запрос)
```
HTTP/1.1 200 OK
Content-Type: application/json

{
  "message": "Rate comparison with yesterday",
  "data": {
    "rate": 34.7844,
    "difference": 0.2857
  }
}
```
#### Пример ответа (данные не найдены)
```
HTTP/1.1 400 Bad Request
Content-Type: application/json

{
  "message": "Rate not found."
}
```
#### Пример ответа (ошибка валидации)
```
HTTP/1.1 422 Unprocessable Entity
Content-Type: application/json

{
  "message": "Invalid data send",
  "errors": {
    "date": [
      "The date field must be a valid date."
    ]
  }
}
```

# Команда для запуска запроса данных за последние полгода
### `php artisan rate:request {currencyCode} {baseCurrencyCode?}`
#### Пример запроса ```docker exec cbr_app php artisan rate:request USD```
#### Пример ответа
```
Запуск запроса
Запрос произведён
Идентификатор запроса: e1af8d7c-040c-368c-a471-e186344ee893
Для проверки результат запустите команду: php artisan rate:check-result e1af8d7c-040c-368c-a471-e186344ee893
```

# Команда для проверки результат запроса
### `php artisan rate:check-result {uuid}`
#### Пример запроса ```docker exec cbr_app php artisan rate:check-result e1af8d7c-040c-368c-a471-e186344ee893```
#### Пример ответа
```
{#660 // app/Console/Commands/HalfYearRatesCheckResult.php:33
  +"uuid": "e1af8d7c-040c-368c-a471-e186344ee893"
  +"data": array:122 [
    0 => {#661
      +"rate": 68.6644
      +"difference": 0.3752
      +"date": "18.01.2023"
    }
    1 => {#662
      +"rate": 68.8728
      +"difference": 0.2084
      +"date": "19.01.2023"
    }
    2 => {#663
      +"rate": 68.8467
      +"difference": -0.0261
      +"date": "20.01.2023"
    }
    ...
  ]
}
```
