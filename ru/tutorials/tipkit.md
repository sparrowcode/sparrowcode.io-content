[TipKit](https://developer.apple.com/documentation/tipkit) показывает подсказки. Выглядят вот так:

![Как выглядят подсказки TipKit](https://cdn.sparrowcode.io/tutorials/tipkit/tipkit-example.jpg)

Добавили в iOS 17. Доступен для iOS, iPadOS, macOS, watchOS, watchOS и visionOS.

Используйте TipKit чтобы показать контекстные подсказки, которые выделяют новые, интересные или неиспользуемые функции, о которых пользователи еще не знают.

# Инициализация

Импортируем `TipKit` и в точке входа в приложение вызываем `Tips.configure`:

`SwiftUI`

```swift
import SwiftUI
import TipKit

@main
struct TipKitExampleApp: App {

    var body: some Scene {
        WindowGroup {
            TipKitDemo()
                .task {
                    try? Tips.configure([
                        .displayFrequency(.immediate),
                        .datastoreLocation(.applicationDefault)
                    ])
                }
        }
    }
}
```

`UIKit`

В AppDelegate добавляем `Tips.configure`

```swift
func application(_ application: UIApplication, didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?) -> Bool {

    try? Tips.configure([
        .displayFrequency(.immediate),
        .datastoreLocation(.applicationDefault)])

    return true
}
```

`displayFrequency` определяет как часто показывать подсказку:

- immediate - будут отображаться сразу
- hourly - ежечасно
- daily - ежедневно
- weekle - еженедельно
- monthly - ежемесячно

`datastoreLocation` - хранилище данных подсказок. 
По умолчанию используется `.applicationDefault`, это папка `support` на устройсте. `.url` используется чтобы указать свой путь. Чтобы использовать одно хранилище для группы приложений `.groupContainer`.

# Создаем подсказку

Протокол Tip определяет контент и когда показывать подсказку. У подсказки есть обязательное поле `title` и опциональные `message` и `image`.

```swift
struct FavoritesTip: Tip {

    var title: Text {
        Text("Добавить в избранное")
    }

    var message: Text? {
        Text("Этот пользователь будет добавлен в папку избранное.")
    }

    var image: Image? {
        Image(systemName: "heart")
    }
}
```

Есть два вида подсказок **Popover** показывается поверх интерерфейса, а **Inline** встраивается как обычная вью.

## Всплывающие `Popover`

`SwiftUI`

Вызываем модификатор `popoverTip` к вью, к которой нужно показать подсказку:

```swift
Image(systemName: "heart")
    .popoverTip(FavoritesTip(), arrowEdge: .bottom)
```

`UIKit`

Прослушиваем подсказки через асинхронный метод `.shouldDisplayUpdates`. Используем `TipUIPopoverViewController`, который принимает подсказку и вью на которой будет вызвана эта посказка. Для закрытия используем `dismiss`

```swift
override func viewDidAppear(_ animated: Bool) {
    super.viewDidAppear(animated)
    
    Task { @MainActor in
        for await shouldDisplay in FavoritesTip().shouldDisplayUpdates {

            if shouldDisplay {
                let popoverController = TipUIPopoverViewController(FavoritesTip(), sourceItem: favoriteButton)
                present(popoverController, animated: true)
            } else if presentedViewController is TipUIPopoverViewController {
                dismiss(animated: true)
            }
        }
    }
```

У Popever-подсказок стрелочка есть всегда, но направление которое вы указали не гарантируется, в UIKit направление не доступно. Как показывается стрелка примеры на скриншоте.

![Всплывающие `Popever` посказки](https://cdn.sparrowcode.io/tutorials/tipkit/popover.png)

## Встраиваемые `Inline`

Inline-подскази меняют лейаут. Ведут себя как вью и не перекрывают интерфейс приложения.

`SwiftUI`

```swift
VStack {
    Image("pug")
        .resizable()
        .scaledToFit()
        .clipShape(RoundedRectangle(cornerRadius: 12))
    TipView(FavoritesTip())
}
```

`UIKit`

Добавляем подсказку как сабвью используя `TipUIView`. Удаляем подсказку `.removeFromSuperview()` 

```swift
Task { @MainActor in
    for await shouldDisplay in FavoritesTip().shouldDisplayUpdates {

        if shouldDisplay {
            let tipView = TipUIView(FavoritesTip())
            view.addSubview(tipView)
        } else if let tipView = view.subviews.first(where: { $0 is TipUIView }) {
            tipView.removeFromSuperview()
        }
    }
}
```

![Встроенные подсказки. Можно со стрелкой и без.](https://cdn.sparrowcode.io/tutorials/tipkit/inline-arrow.png)

У Inline-подсказак стрелочка опциональная и ее направление стабильно:

```swift
// SwiftUI
TipView(inlineTip, arrowEdge: .top)
TipView(inlineTip, arrowEdge: .leading)
TipView(inlineTip, arrowEdge: .trailing)
TipView(inlineTip, arrowEdge: .bottom)

// UIKit
TipUIView(FavoritesTip(), arrowEdge: .bottom)
```

## Добавляем кнопку

![Добавляем кнопки](https://cdn.sparrowcode.io/tutorials/tipkit/actions.png)

Кнопки прописываются в протоколе в поле `actions`:

```swift
struct ActionsTip: Tip {

    var title: Text {...}
    var message: Text? {...}
    var image: Image? {...}
    
    var actions: [Action] {
        Action(id: "reset-password", title: "Сбросить Пароль")
        Action(id: "not-reset-password", title: "Отменить сброс")
    }
}
```

`id` определяет какую кнопку нажали:

`SwiftUI`

```swift
TipView(tip) { action in

    if action.id == "reset-password" {
        // Логика по кнопке
    }
}
```

`UIKit`

```swift
Task { @MainActor in
    for await shouldDisplay in ActionsTip().shouldDisplayUpdates {

        if shouldDisplay {
            let tipView = TipUIView(ActionsTip()) { action in

                if action.id == "reset-password" {
                    // Логика по кнопке
                }

                let controller = TipKitViewController()
                self.present(controller, animated: true)
            }
            view.addSubview(tipView)
        } else if let tipView = view.subviews.first(where: { $0 is TipUIView }) {
            tipView.removeFromSuperview()
        }
    }
}
```

# Закрываем подсказку

Можно нажать на крестик или закрыть кодом:

Работает одинакого для swiftUI и UIkit

```swift
inlineTip.invalidate(reason: .actionPerformed)
```

В методе укажите причину, почему закрыли подсказку. Список причин:

`.actionPerformed` - пользователь выполнил действие, описанное в подсказке
`.displayCountExceeded` - подсказка показана максимальное количество раз
`.actionPerformed` - пользователь явное закрыл подсказку


// Под вопросом ???

`UIKit`

Для стандартного поведения закрытия подсказки не из кода:

```swift
//Popover
if presentedViewController is TipUIPopoverViewController {
    dismiss(animated: true)
}
```

```swift
// Inline
if let tipView = view.subviews.first(where: { $0 is TipUIView }) {
    tipView.removeFromSuperview()
}
```

# Правила для подсказок, когда показывать

Когда показывать подсказку настраивается с помощью параметров

```swift
struct FavoriteRuleTip: Tip {

    var title: Text {...}
    var message: Text? {...}

    @Parameter
    static var hasViewedTip: Bool = false

    var rules: [Rule] {
        #Rule(Self.$hasViewedTip) { $0 == true }
    }
}
```

`Rule` проверяет значение переменной `hasViewedTip`, когда значение равно true, подсказка отобразится.

`SwiftUI`

```swift
struct ParameterRule: View {
    
    var body: some View {
        VStack {
            Spacer()
            Button("Rule"){
                FavoriteRuleTip.hasViewedTip = true
            }
            .buttonStyle(.borderedProminent)
            .popoverTip(FavoriteRuleTip(), arrowEdge: .top)
        }
    }
}
```

`UIKit`

```swift
Task { @MainActor in
    for await shouldDisplay in FavoriteRuleTip().shouldDisplayUpdates {

        if shouldDisplay {
            let rulesController = TipUIPopoverViewController(FavoriteRuleTip(), sourceItem: favoriteButton)
            present(rulesController , animated: true)
        } else if presentedViewController is TipUIPopoverViewController {
            dismiss(animated: true)
        }
    }
}
```

```swift
@objc func favoriteButtonPressed() {
    FavoriteRuleTip.hasViewedTip = true
}
```

![Правила](https://cdn.sparrowcode.io/tutorials/tipkit/rules.png)

# `TipKit` в Preview

Когда дебажите в Preview и закроете подсказу, то она больше не покажется  — это не удобно. Чтобы подсказки появлялись каждый раз, нужно сбросить хранилище данных:

`SwiftUI`

```swift
#Preview {
    TipKitDemo()
        .task {
        
            // Cбрасываем хранилище
            try? Tips.resetDatastore()
            
            // Конфигурируем
            try? Tips.configure([
                .displayFrequency(.immediate),
                .datastoreLocation(.applicationDefault)
            ])
        }
}
```

`UIKit`

Добавить в AppDelegate:

```swift
try? Tips.resetDatastore()
```