# What to watch

![PHP Version](https://img.shields.io/badge/php-%5E8.2-7A86B8)
![MySQL Version](https://img.shields.io/badge/mysql-%5E8.0-F29221)
![Laravel Version](https://img.shields.io/badge/laravel-%5E12.0-F13C30)

## О проекте

«What to watch» — это проект на Laravel, который представляет собой REST-API приложение онлайн-кинотеатра.\
Сервис предоставляет возможность отслеживать прогресс просмотра сериалов и получения списка не просмотренных серий.\
Сериалам можно выставлять оценки, на основании которых формируются рейтинги позволяющие выбрать новые сериалы для просмотра.\
В рамках этого проекта, помимо работы с API, акцент делался на автоматизированном тестировании. Всего было написано более 50 автотестов.

## Основные сценарии использования:
- Получение списка фильмов и фильтрация списков по жанрам
- Получение информации о фильме
- Выставление оценок фильму и добавление отзывов
- Получения списка похожих фильмов
- Добавление фильма в список «К просмотру» (добавление в закладки)
- Просмотр фильмов онлайн
- Регистрация пользователя

## Технические требования
Проект должен разрабатываться на PHP версии 8.0 или выше (при использовании версии 8.1 соответствующее ограничение должно быть указано в файле composer.json). Используемая база данных — MySQL 8.0 и выше.\
Проект должен сопровождаться конфигурацией для развертывания с помощью docker (manifest docker-compose), и инструкцией по развертыванию. Разработка верстки и клиентского приложения не требуется. Ожидается только разработка бекенд api приложения.

Все запросы должны сопровождаться отправкой заголовка принимающего json в качестве ответа, и ответы, как успешные так и об ошибках, должны возвращаться в json формате.

## Описание процессов
см. [Техническое задание](specification.md)


## Инструкция по развёртыванию с помощью docker (manifest docker-compose)

```bash
docker-compose up -d
docker-compose exec app composer install
cp .env.example .env	
./vendor/bin/sail up -d	
./vendor/bin/sail artisan key:generate
```
### Дополнительные команды	(если нужно)
```bash	
./vendor/bin/sail artisan migrate	
./vendor/bin/sail artisan queue:work	
./vendor/bin/sail artisan db:seed	
./vendor/bin/sail artisan test	
```


<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>
