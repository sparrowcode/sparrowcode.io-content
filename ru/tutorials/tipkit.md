[TipKit](https://developer.apple.com/documentation/tipkit) - позволяет легко отображать подсказки в приложениях. Появился в iOS 17 и доступен для iPhone, iPad, Mac, Apple Watch и Apple TV.

![](tipkit-example.png)

# Инициализация и настройка для приложения

В точке входа приложения импортируем `TipKit` и добавляем `Tips.configure`.

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

`Tips.configure()` - конфигурация состояния всех подсказок в приложении.

`displayFrequency` - частота отображения подсказки: 
<ul>
    <li>immediate - будут отображаться сразу</li>
    <li>hourly - ежечасно</li>
    <li>daily - ежедневно</li>
    <li>weekle - еженедельно</li>
    <li>monthly - ежемесячно</li>
</ul>

`datastoreLocation` - расположение хранилища данных, по умолчанию является каталогом `support`.

# Подсказки

Чтобы создать подсказку нужно принять протокол Tip, этот протокол определяет содержание и условия. Подсказка состоит из обязательного поля `title` и опциональных `message` и `image`.
```swift
struct InlineTip: Tip {
    var title: Text {
        Text("Для начала")
    }

    var message: Text? {
        Text("Проведите пальцем влево/вправо для навигации. Коснитесь фотографии, чтобы просмотреть ее детали.")
    }

    var image: Image? {
        Image(systemName: "hand.draw")
    }
}
```

Есть два вида подсказок:

### Inline - встраиваемые

Временно перестраивает интерфейс вокруг себя, чтобы их ничего не перекрывало. Создаем экземпляр `TipView` и передаем ему подсказку для отображения.

```swift
struct TipKitDemo: View {

    private let inlineTip = InlineTip()

    var body: some View {
        VStack {
            Image("pug")
                .resizable()
                .scaledToFit()
                .clipShape(RoundedRectangle(cornerRadius: 12))
            TipView(inlineTip) // Inline Tip
        }
        .padding()
    }
}

// Так же можно указать `arrowEdge` - напраление стрелочки подсказки.
TipView(inlineTip, arrowEdge: .top)
TipView(inlineTip, arrowEdge: .leading)
TipView(inlineTip, arrowEdge: .trailing)
TipView(inlineTip, arrowEdge: .bottom)
```

![](inline-arrow.png)

### Popever - всплывающие

Отображаются по верх интерфейса. Прикрепляем модификатор `popoverTip` кнопке или другим элементам интерфейса.

```swift
struct TipKitDemo: View {
    
    private let popoverTip = PopoverTip()
    
    var body: some View {
        HStack {
            Image(systemName: "heart")
                .font(.largeTitle)
                .popoverTip(popoverTip, arrowEdge: .bottom) // Popover Tip
        }
        
    }
}
```
![](popover.png)

# Добавляем кнопоки в подсказку

Чтобы появилась кнопка, в протокол нужно добавить поле `actions`:

```swift
var actions: [Action] {
    Action(id: "reset-password", title: "Сбросить Пароль")
    Action(id: "not-reset-password", title: "Отменить сброс")
}
```

Выше мы указывали id, именно по нему будем определять какое действие было вызвано.

```swift
TipView(tip, arrowEdge: .bottom) { action in

    if action.id == "reset-password" {
        // действие reset-password
    }
    
    if action.id == "not-reset-password" {
        // действие not-reset-password
    }
    
}
```
![](actions.png)
### <a href="#">Здесь видео</a>
<video src="action-tipkit.mp4" controls></video>

# Закрыть подсказку

Можно нажать на крестик или закрыть кодом, используя метод `invalidate`.

```swift
inlineTip.invalidate(reason: .actionPerformed)
```
Список причин по которым можно делать `invalidate`:

* `.actionPerformed` - пользователь выполнил действие, описанное в подсказке.

* `.displayCountExceeded` - подсказка показана максимальное количество раз.

* `.actionPerformed` - пользователь явное закрыл подсказку.


# Правила отображения подсказки

Правила на основе параметров отслеживают состояние приложения. В примере ниже `Rule` проверяет значение переменной `hasViewedGetStartedTip`, когда значение равно true, подсказка отобразится.

```swift
struct FavoriteRuleTip: Tip {

    var title: Text {
        Text("Добавить в избранное")
    }
    
    var message: Text? {
        Text("Этот пользователь будет добавлен в папку избранное.")
    }

    @Parameter
    static var hasViewedGetStartedTip: Bool = false

    var rules: [Rule] {
        #Rule(Self.$hasViewedGetStartedTip) { $0 == true }
    }

}
```

```swift
struct ParameterRule: View {
    @State private var showDetail = false
    
    var body: some View {
        VStack {
            Rectangle()
                .frame(height: 100)
                .popoverTip(FavoriteRuleTip(), arrowEdge: .top)
            .onTapGesture {
                
                // пользователь выполнил действие описанное в подсказке, отключаем подсказку GettingStartedTip
                GettingStartedTip().invalidate(reason: .actionPerformed)
                
                // значение hasViewedGetStartedTip true, показываем подсказку FavoriteRuleTip
                FavoriteRuleTip.hasViewedGetStartedTip = true
            }
            TipView(GettingStartedTip())
        }
        .padding()
    }
}
```
### <a href="#">Здесь видео</a>
<video src="rules-video.mp4" controls></video>

# Preview

Если закрыть подсказку в preview она больше не покажется, это не очень удобно. Чтобы такого не происходило нужно сбросить хранилище данных подсказок `Tips.resetDatastore()`

```swift
#Preview {
    TipKitDemo()
        .task {
            try? Tips.resetDatastore()
            
            try? Tips.configure([
                .displayFrequency(.immediate),
                .datastoreLocation(.applicationDefault)
            ])
        }
}
```