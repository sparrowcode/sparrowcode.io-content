# Создаем Модификатор

Для кастомных модификаторов есть встроенный инструмент - нужно создать структуру и реализовать протокол `ViewModifier`. По протоколу нужно реализовать метод `body` и вернуть новую `View`.

Для примера сделаем модификатор, который объединяет стили для текста:

```swift
struct LargeTitleModifier: ViewModifier {

    func body(content: Content) -> some View {
        content
            .font(.largeTitle)
            .foregroundStyle(.primary)
    }
}
```

Вы можете использовать и другие модификаторы и даже встраивать `View`.

# Применить Модификатор

Вызываем через `.modifier` и передаем кастомный модификатор:

```swift
Text("Hello World")
    .modifier(LargeTitleModifier())
```

# Нативный стиль

Чтобы модификатор вызывался в нативном стиле, нужно сделать extension для `View`:

```swift
extension View {

    func largeTitleStyle() -> some View {
        modifier(LargeTitleModifier())
    }
}
```

Чтобы сузить доступность модификатора, вы можете сделать расширение только для `Text`.

Теперь вызов будет в нативном стиле:

```swift
Text("Hello World")
    .largeTitleStyle()
```