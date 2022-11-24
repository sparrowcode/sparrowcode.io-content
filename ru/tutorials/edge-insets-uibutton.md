![Про `contentEdgeInsets` в Swift.](https://cdn.sparrowcode.io/tutorials/edge-insets-uibutton/preview.png)

Вы управляете тремя отступами - `imageEdgeInsets`, `titleEdgeInsets` и `contentEdgeInsets`. Перед погружением в процесс, гляньте [проект-пример](https://cdn.sparrowcode.io/tutorials/edge-insets-uibutton/example-project.zip). В проекте наглядно показывается как работают комбинации отступов. На видео я поставил заливку для элементов:
- Красный -> фон
- Жёлтая -> иконка
- Синий -> заголовок

[Управление отступами у `UIButton`.](https://cdn.sparrowcode.io/tutorials/edge-insets-uibutton/edge-insets-uibutton-example-preview.mov)

# `contentEdgeInsets`

Добавляет отступы вокруг заголовка и иконки. Если поставить отрицательные значения - отступ будет уменьшаться. Код:

```swift
previewButton.contentEdgeInsets.left = 10
previewButton.contentEdgeInsets.right = 10
previewButton.contentEdgeInsets.top = 5
previewButton.contentEdgeInsets.bottom = 5
```

![`contentEdgeInsets` отступы.](https://cdn.sparrowcode.io/tutorials/edge-insets-uibutton/content-edge-insets.png)

Отступы вокруг контента влияют только на размер кнопки. Фрейм и кликабельная область увеличиваются соответственно.

# `imageEdgeInsets` и `titleEdgeInsets`

Они в одной секции, потому что ваша задача добавить отступы с одной стороны и уменьшить их с другой. Добавим отступ между картинкой и заголовком `10pt`. Первая идея - добавить отступ через проперти `imageEdgeInsets`:

[Отступ `imageEdgeInsets` между иконкой и текстом.](https://cdn.sparrowcode.io/tutorials/edge-insets-uibutton/image-edge-insets-space-icon-title.mov)

Отступ добавляется, но не влияет на размер кнопки - иконка вылетает за кнопку. `titleEdgeInsets` ведет себя так же - не меняет размер кнопки. Если для текста поставить положительный отступ слева, а для иконки отрицательный отступ слева - то появится расстояние в 10pt между текстом и иконкой.

```swift
previewButton.imageEdgeInsets.left = -10
previewButton.titleEdgeInsets.left = 10
```

Это та симметрия, про которую писал выше.

> `contentEdgeInsets` меняет размер кнопки. 
> `imageEdgeInsets` и `titleEdgeInsets` не меняют размер кнопки. 

# Иконка справа от текста

Давайте поставим иконку справа от заголовка:

```swift
let buttonWidth = previewButton.frame.width
let imageWidth = previewButton.imageView?.frame.width ?? .zero
```

Смещаем заголовок к левому краю. Отступ слева был `imageWidth`. Если уменьшите на это значение, то получите левый край.

```swift
previewButton.titleEdgeInsets = UIEdgeInsets(
    top: 0, 
    left: -imageWidth, 
    bottom: 0, 
    right: imageWidth
)
```

Перемещаем иконку к правому краю. Дефолтный отступ был `0`, значит, у новой точки Y шириной станет ширина иконки.

```swift
previewButton.imageEdgeInsets = UIEdgeInsets(
    top: 0, 
    left: buttonWidth - imageWidth, 
    bottom: 0, 
    right: 0
)
```

# Deprecated

Обратите внимание, с iOS 15 отступы помечены как `depriсated`.

![Скриншот с сайта Apple Developer.](https://cdn.sparrowcode.io/tutorials/edge-insets-uibutton/depricated.png)

Несколько лет проперти будут работать. Apple рекомендуют использовать конфигурацию.

Поиграть с отступами можно в [проекте-примере](https://cdn.sparrowcode.io/tutorials/edge-insets-uibutton/example-project.zip). Задать вопрос в комментариях [к посту](https://t.me/sparrowcode/99).
