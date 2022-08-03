Следите за совместимостью символов - не все доступны для 14-ой и предыдущих iOS. Глянуть с какой версии доступен символ можно [в приложении](https://developer.apple.com/sf-symbols/). Примеры кода будут для `SwiftUI` и `UIKit`.

Render Modes - это отрисовка иконки в цветовой схеме. Доступны монохром, иерархический, палетка и мульти-цвет.

![Render Modes в SFSymbols.](https://cdn.sparrowcode.io/tutorials/sf-symbols-and-render-mode/render-modes-preview.jpg)

Символ может поддерживать не все рендеры. Если рендер не доступен, то символ будет отрисован в монохроме. Сравнить рендеры можно в официальном приложении [SF Symbols](https://developer.apple.com/sf-symbols/).

## Monochrome Render

Иконка заливается цветом. Управлять цветом через `tintColor`.

```swift
// UIKit
let image = UIImage(systemName: "doc")
let imageView = UIImageView(image: image)
imageView.tintColor = .systemRed

// SwiftUI
Image(systemName: "doc")
    .foregroundColor(.red)
```

Способ работает не только для SF Symbols, а для любых изображений.

## Hierarchical Render

Рисует иконку в одном цвете, но создает глубину с помощью прозрачности для элементов символа.

![Hierarchical Render в SFSymbols.](https://cdn.sparrowcode.io/tutorials/sf-symbols-and-render-mode/hierarchical-render.jpg)

```swift
// UIKit
let config = UIImage.SymbolConfiguration(hierarchicalColor: .systemIndigo)
let image = UIImage(systemName: "square.stack.3d.down.right.fill", withConfiguration: config)

// SwiftUI
Image(systemName: "square.stack.3d.down.right.fill")
    .symbolRenderingMode(.hierarchical)
    .foregroundColor(.indigo)
```

Обратите внимание, иногда иерархический рендер выглядит так же, как `Monochrome Render`.

## Palette Render

Рисует иконку в кастомных цветах. Каждому символу нужно конкретное количество цветов.

![Palette Render в SFSymbols.](https://cdn.sparrowcode.io/tutorials/sf-symbols-and-render-mode/palette-render.jpg)

```swift
// UIKit
let config = UIImage.SymbolConfiguration(paletteColors: [.systemRed, .systemGreen, .systemBlue])
let image = UIImage(systemName: "person.3.sequence.fill", withConfiguration: config)

// SwiftUI
Image(systemName: "person.3.sequence.fill")
    .symbolRenderingMode(.palette)
    .foregroundStyle(.red, .green, .blue)
```

Чтобы сохранить универсальный API, можно передать любое количество цветов. Вот правила, по которым это работает:

- Если у символа 1 сегмент для цвета, он будет использовать первый указанный цвет. 
- Если у символа 2 сегмента, но будет указан 1 цвет, он будет использоваться для обоих сегментов.
- Если укажете 2 цвета — они применятся соответственно.
- Если указать 3 цвета для символа с 2-мя сегментами, третий игнорируется.

## Multicolor Render

Важные элементы будут покрашены в фиксированный цвет, а для заполняющего цвет можно настроить. На превью заполняющий цвет `.systemCyan`:

![Multicolor Render в SFSymbols.](https://cdn.sparrowcode.io/tutorials/sf-symbols-and-render-mode/multicolor-render.jpg)

```swift
// UIKit
let config = UIImage.SymbolConfiguration.configurationPreferringMulticolor()
let image = UIImage(systemName: "externaldrive.badge.plus", withConfiguration: config)

// SwiftUI
Image(systemName: "externaldrive.badge.plus")
    .symbolRenderingMode(.multicolor)
```

Изображения, у которых нет многоцветного варианта, будут автоматически отображаться в `Monochrome Render`.

## Symbol Variant

Некоторые символы имеют поддержку форм, например колокольчик `bell` можно вписать в квадрат или круг. В `UIKit` нужно вызывать их по имени - например, `bell.square`, но в SwiftUI есть модификатор `.symbolVariant()`:

```swift
// Колокольчик перечеркнут
Image(systemName: "bell")
    .symbolVariant(.slash)

// Вписывает в квадрат
Image(systemName: "bell")
    .symbolVariant(.square)

// Можно комбинировать
Image(systemName: "bell")
    .symbolVariant(.fill.slash)
```

Обратите внимание, в последнем примере можно комбинировать варианты символов.

## Адаптация

SwiftUI умеет отображать символы соответственно контексту. Для iOS Apple использует залитые иконки, но в macOS иконки без заливки - только линии. Если вы используете SF Symbols для Side Bar, то это не нужно указывать специально - символ адаптируется.

```swift
Label("Home", systemImage: "person")
    .symbolVariant(.none)
```

