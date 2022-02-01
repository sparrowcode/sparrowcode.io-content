## Что Нового

Добавил поддержу SPM. Появился новый статус у разрешений `.notDetermined`. Добавил локализацию, на помент написания статьи SPPermissions поддерживает русский, английский и арабский. Добавил поддержку RTL для арабских языков. Изменил струкрутуру проекта.

## Установка через Swift Package Manager

Главной мотивацией была поддержка SPM. Импортируйте пакет:

```swift
https://github.com/ivanvorobei/SPPermissions
```

Выберите только нужные разрешения:

![Swift Package Manager Install Xcode Preivew](https://cdn.ivanvorobei.by/websites/sparrowcode.io/release-sppermissions-v6/spm-install-preview.png)

SPM требует определенный импорт файлов. Вы должны импортировать базовый модуль, он отвечает за интерфейсы, логику и локализацию. Следом импортируйте модели разрещений, которые нужны:

```swift
import SPPermissions
import SPPermissionsCamera
import SPPermissionsContacts
```

Не нужно испортировать все разрешения. Библиотека разбита на модули, потому что если вы добавите весь код в проект - эпл отклонит приложение. Добавляете только используемые разрешения.

Для Cocoapods без изменений.

## Синтаксис

Теперь разрешения это не enum, а проперти внутри класса `SPPermissions.Permission`

```swift
SPPermissions.Permission.camera
```

Это не должно повлиять на синтаксис, если вы использовали сокращенную форму.

## DataSource и Delegate

Метод для передачи текста для алерта (когда разрешение заблокировано) переехал в Data Source.

```swift
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
```

Методы делегата остались теми же, но переименованы. Если вы использовали эти протоколы в версии 5.x, обновите их сейчас. Методы являются опциональными, вы не увидите ошибку об изменении синтаксиса.

```swift
extension Controller: SPPermissionsDelegate {
    
    func didHidePermissions(_ permissions: [SPPermissions.Permission]) {}
    func didAllowPermission(_ permission: SPPermissions.Permission) {}
    func didDeniedPermission(_ permission: SPPermissions.Permission) {}
}
```

Подробную документацию можно найти на [странице библиотеки](https://github.com/ivanvorobei/SPPermissions).

