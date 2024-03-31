[TipKit](https://developer.apple.com/documentation/tipkit) нужен чтобы показать контекстные подсказки. Они выделяют новые или неиспользуемые функции, о которых пользователь еще не знает. Выглядят вот так:

![Как выглядят подсказки TipKit](https://cdn.sparrowcode.io/tutorials/tipkit/tipkit-example.jpg)

Добавили в iOS 17. Доступен для iOS, iPadOS, macOS, watchOS, watchOS и visionOS.

# Инициализация

Импортируем `TipKit` и в точке входа в приложение вызываем `Tips.configure`:

**Для SwiftUI**

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

**Для UIKit** в AppDelegate добавляем `Tips.configure`

```swift
func application(_ application: UIApplication, didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?) -> Bool {

    try? Tips.configure([
        .displayFrequency(.immediate),
        .datastoreLocation(.applicationDefault)])
    return true
}
```

`displayFrequency` определяет как часто показывать подсказку:

immediate - будут отображаться сразу. Есть варианты показа - ежечасно, ежедневно, еженедельно и ежемесячно.

`datastoreLocation` - хранилище данных подсказок. Это может быть: 

1. `.applicationDefault` - это папка `support`. Она лежит в песочнице приложения, каталоге Data Container.

2. `.url` - указать свой путь. 

3. `.groupContainer` - чтобы использовать одно хранилище для группы приложений.

По умолчанию используется `.applicationDefault`.

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

**Для SwiftUI** Вызываем модификатор `popoverTip` у вью, к которой нужно показать подсказку

```swift
Image(systemName: "heart")
    .popoverTip(FavoritesTip(), arrowEdge: .bottom)
```

**В UIKit** прослушиваем подсказки через асинхронный метод. Если подсказка сделана правильно, в `shouldDisplay` будет значение true. Добавляем popover контроллер, который принимает подсказку и вью на которой будет вызвана эта посказка.

```swift
override func viewDidAppear(_ animated: Bool) {
    super.viewDidAppear(animated)
    
    Task { @MainActor in
        for await shouldDisplay in FavoritesTip().shouldDisplayUpdates {

            if shouldDisplay {
                let popoverController = TipUIPopoverViewController(FavoritesTip(), sourceItem: favoriteButton)
                present(popoverController, animated: true)
            }
            //не работает крестик, все слодно. Читайте в разделе Закрываем подсказку
        }
    }
```

У `Popever`-подсказок стрелочка есть всегда, но указанное направление не гарантируется, в UIKit направление не доступно.

Примеры как позывается стрелка:

![Всплывающие `Popever` посказки](https://cdn.sparrowcode.io/tutorials/tipkit/popover.png)

## Встраиваемые `Inline`

`Inline`-подскази меняют лейаут. Ведут себя как вью и не перекрывают интерфейс приложения.

**SwiftUI**

```swift
VStack {
    Image("pug")
        .resizable()
        .scaledToFit()
        .clipShape(RoundedRectangle(cornerRadius: 12))
    TipView(FavoritesTip())
}
```

**UIKit**

```swift
Task { @MainActor in
    for await shouldDisplay in FavoritesTip().shouldDisplayUpdates {

        if shouldDisplay {
            let tipView = TipUIView(FavoritesTip())
            view.addSubview(tipView)
        }
        //не работает крестик, все слодно. Читайте в разделе Закрываем подсказку
    }
}
```

![Встроенные подсказки. Можно со стрелкой и без.](https://cdn.sparrowcode.io/tutorials/tipkit/inline-arrow.png)

У `Inline`-подсказак стрелочка опциональная и ее направление работает нормально:

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

В подсказках есть кнопки, чтобы расширить их возможности.

![Добавляем кнопки](https://cdn.sparrowcode.io/tutorials/tipkit/actions.png)

Кнопки прописываются в протоколе, поле `actions`:

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

**SwiftUI**

```swift
TipView(tip) { action in

    if action.id == "reset-password" {
        // Логика по кнопке
    }
}
```

**UIKit**

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
        }
    }
}
```

# Закрываем подсказку

Можно нажать на крестик или закрыть кодом, работает одинакого для SwiftUI и UIKit:

```swift
inlineTip.invalidate(reason: .actionPerformed)
```

В методе укажите причину, почему закрыли подсказку. Список причин:

`.actionPerformed` - пользователь выполнил действие, описанное в подсказке
`.displayCountExceeded` - подсказка показана максимальное количество раз
`.actionPerformed` - пользователь явное закрыл подсказку

**В UIKit** чтобы заработал крестик, нужно работать как с обычным контроллером или вью.

 В `popover`-подсказке нужно закрыть контроллер:

```swift
//Popover
if presentedViewController is TipUIPopoverViewController {
    dismiss(animated: true)
}
```

Для `inline`-подсказки нужно удалить вью:

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

**SwiftUI**

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

**UIKit**

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

**Для UIKit** Добавить в AppDelegate:

```swift
    try? Tips.resetDatastore()
```

В превью на UIKit не сбрасывается.

> Не забудьте убрать resetDatastore, иначе в релизе подсказки будут постоянно показываться.