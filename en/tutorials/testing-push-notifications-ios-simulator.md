Before testing push-notifications on the simulator, you need to get permission from the user. How to request permission is described at the end of the tutorial. You can test both regular and Rich-notifications, which are notifications with pictures, sounds and action-buttons.

> Apple Push Notification Service-server sends a notification content file to devices. To test push notifications, you can simulate this request
 
You can do this through a json-file with data, or through a terminal.

# Drag and drop json-file

Create a file with the data for the push. Here I will add text, sound and number to the application icon badge:

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

You can specify more content, such as a picture or actions. All available keys for push notifications at the [link](https://developer.apple.com/documentation/usernotifications/unnotificationcontent).

Now you need to add `Simulator Target Bundle` to the file, so that the simulator understands which target is getting a push:

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

If the bundle is not specified, you will get this error:

![Error because you did not specify a Target Bundle](https://cdn.sparrowcode.io/tutorials/testing-push-notifications-ios-simulator/invalid-notification.png?v=2)

If all is well, a push will appear on the simulator:

![Push notification](https://cdn.sparrowcode.io/tutorials/testing-push-notifications-ios-simulator/push.png?v=2)

# Through Terminal

In this method you also use the APNS-file, but you pass it through the terminal. Check in the Xcode settings that `Command Line Tools` is set, otherwise **simctl** will give an error. If you can't see the path at the bottom, select the Xcode version again:

![Turn on Command Line Tools](https://cdn.sparrowcode.io/tutorials/testing-push-notifications-ios-simulator/command-line-tools.png?v=2)

The command is used to send a push:

```console
xcrun simctl push <id simulator> <bundle id> <path to apns file>
```

The `Bundle id` is the bundle of your application. And to find out the `id simulator` the command is used:

```console
xcrun simctl list
```

It will show a list of all simulators and their id. Note that a running simulator will have *Booted*:

![List of all available simulators](https://cdn.sparrowcode.io/tutorials/testing-push-notifications-ios-simulator/id-simulator-list.png?v=2)


Collect the command with the `id simulator` and call it:

```console
xcrun simctl push 4D1C144E-7C68-484D-894D-CF17928D3D3A com.bundle.example payload.apns
```

If you have a simulator running, you can specify *Booted* instead of the key, so the push will automatically fly to the running simulator.

If everything is done correctly, you will get this message:

![Message about sending a push-notification](https://cdn.sparrowcode.io/tutorials/testing-push-notifications-ios-simulator/notification-sent.png?v=2)

# Permission

In order for push-notifications to be shown on the simulator and the device, you need to request permission. You can do this manually or via our library.

## Permission request

Import `UserNotifications` and invoke the system query:

```swift
UNUserNotificationCenter.current().requestAuthorization(options: [.alert, .sound, .badge]) {(granted, error) in
   print("Permission Granted: \(granted)")
}
```

Requests need to be made anywhere before notices are sent. This is roughly what our library does [PermissionsKit](https://github.com/sparrowcode/PermissionsKit) :

```swift
import PermissionsKit

Permission.notification.request {}
```

## Permission reset

If you need to reset the permission for push-notifications, all you need to do is uninstall the app

> Sometimes the permission may remain even after reinstalling, then after uninstalling wait a minute and install again.