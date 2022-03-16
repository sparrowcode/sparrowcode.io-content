Модификатор `keyboardShortcut` добавляет сочетания клавиш:

```swift
struct ContentView: View {
    var body: some View {
        Button("Refresh content") {
            print("⌘ + R pressed")
        }
        .keyboardShortcut("r", modifiers: [.command])
    }
}
```

![Обновляем контент](https://cdn.sparrowcode.io/articles/keyboard-shortcut-swiftui/refresh_content.jpg)

По нажатию двух клавиш `Command` + `R` выведем сообщение в консоль.

## Инициализация

Первый параметр модификатора `keyboardShortcut` должен быть экземпляром структуры [KeyEquivalent](https://developer.apple.com/documentation/swiftui/keyequivalent?changes=_5). `KeyEquivalent` наследуется от протокола `ExpressibleByExtendedGraphemeClusterLiteral`, позволяющий создать экземпляр `KeyEquivalent` используя строковый литерал, содержащий только 1 символ.

```swift
init(_ key: KeyEquivalent, modifiers: EventModifiers = .command)
```

Второй параметр `modifiers:` наследуется от структуры [EventModifiers](https://developer.apple.com/documentation/swiftui/eventmodifiers?changes=_5), который представляет собой уникальный набор клавиш модификаторов.
В примере выше используем клавишу `R` и модификатор `.command`, который устанавливается по умолчанию в SwiftUI:

Пример с переключателем:

```swift
struct ContentView: View {
    @State private var isEnabled = false
    
    var body: some View {
        VStack {
            Text("Press ⌘ + T")
            Toggle(isOn: $isEnabled) {
                Text(String(isEnabled))
            }
            .padding()
        }
        .keyboardShortcut("t")
    }
}
```

Нажимая на `⌘ + T` — меняем положение переключателя. Применили модификатор ко всем элементам `VStack`.

[Переключатель](https://cdn.sparrowcode.io/articles/keyboard-shortcut-swiftui/keyboard_shortcut_toggle.mov)

Другой пример инициализации:

```swift
Button("Confirm action") {
    print("Launching starship…")
}
.keyboardShortcut(.defaultAction)
```

Проперти `.defaultAction` — стандартная комбинация клавиш для кнопки по умолчанию Enter.
В последнем примере я положил сочетание клавиш `Escape` + `Option` + `Shift` в константу `updateArticles`:

```swift
struct ContentView: View {
    let updateArticles = KeyboardShortcut(.escape, modifiers: [.option, .shift])
    
    var body: some View {
        Button { 
            print("Sync articles…")
        } label: { 
            VStack(spacing: 30) {
                Image(systemName: "books.vertical")
                    .imageScale(.large)
                Text("Update articles")
            }
        }
        .keyboardShortcut(updateArticles)
    }
}
```

[Синхронизация статей](https://cdn.sparrowcode.io/articles/keyboard-shortcut-swiftui/keyboard_sync_articles.mov)
