Учитывайте что вам нужен запрос разрешений даже для симулятора.

# Перетаскиваем APNS файла

APNS присылает на телефон файл payload.apns - Apple Push Notification Service. Файл apns Можно сэмулировать с вашего компьютера, ниже показан пример.


В payload.apns указываются данные которые будут в пуше - например текстовое сообщение, звуковой сигнал или число на бейдже иконки. Список всех доступных ключей можно посмотреть [тут](https://developer.apple.com/documentation/usernotifications/unnotificationcontent).

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

![Ошибка, потому что не указан Target Bundle](https://cdn.sparrowcode.io/tutorials/testing-push-notifications-ios-simulator/invalid-notification.png?v=1)

Если все заполненно правильно, придет push:

![Пуш уведомление](https://cdn.sparrowcode.io/tutorials/testing-push-notifications-ios-simulator/push.png?v=1)

# Через с терминал

Вы можете быть apns сервером не только с помощью json файла, но и из командной строки.

Проверьте в настройках Xcode что `Command Line Tools` установлен, иначе **simctl** будет выдавать ошибку. Когда `Command Line Tools` установлен, под ним будет указан путь к Xcode на вашем маке. Если путь не появился, выберите еще раз нужную версию Xcode.

![Включаем Command Line Tools](https://cdn.sparrowcode.io/tutorials/testing-push-notifications-ios-simulator/command-line-tools.png?v=1)

Для запуска пуша спользуется команда:

```console
xcrun simctl push <id simulator> <bundle id> <path to apns file>
```

`Bundle id` - это бандл вашего прилоджения.Чтобы Узнать `id simulator` используется команда:

```console
xcrun simctl list
```

Она покажет список всех симуляторов и их id. Обратите внимание, у запущенного симулятора будет указанно **Booted**

![Список всех доступных симуляторов](https://cdn.sparrowcode.io/tutorials/testing-push-notifications-ios-simulator/id-simulator-list.png?v=1)


Чтобы запустить push-уведомление Когда есть запущенный симулятор, можно спользовать **booted** в место ключа.

Запускаем с `id симулятора`:

```console
xcrun simctl push 4D1C144E-7C68-484D-894D-CF17928D3D3A com.TestPushNotifications payload.apns
```

Запускаем через `booted`:

```console
xcrun simctl push booted com.TestPushNotifications payload.apns
```

Если все сделано сделанно правильно получите такое сообщение:

![Сообщение об успешной отравки push-уведомления](https://cdn.sparrowcode.io/tutorials/testing-push-notifications-ios-simulator/notification-sent.png?v=1)

# Разрешение

Что бы использовать push-уведомления нужно запросить разрешение. Можно сделать это самим или через **PermissionsKit**.

## Запрос

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

Пример использования популярной библиотеки **[PermissionsKit](https://github.com/sparrowcode/PermissionsKit)**

Импортируем `PermissionsKit`, `NotificationPermission` и включаем разрешение для push-уведомдений:

```swift
import PermissionsKit
import NotificationPermission

class AppDelegate: NSObject, UIApplicationDelegate {

   func application(_ application: UIApplication, didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey : Any]? = nil) -> Bool {

      Permission.notification.request {
         print("Permission granted")
      }

    return true
   }
}
```

## Сброс

Если во время тестирования нужно сбросить разрешения на push-уведомления, просто удалите и переустановите приложение.