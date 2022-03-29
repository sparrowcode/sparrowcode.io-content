Представьте, что вы управляете тремя отступами - `imageEdgeInsets`, `titleEdgeInsets` и `contentEdgeInsets`. Чаще всего ваша задача сводится к выставлению симметрично-противоположных значений.

Перед погружением в процесс гляньте [проект-пример](https://cdn.sparrowcode.io/tutorials/edge-insets-uibutton/example-project.zip). Каждый ползунок отвечает за конкретный отступ — вы можете их комбинировать. На видео такие настройки: цвет фона - красный, цвет иконки - жёлтый, а тайтла - синий.

[Edge Insets UIButton Example Project Preview](https://cdn.sparrowcode.io/tutorials/edge-insets-uibutton/edge-insets-uibutton-example-preview.mov)

Сделайте отступ между заголовком и иконкой `10pt`. Когда получится, убедитесь, контролируете ли вы результат или получилось наугад. В конце туториала вы будете знать, как это работает.

## contentEdgeInsets

Свойство ведёт себя предсказуемо и добавляет отступы вокруг заголовка и иконки. Если поставите отрицательные значения - отступ будет уменьшаться. Код:

```swift
// Я знаю про сокращённую запись
previewButton.contentEdgeInsets.left = 10
previewButton.contentEdgeInsets.right = 10
previewButton.contentEdgeInsets.top = 5
previewButton.contentEdgeInsets.bottom = 5
```

![contentEdgeInsets](https://cdn.sparrowcode.io/tutorials/edge-insets-uibutton/content-edge-insets.png)

Вокруг контента добавились отступы. Они добавляются пропорционально и влияют только на размер кнопки. Зачем нужны? Расширить область нажатия, если кнопка маленькая.

## imageEdgeInsets и titleEdgeInsets

Я вынес их в одну секцию не просто так. Чаще всего задача будет сводиться к симметричному добавлению отступов с одной стороны и уменьшению с другой. Звучит сложно, но сейчас разрулим.

Добавим отступ между картинкой и заголовком, пускай `10pt`. Первая мысль - добавить отступ через проперти `imageEdgeInsets`:

[imageEdgeInsets space between icon and title](https://cdn.sparrowcode.io/tutorials/edge-insets-uibutton/image-edge-insets-space-icon-title.mov)

Тут поведение сложнее. Отступ добавляется, но не влияет на размер кнопки. Если бы влиял, проблема бы решилась.

Напарник `titleEdgeInsets` работает так же - не меняет размер кнопки. Логично добавить отступ для заголовка, но противоположный по значению. Выглядеть это будет так:

```swift
previewButton.imageEdgeInsets.left = -10
previewButton.titleEdgeInsets.left = 10
```

Это та симметрия, про которую писал выше.

>`imageEdgeInsets` и `titleEdgeInsets` не меняют размер кнопки. А вот `contentEdgeInsets` меняет. Запомните это, и больше не будет проблем с правильными отступами. 

Давайте усложним задачу - поставим иконку справа от заголовка.

```swift
let buttonWidth = previewButton.frame.width
let imageWidth = previewButton.imageView?.frame.width ?? .zero

// Смещаем заголовок к левому краю. 
// Отступ слева был `imageWidth`. Если уменьшите на это значение, то получите левый край.
previewButton.titleEdgeInsets = UIEdgeInsets(
    top: 0, 
    left: -imageWidth, 
    bottom: 0, 
    right: imageWidth
)

// Перемещаем иконку к правому краю.
// Дефолтный отступ был 0, значит, у новой точки Y шириной станет ширина иконки.
previewButton.imageEdgeInsets = UIEdgeInsets(
    top: 0, 
    left: buttonWidth - imageWidth, 
    bottom: 0, 
    right: 0
)
```

## Готовый класс

В моей библиотеке [SparrowKit](https://github.com/ivanvorobei/SparrowKit) уже есть готовый класс кнопки [`SPButton`](https://github.com/ivanvorobei/SparrowKit/blob/main/Sources/SparrowKit/UIKit/Classes/Buttons/SPButton.swift) с поддержкой отступа между картинкой и текстом.

```swift
button.titleImageInset = 8
```

Работает для RTL-локализации. Если картинки нет, отступ не добавляется. Разработчику нужно только выставить значение отступа.

![Deprecated imageEdgeInsets и titleEdgeInsets](https://cdn.sparrowcode.io/tutorials/edge-insets-uibutton/depricated.png)

## Deprecated

Обратите внимание, с iOS 15 наши друзья помечены `depriсated`.

Несколько лет проперти будут работать. Apple рекомендуют использовать конфигурацию. Посмотрим, что останется в живых - конфигурация или старый добрый `padding`.

На этом всё. Чтобы наглядно побаловаться, качайте [проект-пример](https://cdn.sparrowcode.io/tutorials/edge-insets-uibutton/example-project.zip). Задать вопросы можно в комментариях [к посту](https://t.me/sparrowcode/99).
