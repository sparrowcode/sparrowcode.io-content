<?php

use App\HTMLElements;
use App\TutorialModel;
use App\ButtonModel;

/** @var TutorialModel $tutorial */

HTMLElements::tutorialHeader(
    $tutorial,
    [
        new ButtonModel(
            'developer.apple.com',
            'https://developer.apple.com/sf-symbols/',
            true
        )
    ],
    [
        "https://cdn.ivanvorobei.by/websites/sparrowcode.io/sf-symbols-3/google-structured-data/article_1_1.jpg",
        "https://cdn.ivanvorobei.by/websites/sparrowcode.io/sf-symbols-3/google-structured-data/article_16_9.jpg",
        "https://cdn.ivanvorobei.by/websites/sparrowcode.io/sf-symbols-3/google-structured-data/article_4_3.jpg"
    ]
);

HTMLElements::text(
    "Примеры кода будут для `SwiftUI` и `UIKit`. Внимательно следите за совместимостью символов - не все доступны для 14 и предудыщих iOS. Глянуть с какой версии доступен символ можно " . HTMLElements::embeddedLink('в приложении', 'https://developer.apple.com/sf-symbols/') . "."
);

HTMLElements::titleSection("Render Modes");

HTMLElements::text(
    "Render Modes - это отрисовка иконки в цветовой схеме. Доступны монохром, иерархический, палетка и мульти-цвет. Наглядное превью:"
);

HTMLElements::image(
    "SFSymbols Render Modes Preview",
    "https://cdn.ivanvorobei.by/websites/sparrowcode.io/sf-symbols-3/render-modes-preview.jpg",
    100
);

HTMLElements::text(
    "Рендеры доступны для каждого символа, но возможны ситуации когда результат для разных рендеров будет совпадать и иконка не изменит внешнего вида. Лучше выбирать " . HTMLElements::embeddedLink('в приложении', 'https://developer.apple.com/sf-symbols/') . ", предварительно установив нужный рендер."
);

HTMLElements::titleParagraph("Monochrome Render");

HTMLElements::text(
    "Иконка целиком красится в указанный цвет. Цвет управляется через `tintColor`."
);

HTMLElements::blockCode('
// UIKit
let image = UIImage(systemName: "doc")
let imageView = UIImageView(image: image)
imageView.tintColor = .systemRed

// SwiftUI
Image(systemName: "doc")
    .foregroundColor(.red)
');

HTMLElements::text(
    "Способ работает для любых изображений, не только для SF Symbols."
);

HTMLElements::titleParagraph("Hierarchical Render");

HTMLElements::text(
    "Отрисовывает иконку в одном цвете, но создает глубину с помощью прозрачности для элементов символа."
);

HTMLElements::blockCode('
// UIKit
let config = UIImage.SymbolConfiguration(hierarchicalColor: .systemIndigo)
let image = UIImage(systemName: "square.stack.3d.down.right.fill", withConfiguration: config)

// SwiftUI
Image(systemName: "square.stack.3d.down.right.fill")
    .symbolRenderingMode(.hierarchical)
    .foregroundColor(.indigo)
');

HTMLElements::text(
    "Обратите внимание, иногда рендер с моно-цветом совпадает с иерархическим."
);

HTMLElements::image(
    "SFSymbols Hierarchical Render",
    "https://cdn.ivanvorobei.by/websites/sparrowcode.io/sf-symbols-3/hierarchical-render.jpg",
    100
);

HTMLElements::titleParagraph("Palette Render");

HTMLElements::text(
    "Отрисовывает иконку в кастомных цветах. Каждому символу нужно опредленное количество цветов."
);

HTMLElements::blockCode('
// UIKit
let config = UIImage.SymbolConfiguration(paletteColors: [.systemRed, .systemGreen, .systemBlue])
let image = UIImage(systemName: "person.3.sequence.fill", withConfiguration: config)

// SwiftUI
Image(systemName: "person.3.sequence.fill")
    .symbolRenderingMode(.palette)
    .foregroundStyle(.red, .green, .blue)
');

HTMLElements::text(
    "Если у символа 1 сегмент для цвета, он будет использовать первый указанный цвет. Если у символа 2 сегмента, но будет указан 1 цвет, он будет использоваться для обоих сегментов. Если укажете 2 цвета - они применятся соотвественно. Если указать 3 цвета, третий игнорируется."
);

HTMLElements::image(
    "SFSymbols Palette Render",
    "https://cdn.ivanvorobei.by/websites/sparrowcode.io/sf-symbols-3/palette-render.jpg",
    100
);

HTMLElements::titleParagraph("Multicolor Render");

HTMLElements::text(
    "Важные элементы будут иметь фиксированный цвет, для заполняющего можно указать кастомный."
);

HTMLElements::blockCode('
// UIKit
let config = UIImage.SymbolConfiguration.configurationPreferringMulticolor()
let image = UIImage(systemName: "externaldrive.badge.plus", withConfiguration: config)

// SwiftUI
Image(systemName: "externaldrive.badge.plus")
    .symbolRenderingMode(.multicolor)
');

HTMLElements::text(
    "Изображения, у которых нет многоцветного варианта, будут автоматически отображаться в моно-цвете. На превью заполняющий цвет `.systemCyan`:"
);

HTMLElements::image(
    "SFSymbols Multicolor Render",
    "https://cdn.ivanvorobei.by/websites/sparrowcode.io/sf-symbols-3/multicolor-render.jpg",
    100
);

HTMLElements::titleSection("Symbol Variant");

HTMLElements::text(
    "Некоторые символы имеют поддержку форм, например колокольчик `bell` можно вписать в квадарт или круг. В `UIKit` нужно вызывать их по имени - например, `bell.square`, но в SwiftUI есть модификатор `.symbolVariant()`:"
);

HTMLElements::blockCode("
// Колокльчик перечеркнут
Image(systemName: 'bell')
    .symbolVariant(.slash)

// Вписывает в квадарт
Image(systemName: 'bell')
    .symbolVariant(.square)

// Можно комбинировать
Image(systemName: 'bell')
    .symbolVariant(.fill.slash)
");

HTMLElements::text(
    "Обратите внимание, в последнем примере можно комбинировать варианты символов."
);

HTMLElements::titleParagraph("Адаптация");

HTMLElements::text(
    "SwiftUI умеет отображать символы соотвественно контексту. Для iOS Apple использует залитые иконки, но в macOS иконки без заливки, только линии. Если вы используете SF Symbols для Side Bar, то не нужно указывать, залитый символ или нет - он будет автоматически адаптироваться в зависимости от системы."
);

HTMLElements::blockCode("
Label('Home', systemImage: 'person')
    .symbolVariant(.none)
");

HTMLElements::text(
    "Это все изменения в новой версии. Напишите " . HTMLElements::embeddedTelegramPostLink("82", 'в коментариях к посту') . " была ли полезна статья, и используете ли SF Symbols в проектах."
);

HTMLElements::tutorialFooter($tutorial);
