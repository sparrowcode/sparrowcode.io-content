To get root control, you need to look at the application hierarchy.

# Scenes for iOS 13 and later

The UI architecture with iOS 13:

![`UIWindowScene` c iOS 13 and above.](https://cdn.sparrowcode.io/tutorials/how-to-get-root-view-controller/uiwindowscene.jpg)

There can be several scenes on the screen, and scenes can have several windows. Each window has its own root controller, which means that an application can have more than one root controller.

Let's say you're only looking for root controllers for the active scene, let's filter them out:

```swift
// Window only has `UIWindowScene`.:
let windowScenes = UIApplication.shared.connectedScenes.compactMap { $0 as? UIWindowScene }

// Getting active:
let activeScenes = windowScenes.filter { $0.activationState == .foregroundActive }
```

The scene have a `keyWindow`:

```swift
let firstActiveScene = activeScene.first
let keyWindow = firstActiveScene?.keyWindow?.rootViewController
```

Но на экране может быть два равнозначных окна. Например, две заметки в Split-режиме на iPad. В переборе вам нужно **выбрать главную сцену и контроллер вручную**. Сделать это можно через проверку типа:

But you can have two equivalent windows on the screen. For example, two notes in Split mode on an iPad. You need to **select the main scene and controller manually**. You can do this through type checking:

```swift
// Get the scene by delegate class:
let scene = windowScenes.first(where: { ($0.delegate as? RootSceneDelegate) != nil })

// Go through the windows with the root controller:
let controller = scene?.windows.first(where: { $0.rootViewController as? RootSplitController != nil })
```

> As of iOS 13, there is no main root controller. You decide which one is the root.

# Windows for iOS 12 and below

Before iOS 13, there were only Window. Root Controller can be obtained unambiguously - the application is launched with it:

![`UIWindow` for iOS 12 and below.](https://cdn.sparrowcode.io/tutorials/how-to-get-root-view-controller/uiwindow.jpg)

To get root, you need to get key-window and access `rootViewController`:

```swift
// Key window -> root controller
UIApplication.shared.keyWindow?.rootViewController
```

An alternative way is to access the array of windows and grab the first one:

```swift
UIApplication.shared.windows.first?.rootViewController
```

The first window was always root, because the application started with it.

> The `UIApplication.shared.keyWindow` and `UIApplication.shared.windows` properties are deprecated. But if your application does not use scenes, there will be no warning.

# For SwiftUI

You can save root-view and pass it as a parameter. But if you want access via UIKit, the `UIApplication.shared` call works for SwiftUI as well.

If you want to get the root controller nicely, such as getting `UISplitViewController` from UIKit in SwiftUI code, try the SwiftUI Introspect framework:

[SwiftUI Introspect](https://github.com/siteline/swiftui-introspect): Introspect underlying UIKit/AppKit components from SwiftUI.

So for example for root view `NavigationSplitView`:

```swift
NavigationSplitView {
    Text("Root")
} detail: {
    Text("Detail")
}
.introspect(.navigationSplitView, on: .iOS(.v16, .v17)) {
    print(type(of: $0)) // Здесь UISplitViewController
}
```


