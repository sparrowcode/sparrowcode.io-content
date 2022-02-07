Примеры кода будут для `SwiftUI` и `UIKit`. Внимательно следите за совместимостью символов - не все доступны для 14 и предыдущих iOS. Глянуть с какой версии доступен символ можно [в приложении](https://developer.apple.com/sf-symbols/).

## Render Modes

Render Modes - это отрисовка иконки в цветовой схеме. Доступны монохром, иерархический, палетка и мульти-цвет. Наглядное превью:

![SFSymbols Render Modes Preview](https://cdn.ivanvorobei.by/websites/sparrowcode.io/sf-symbols-3/render-modes-preview.jpg)

Рендеры доступны для каждого символа, но возможны ситуации когда результат для разных рендеров будет совпадать и иконка не изменит внешнего вида. Лучше выбирать [в приложении](https://developer.apple.com/sf-symbols/), предварительно установив нужный рендер.

## Monochrome Render

Иконка целиком красится в указанный цвет. Цвет управляется через `tintColor`.

```swift
// UIKit
let image = UIImage(systemName: "doc")
let imageView = UIImageView(image: image)
imageView.tintColor = .systemRed

// SwiftUI
Image(systemName: "doc")
    .foregroundColor(.red)
```

Способ работает для любых изображений, не только для SF Symbols.

## Hierarchical Render

Отрисовывает иконку в одном цвете, но создает глубину с помощью прозрачности для элементов символа.

```swift
// UIKit
let config = UIImage.SymbolConfiguration(hierarchicalColor: .systemIndigo)
let image = UIImage(systemName: "square.stack.3d.down.right.fill", withConfiguration: config)

// SwiftUI
Image(systemName: "square.stack.3d.down.right.fill")
    .symbolRenderingMode(.hierarchical)
    .foregroundColor(.indigo)
```

Обратите внимание, иногда рендер с моно-цветом совпадает с иерархическим.

![SFSymbols Hierarchical Render](https://cdn.ivanvorobei.by/websites/sparrowcode.io/sf-symbols-3/hierarchical-render.jpg)

## Palette Render

Отрисовывает иконку в кастомных цветах. Каждому символу нужно определенное количество цветов.

```swift
// UIKit
let config = UIImage.SymbolConfiguration(paletteColors: [.systemRed, .systemGreen, .systemBlue])
let image = UIImage(systemName: "person.3.sequence.fill", withConfiguration: config)

// SwiftUI
Image(systemName: "person.3.sequence.fill")
    .symbolRenderingMode(.palette)
    .foregroundStyle(.red, .green, .blue)
```

Если у символа 1 сегмент для цвета, он будет использовать первый указанный цвет. Если у символа 2 сегмента, но будет указан 1 цвет, он будет использоваться для обоих сегментов. Если укажете 2 цвета - они применятся соответственно. Если указать 3 цвета, третий игнорируется.

![SFSymbols Palette Render](https://cdn.ivanvorobei.by/websites/sparrowcode.io/sf-symbols-3/palette-render.jpg)

## Multicolor Render

Важные элементы будут иметь фиксированный цвет, для заполняющего можно указать кастомный.

```swift
// UIKit
let config = UIImage.SymbolConfiguration.configurationPreferringMulticolor()
let image = UIImage(systemName: "externaldrive.badge.plus", withConfiguration: config)

// SwiftUI
Image(systemName: "externaldrive.badge.plus")
    .symbolRenderingMode(.multicolor)
```

Изображения, у которых нет многоцветного варианта, будут автоматически отображаться в моно-цвете. На превью заполняющий цвет `.systemCyan`:

![SFSymbols Multicolor Render](https://cdn.ivanvorobei.by/websites/sparrowcode.io/sf-symbols-3/multicolor-render.jpg)

## Symbol Variant

Некоторые символы имеют поддержку форм, например колокольчик `bell` можно вписать в квадрат или круг. В `UIKit` нужно вызывать их по имени - например, `bell.square`, но в SwiftUI есть модификатор `.symbolVariant()`:

```swift
// Колокольчик перечеркнут
Image(systemName: 'bell')
    .symbolVariant(.slash)

// Вписывает в квадрат
Image(systemName: 'bell')
    .symbolVariant(.square)

// Можно комбинировать
Image(systemName: 'bell')
    .symbolVariant(.fill.slash)
```

Обратите внимание, в последнем примере можно комбинировать варианты символов.

## Адаптация

SwiftUI умеет отображать символы соответственно контексту. Для iOS Apple использует залитые иконки, но в macOS иконки без заливки, только линии. Если вы используете SF Symbols для Side Bar, то не нужно указывать, залитый символ или нет - он будет автоматически адаптироваться в зависимости от системы.

```swift
Label('Home', systemImage: 'person')
    .symbolVariant(.none)
```

Это все изменения в новой версии. Напишите [в комментариях к посту](https://t.me/sparrowcode/82) была ли полезна статья, и используете ли SF Symbols в проектах.

