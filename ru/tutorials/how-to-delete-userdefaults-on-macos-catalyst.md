Чтобы ресетнуть приложение для macOS Catalyst, нужно знать имя папки пользователя, бандл приложения, AppGroup и suit для UserDefaults — если используете. В туториале я буду использовать такие примеры: папку пользователя `ivanvorobei`, bundle приложения `by.ivanvorobei.apps.debts`, идентификатор AppGroup `group.by.ivanvorobei.apps.debts`.

Будьте внимательны, используйте значения от вашего приложения.

## Очистить UserDefaults

Если хотите удалить дефолтный `UserDefaults`, откройте терминал и введите команду:

```swift
// Удаляем `UserDefaults` целиком 
defaults delete by.ivanvorobei.apps.debts

// Удаляем из `UserDefaults` по ключу 
defaults delete by.ivanvorobei.apps.debts key
```

Если использовали кастомный домен, вызывайте команду:

```swift
// Создается вот так
// UserDefaults(suiteName: "Custom")
defaults delete suit.name
```

## AppGroup

Если используете `AppGroup`, удалите эти папки:

```swift
/Users/ivanvorobei/Library/Group Containers/group.by.ivanvorobei.apps.debts
/Users/ivanvorobei/Library/Application Scripts/group.by.ivanvorobei.apps.debts
```

Если хранили в дефолтном пути, удалите эту папку:

```swift
/Users/ivanvorobei/Library/Containers/by.ivanvorobei.apps.debts
```

## База данных Realm

Файлы базы данных `Realm` хранятся как обычные файлы. Они находятся либо в AppGroup, либо в дефолтной папке. Если выполните пункты выше, база данных удалится.

## Ещё папки

Я нашёл ещё папки, но не знаю, для чего они нужны. Оставлю пути здесь:

```swift
/Users/ivanvorobei/Library/Application Scripts/group.by.ivanvorobei.apps.debts
/Users/ivanvorobei/Library/Developer/Xcode/Products/by.ivanvorobei.apps.debts (macOS)
```

Если вы знаете, для чего они, или знаете ещё папки, дайте знать — я обновлю туториал.
