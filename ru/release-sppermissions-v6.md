<?php

use App\HTMLElements;
use App\TutorialModel;
use App\ButtonModel;

/** @var TutorialModel $tutorial */

HTMLElements::tutorialHeader(
    $tutorial,
    [
        new ButtonModel(
            'Открыть на github.com',
            'https://github.com/ivanvorobei/SPPermissions',
            true
        )
    ]
);

HTMLElements::titleSection(
    'Что Нового'
);
HTMLElements::text(
    "Добавил поддержу SPM. Появился новый статус у разрешений `.notDetermined`. Добавил локализацию, на помент написания статьи SPPermissions поддерживает русский, английский и арабский. Добавил поддержку RTL для арабских языков. Изменил струкрутуру проекта."
);
HTMLElements::titleSection(
    'Установка через Swift Package Manager'
);
HTMLElements::text(
    "Главной мотивацией была поддержка SPM. Импортируйте пакет:"
);
HTMLElements::blockCode("
https://github.com/ivanvorobei/SPPermissions
");

HTMLElements::text(
    "Выберите только нужные разрешения:"
);

HTMLElements::image(
    "Swift Package Manager Install Xcode Preivew",
    "https://cdn.ivanvorobei.by/websites/sparrowcode.io/release-sppermissions-v6/spm-install-preview.png",
    85
);

HTMLElements::text(
    "SPM требует определенный импорт файлов. Вы должны импортировать базовый модуль, он отвечает за интерфейсы, логику и локализацию. Следом импортируйте модели разрещений, которые нужны:"
);

HTMLElements::blockCode("
import SPPermissions
import SPPermissionsCamera
import SPPermissionsContacts
");

HTMLElements::text(
    "Не нужно испортировать все разрешения. Библиотека разбита на модули, потому что если вы добавите весь код в проект - эпл отклонит приложение. Добавляете только используемые разрешения."
);

HTMLElements::text(
    "Для Cocoapods без изменений."
);

HTMLElements::titleSection(
    'Синтаксис'
);

HTMLElements::text(
    "Теперь разрешения это не enum, а проперти внутри класса `SPPermissions.Permission`"
);

HTMLElements::blockCode("
SPPermissions.Permission.camera
");

HTMLElements::text(
    "Это не должно повлиять на синтаксис, если вы использовали сокращенную форму."
);

HTMLElements::titleSection(
    'DataSource и Delegate'
);

HTMLElements::text(
    "Метод для передачи текста для алерта (когда разрешение заблокировано) переехал в Data Source."
);

HTMLElements::blockCode("
extension Controller: SPPermissionsDataSource {
    
    func configure(_ cell: SPPermissionsTableViewCell, for permission: SPPermissions.Permission) -> SPPermissionsTableViewCell {
    
        // Here you can customise cell, like texts or colors.
        
        return cell
    }
    
    func deniedAlertTexts(for permission: SPPermissions.Permission) -> SPPermissionDeniedAlertTexts? {
    
        // Вы можете вернуть кастомные текста. 
        // Если вернете `nil`, то алерт не покажется.
        // Я вернул дефолтные.
        
        return .default
    }
}
");

HTMLElements::text(
    "Методы делегата остались теми же, но переименованы. Если вы использовали эти протоколы в версии 5.x, обновите их сейчас. Методы являются опциональными, вы не увидите ошибку об изменении синтаксиса."
);

HTMLElements::blockCode("
extension Controller: SPPermissionsDelegate {
    
    func didHidePermissions(_ permissions: [SPPermissions.Permission]) {}
    func didAllowPermission(_ permission: SPPermissions.Permission) {}
    func didDeniedPermission(_ permission: SPPermissions.Permission) {}
}
");

HTMLElements::text(
    "Подробную документацию можно найти на " . HTMLElements::embeddedLink("странице библиотеки", "https://github.com/ivanvorobei/SPPermissions") . "."
);

HTMLElements::tutorialFooter($tutorial);
