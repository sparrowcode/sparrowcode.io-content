`@State` используйте только внутри вью. Изменения стейта перерисовывает вью.

`@StateObject` будет доступен во всех вью куда вы его передадите.

`@Binding` создает ссылку на `@State`, для использования в другом вью.

`@ObservedObject` тоже самое что и `@StateObject`, но при перерисовке уничтожается.

`@EnvironmentObject` похож на `@ObservedObject`, но передается как модификатор.

`@Environment` позволяет прочитать значения, встроенные в окружение SwiftUI.

# @State

Не храните данные в `@State` это только для состояний. Когда он меняется вью перерисовывается. 

В примере кнопка, у которой переключаем состояние с Play на Pause:

```swift
struct PlayButton: View {
   @State private var isPlaying: Bool = false // Create the state.

   var body: some View {
      Button(isPlaying ? "Pause" : "Play") {
         isPlaying.toggle() // Write the state.
      }
   }
}
```

> `@State` должен меняться только внутри вью. Поэтому делайте его приватным

Если у вас большие данные используется **@StateObject**.

# @StateObject

Будет доступен во всех вью куда его передадите. Он управляет экземплярами, соответствующих протоколу **ObservableObject**. 

`@Published` помечает свойство за которым нужно наблюдать. `@StateObject` используется во вью, которые должны реагировать на изменения.

```swift
class DataProvider: ObservableObject {
   @Published var currentValue = "a value"
}

struct DataOwnerView: View {
   @StateObject private var provider = DataProvider()

   var body: some View {
      Text("provider value: \(provider.currentValue)")
   }
}
```

`@StateObject` остается уникальным и не будет пересоздан если вью перерисуется.

# @Binding

Предоставляет доступ по ссылке к стейту другого вью.

Используется символ `$` для передачи привязываемой ссылки, без него Swift передаст копию значения вместо ссылки.

```swift
struct StateView: View {

   @State private var intValue = 0

   var body: some View {
      VStack {
         Text("intValue equals \(intValue)")
         BindingView(intValue: $intValue) // binding reference
      }
   }
}
```

Меняем значение стейта в новом вью:

```swift
struct BindingView: View {
   @Binding var intValue: Int

   var body: some View {
      Button("Increment") {
         intValue += 1
      }
   }
}
```

# @ObservedObject

`@ObservedObject` это как `@StateObject`, но наблюдаемые объекты уничтожаются и создаются повторно при перерисовке вью.

```swift
class DataProvider: ObservableObject {
   @Published var currentValue = "a value"
}

struct DataOwnerView: View {
   @ObservedObject var provider: DataProvider

   var body: some View {
      Text("provider value: \(provider.currentValue)")
   }
}
```

> Будет плохая производительность, когда часто будет перерисовывать тяжелый объект

# @EnvironmentObject

`@EnvironmentObject` то же самое что `@ObservedObject`. Передается через модификатор, а не инициализатор. Хорошо подходит для пользовательских настроек, тем или состояний приложения.


```swift
class DataProvider: ObservableObject {
   @Published var currentValue = "value"
}

struct EnvironmentUsingView: View {
   @EnvironmentObject var dependency: DataProvider

   var body: some View {
      Text(dependency.currentValue)
   }
}
```

```swift
struct MyApp: App {
   @StateObject var dataProvider = DataProvider()

   var body: some Scene {
      WindowGroup {
         EnvironmentUsingView()
            .environmentObject(dataProvider)
      }
   }
}
```

> `@EnvironmentObject` может вызвать ненужные обновления вью. Часто несколько вью с разных уровней наблюдают за одним и тем же экземпляром и реагируют на него.

# @Environment

Окружение - это встроенные значения вью в SwiftUI.

@Environment позволяет получить значения из окружения — ориентацию, цветовую схему и тд. Все доступные значения можно посмотреть [тут](https://developer.apple.com/documentation/swiftui/environmentvalues).

![Значения по умолчанию](https://cdn.sparrowcode.io/tutorials/difference-property-wrappers-in-swiftui/environment-default.png)

В примере получаем значение цветовой схемы `colorScheme` и обновляем вью при ее изменении:

```swift
struct MyView: App {
   @Environment(\.colorScheme) var colorScheme: ColorScheme

   var body: some View {
      Text("The color scheme is \(colorScheme == .dark ? "dark" : "light")")
   }
}
```

Здесь изменяем `Environment` для всей иерархии, добавив модификатор к корневому вью:

```swift
@main
struct Property_Wrappers: App {
   var body: some Scene {
      WindowGroup {
         ContentView()
            .environment(\.multilineTextAlignment, .center)
            .environment(\.lineLimit, nil)
            .environment(\.lineSpacing, 8)
      }
   }
}
```

Каждое вью внутри SwiftUI по умолчанию наследует среду от родительского вью. Можно переопределить любые значения для дочерних вью, присоединив модификатор **.environment**.