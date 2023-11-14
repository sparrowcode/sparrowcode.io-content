# Create Modifier

There is a built-in tool for custom modifiers - you need to create a structure and implement the `ViewModifier` protocol. The protocol should be used to implement the `body` method and return a new `View`.

To give an example, let's make a modifier that combines styles for text:

```swift
struct LargeTitleModifier: ViewModifier {

    func body(content: Content) -> some View {
        content
            .font(.largeTitle)
            .foregroundStyle(.primary)
    }
}
```

You can use other modifiers and even embed `View`.

# Apply Modifier

Call via `.modifier` and pass a custom modifier:

```swift
Text("Hello World")
    .modifier(LargeTitleModifier())
```

# Native Style

In order for the modifier to be called natively, you need to make an extension for `View`:

```swift
extension View {

    func largeTitleStyle() -> some View {
        modifier(LargeTitleModifier())
    }
}
```

To narrow down the availability of the modifier, you can make the extension only for `Text`.

The call will now be in native style:

```swift
Text("Hello World")
    .largeTitleStyle()
```