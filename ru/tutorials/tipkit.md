С помощью TipKit разработчики обращают внимание пользователей на новые фичи. Это нативные подсказки. Выглядят вот так:

![Подсказки `TipKit`](https://cdn.sparrowcode.io/tutorials/tipkit/tipkit-example.jpg)

Apple сделала и UI, и управление когда показывать подсказки. Фреймворк появился в iOS 17. Подсказки доступны для всех платформ — для iOS, iPadOS, macOS, watchOS и visionOS.

[Framework `TipKit`](https://developer.apple.com/documentation/tipkit): Официальная документация Apple по TipKit

В каждом разделе туториала примеры и на SwiftUI, и на UIKit.

# Инициализация

Импортируем `TipKit` и в точке входа в приложение вызываем метод конфигураци:

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

**Для UIKit**, в AppDelegate:

```swift
func application(_ application: UIApplication, didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?) -> Bool {

    try? Tips.configure([
        .displayFrequency(.immediate),
        .datastoreLocation(.applicationDefault)])
        
    return true
}
```

`displayFrequency` определяет как часто показывать подсказку. В примере стоит `.immediate`, подсказки будут показываться сразу. Можно поставить ежечасно, ежедневно, еженедельно и ежемесячно.

`datastoreLocation` - хранилище данных подсказок. Это может быть: 

- `.applicationDefault` — дефолтная локация, доступно только приложению
- `.groupContainer` - через группу, доступно между таргетами
- `.url` - указываете свой путь

По умолчанию стоит `.applicationDefault`.

# Создаем подсказку

Протокол `Tip` определяет контент и когда показывать подсказку. Картинка и подзаголовок опциональные:

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

Есть два вида подсказок — **Popover** показывается поверх интерерфейса, а **Inline** встраивается как обычная вью.

## Всплывающие `Popover`

**Для SwiftUI** 

Вызываем модификатор `popoverTip` у вью, к которой добавить подсказку:

```swift
Image(systemName: "heart")
    .popoverTip(FavoritesTip(), arrowEdge: .bottom)
```

**Для UIKit** 

Слушаем подсказки через асинхронный метод. Когда `shouldDisplay` будет в тру, добавляем popover-контроллер. Передаем ему подсказку и вью, к которой привязать подсказку:

```swift
override func viewDidAppear(_ animated: Bool) {
    super.viewDidAppear(animated)
    
    Task { @MainActor in
        for await shouldDisplay in FavoritesTip().shouldDisplayUpdates {

            if shouldDisplay {
                let popoverController = TipUIPopoverViewController(FavoritesTip(), sourceItem: favoriteButton)
                present(popoverController, animated: true)
            }
            
            // Сейчас крестик работать не будет, это нормально.
            // Разберем дальше как это поправить
        }
    }
```

У `Popever`-подсказок стрелочка есть всегда, но направление стрелки может отличаться от того что укажите. В UIKit направление стрелочки выбрать нельзя.

![Всплывающие `Popever` посказки со стрелками](https://cdn.sparrowcode.io/tutorials/tipkit/popover.png)

## Встраиваемые `Inline`

`Inline`-подскази встраиваются между ваших вью и меняют лейаут. Они не перекрывают интерфейс приложения как `Popever`-подсказки. Добавлять их как обычные вью:

**Для SwiftUI**

```swift
VStack {
    Image("pug")
        .resizable()
        .scaledToFit()
        .clipShape(RoundedRectangle(cornerRadius: 12))
    TipView(FavoritesTip())
}
```

**Для UIKit**

Добавляем так же через асинхронный метод, только когда shouldDisplay в тру:

```swift
Task { @MainActor in
    for await shouldDisplay in FavoritesTip().shouldDisplayUpdates {

        if shouldDisplay {
            let tipView = TipUIView(FavoritesTip())
            view.addSubview(tipView)
        }
        
        // Сейчас крестик работать не будет, это нормально.
        // Разберем дальше как это поправить
    }
}
```

![`Inline`-подсказки. Они могут быть со стрелкой и без.](https://cdn.sparrowcode.io/tutorials/tipkit/inline-arrow.png)

У `Inline`-подсказок стрелочка опциональная. Направление стрелки будет именно такое, как вы укажите:

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

В подсказку можно добавить кнопку, а по кнопке выызвать вашу логику. Можно использовать чтобы открыть подробный туториал или направить на нужный экран.

![Как выглядят кнопки в подсказках `TipKit`](https://cdn.sparrowcode.io/tutorials/tipkit/actions.png)

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

`id` нужен чтобы определить какую кнопку нажали:

**Для SwiftUI**

```swift
TipView(tip) { action in

    if action.id == "reset-password" {
        // Делаем то что нужно по нажатию
    }
}
```

**Для UIKit**

```swift
Task { @MainActor in
    for await shouldDisplay in ActionsTip().shouldDisplayUpdates {

        if shouldDisplay {
            let tipView = TipUIView(ActionsTip()) { action in

                if action.id == "reset-password" {
                    // Делаем то что нужно по нажатию
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

Подсказку может закрыть пользователь, когда нажмет на крестик. Но можно закрыть и кодом. Код одинаковый для SwiftUI и UIKit:

```swift
inlineTip.invalidate(reason: .actionPerformed)
```

В методе укажите причину, почему закрыли подсказку:

- `.actionPerformed` - пользователь выполнил действие в подсказке
- `.displayCountExceeded` - подсказку показали максимальное количество раз
- `.actionPerformed` - пользователь явное закрыл подсказку

В UIKit для крестика нужно дописать код. Для `popover`-подсказки закрываем контроллер:

```swift
if presentedViewController is TipUIPopoverViewController {
    dismiss(animated: true)
}
```

Для `inline`-подсказки удаляем вью:

```swift
if let tipView = view.subviews.first(where: { $0 is TipUIView }) {
    tipView.removeFromSuperview()
}
```

# Правила для подсказок: когда показывать

Когда показывать подсказку настраивается с помощью параметров:

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

**Для SwiftUI**

```swift
struct ParameterRule: View {
    
    var body: some View {
        VStack {
            Spacer()
            Button("Rule") {
                FavoriteRuleTip.hasViewedTip = true
            }
            .buttonStyle(.borderedProminent)
            .popoverTip(FavoriteRuleTip(), arrowEdge: .top)
        }
    }
}
```

**Для UIKit**

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

Если закроете подсказку в Preview, она больше не покажется  — это не удобно. Чтобы подсказки появлялись каждый раз, нужно сбросить хранилище данных:

**SwiftUI**

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

**UIKit** 

Добавить в AppDelegate:

```swift
try? Tips.resetDatastore()
```

> Не забудьте убрать `.resetDatastore`, иначе в релизе подсказки будут показываться постоянно.