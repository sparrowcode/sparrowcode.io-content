Чтобы ресетнуть приложение для macOS Catalyst, нужно знать эти значения:

- Папку пользователя `ivanvorobei`
- Bundle приложения `io.ivanvorobei.apps.debts`
- Идентификатор AppGroup `group.io.ivanvorobei.apps.debts`.

Будьте внимательны, используйте значения от вашего приложения.

# Очистить UserDefaults

Чтобы удалить дефолтный `UserDefaults`, откройте терминал и введите команду:

```
// Удаляем `UserDefaults` целиком 
defaults delete io.ivanvorobei.apps.debts

// Удаляем из `UserDefaults` по ключу 
defaults delete io.ivanvorobei.apps.debts key
```

Если использовали кастомный домен, вызывайте команду:

```
// Создается так: 
UserDefaults(suiteName: "Custom")

// Удаляется так:
defaults delete Custom
```

# AppGroup

Если используете `AppGroup`, удалите эти папки:

```
/Users/ivanvorobei/Library/Group Containers/group.io.ivanvorobei.apps.debts
/Users/ivanvorobei/Library/Application Scripts/group.io.ivanvorobei.apps.debts
```

Если хранили в дефолтном пути, удалите эту папку:

```
/Users/ivanvorobei/Library/Containers/io.ivanvorobei.apps.debts
```

# База данных Realm

Файлы базы данных `Realm` хранятся как обычные файлы. Они находятся либо в AppGroup, либо в дефолтной папке. Если выполните пункты выше, база данных удалится.
