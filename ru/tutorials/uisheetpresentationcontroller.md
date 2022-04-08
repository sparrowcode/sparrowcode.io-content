Попытки управлять высотой модальных контроллеров мучают разработчиков уже 4 года. Когда я был молодым, сделал [свою версию](https://github.com/ivanvorobei/SPStorkController) на снепшотах. C появлением нативных модальных контроллеров проблема решилось частично. Только с iOS 15 управлять высотой можно из коробки:

[UISheetPresentationController Preview](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/uisheetpresentationcontroller.mov)

Выглядит круто, а кейсов использования много. Чтобы показать дефолтный `sheet`-controller, используйте код:

```swift
let controller = UIViewController()
if let sheetController = controller.sheetPresentationController {
    sheetController.detents = [.medium(), .large()]
}
present(controller, animated: true)
```

Это модальный контроллер, которому добавили сложное поведение. Можно оборачивать в навигационный контроллер, добавлять заголовок и бар-кнопки. Оберните код с `sheetController` в `if #available(iOS 15.0, *) {}`, если проект поддерживает предыдущие версии iOS.

## Detents (стопоры)

Стопор - это высота, к которой стремится контроллер. Прямо как в пейджинге скролла или когда электрон не на своём энергетическом уровне.

Доступно два стопора: `.medium()` с размером на половину экрана и `.large()`, который повторяет большой модальный контроллер. Если оставить только `.medium()`-стопор, то контроллер откроется на половину экрана и подниматься выше не будет. Установить свою высоту нельзя, только доступные стопоры. По умолчанию контроллер показывается со стопором `.large()`.

Доступные стопоры указываются так:

```swift
sheetController.detents = [.medium(), .large()]
```

Если указать только один стопор, то переключится жестом будет нельзя.

### Переключение между стопорами

Чтобы перейти из одного стопора в другой, используйте код:

```swift
sheetController.animateChanges {
    sheetController.selectedDetentIdentifier = .medium
}
```

Можно вызывать без блока анимации. Так же можно переключть стопор без возможности изменять его, для этого меняем доступные стопоры:

```swift
sheetController.animateChanges {
    sheetController.detents = [.large()]
}
```

Контроллер переключится в `.large()` стопор и не даст переключится жестом в `.medium()`.

## Запретить Dismiss

Если вы хотите зафиксировать контроллер в одном стопоре, без возможности закрыть его, установите `isModalInPresentation` в `true` родителю:

```swift
navigationController.isModalInPresentation = true
if let sheetController = nav.sheetPresentationController {
    sheetController.detents = [.medium()]
    sheetController.largestUndimmedDetentIdentifier = .medium
}
```

## Scroll Контента

Если активен `.medium()`-стопор и контнент контроллера скролится, то если скролить вверх - модальный контрллер перейдет в `.large()` стопор. Контент останется на месте. Чтобы изменить поведение, укажите:

```swift
sheetController.prefersScrollingExpandsWhenScrolledToEdge = false
```

Теперь при скроле вверх будет отрабатывать скрол контента, и не будет переключение в большой стопор. Сделать это можно будет только потянув за navigation-бар.

## Альбомная ориентация

По умолчанию `sheet`-контроллер в альбомной ориентации выглядит как обычный контроллер. Дело в том, что `.medium()` -стопор недоступен, а `.large()` - это и есть дефолтный режим модального контроллера. Но можно добавить отступы по краям.

```swift
sheetController.prefersEdgeAttachedInCompactHeight = true
```

Вот как это выглядит:

![Landscape for UISheetPresentationController](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/landscape.jpg)

Чтобы контроллер учитывал prefered-размер, установите `widthFollowsPreferredContentSizeWhenEdgeAttached` в `true`.

## Затемнение фона

Указываете самый большой стопор, который не нужно затемнять. Всё, что больше этого стопора, будет затемняться. Код:

```swift
sheetController.largestUndimmedDetentIdentifier = .medium
```

Указано, что `.medium` затемняться не будет, а всё, что больше, будет. Можно убрать затемнение для самого большого стопора. Без затемнения будут доступны кнопки за модальным контроллером - вы сможете взаимодействовать с фоном.

## Индикатор

Чтобы добавить индикатор вверху контроллера, установите `.prefersGrabberVisible` в `true`. По умолчанию индикатор спрятан. Индикатор не влияет на safe area и layout margins, по крайней мере, на момент написания статьи.

![Grabber for UISheetPresentationController](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/prefers-grabber-visible.jpg)

## Corner Radius

Управляйте закруглением краёв у контроллера. Для этого установите `.preferredCornerRadius`. Обратите внимание, что закругление меняется не только у презентуемого контроллера, но и у родителя.

![Grabber for UISheetPresentationController](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/preferred-corner-radius.jpg)

На скриншоте я установил corner-радиус в `22`. Радиус сохраняется для `.medium`-стопора. На этом всё. Напишите в [комментариях к посту](https://t.me/sparrowcode/71), будете ли использовать в своих проектах sheet-контроллеры.

