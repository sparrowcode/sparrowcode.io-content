The modifier `keyboardShortcut` adds keyboard shortcuts:

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

![Updating content](https://cdn.sparrowcode.io/tutorials/keyboard-shortcut-swiftui/refresh_content.jpg)

Now by pressing the two keys `Command` + `R` we will display a message in the console.

The first parameter of the `keyboardShortcut` modifier must be an instance of the [KeyEquivalent](https://developer.apple.com/documentation/swiftui/keyequivalent?changes=_5) structure, it inherits from the `ExpressibleByExtendedGraphemeClusterLiteral` protocol and creates an instance of `KeyEquivalent` with a string literal of 1 character.

```swift
init(_ key: KeyEquivalent, modifiers: EventModifiers = .command)
```

But the second parameter `modifiers` is inherited from the [EventModifiers](https://developer.apple.com/documentation/swiftui/eventmodifiers?changes=_5) structure. This is a unique set of modifier keys.
In the example above, we use the `R` key and the `.command` modifier, which is set by default in SwiftUI.

Let's take a look at the switch example:

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

Press `⌘ + T` and change the switch position. Apply the modifier to all `VStack` elements.

[Switch](https://cdn.sparrowcode.io/tutorials/keyboard-shortcut-swiftui/keyboard_shortcut_toggle.mov)

Another example:

```swift
Button("Confirm action") {
    print("Launching starship…")
}
.keyboardShortcut(.defaultAction)
```

The property `.defaultAction` is the default key combination for the default Enter button.
I put the key combination `Escape` + `Option` + `Shift` in the constant `updateArticles`:

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

[Synchronizing articles](https://cdn.sparrowcode.io/tutorials/keyboard-shortcut-swiftui/keyboard_sync_articles.mov)
