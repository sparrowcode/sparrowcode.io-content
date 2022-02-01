<?php

use App\HTMLElements;
use App\TutorialModel;
use App\ButtonModel;

/** @var TutorialModel $tutorial */

HTMLElements::tutorialHeader(
    $tutorial,
    [
        new ButtonModel(
            'developer.apple.com',
            'https://developer.apple.com/app-store/product-page-optimization/',
            true
        )
    ],
    [
        "https://cdn.ivanvorobei.by/websites/sparrowcode.io/product-page-optimization-alternative-icons/google-structured-data/article_16_9.jpg",
        "https://cdn.ivanvorobei.by/websites/sparrowcode.io/product-page-optimization-alternative-icons/google-structured-data/article_16_9.jpg"
    ]
);

HTMLElements::text(
    "С помощью  " . HTMLElements::embeddedLink('Product Page Optimization', 'https://developer.apple.com/app-store/product-page-optimization/') . " вы можете создавать варианты скриншотов, промо-текстов и иконок. Скриншоты и текст добавляются в App Store Connect, а вот иконки добавляет разработчик в Xcode-проект."
);

HTMLElements::text(
    "В документации сказано «поместите иконки в Asset Catalog, отправьте бинарный файл в App Store Connect и используйте SDK». Но как закинуть иконки и что за SDK - не сказали. Давайте разбираться, шаги подкрепил скриншотами."
);

HTMLElements::titleSection("Добавляем иконки в Assets");

HTMLElements::text(
    "Алтернативную иконку делаем в нескольких разрешениях, как и основную. Я использую приложение " . HTMLElements::embeddedLink('AppIconBuilder', 'https://apps.apple.com/app/id1294179975') . ". Неймнг пишем любой, но учтите - имя отобразится в App Store Connect."
);

HTMLElements::image(
    'Добавляем иконки в Assets',
    'https://cdn.ivanvorobei.by/websites/sparrowcode.io/product-page-optimization-alternative-icons/adding-icons-to-assets.png',
    75
);

HTMLElements::titleSection("Настройки в таргете");

HTMLElements::text(
    "Нужен Xcode 13 и выше. Выберите таргет приложения и перейдите на вкладку `Build Settings`. В поиск вставьте `App Icon` и вы увидите секцию `Asset Catalog Compiler`."
);

HTMLElements::image(
    'Настройки в таргете',
    'https://cdn.ivanvorobei.by/websites/sparrowcode.io/product-page-optimization-alternative-icons/adding-settings-to-target.png',
    75
);

HTMLElements::text("Нас интересуют 3 параметра:");

HTMLElements::text(  "`Alternate App Icons Sets` - перечисление названий иконок, которые добавили в каталог.");
HTMLElements::text(  "`Include All App Icon Assets` - установите в `true`, что бы включить альтернативные иконки в сборку.");
HTMLElements::text(  "`Primary App Icon Set Name` - название иконки по умолчанию. Не проверял, но скорее всего альтернативную иконку можно сделать основной.");

HTMLElements::titleSection("Cборка");

HTMLElements::text("Остается собрать приложение и отправить на проверку.");

HTMLElements::important("Альтернативные иконки будут доступны после прохождения ревью.");

HTMLElements::text("Теперь можно собирать разные страницы приложения и создавать ссылки для A/B тестов.");

HTMLElements::tutorialFooter($tutorial);
