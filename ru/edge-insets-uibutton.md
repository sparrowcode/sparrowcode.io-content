<?php

use App\HTMLElements;
use App\TutorialModel;
use App\ButtonModel;
use App\Constants;

/** @var TutorialModel $tutorial */

HTMLElements::tutorialHeader(
    $tutorial,
    [
        new ButtonModel(
            'titleEdgeInsets',
            'https://developer.apple.com/documentation/uikit/uibutton/1624010-titleedgeinsets',
            true
        ),
        new ButtonModel(
            'imageEdgeInsets',
            'https://developer.apple.com/documentation/uikit/uibutton/1624034-imageedgeinsets',
            true
        ),
        new ButtonModel(
            'Проект-пример',
            'https://cdn.ivanvorobei.by/websites/sparrowcode.io/edge-insets-uibutton/example-project.zip',
            true
        )
    ],
    [
        "https://cdn.ivanvorobei.by/websites/sparrowcode.io/edge-insets-uibutton/google-structured-data/article_1_1.jpg",
        "https://cdn.ivanvorobei.by/websites/sparrowcode.io/edge-insets-uibutton/google-structured-data/article_16_9.jpg",
        "https://cdn.ivanvorobei.by/websites/sparrowcode.io/edge-insets-uibutton/google-structured-data/article_4_3.jpg"
    ]
);

HTMLElements::text(
    "Вы управляете тремя отступами - `imageEdgeInsets`, `titleEdgeInsets` и `contentEdgeInsets`. Чаще всего ваша задача сводится к выставлению симметрично-противоположных значений."
);

HTMLElements::text(
    "Перед тем как начнем погружаться, гляньтье " . HTMLElements::embeddedLink("проект-пример", "https://cdn.ivanvorobei.by/websites/sparrowcode.io/edge-insets-uibutton/example-project.zip") . ". Каждый ползунок отвечает за конкретный отсуп и вы можете их комбинировать. На видео я выставил цвет фона - красный, цвет иконки - желтый, а цвет тайтла - синий."
);

HTMLElements::video(
    "Edge Insets UIButton Example Project Preview",
    "https://cdn.ivanvorobei.by/websites/sparrowcode.io/edge-insets-uibutton/edge-insets-uibutton-example-preview.mov",
    100
);

HTMLElements::text(
    "Сделайте отступ между заголовоком и иконкой `10pt`. Когда получится, убедитесь, контролируете результат или получилось наугад. В конце туториала вы будете знать как это работает."
);

HTMLElements::titleSection(
    "contentEdgeInsets"
);

HTMLElements::text(
    "Ведёт себя предсказуемо. Он добавляет отступы вокруг заголовка и иконки. Если поставите отрицательные значения - то отступ будет уменьшаться. Код:"
);

HTMLElements::blockCode("
// Я знаю про сокращенную запись
previewButton.contentEdgeInsets.left = 10
previewButton.contentEdgeInsets.right = 10
previewButton.contentEdgeInsets.top = 5
previewButton.contentEdgeInsets.bottom = 5
");

HTMLElements::imageToRight(
    "contentEdgeInsets",
    "https://cdn.ivanvorobei.by/websites/sparrowcode.io/edge-insets-uibutton/content-edge-insets.png",
    420
);

HTMLElements::text(
    "Вокруг контента добавились отступы. Они добавляются пропорционально и влияют только на размер кнопки. Практический смысл - расширить область нажатия, если кнопка маленькая."
);

HTMLElements::titleSection(
    "imageEdgeInsets и titleEdgeInsets"
);

HTMLElements::text(
    "Я вынес их в одну секцию не просто так. Чаще всего задача будет сводится к симметричному добавлению отсупов с одной стороны, и уменьшению с другой. Звучит сложно, сейчас разрулим."
);

HTMLElements::text(
    "Добавим отступ между картинкой и заголовоком, пускай `10pt`. Первая мысль - добавить отступ через проперти `imageEdgeInsets`:"
);

HTMLElements::video(
    "imageEdgeInsets space between icon and title",
    "https://cdn.ivanvorobei.by/websites/sparrowcode.io/edge-insets-uibutton/image-edge-insets-space-icon-title.mov",
    100
);

HTMLElements::text(
    "Поведение сложнее. Отступ добавляется, но не влияет на размер кнопки. Если бы влиял - проблема была решена."
);

HTMLElements::text(
    "Напарник `titleEdgeInsets` работает так же - не меняет размер кнопки. Логично добавить отступ для заголовка, но противоположный по значению. Выглядеть это будет так:"
);

HTMLElements::blockCode("
previewButton.imageEdgeInsets.left = -10
previewButton.titleEdgeInsets.left = 10
");

HTMLElements::text(
    "Это та симметрия, про которую писал выше."
);

HTMLElements::important(
    "`imageEdgeInsets` и `titleEdgeInsets` не меняют размер кнопки. А вот `contentEdgeInsets` - меняет."
);

HTMLElements::text(
    "Запомните это, и больше не будет проблем с правильными отступами. Давайте усложним задачу - поставим иконку справа от заголовка."
);

HTMLElements::blockCode("
let buttonWidth = previewButton.frame.width
let imageWidth = previewButton.imageView?.frame.width ?? .zero

// Смещаем заголовок к левому краю. 
// Отступ слева был `imageWidth`, значит уменьшив на это значение получим левый край.
previewButton.titleEdgeInsets = UIEdgeInsets(
    top: 0, 
    left: -imageWidth, 
    bottom: 0, 
    right: imageWidth
)

// Перемещаем иконку к правому краю.
// Дефолтный отступ был 0,значит новая точка Y будет ширина - ширина иконки.
previewButton.imageEdgeInsets = UIEdgeInsets(
    top: 0, 
    left: buttonWidth - imageWidth, 
    bottom: 0, 
    right: 0
)
");

HTMLElements::titleSection(
    "Готовый класс"
);

HTMLElements::text(
    "В моей библиотеке " . HTMLElements::embeddedLink("SparrowKit", Constants::$github_sparrowkit) . " уже есть готовый класс кнопки " . HTMLElements::embeddedLink("`SPButton`", 'https://github.com/ivanvorobei/SparrowKit/blob/main/Sources/SparrowKit/UIKit/Classes/Buttons/SPButton.swift') . " с поддержкой отсупа между картинкой и текстом."
);

HTMLElements::blockCode("
button.titleImageInset = 8
");

HTMLElements::text("Работает для RTL локализации. Если картинки нет, отступ не добавляется. Разработчку нужно только выставить значение отступа.");

HTMLElements::imageToRight(
    "Deprecated imageEdgeInsets и titleEdgeInsets",
    "https://cdn.ivanvorobei.by/websites/sparrowcode.io/edge-insets-uibutton/depricated.png",
    420
);

HTMLElements::titleSection(
    "Deprecated"
);

HTMLElements::text(
    "Я должен обратить внимание, с iOS 15 наши друзья помечены `depriсated`."
);

HTMLElements::text(
    "Несколько лет проперти будут работать. Apple рекомендуют использовать конфигурацию. Посмотрим, что останется в живых - конфигурация, или старый добрый `padding`."
);

HTMLElements::text(
    "На этом всё. Чтобы наглядно побаловаться, качайте " . HTMLElements::embeddedLink('проект-пример', 'https://cdn.ivanvorobei.by/websites/sparrowcode.io/edge-insets-uibutton/example-project.zip') . ". Задать вопросы можно в комментариях " . HTMLElements::embeddedTelegramPostLink(99, 'к посту') . "."
);

HTMLElements::tutorialFooter($tutorial);
