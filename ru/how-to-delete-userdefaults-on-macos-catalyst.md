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
            'https://developer.apple.com/mac-catalyst/',
            true
        )
    ]
);

HTMLElements::text(
    "Чтобы ресетнуть приложение для macOS Catalyst, нужно знать имя папки пользователя, бандл приложения, AppGroup и suit для UserDefaults (если используете). В туториале я буду использовать следующие примеры:"
);

HTMLElements::text(
    "Папка пользователя `ivanvorobei`, bundle приложения `by.ivanvorobei.apps.debts`, идентификатор AppGroup `group.by.ivanvorobei.apps.debts`."
);

HTMLElements::text(
    "Будьте внимательны, используйте значения от вашего приложения."
);

HTMLElements::titleSection("Очистить UserDefaults");

HTMLElements::text(
    "Если вы хотите удалить дефолтный `UserDefaults`, откройте терминал и введите команду:"
);

HTMLElements::blockCode("
// Удаляем `UserDefaults` целиком 
defaults delete by.ivanvorobei.apps.debts

// Удаляем из `UserDefaults` по ключу 
defaults delete by.ivanvorobei.apps.debts key
");

HTMLElements::text(
    "Если вы использовали кастомный домен, вызывайте эту команду:"
);

HTMLElements::blockCode("
// Создается вот так
// UserDefaults(suiteName: \"Custom\")
defaults delete suit.name
");

HTMLElements::titleSection("AppGroup");

HTMLElements::text(
    "Если вы используйте `AppGroup`, нужно удалить следующие папки:"
);

HTMLElements::blockCode("
/Users/ivanvorobei/Library/Group Containers/group.by.ivanvorobei.apps.debts
/Users/ivanvorobei/Library/Application Scripts/group.by.ivanvorobei.apps.debts
");

HTMLElements::text(
    "Если хранили в дефолтном пути, то эта папка:"
);

HTMLElements::blockCode("
/Users/ivanvorobei/Library/Containers/by.ivanvorobei.apps.debts
");

HTMLElements::titleSection("База данных Realm");

HTMLElements::text(
    "Файлы базы данных `Realm` хранятся как обычные файлы. Они находятся либо в AppGroup, либо в дефолтной папке. Выполнив пункты выше, база данных будет удалена."
);

HTMLElements::titleSection("Ещё папки");

HTMLElements::text(
    "Мне удалось найти еще папки, но для чего они не знаю. Оставлю пути здесь:"
);

HTMLElements::blockCode("
/Users/ivanvorobei/Library/Application Scripts/group.by.ivanvorobei.apps.debts
/Users/ivanvorobei/Library/Developer/Xcode/Products/by.ivanvorobei.apps.debts (macOS)
");

HTMLElements::text(
    "Если вы знаете для чего они или знаете еще папки, дайте мне знать - я обновлю туториал."
);

HTMLElements::tutorialFooter($tutorial);
