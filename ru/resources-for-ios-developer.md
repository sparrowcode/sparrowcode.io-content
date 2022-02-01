<?php

use App\HTMLElements;
use App\TutorialModel;
use App\Constants;
use App\AreaModel;

/** @var TutorialModel $tutorial */

HTMLElements::tutorialHeader(
    $tutorial,
    []
);

HTMLElements::text(
    'Это сборник полезных ресурсов для iOS разработчиков. Я не раставлял ссылки по рейтингу. Ссылки сгрупированы по формату материала - видео, текст, новости и т.д.'
);

HTMLElements::text(
    'Описание под каждым ресурсом это собирательный отзыв комьюнити. Его цель помочь быстрее сориентироваться в этом списке.'
);

HTMLElements::text(
    'Если вы знаете хорошие ресурсы, ' . HTMLElements::embeddedLink('напишите мне', Constants::$telegram_my). ' - я добавлю их сюда.'
);

/* Ресурсы */

HTMLElements::titleSection('Ресурсы Apple');

$resources = [
    new AreaModel(
        'Дизайн',
        null,
        'UI элементы и готовые шаблоны из них. Доступно для Sketch, Photoshop и XD. Последние шрифты San Francisco и New York. Бейджы "Доступно в AppStore" и другие.',
        'https://developer.apple.com/design/resources/',
        true
    ),
    new AreaModel(
        'Разработка',
        null,
        'Документации для разработчиков. В туториалах рассказывается о технологиях с примерами кода. Уже доступны туториалы о Xcode Cloud и Concurrency.',
        'https://developer.apple.com/documentation/',
        true
    ),
    new AreaModel(
        'Гайды',
        null,
        'Про проективрование интерфейса - архитектуру, жесты, UI-элементы и другое. Есть интерактивные видео для наглядности.',
        'https://developer.apple.com/design/',
        true
    ),
    new AreaModel(
        'Релизы',
        null,
        'Новые версии операционных систем и приложений. Можно глянуть список изменений и скачать Xcode не из стора.',
        'https://developer.apple.com/download/release/',
        true
    ),
    new AreaModel(
        'Видео с WWDC',
        null,
        'Видео-туториалы от эпл с сессии WWDC. Есть субтитры на английском языке. Спикеры говорят медленно и с наглядной графикой - можно смотреть даже со слабым английским.',
        'https://developer.apple.com/videos/',
        true
    ),
    new AreaModel(
        'Генератор промо-изображений',
        '',
        'Доступны стили `новое приложение`, `обновление`, `подписка` и `оффер`. Настраивается язык и цвет фона. Есть размеры для сторис, банеры и квадраты.',
        'https://tools.applemediaservices.com/apple-app-store-promote',
        true
    )
];

HTMLElements::areas($resources);

/* Русскоязычные видео */

HTMLElements::titleSection('Русскоязычные видео');

$russian_videos = [
    new AreaModel(
        'Школа мобильной разработки от Яндекса',
        null,
        'Хорошие спикеры и материал. Ролики по 1-2 часу. Звук записан с вебки.',
        'https://www.youtube.com/playlist?list=PLQC2_0cDcSKBUXhSGqAbVAp3SFBKPnpFI',
        true
    ),
    new AreaModel(
        'Код Воробья',
        null,
        'Канал вашего покороного слуги. Мне стоит делать ролики чаще.',
        'https://www.youtube.com/channel/UCNUGzZfcOyX4YpP36VzeZ6A',
        true
    ),
    new AreaModel(
        'iCode School',
        null,
        'Каждый ролик посвящен конкретному классу. Начинающим глянуть плейлист `Основы программирования`. Автора приятно слушать, но звук записан как в бочке.',
        'https://www.youtube.com/channel/UCx1xu0yc1mh-gjAq8YKRobg',
        true
    ),
    new AreaModel(
        'Ivan Skorokhod',
        null,
        'Перевод стэнфордского курса по iOS разработке. Есть ролики про Swift. Хорошая подача, плохой звук.',
        'https://www.youtube.com/channel/UChfEfFKYILtO5yZSX2irynw',
        true
    ),
    new AreaModel(
        'SwiftBook',
        null,
        'Интервью с разработчиками и практические задачи. Автор зачитывает код, который печатает - меня это утомляет. Хороший звук.',
        'https://www.youtube.com/channel/UCXlCPCsB09ftBA5bQfiSWoQ',
        true
    ),
    new AreaModel(
        'MadBrains',
        null,
        'В формате тех. докладов разбирают практические задачи. Есть видео о том как получить реджект и про RX. Ролики большие, но смотреть интересно.',
        'https://www.youtube.com/c/MadBrains',
        true
    )
];

HTMLElements::areas($russian_videos);

/* Рускоязычные Туториалы */

HTMLElements::titleSection('Рускоязычные туториалы');

$russian_tutorials = [
    new AreaModel(
        'Habr',
        null,
        'Портал с туториалами и практическми задачами. Авторы отвечают в комментариях. Ссылку привел конкретно по iOS разработке, но гляньте и другие потоки.',
        'https://habr.com/ru/hub/ios_dev/',
        true
    ),
    new AreaModel(
        'Apptractor',
        null,
        'В ' . HTMLElements::embeddedLink('телеграм-канале', 'https://telegram.me/apptractor') . ' приходит ежедневная подборка туториалов. По воскресеньям дайджест материалов за неделю.',
        'https://apptractor.ru',
        true
    ),
    new AreaModel(
        'SwiftBook',
        null,
        'Туториалы и переводы. Документация по Swift на русском языке. Есть платный курс для iOS разработчиков.',
        'https://swiftbook.ru',
        true
    )
];

HTMLElements::areas($russian_tutorials);

/* Забугорные Туториалы */

HTMLElements::titleSection('Забугорные туториалы');

$foreign_tutorials = [
    new AreaModel(
        'Ray Wenderlich',
        null,
        'Большие туториалы в практическом контектсе. У автора есть книги по гиту, базе данных и `SwiftUI`. Есть видео-курсы. Некоторый контент платный.',
        'https://www.raywenderlich.com',
        true
    ),
    new AreaModel(
        'useyourloaf.com',
        null,
        'Короткие статьи с практикой. Часто нахожу сайт в выдаче. Stackoverflow на максималках.',
        'https://useyourloaf.com',
        true
    ),
    new AreaModel(
        'iosdevweekly.com',
        null,
        'Подборки разбиты по категориям - инструменты, код, дизайн и маркетинг. Похож на `AppTractor`, только забугорный.',
        'https://iosdevweekly.com',
        true
    ),
    new AreaModel(
        'hackingwithswift.com',
        null,
        'Короткие туториалы. Часто встречаю в гугле в выдаче. Есть платные курсы.',
        'https://www.hackingwithswift.com/',
        true
    ),
    new AreaModel(
        'swiftsenpai.com',
        null,
        'Разбирают сложные инструменты. Много туториалов по новым технологиям.',
        'https://swiftsenpai.com',
        true
    ),
    new AreaModel(
        'nshipster.com',
        null,
        'Туториалы с глубоким погружением. Есть про среду разработки и зависимости.',
        'https://nshipster.com',
        true
    ),
    new AreaModel(
        'swiftontap.com',
        null,
        'Документация по `SwiftUI` с примерами. Практическое руководство.',
        'https://swiftontap.com',
        true
    ),
    new AreaModel(
        'theswiftdev.com',
        null,
        'Туториалы с не классическими практическими задачами типа как запускать swift-файлы как скрипты и обрабатывать препроцессор инфо.',
        'https://theswiftdev.com',
        true
    )
];

HTMLElements::areas($foreign_tutorials);

HTMLElements::titleSection('Забугорные видео');

$foreign_videos = [
    new AreaModel(
        'Стенфордский курс, оригинал',
        null,
        'Популярный курс среди начинающих разработчиков. Если хорошо с английским, начните с этого. В разделе с локализованными ресурсами есть ссылки на переводы.',
        'https://www.youtube.com/playlist?list=PL3d_SFOiG7_8ofjyKzX6Nl1wZehbdiZC_',
        true
    ),
    new AreaModel(
        'Kavsoft',
        null,
        'Туториалы и практические примеры на SwiftUI. Автор не озвучивает ролики, пояснения появляются текстом на экране.',
        'https://www.youtube.com/c/Kavsoft',
        true
    )
];

HTMLElements::areas($foreign_videos);

/* Чаты */

HTMLElements::titleSection('Чаты');

$chats = [
    new AreaModel(
        'Чат Код Воробья',
        null,
        'Наш чат. Модерируем токсичных разработчиков, помогаем начинающим и продолжающим.',
        'https://sparrowcode.io/telegram/chat',
        true
    ),
    new AreaModel(
        'SwiftBook Чат',
        null,
        'Чат популярной платформы. В чате сейчас больше 5к людей.',
        'https://telegram.me/swiftbook_chat',
        true
    )
];

/* Забугорные Видео */

HTMLElements::areas($chats);

/* Подборки библиотек */

HTMLElements::titleSection('Подборки библиотек');

$compilation = [
    new AreaModel(
        'cocoacontrols.com',
        null,
        'Подборка UI-библиотек, сразу с превью.',
        'https://www.cocoacontrols.com',
        true
    ),
    new AreaModel(
        'swiftpackageindex.com',
        null,
        'Поиск SPM-библиотек. Автор отбирает библиотеки.',
        'https://swiftpackageindex.com',
        true
    ),
    new AreaModel(
        'iosdev.tools',
        null,
        'Короткий обзор библиотек в формате новостей.',
        'https://iosdev.tools',
        true
    ),
    new AreaModel(
        'swift.libhunt.com',
        null,
        'Библиотеки разбиты на 74 категории. Есть реклама - мешает в навигации.',
        'https://swift.libhunt.com',
        true
    )
];

HTMLElements::areas($compilation);

/* Мастхев библиотеки */

HTMLElements::titleSection('Мастхев библиотеки');

$frameworks = [
    new AreaModel(
        'Alamofire',
        null,
        'Фасад для сетевых запросов.',
        'https://github.com/Alamofire/Alamofire',
        true
    ),
    new AreaModel(
        'SwiftyJSON',
        null,
        'Будете быстрее разоврачивать значения в `JSON`.',
        'https://github.com/SwiftyJSON/SwiftyJSON',
        true
    ),
    new AreaModel(
        'Nuke',
        null,
        'Использует нативные инструменты чтобы кэшировать изображения.',
        'https://github.com/kean/Nuke',
        true
    ),
    new AreaModel(
        'SPPermissions',
        null,
        'Работа с разрешениями.',
        'https://github.com/ivanvorobei/SPPermissions',
        true
    )
];

HTMLElements::areas($frameworks);

/* Интересные репозитории */

HTMLElements::titleSection('Интересные репозитории');

$repos = [
    new AreaModel(
        'Awesome-iOS',
        null,
        'Подборка библиотек. Репозитории разбиты на 200 категорий. Есть подборки с курсами.',
        'https://github.com/vsouza/awesome-ios',
        true
    ),
    new AreaModel(
        'Awesome iOS ещё один',
        null,
        'Мой сборник библиотек. Есть ' . HTMLElements::embeddedLink('сайт', Constants::$project_awesome_ios_web) . '. Планирую написать приложение.',
        'https://github.com/ivanvorobei/awesome-ios',
        true
    ),
    new AreaModel(
        'GitHub Trends',
        null,
        'Популярные Swift-библиотеки на GitHub.',
        'https://github.com/trending/swift?since=daily&spoken_language_code=',
        true
    )
];

HTMLElements::areas($repos);

/* Вопросы */

HTMLElements::titleSection('Инструменты');

$instruments = [
    new AreaModel(
        'nsdateformatter.com',
        null,
        'Примеры форматирования даты с помощью `DateFormatter`.',
        'https://nsdateformatter.com',
        true
    ),
    new AreaModel(
        'epochconverter.com',
        null,
        'Конвертор `Timestamp`.',
        'https://www.epochconverter.com',
        true
    ),
    new AreaModel(
        'Генератор промо-изображений',
        '',
        'Доступны стили `новое приложение`, `обновление`, `подписка` и `оффер`. Настраивается язык и цвет фона. Есть размеры для сторис, банеры и квадраты.',
        'https://tools.applemediaservices.com/apple-app-store-promote',
        true
    )
];

HTMLElements::areas($instruments);

/* Вопросы */

HTMLElements::titleSection('Вопросы');

$questions = [
    new AreaModel(
        'Stackoverflow',
        null,
        'Чаще всего запрос в гугол приведет вас сюда. Можно задавать свои вопросы. Есть система рейтинга.',
        'https://stackoverflow.com',
        true
    ),
    new AreaModel(
        'Русский Stackoverflow',
        null,
        'Аналог англоязычного портала. Не активен в русском сегменте.',
        'https://ru.stackoverflow.com',
        true
    ),
    new AreaModel(
        'Q&A',
        null,
        'Русский агрегатор вопросов.',
        'https://qna.habr.com',
        true
    )
];

HTMLElements::areas($questions);

HTMLElements::titleSection('На этом всё');

HTMLElements::text(
    'Если вы знаете хорошие ресурсы, ' . HTMLElements::embeddedLink('напишите мне', Constants::$telegram_my). ' чтобы добавить их в статью.'
);

HTMLElements::tutorialFooter($tutorial);