In iOS 14 and SwiftUI 2 add a modifier `.redacted(reason:)`, with which you can create a placeholder view:

```swift
VStack {
    Label("Swift Playground", systemImage: "swift")
    Label("Swift Playground", systemImage: "swift")
        .redacted(reason: .placeholder)
}
```

![View placeholder](https://cdn.sparrowcode.io/tutorials/redacted-modifier-swiftui/redacted_placeholder.jpg)

Use a placeholder to:

1. Show the View which content will be available after loading.
2. Show inaccessible or partially accessible content.
3. Use instead of `ProgressView()`, which I [described in the guide](https://sparrowcode.io/ru/mastering-progressview-swiftui).

Take a complex example:

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
            description: "AirTag is a supereasy way to keep track of your stuff. Attach one to your keys. Put another in your backpack."
        )
}
```

The model has a name, a system icon and a description. Put `airTag` in the extension. Let's create a separate view:

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
                
                Button("Jump to buy") {}
                .buttonStyle(.bordered)
                .padding(.vertical)
            }
        }
        .padding(.horizontal)
    }
}
```

Add a `DeviceView` to ContentView:

```swift
struct ContentView: View {

    var body: some View {
        DeviceView(device: .airTag)
            .redacted(reason: .placeholder)
    }
}
```

![DeviceView Result](https://cdn.sparrowcode.io/tutorials/redacted-modifier-swiftui/redacted_deviceview.jpg)

On the left - the view without the modifier. On the right - with it. For clarity, add a toggle:

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

[Toggle](https://cdn.sparrowcode.io/tutorials/redacted-modifier-swiftui/redacted_toggle.mov)

## Unredacted

Unredacted modifier allows us to keep the view unredacted while applying the redacted modifier:

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
            // Ommited
```

![Unredacted Result](https://cdn.sparrowcode.io/tutorials/redacted-modifier-swiftui/redacted_unredacted.jpg)

In the example, the icon and the name of the device are not hidden.

## Clickable

The button is still clickable and performs actions even after the modifier is applied:

```swift
VStack {
    Text(device.description)
        .font(.footnote)
    
    Button("Jump to buy") {
        print("Button is clickable!")
    }
    .buttonStyle(.bordered)
    .padding(.vertical)
}
```

[Clickable Button](https://cdn.sparrowcode.io/tutorials/redacted-modifier-swiftui/redacted_available_button.mov)

Manually control the button's behavior, I'll show you how below.

## Reasons

Apple designed the structure [RedactionReasons](https://developer.apple.com/documentation/swiftui/redactionreasons). The reasons to apply a redaction to data displayed on screen.

Two options `privacy` and `placeholder` available. Privacy displayed data should be obscured to protect private information. Placeholder displayed data should appear as generic placeholders.


You can implement it like this:

```swift
extension RedactionReasons {

	static let name = RedactionReasons(rawValue: 1 << 20)
	static let description = RedactionReasons(rawValue: 2 << 20)
}
```

Implemented using the `OptionSet` protocol.

## Environment

SwiftUI provides a special environment value called `\.redactionReasons` to get the redaction reason applied to the current view hierarchy. Change `DevicesView` with `unredacted(when:)`:

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
                
                Button("Jump to buy") {
                    print("Button is not clickable!")
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

I added a custom method `unredacted(when:)` to demonstrate the `reasons` property:

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

If you toggle it, the button is not clickable.

![Custom unredacted](https://cdn.sparrowcode.io/tutorials/redacted-modifier-swiftui/redacted_custom_unredacted.jpg)

## Building our own Redacted API

Let's start by defining our reasons :

```swift
enum Reasons {

    case blurred
    case standart
    case sensitiveData
}
```

Then we define a modifier for each of our reasons:

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

To see the result from the modifiers above in the live preview, you need this code:

```swift
struct Blurred_Previews: PreviewProvider {

    static var previews: some View {
        Text("Hello, world!")
            .modifier(Blurred())
    }
}
```

![Blurred Previews](https://cdn.sparrowcode.io/tutorials/redacted-modifier-swiftui/redacted_blurred_previews.jpg)

I took the `Blurred` modifier. As we did before, we then define a Redactable view modifier:

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

RedactableModifier has a `reason` property that takes the optional `Reasons`.
Lastly, let's create the View extension to be used at call site:

```swift
extension View {

    func redacted(with reason: Reasons?) -> some View {
        modifier(RedactableModifier(with: reason ?? .standart))
    }
}
```

I didn't make a separate view in which to call the modifiers. Instead, I put everything in the live preview.
Here's an example on how to use it:

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

Final result:

![RedactableModifier Result](https://cdn.sparrowcode.io/tutorials/redacted-modifier-swiftui/redacted_redactable_modifier.jpg)
