Чтобы ресетнуть приложение для macOS Catalyst, нужно знать имя папки пользователя, бандл приложения, AppGroup и suit для UserDefaults (если используете). В туториале я буду использовать следующие примеры:

Папка пользователя `ivanvorobei`, bundle приложения `by.ivanvorobei.apps.debts`, идентификатор AppGroup `group.by.ivanvorobei.apps.debts`.

Будьте внимательны, используйте значения от вашего приложения.

## Очистить UserDefaults

Если вы хотите удалить дефолтный `UserDefaults`, откройте терминал и введите команду:

```swift
// Удаляем `UserDefaults` целиком 
defaults delete by.ivanvorobei.apps.debts

// Удаляем из `UserDefaults` по ключу 
defaults delete by.ivanvorobei.apps.debts key
```

Если вы использовали кастомный домен, вызывайте эту команду:

```swift
// Создается вот так
// UserDefaults(suiteName: "Custom")
defaults delete suit.name
```

## AppGroup

Если вы используйте `AppGroup`, нужно удалить следующие папки:

```swift
/Users/ivanvorobei/Library/Group Containers/group.by.ivanvorobei.apps.debts
/Users/ivanvorobei/Library/Application Scripts/group.by.ivanvorobei.apps.debts
```

Если хранили в дефолтном пути, то эта папка:

```swift
/Users/ivanvorobei/Library/Containers/by.ivanvorobei.apps.debts
```

## База данных Realm

Файлы базы данных `Realm` хранятся как обычные файлы. Они находятся либо в AppGroup, либо в дефолтной папке. Выполнив пункты выше, база данных будет удалена.

## Ещё папки

Мне удалось найти еще папки, но для чего они не знаю. Оставлю пути здесь:

```swift
/Users/ivanvorobei/Library/Application Scripts/group.by.ivanvorobei.apps.debts
/Users/ivanvorobei/Library/Developer/Xcode/Products/by.ivanvorobei.apps.debts (macOS)
```

Если вы знаете для чего они или знаете еще папки, дайте мне знать - я обновлю туториал.

