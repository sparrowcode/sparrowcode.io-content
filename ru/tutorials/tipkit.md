[TipKit](https://developer.apple.com/documentation/tipkit) показывает подсказки. Выглядят вот так:

![Как выглядят подсказки TipKit](https://cdn.sparrowcode.io/tutorials/tipkit/tipkit-example.jpg)

Добавили в iOS 17. Доступен для iOS, iPadOS, macOS, watchOS, watchOS и visionOS.

Используйте TipKit чтобы показать контекстные подсказки, которые выделяют новые, интересные или неиспользуемые функции, о которых пользователи еще не знают.

# Инициализация

Импортируем `TipKit` и в точке входа в приложение вызываем `Tips.configure`:

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
struct PopoverTip: Tip {

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

Вызываем модификатор `popoverTip` к вью, к которой нужно показать подсказку:

```swift
Image(systemName: "heart")
    .popoverTip(PopoverTip(), arrowEdge: .bottom)
```

У Popever-подсказок стрелочка есть всегда, но направление которое вы указали не гарантируется. Как показывается стрелка примеры на скриншоте.

![Всплывающие `Popever` посказки](https://cdn.sparrowcode.io/tutorials/tipkit/popover.png)

## Встраиваемые `Inline`

Inline-подскази меняют лейаут. Ведут себя как вью и не перекрывают интерфейс приложения.

```swift
VStack {
    Image("pug")
        .resizable()
        .scaledToFit()
        .clipShape(RoundedRectangle(cornerRadius: 12))
    TipView(inlineTip)
}
```

![Встроенные подсказки. Можно со стрелкой и без.](https://cdn.sparrowcode.io/tutorials/tipkit/inline-arrow.png)

У Inline-подсказак стрелочка опциональная и ее направление стабильно:

```swift
TipView(inlineTip, arrowEdge: .top)
TipView(inlineTip, arrowEdge: .leading)
TipView(inlineTip, arrowEdge: .trailing)
TipView(inlineTip, arrowEdge: .bottom)
```

## Добавляем кнопку

![Добавляем кнопки](https://cdn.sparrowcode.io/tutorials/tipkit/action.png)

Кнопки прописываются в протоколе в поле `actions`:

```swift
struct PopoverTip: Tip {

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

```swift
TipView(tip) { action in

    if action.id == "reset-password" {
        // Логика по кнопке
    }
}
```

# Закрываем подсказку

Можно нажать на крестик или закрыть кодом:

```swift
inlineTip.invalidate(reason: .actionPerformed)
```

В методе укажите причину, почему закрыли подсказку. Список причин:

`.actionPerformed` - пользователь выполнил действие, описанное в подсказке
`.displayCountExceeded` - подсказка показана максимальное количество раз
`.actionPerformed` - пользователь явное закрыл подсказку

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

![Правила](https://cdn.sparrowcode.io/tutorials/tipkit/rules.png)

# `TipKit` в Preview

Когда дебажите в Preview и закроете подсказу, то она больше не покажется  — это не удобно. Чтобы подсказки появлялись каждый раз, нужно сбросить хранилище данных:

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

# `UIKit`

## Инициализация

```swift
func application(_ application: UIApplication, didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?) -> Bool {
    Task {
    try? Tips.configure([
        .displayFrequency(.immediate),
        .datastoreLocation(.applicationDefault)])
    }
    return true
}
```

## Создаем подсказку

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

### Всплывающие `Popover`

```swift
override func viewDidAppear(_ animated: Bool) {
        super.viewDidAppear(animated)
        
        Task { @MainActor in
            for await shouldDisplay in FavoritesTip().shouldDisplayUpdates {

                if shouldDisplay {
                    let controller = TipUIPopoverViewController(FavoritesTip(), sourceItem: favoriteButton)
                    present(controller, animated: true)
                } else if presentedViewController is TipUIPopoverViewController {
                    dismiss(animated: true)
                }
            }
        }
    }
```

### Встраиваемые `Inline`

```swift
if shouldDisplay {
    let tipView = TipUIView(FavoritesTip())
    view.addSubview(tipView)
} else if let tipView = view.subviews.first(where: { $0 is TipUIView }) {
    tipView.removeFromSuperview()
}
```

### Добавляем кнопку

```swift
struct PopoverTip: Tip {

    var title: Text {...}
    var message: Text? {...}
    var image: Image? {...}
    
    var actions: [Action] {
        Action(id: "reset-password", title: "Сбросить Пароль")
        Action(id: "not-reset-password", title: "Отменить сброс")
    }
}
```

```swift
if shouldDisplay {
    let tipView = TipUIView(ActionsTip()) { action in
        guard action.id == "reset-password" else { return }
        let controller = TipKitViewController()
        self.present(controller, animated: true)
    }
    view.addSubview(tipView)
} else if let tipView = view.subviews.first(where: { $0 is TipUIView }) {
    tipView.removeFromSuperview()
}
```

## Закрываем подсказку

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

## Правила для подсказок, когда показывать

```swift
if shouldDisplay {
    let controller = TipUIPopoverViewController(FavoriteRuleTip(), sourceItem: favoriteButton)
    present(controller, animated: true)
} else if presentedViewController is TipUIPopoverViewController {
    dismiss(animated: true)
}
```

```swift
@objc func favoriteButtonPressed() {
    FavoriteRuleTip.hasViewedTip = true
}
```

# `TipKit` в Preview