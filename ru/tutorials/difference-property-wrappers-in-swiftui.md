Рассмотрим основные обертки свойств, которые часто используются в SwiftUI. 
Property Wrappers позволяют спрятать кастомную логику за простым определением переменной добавив @, которая может быть извлечена в отдельную структуру для повторного использования в кодовой базе.

1. Используйте `@State`, когда вашему вью нужно изменить одно из своих собственных свойств.

2. Используйте `@StateObject` для создания наблюдаемого объекта, который будет использоваться совместно в нескольких вью.

3. Используйте `@Binding`, когда вашему вью нужно изменить свойство, принадлежащее вью-предку или свойство наблюдаемого объекта, на который ссылается предок.

4. Используйте `@ObservedObject`, если ваше вью зависит от наблюдаемого объекта, который он может создать самостоятельно или который может быть передан в инициализатор этого вью.

5. Используйте `@EnvironmentObject`, когда было бы слишком громоздко передавать наблюдаемый объект через все инициализаторы всех предков вашего вью.

6. Используйте `@Environment`, если ваше вью зависит от типа, который не может соответствовать ObservableObject или когда ваши вью зависят более чем от одного экземпляра того же типа, если этот тип не должен использоваться в качестве наблюдаемого объекта.

# @State

Используйте @State внутри вью для управления состоянием, рассматривайте его как часть вью. @State не подходит для хранения больших объемов данных или сложных моделей данных, для этого лучше использовать **@StateObject**.

Реагирует на любые изменения, внесенные в @State, перестраивая вью. В основном используется для хранения простых данных тип-значение. Он обычно используется для простого управления состоянием компонентов пользовательского интерфейса, например состояний переключения, ввода текста.

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

Внешние источники не должны изменять ваш @State, поэтому делайте его приватным.

# @StateObject

Используется для управления экземплярами объектов, соответствующих протоколу **ObservableObject**. Экземпляр аннотированного объекта остается уникальным на протяжении всего жизненного цикла вью, он не будет пересоздан если вью обновится.

@StateObject обычно используется наверху иерархии вью для создания и обслуживания ObservableObject экземпляров. Хорошо подходит для управления сложными моделями данных и связанной с ними логикой.

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

Обертка **@Published** добавляет willSet наблюдателя для свойства. @StateObject используется только во вью, которые должны реагировать на изменения свойств экземпляра.

# @Binding

Предоставляет доступ по ссылке для типа-значения. Иногда нужно сделать состояние нашего вью доступным для его детей. Но мы не можем просто взять и передать это значение, поскольку это тип-значение, и Swift передаст копию этого значения. Здесь приходит на помощь @Binding

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

Используется специальный символ `$` для передачи привязываемой ссылки, без знака `$` Swift передаст копию значения вместо ссылки.

# @ObservedObject

@ObservedObject во многом похож на @StateObject, но имеет одно главное отличие - наблюдаемые объекты уничтожаются и создаются повторно при перерисовке вью, содержащей их.

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

> Может повлиять на производительность, если вью часто пересоздает тяжелый объект

# @EnvironmentObject

@EnvironmentObject используется для данных, которые должны быть доступны многим вью. Это позволяет обмениваться данными модели везде, где это необходимо, а также гарантирует что вью автоматически обновляются при изменении этих данных.

@EnvironmentObject похож на @ObservedObject, разница только во внедрении. @ObservedObject внедряется, как и любое другое свойство - при каждой инициализации. @EnvironmentObject вводится только один раз в корень иерархии вью и доступен для любого более глубокого вью.

Хорошо подходит для совместного использования одной и той же модели данных в нескольких вью, таких как пользовательские настройки, темы или состояния приложения. Для сложных иерархий вью, где нескольким вью требуется доступ к одному и тому же ObservableObject экземпляру.

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

В отличие от @ObservedObject, мы используем отправку данных в модификатор **.environmentObject()**.

Не злоупотребляйте @EnvironmentObject, он может вызвать ненужные обновления вью. Часто несколько вью с разных уровней наблюдают за одним и тем же экземпляром и реагируют на него. Внимательно следите за тем что делаете, чтобы избежать снижения производительности.

# @Environment

Обертка @Environment позволяет читать значения из окружения вью. Можно настроить значение окружения самостоятельно или использовать доступные значения по умолчанию.

Все доступные значения по умолчанию можно посмотреть [тут](https://developer.apple.com/documentation/swiftui/environmentvalues).

![Значения по умолчанию](https://cdn.sparrowcode.io/tutorials/difference-property-wrappers-in-swiftui/environment-default.png)

Например, можно прочитать значение цветовой схемы и автоматически обновить свое вью при изменении цветовой схемы. Чтобы получить доступ к значениям среды, создаем @Environment переменную, определяющую ключевой путь к значению, которое вы хотите прочитать и записать.

```swift
struct MyView: App {
   @Environment(\.colorScheme) var colorScheme: ColorScheme

   var body: some View {
      Text("The color scheme is \(colorScheme == .dark ? "dark" : "light")")
   }
}
```

Можно легко изменить Environment для всей иерархии вью, добавив модификатор среды к корневому вью.

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

Каждое вью внутри SwiftUI по умолчанию наследует среду от родительского представления. Вы можете переопределить любые значения при создании дочернего вью, присоединив модификатор **.environment**.