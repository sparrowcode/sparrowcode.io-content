Чтобы получить root-контроллер, нужно глянуть на иерархию приложения.

# Scenes (Сцены) для iOS 13 и выше

Наглядно UI-архитектура с iOS 13 на картинке:

![Схема `UIWindowScene` c iOS 13 и выше.](https://cdn.sparrowcode.io/tutorials/how-to-get-root-view-controller/uiwindowscene.jpg)

На экране может быть несколько сцен, а у сцен несколько окон. Для каждого окна свой root-контроллер, а это значит что у приложения может быть больше одного root-контроллера.

Допустим вы ищите root-контроллер только для активной сцены, отфильтруем их:

```swift
// Window есть только у `UIWindowScene`:
let windowScenes = UIApplication.shared.connectedScenes.compactMap { $0 as? UIWindowScene }

// Получаем активные:
let activeScenes = windowScenes.filter { $0.activationState == .foregroundActive }
```

Теперь у сцены можно получить `keyWindow`:

```swift
let firstActiveScene = activeScene.first
let keyWindow = firstActiveScene?.keyWindow?.rootViewController
```

Но на экране может быть два равнозначных окна. Например, две заметки в Split-режиме на iPad. В переборе вам нужно **выбрать главную сцену и контроллер вручную**. Сделать это можно через проверку типа:

```swift
// Получаем сцену по классу делегата:
let scene = windowScenes.first(where: { ($0.delegate as? RootSceneDelegate) != nil })

// Перебираем окна с нужным root-контроллером:
let controller = scene?.windows.first(where: { $0.rootViewController as? RootSplitController != nil })
```

> Начиная с iOS 13 главного root-контроллера нет. Вы сами решаете какой из них главный.

# Windows (Окна) для iOS 12 и ниже

До iOS 13 были только Window. Root-контроллер можно получить однозначно - с ним запускается приложение:

![Схема `UIWindow` для iOS 12 и ниже.](https://cdn.sparrowcode.io/tutorials/how-to-get-root-view-controller/uiwindow.jpg)

Чтобы получить root, нужно получить key-window и обратится к `rootViewController`:

```swift
// Главное окно -> главный контроллер
UIApplication.shared.keyWindow?.rootViewController
```

Альтернативный способ обратится к массиву окон и взять первое:

```swift
UIApplication.shared.windows.first?.rootViewController
```

Первое окно всегда было root, потому что с ним запускалось приложение. 

> Проперти `UIApplication.shared.keyWindow` и `UIApplication.shared.windows` deprecated. Но если ваше приложение не использует сцены, то варнинга не будет. 

# Для SwiftUI

Вы можете сохранить root-view и передать её как параметр. Но если вы хотите доступ через UIKit, то вызов `UIApplication` работает и для SwiftUI. 

Если вы хотите развернуть root-контроллер красиво, например, получить `UISplitViewController` из UIKit в коде SwiftUI, попробуйте библиотеку SwiftUI Introspect:

[SwiftUI Introspect](https://github.com/siteline/swiftui-introspect): Introspect underlying UIKit/AppKit components from SwiftUI.

Так например для root-вью `NavigationSplitView`:

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


