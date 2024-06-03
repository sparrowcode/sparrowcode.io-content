`@State` используется чтобы менять свойства только внутри вью, его изменение перерисовывает вью.

`@StateObject` будет синхронизироваться во всех вью куда вы его дадите, в отличии от `@State`.

`@Binding` создает ссылку на родительское свойство для изменения.

`@ObservedObject` будет синхронизироваться во всех вью куда вы его дадите как `@StateObject`, но при перерисовки уничтожается.

`@EnvironmentObject` позволяет в качестве модификатора внедрить экземпляр класса, который соответствует протоколу ObservableObject в иерархию вью.

`@Environment` позволяет прочитать значения, хранящееся в окружении вью.

# @State

Не храните данные в `@State` это только для состояний. Когда он меняется вью перерисовывается. Например вы можете показывать кнопку, переключать что-то.

```swift
struct PlayButton: View {
   @State private var isPlaying: Bool = false // Create the state.

   var body: some View {
      Button(isPlaying ? "Pause" : "Play") { // Read the state.
         isPlaying.toggle() // Write the state.
      }
   }
}
```

> `@State` должен менятся только внутри вью. Поэтому делайте его приватным

Если у вас большие данные используется **@StateObject**.

# @StateObject

`@StateObject` используется наверху иерархии вью, хорошо подходит для управления сложными данными. Он управляет экземплярами объектов, соответствующих протоколу **ObservableObject**. `@StateObject` остается уникальным и не будет пересоздан если вью перерисуется.

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

Обертка `@Published` добавляет willSet наблюдателя для свойства. `@StateObject` используется только во вью, которые должны реагировать на изменения.

# @Binding

Предоставляет доступ по ссылке к родительскому стейту.

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

Пример как менять значение?

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

`@EnvironmentObject` используется чтобы ваши вью имели доступ к общем данным без необходимости передавать его через инициализаторы или биндинги. Вью будут следить за данными `@EnvironmentObject` и автоматически обновляться. Хорошо подходит для пользовательских настроек, тем или состояний приложения.

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

В отличие от `@ObservedObject`, используется модификатор **.environmentObject()**.

> `@EnvironmentObject` может вызвать ненужные обновления вью. Часто несколько вью с разных уровней наблюдают за одним и тем же экземпляром и реагируют на него.

# @Environment

// окруждение что такое
// енв- получает значнеия
// пример

Окружения что такое? пример

@Environment позволяет получить значения из окружения — ориентацию и тд. Все доступные значения по умолчанию можно посмотреть [тут](https://developer.apple.com/documentation/swiftui/environmentvalues).

![Значения по умолчанию](https://cdn.sparrowcode.io/tutorials/difference-property-wrappers-in-swiftui/environment-default.png)

Например, можно прочитать значение цветовой схемы и обновить вью при ее изменении. Чтобы получить доступ к значениям среды, используем `@Environment` для чтения значения colorScheme из среды.

```swift
struct MyView: App {
   @Environment(\.colorScheme) var colorScheme: ColorScheme

   var body: some View {
      Text("The color scheme is \(colorScheme == .dark ? "dark" : "light")")
   }
}
```

Можно легко изменить **Environment** для всей иерархии вью, добавив модификатор среды к корневому вью.

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

Можно настроить значение окружения самостоятельно или использовать доступные значения по умолчанию.

Каждое вью внутри SwiftUI по умолчанию наследует среду от родительского вью. Вы можете переопределить любые значения при создании дочернего вью, присоединив модификатор **.environment**.