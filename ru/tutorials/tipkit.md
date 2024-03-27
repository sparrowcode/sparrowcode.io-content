[TipKit](https://developer.apple.com/documentation/tipkit) показывает подсказки. Выглядят вот так:

![Как выглядят подсказки TipKit](https://cdn.sparrowcode.io/tutorials/tipkit/tipkit-example.jpg)

// поправить текст и системы
Добавили в iOS 17. Доступен для iOS, macOS, Apple Watch и Apple TV и visionOS.

// Найти конкурентов на гитхабе
// Встроить в вввдение "Use TipKit to show contextual tips that highlight new, interesting, or unused features people haven’t discovered on their own yet."

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

// Непонятно: что там хранится, какие есть варианты, что ставим чаще всего если варианты не однозначные. Сумарно поменять предложение.
`datastoreLocation` - расположение хранилища данных, по умолчанию является каталогом `support`.

# Создаем подсказку

Ппротокол Tip определяет контент и когда показывать подсказку. У подсказки есть обязательное поля `title` и опциональне `message` и `image`.

```swift
struct PopoverTip: Tip {

    var title: Text {
        // Другой заголовок
        Text("Для начала")
    }

    var message: Text? {
        Text("Проведите пальцем влево/вправо для навигации.")
    }

    var image: Image? {
        Image(systemName: "hand.draw")
    }
}
```

Есть два вида подсказок Popover показывается поверх интерерфейса, а Inline встраивается как обычная вью.

## Всплывающие `Popever`

Вызываем модификатор `popoverTip` к вью, к которой нужно показать подсказку:

```swift
Image(systemName: "heart")
    .popoverTip(PopoverTip(), arrowEdge: .bottom)
```

![Всплывающие `Popever` посказки](https://cdn.sparrowcode.io/tutorials/tipkit/popover.png)

// Поправить
У Popever-подсказок стрелочка есть всегда, но направление которое вы указали не гарантируется.

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

![Встроенные подсказки](https://cdn.sparrowcode.io/tutorials/tipkit/inline-arrow.png)

У Inline-подсказак стрелка опциональная:

```swift
TipView(inlineTip, arrowEdge: .top)
TipView(inlineTip, arrowEdge: .leading)
TipView(inlineTip, arrowEdge: .trailing)
TipView(inlineTip, arrowEdge: .bottom)
```

## Добавляем кнопку

Кнопки прописываются в протоколе в поле `actions`:

```swift
var actions: [Action] {
    Action(id: "reset-password", title: "Сбросить Пароль")
    Action(id: "not-reset-password", title: "Отменить сброс")
}
```

`id` определяет какую кнопку нажали:

```swift
TipView(tip) { action in

    if action.id == "reset-password" {
    
    }
    
    if action.id == "not-reset-password" {
        
    }
}
```

// много лишнего
[Добавляем кнопки](https://cdn.sparrowcode.io/tutorials/tipkit/action-tipkit.mp4)

# Закрываем подсказку

Можно нажать на крестик или закрыть кодом:

```swift
inlineTip.invalidate(reason: .actionPerformed)
```

В метод укажите причину, почему закрыли подсказку. Список причин:

`.actionPerformed` - пользователь выполнил действие, описанное в подсказке
`.displayCountExceeded` - подсказка показана максимальное количество раз
`.actionPerformed` - пользователь явное закрыл подсказку


// Спорный пункт
# Правила для подсказок, когда показывать

Когда показывать подсказку настраивается с помощью параметров

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

`Rule` проверяет значение переменной `hasViewedGetStartedTip`, когда значение равно true, подсказка отобразится.

// Поменять на кнопку
// Пример сложный
// Потмер показать просто через кнопку, которая меняет параметр
```swift
struct ParameterRule: View {

    var body: some View {
        VStack {
            Rectangle()
                .frame(height: 100)
                .popoverTip(FavoriteRuleTip(), arrowEdge: .top)
            .onTapGesture {
                
                // Закрываем кодом: пользователь выполнил действие
                GettingStartedTip().invalidate(reason: .actionPerformed)
                
                // НЕПОНЯТНО 
                // значение hasViewedGetStartedTip true, показываем подсказку FavoriteRuleTip
                FavoriteRuleTip.hasViewedGetStartedTip = true
            }
            TipView(GettingStartedTip())
        }
        .padding()
    }
}
```

// Видос на замену
[Правила](https://cdn.sparrowcode.io/tutorials/tipkit/rules-video.mp4)

# `TipKit` в Preview

Когда дебажите в Preview и закроете подсказу, то она больше не покажется  — это не удобно. Чтобы подсказки появлилсь каждый раз, нужно сбросить хранилище данных:

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

// Примеры на UIKit? 
