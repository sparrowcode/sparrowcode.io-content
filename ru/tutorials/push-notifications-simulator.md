# Отправка push-уведомлений на симуляторе

Учитывайте что вам нужен запрос разрешений даже для симулятора.

# Перетаскиваем APNS файла

Файл APNS - Apple Push Notification Service, это обычный JSON.

![Так выглядит фаил apns](https://cdn.sparrowcode.io/tutorials/push-notifications-simulator/apns-file.png)

В нем указыватся что будет в пуше - например текстовое сообщение, звуковой сигнал и число на бейдже иконки. Список всех доступных ключей пожно посмотреть [тут](https://developer.apple.com/documentation/usernotifications/unnotificationcontent).

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

Самый простой способ запустить push на симуляторе, просто перетащить файл apns в симулятор. Нужно обязательно указать поле `Target Bundle`в apns файле.

```JSON
{
   "aps" : {
      "alert" : {
         "title" : "Game Request",
         "body" : "Bob wants to play poker"
      }
   },
   "Simulator Target Bundle": "com.TestPushNotifications"
}
```

Иначе получите ошибку:

![Ошибка, потому что не указан Target Bundle](https://cdn.sparrowcode.io/tutorials/push-notifications-simulator/invalid-notification.png)

Если все заполненно правильно, придет push:

![Пуш уведомление](https://cdn.sparrowcode.io/tutorials/push-notifications-simulator/push.png)

# Работа с терминалом:

Все это можно сделать и через командную стороку.

## Настройка simctl

Проверьте в настройках Xcode что `Command Line Tools выбрана`:

![Включаем Command Line Tools](https://cdn.sparrowcode.io/tutorials/push-notifications-simulator/command-line-tools.png)

## Работаем с xcrun

Для запуска пуша спользуется команда:

```console
xcrun simctl push <id simulator> <bundle id> <path to apns file>
```

`Bundle id` - это бандл вашего прилоджения.Чтобы Узнать `id simulator` используется команда:

```console
xcrun simctl list
```

Она покажет список всех симуляторов и их id. Обратите внимание, у запущенного симулятора будет указанно **Booted**

![Список всех доступных симуляторов](https://cdn.sparrowcode.io/tutorials/push-notifications-simulator/id-simulator-list.png)

## Запускаем push-уведомления

Когда есть запущенный симулятор, можно спользовать **booted** в место ключа.

Запускаем с id симулятора:

```console
xcrun simctl push 4D1C144E-7C68-484D-894D-CF17928D3D3A com.TestPushNotifications payload.apns
```

Запускаем через booted:

```console
xcrun simctl push booted com.TestPushNotifications payload.apns
```

Если все сделано сделанно правино получите такое сообщение:

![Сообщение об успешной отравки push-уведомления](https://cdn.sparrowcode.io/tutorials/push-notifications-simulator/notification-sent.png)

# Настройка и конфигурация

В точку входа приложения импортируем `UserNotifications` и добавляем **AppDelegate**. В методе **didFinishLaunchingWithOptions** включаем разрешение для push-уведомдений.

```swift
class AppDelegate: NSObject, UIApplicationDelegate {

   func application(_ application: UIApplication, didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey : Any]? = nil) -> Bool {
    
      UNUserNotificationCenter.current().requestAuthorization(options: [.alert, .sound, .badge]) {(granted, error) in
         print("Permission granted: \(granted)")
      }

    return true
   }
}
```

# Сброс разрешений

Если во время тестирования нужно сбросить разрешения на push-уведомления, просто удалите и переустановите приложение.