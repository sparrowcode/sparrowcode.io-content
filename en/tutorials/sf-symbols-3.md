The code examples will be for `SwiftUI` and `UIKit`. Watch carefully for character compatibility - not all characters are available for iOS 14 and earlier. You can see which version of the symbol is available [in the app](https://developer.apple.com/sf-symbols/).

## Render Modes

Render Modes is to render an icon in a color scheme. Monochrome, hierarchical, palette and multi-color are available. A clear preview:

![SFSymbols Render Modes Preview](https://cdn.sparrowcode.io/tutorials/sf-symbols-3/render-modes-preview.jpg)

Renders are available for each symbol, but there may be situations when the result for different renders will be the same and the icon will not change appearance. It is better to choose [in application](https://developer.apple.com/sf-symbols/), having previously set the desired renderer.

## Monochrome Render

The whole icon is colored in the specified color. The color is controlled by `tintColor`.

```swift
// UIKit
let image = UIImage(systemName: "doc")
let imageView = UIImageView(image: image)
imageView.tintColor = .systemRed

// SwiftUI
Image(systemName: "doc")
    .foregroundColor(.red)
```

The method works for any image, not just SF Symbols.

## Hierarchical Render

Draws the icon in a single color, but creates depth with transparency for the elements of the symbol.

```swift
// UIKit
let config = UIImage.SymbolConfiguration(hierarchicalColor: .systemIndigo)
let image = UIImage(systemName: "square.stack.3d.down.right.fill", withConfiguration: config)

// SwiftUI
Image(systemName: "square.stack.3d.down.right.fill")
    .symbolRenderingMode(.hierarchical)
    .foregroundColor(.indigo)
```

Note, sometimes the mono-color render is the same as the hierarchical one.

![SFSymbols Hierarchical Render](https://cdn.sparrowcode.io/tutorials/sf-symbols-3/hierarchical-render.jpg)

## Palette Render

Draws the icon in custom colors. Each symbol needs a certain number of colors.

```swift
// UIKit
let config = UIImage.SymbolConfiguration(paletteColors: [.systemRed, .systemGreen, .systemBlue])
let image = UIImage(systemName: "person.3.sequence.fill", withConfiguration: config)

// SwiftUI
Image(systemName: "person.3.sequence.fill")
    .symbolRenderingMode(.palette)
    .foregroundStyle(.red, .green, .blue)
```

If a symbol has 1 segment for a color, it will use the first color specified. If the symbol has 2 segments, but 1 color is specified, it will be used for both segments. If you specify 2 colors, they will be applied accordingly. If you specify 3 colors, the third is ignored.

![SFSymbols Palette Render](https://cdn.sparrowcode.io/tutorials/sf-symbols-3/palette-render.jpg)

## Multicolor Render

Important elements will have a fixed color, for the filler you can specify a custom color.

```swift
// UIKit
let config = UIImage.SymbolConfiguration.configurationPreferringMulticolor()
let image = UIImage(systemName: "externaldrive.badge.plus", withConfiguration: config)

// SwiftUI
Image(systemName: "externaldrive.badge.plus")
    .symbolRenderingMode(.multicolor)
```

Images that do not have a multicolor option will automatically be displayed in mono-color. In the preview, the fill color is `.systemCyan`:

![SFSymbols Multicolor Render](https://cdn.sparrowcode.io/tutorials/sf-symbols-3/multicolor-render.jpg)

## Symbol Variant

Some symbols have shape support, e.g. a bell `bell` can be inscribed in a quadrat or a circle. In `UIKit` you have to call them by name - for example `bell.square`, but in SwiftUI there is a modifier `.symbolVariant()`:

```swift
// The bell is crossed out
Image(systemName: 'bell')
    .symbolVariant(.slash)

// Inscribes in the square
Image(systemName: 'bell')
    .symbolVariant(.square)

// You can combine
Image(systemName: 'bell')
    .symbolVariant(.fill.slash)
```

Note, in the last example you can combine character variants.

## Adaptation

SwiftUI can display characters according to context. For iOS, Apple uses filled icons, but in macOS icons have no fill, only lines. If you use SF Symbols for the Side Bar, you don't need to specify whether the symbol is filled or not - it will automatically adapt depending on the system.

```swift
Label('Home', systemImage: 'person')
    .symbolVariant(.none)
```

These are all the changes in the new version.

