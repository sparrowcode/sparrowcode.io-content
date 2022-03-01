В iOS 14 и SwiftUI 2 добавили новый модификатор `.redacted(reason:)`, с помощью которого можно сделать прототип вью. Выглядит вот так:

```swift
VStack {
    Label("Swift Playground", systemImage: "swift")
    Label("Swift Playground", systemImage: "swift")
        .redacted(reason: .placeholder)
}
```

![Redacted placeholder](https://cdn.ivanvorobei.io/websites/sparrowcode.io/redacted-modifier-swiftui/redacted_placeholder.jpg)

Прототип можно использовать для разных целей, например:

1. Показать вью, содержимое которой будет доступно после загрузки.
2. Показать недоступное или частично доступное содержимое.
3. Использовать вместо `ProgressView()`, о которой я [рассказал в отдельном гайде](https://sparrowcode.io/ru/mastering-progressview-swiftui).


## Более комплексный пример

Начнем с подготовления модели:

```swift
struct Device {
    let name: String
    let systemIcon: String
    let description: String
}

extension Device {
    static let airTag: Self =
        .init(
            name: "AirTag",
            systemIcon: "airtag",
            description: "Cуперлёгкий способ находить свои вещи. Прикрепите один трекер AirTag к ключам, а другой — к рюкзаку. И теперь их видно на карте в приложении «Локатор»."
        )
}
```

Модель имеет три свойства: название, системная иконка и описание. Для удобства я вынес `airTag` в расширение.

Создадим отдельную вью:

```swift
struct DeviceView: View {
    let device: Device
    
    var body: some View {
        VStack(spacing: 20) {
            HStack {
                Image(systemName: device.systemIcon)
                    .resizable()
                    .frame(width: 42, height: 42)
                Text(device.name)
                    .font(.title2)
            }
            VStack {
                Text(device.description)
                    .font(.footnote)
                
                Button("Перейти к покупке") {}
                .buttonStyle(.bordered)
                .padding(.vertical)
            }
        }
        .padding(.horizontal)
    }
}
```

Добавляем `DeviceView` в основную вью:

```swift
struct ContentView: View {

    var body: some View {
        DeviceView(device: .airTag)
            .redacted(reason: .placeholder)
    }
}
```

![DeviceView result](https://cdn.ivanvorobei.io/websites/sparrowcode.io/redacted-modifier-swiftui/redacted_deviceview.jpg)

Слева вью без модификатора, а справа с ним. Для наглядности добавим переключатель:

```swift
struct ContentView: View {
    @State private var toggleRedacted: Bool = false
    
    var body: some View {
        VStack {
            DeviceView(device: .airTag)
                .redacted(reason: toggleRedacted ? .placeholder : [])
            
            Toggle("Toggle redacted", isOn: $toggleRedacted)
                .padding()
        }
    }
}
```

[Redacted Toggle](https://cdn.ivanvorobei.io/websites/sparrowcode.io/redacted-modifier-swiftui/redacted_toggle.mov)

## Unredacted

Если вы хотите не скрывать некоторый контент, то примените модификатор `unredacted()`:

```swift
VStack(spacing: 20) {
    HStack {
        Image(systemName: device.systemIcon)
            .resizable()
            .frame(width: 42, height: 42)
        Text(device.name)
            .font(.title2)
    }
    .unredacted()
    
    VStack {
        Text(device.description)
            .font(.footnote)
            // код ниже скрыт
```

![Unredacted result](https://cdn.ivanvorobei.io/websites/sparrowcode.io/redacted-modifier-swiftui/redacted_unredacted.jpg)

В нашем примере иконка и название девайса не будут скрыты.

## Подводный камень

Заключается в том, что кнопка остается кликабельной и может совершать действия даже после применения модификатора:

```swift
VStack {
    Text(device.description)
        .font(.footnote)
    
    Button("Перейти к покупке") {
        print("Кнопка кликабельна!")
    }
    .buttonStyle(.bordered)
    .padding(.vertical)
}
```

![Button still available](https://cdn.ivanvorobei.io/websites/sparrowcode.io/redacted-modifier-swiftui/redacted_available_button.mov)

Поведением кнопки необходимо управлять самостоятельно. Чуть ниже я покажу как.

## Причины редактирования

Apple спроектировала новую структуру [RedactionReasons](https://developer.apple.com/documentation/swiftui/redactionreasons), которая отвечает за **причину** редактирования, применяемая к вью.
Доступно два варианта: `privacy` и `placeholder`. Privacy отвечает за отображение данных, которые должны быть скрыты в качестве приватной информации. Placeholder отвечает за обобщенный прототип.

Реализовать свою причину можно вот так:

```swift
extension RedactionReasons {
		static let name = RedactionReasons(rawValue: 1 << 20)
		static let description = RedactionReasons(rawValue: 2 << 20)
}
```

Реализация происходит с помощью протокола `OptionSet`.

## Environment

У окружения есть проперти `\.redactionReasons` — текущая причина редактирования применяемая к иерархии вью.

Изменим нашу `DevicesView` с помощью своего метода `unredacted(when:)`:

```swift
struct DeviceView: View {
    let device: Device
    @Environment(\.redactionReasons) var reasons 
    
    var body: some View {
        VStack(spacing: 20) {
            HStack {
                Image(systemName: device.systemIcon)
                    .resizable()
                    .frame(width: 42, height: 42)
                Text(device.name)
                    .unredacted(when: !reasons.contains(.name))
                    .font(.title2)
            }
            
            VStack {
                Text(device.description)
                    .unredacted(when: !reasons.contains(.description))
                    .font(.footnote)
                
                Button("Перейти к покупке") {
                    print("Кнопка не кликабельна!")
                }
                .disabled(!reasons.isEmpty)
                .buttonStyle(.bordered)
                .padding(.vertical)
            }
        }
        .padding(.horizontal)
    }
}
```

Я добавил кастомный метод `unredacted(when:)` для демонстрации работы свойства `reasons`:

```swift
extension View {
    @ViewBuilder
    func unredacted(when condition: Bool) -> some View {
        switch condition {
            case true: unredacted()
            case false: redacted(reason: .placeholder)
        }
    }
}
```

При смене положения переключателя, кнопка становится не кликабельной.

![Custom unredacted method](https://cdn.ivanvorobei.io/websites/sparrowcode.io/redacted-modifier-swiftui/redacted_custom_unredacted.jpg)


## Собственный API

Начнем с реализации своих причин:

```swift
enum Reasons {
    case blurred
    case standart
    case sensitiveData
}
```

Реализуем свои вью модификаторы, подходящие под причины выше:

```swift
struct Blurred: ViewModifier {
    func body(content: Content) -> some View {
        content
            .padding()
            .blur(radius: 4)
            .background(.thinMaterial, in: Capsule())
    }
}

struct Standart: ViewModifier {
    func body(content: Content) -> some View {
        content
            .padding()
    }
}

struct SensitiveData: ViewModifier {
    func body(content: Content) -> some View {
        VStack {
            Text("Are you over 18 years old?")
                .bold()
            
            content
                .padding()
                .frame(width: 160, height: 160)
                .overlay(.black, in: RoundedRectangle(cornerRadius: 20))
        }
    }
}
```

Для того, чтобы увидеть результат из модификаторов выше в live preview, необходимо написать код ниже:

```swift
struct Blurred_Previews: PreviewProvider {
    static var previews: some View {
        Text("Hello, world!")
            .modifier(Blurred())
    }
}
```

![Blurred Previews](https://cdn.ivanvorobei.io/websites/sparrowcode.io/redacted-modifier-swiftui/redacted_blurred_previews.jpg)

В качестве примера я взял `Blurred` модификатор.
Перейдем к следующему модификатору вью `RedactableModifier`:

```swift
struct RedactableModifier: ViewModifier {
    let reason: Reasons?
    
    init(with reason: Reasons) { self.reason = reason }
    
    @ViewBuilder
    func body(content: Content) -> some View {
        switch reason {
            case .blurred: content.modifier(Blurred())
            case .standart: content.modifier(Standart())
            case .sensitiveData: content.modifier(SensitiveData())
            case nil: content
        }
    }
}
```

Структура имеет `reason` свойство, которое принимает опциональное перечисление `Reasons`.
Последним шагом будет реализация своего метода к протоколу `View`:

```swift
extension View {
    func redacted(with reason: Reasons?) -> some View {
        modifier(RedactableModifier(with: reason ?? .standart))
    }
}
```

Я не стал делать отдельную вью, в которой буду вызывать модификаторы, а вместо этого поместил все в live preview:

```swift
struct RedactableModifier_Previews: PreviewProvider {
    static var previews: some View {
        VStack(spacing: 30) {
            Text("Usual content")
                .redacted(with: nil)
            Text("How are good your eyes?")
                .redacted(with: .blurred)
            Text("Sensitive data")
                .redacted(with: .sensitiveData)
        }
    }
}
```

![RedactableModifier](https://cdn.ivanvorobei.io/websites/sparrowcode.io/redacted-modifier-swiftui/redacted_redactable_modifier.jpg)


<!-- Вывод гайда -->
Добавить прототип вью не сложно, как и кастомизировать его. Надеюсь в следующих версиях появится больше вариантов для редактирования.
