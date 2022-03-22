<?php

use App\HTMLElements;
use App\PageModel;
use App\ButtonModel;
use App\Constants;
use App\Tutorials;
use App\AreaModel;
use App\Configurator;

$page = new PageModel(
    'home',
    'Код Воробья',
    'Вакансии',
    'Полное рабочий день или частичная занятость. Только удалёнка. Компания в UK, компенсируем подоходный налог в вашей стране. Зарплату платим долларах. Задать вопросы ' . HTMLElements::embeddedLink('в телеграм', Constants::$telegram_my) . '.',
    [],
    '12.03.2022',
    '12.03.2022'
);

HTMLElements::header($page, []);
/*
HTMLElements::titleSection('iOS Разработчик: UIKit и SwiftUI');

HTMLElements::text('Работа над интерфейсом. Можно начинающим и продолжающим. Нет требований к опыту работы и образованию. Удаленка. ЗП до `1000$ / месяц`. Не уточняйте или вакансия актуальна, точно актуальна - ищем несколько разработчиков.');

HTMLElements::text('Тестовое задание: ' . HTMLElements::embeddedLink('повторить сканер QR-кода', 'https://cdn.ivanvorobei.io/temp/qr-scanner-example.mp4') . ' из приложения камеры: трекинг, рамку и вью внизу. Скидывать скринкаст, код проекта скидывать не нужно. Присылать ' . HTMLElements::embeddedLink('в телеграм', Constants::$telegram_my) . '.');

// HTMLElements::line();

HTMLElements::titleSection('iOS Разработчик: Crypto ETH');

HTMLElements::text('Уметь создавать кошелек, делать транзакции и подтверждать операции в дапах. Приветствуется опыт работы с `NFT` и `ENS`. Зарплата договорная.');
*/

HTMLElements::important('Приглашаем авторов. Платим 40$ за туториал. Подробности ' . HTMLElements::embeddedLink('здесь', 'https://sparrowcode.io/ru/contribute') . '.');

HTMLElements::titleSection('iOS Разработчик: iOS Приложение');

HTMLElements::text('Приложение для управление GitHub Projects. Список проектов, изменение карточек и полей в задачах.
У гитхаба есть ' . HTMLElements::embeddedLink('API', 'https://docs.github.com/en/issues/trying-out-the-new-projects-experience/using-the-api-to-manage-projects') . ' на `http` запросах. Без базы данных. Можно джуну+ без комерческого опыта. Работа для одного разработчика. Предпочтительно UIKit. Обязательно лейаут кодом и понимание Diffable для коллекции и таблицы. Можно совмещать с основной работой.');

HTMLElements::titleSection('iOS Разработчик: Mac Приложение');

HTMLElements::text('UIKit` + Catalyst или нативно. Приложение-генератор ' . HTMLElements::embeddedLink('структуры проекта', 'https://github.com/strongself/Generamba') . '. Нужно сделать интрефейс и генерацию. Будут разные структуры - Viper, MVC, кастомные. Проект для одного разработчика: только вы будете заниматься кодом. Можно сдельную, можно ЗП. Можно совмещать с основой работой. В личку присылайте свои текущие проекты, если они не в сторе - скринкасты. Тестового задания нет.');

HTMLElements::titleSection('Редактор');

HTMLElements::text('Обязательно база по iOS разработке. Нужно чистить лишние слова в туториалах, приводить предложения к подлежащее+сказуемое, вычитывать ошибки. Работы мало, поэтому только парт-тайм. Можно совмещать с основной работой. Обязательно владеть книгой ' . HTMLElements::embeddedLink('Пиши Сокращай', 'https://www.ozon.ru/product/pishi-sokrashchay-kak-sozdavat-silnye-teksty-sarycheva-lyudmila-ilyahov-maksim-241182327/?sh=yYPBQQAAAA'));
HTMLElements::text('Тестовое задание: отредактировать текст в любой статье ' . HTMLElements::embeddedLink('из репозитория', 'https://github.com/sparrowcode/Articles/tree/main/ru/articles') . '. После сделать `Pull Request`. Перед тестовым заданием напишите желаему ЗП/сдельную сумму.');

HTMLElements::line();

HTMLElements::footer();