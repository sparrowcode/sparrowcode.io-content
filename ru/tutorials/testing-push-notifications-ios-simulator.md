Перед тем как тестировать push-уведомления на симуляторе, нужно получить разрешение от пользователя. Как запросить разрешение написано в конце туториала. На симуляторе можно тестировать как обычные, так и Rich-уведомления, это которые с картинками, звуками и кнопками-действиями.

> Apple Push Notification Service-сервер присылает устройствам файл c контентом уведомления. Чтобы тестировать пуш-уведомления, можно сэмулировать этот запрос
 
Можно это сделать через json-файл с данными, или через терминал.

# Перетащить json-файла

Создаем файл с данными для пуша. Здесь я добавлю текст, звук и число в бейдже иконки приложения:

```JSON
{
   "aps" : {
      "alert" : {
         "title" : "Game Request",
         "body" : "Bob wants to play poker"
      },
      "badge" : 9,
      "sound" : "bingbong.aiff"
   }
}
```

Вы можете указать больше контента, например, картинку или действия. Все доступные ключи для push-уведомлений [по ссылке](https://developer.apple.com/documentation/usernotifications/unnotificationcontent).

Теперь в файл нужно добавить `Simulator Target Bundle`, чтобы симулятор понимал какому таргету прилетает пуш:

```JSON
{
   "aps" : {
      "alert" : {
         "title" : "Game Request",
         "body" : "Bob wants to play poker"
      }
   },
   "Simulator Target Bundle": "com.bundle.example"
}
```

Если бандл не указали, то получите такую ошибку:

![Ошибка, потому что не указали Target Bundle](https://cdn.sparrowcode.io/tutorials/testing-push-notifications-ios-simulator/invalid-notification.png?v=2)

Если все в порядке, то на симуляторе появится пуш:

![Пуш уведомление](https://cdn.sparrowcode.io/tutorials/testing-push-notifications-ios-simulator/push.png?v=2)

# Через Terminal

В этом способе вы так же используете APNS-файл, но передаете его через терминал. Проверьте в настройках Xcode что `Command Line Tools` установлен, иначе **simctl** будет выдавать ошибку. Если внизу не видно путь, то выберите еще раз версию Xcode:

![Включаем Command Line Tools](https://cdn.sparrowcode.io/tutorials/testing-push-notifications-ios-simulator/command-line-tools.png?v=2)

Для отправки пуша используется команда:

```console
xcrun simctl push <id simulator> <bundle id> <path to apns file>
```

`Bundle id` - это бандл вашего приложения. А чтобы узнать `id simulator` используется команда:

```console
xcrun simctl list
```

Она покажет список всех симуляторов и их id. Обратите внимание, у запущенного симулятора будет указанно *Booted*:

![Список всех доступных симуляторов](https://cdn.sparrowcode.io/tutorials/testing-push-notifications-ios-simulator/id-simulator-list.png?v=2)


Собираем команду с `id симулятора` и вызываем:

```console
xcrun simctl push 4D1C144E-7C68-484D-894D-CF17928D3D3A com.bundle.example payload.apns
```

Если у вас запущен симулятор, то вместо ключа можно указать *Booted*, так пуш автоматически улетит на запущенный симулятор.

Если все сделано правильно получите такое сообщение:

![Сообщение об отправке push-уведомления](https://cdn.sparrowcode.io/tutorials/testing-push-notifications-ios-simulator/notification-sent.png?v=2)

# Разрешения

Чтобы push-уведомления показывались на симуляторе и устройстве, нужно запросить разрешение. Можно это сделать вручную или через нашу библиотеку.

## Запрос разрешения

Импортируем `UserNotifications` и вызываем системный запрос:

```swift
UNUserNotificationCenter.current().requestAuthorization(options: [.alert, .sound, .badge]) {(granted, error) in
   print("Permission Granted: \(granted)")
}
```

Запрашивать нужно в любом месте до отправки уведомлений. Примерно то же самое делает наша библиотека [PermissionsKit](https://github.com/sparrowcode/PermissionsKit) :

```swift
import PermissionsKit

Permission.notification.request {}
```

## Сброс разрешения

Если нужно сбросить разрешение на push-уведомления, достаточно удалить приложение

> Иногда разрешение может остаться даже после переустановки, тогда после удаления подождите минуту и установите снова.