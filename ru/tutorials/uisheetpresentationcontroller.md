Когда я был молодым, сделал [либу](https://github.com/ivanvorobei/SPStorkController) для управления высотой контроллера на снепшотах. Новые модальные контроллеры частично решили проблему нативно. А с iOS 15 управлять высотой можно из коробки:

[Пример работы UISheetPresentationController со сторами посередине и вверху.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/header.mov)

Выглядит круто, кейсов много. Чтобы показать дефолтный `sheet`-controller, используйте код:

```swift
let controller = UIViewController()
if let sheetController = controller.sheetPresentationController {
    sheetController.detents = [.medium(), .large()]
}
present(controller, animated: true)
```

Это обычный модальный контроллер, которому добавили сложное поведение. Можно оборачивать в навигационный контроллер, добавлять заголовок и бар-кнопки. Оберните код с `sheetController` в `if #available(iOS 15.0, *) {}`, если проект поддерживает предыдущие версии iOS.

## Detents (стопоры)

Стопор - это высота, к которой стремится контроллер. Прямо как в пейджинге скролла или когда электрон не на своём энергетическом уровне.

Доступно два стопора: `.medium()` с размером на половину экрана и `.large()`, который повторяет большой модальный контроллер. Если оставить только `.medium()`-стопор, то контроллер откроется на половину экрана и подниматься выше не будет. Установить свою высоту в пикселях нельзя, выбираем только из доступных стопоров. По умолчанию контроллер показывается со стопором `.large()`.

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

## Dismiss

Если вы хотите зафиксировать контроллер в одном стопоре, без возможности закрыть его, установите `isModalInPresentation` в `true` родителю:

```swift
navigationController.isModalInPresentation = true
if let sheetController = nav.sheetPresentationController {
    sheetController.detents = [.medium()]
    sheetController.largestUndimmedDetentIdentifier = .medium
}
```

[Пример работы sheet-контроллера с запретом на закрытие.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/prevent-dismiss.mov)

## Scroll Контента

Если активен `.medium()`-стопор и контент контроллера скролится, то если скролить вверх - модальный контрллер перейдет в `.large()` стопор. Контент останется на месте.

[Пример стандартного скрола на sheet-контроллере.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/scrolling-expands-true.mov)

Чтобы сначала скролить контент, укажите:

```swift
sheetController.prefersScrollingExpandsWhenScrolledToEdge = false
```

[Пример скрола на sheet-контроллере с `prefersScrollingExpandsWhenScrolledToEdge = false`.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/scrolling-expands-false.mov)

Теперь при скроле вверх будет отрабатывать скрол контента. Чтобы перейти в большой стопор, нужно потянув за navigation-бар.

## Альбомная ориентация

По умолчанию `sheet`-контроллер в альбомной ориентации выглядит как обычный контроллер. Дело в том, что `.medium()` -стопор недоступен, а `.large()` - это и есть дефолтный режим модального контроллера. Но можно добавить отступы по краям.

```swift
sheetController.prefersEdgeAttachedInCompactHeight = true
```

Вот как это выглядит:

![Пример sheet-контроллера в альбомной ориентации с отступами по краям.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/edge-attached.png)

Чтобы контроллер учитывал prefered-размер, установите `widthFollowsPreferredContentSizeWhenEdgeAttached` в `true`.

## Затемнение фона

Если фон затемнен, кнопка за модальным контроллером будет не кликабельная. Чтобы разрешить взаимодействие с фоном, нужно убрать затемнение. Указываете самый большой стопор, который не нужно затемнять. Код:

```swift
sheetController.largestUndimmedDetentIdentifier = .medium
```

[Пример отключения затемнения для `.medium` стопора на sheet-контроллере.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/undimmed-detent.mov)

Указано, что `.medium` затемняться не будет, а всё, что больше - будет. Можно убрать затемнение и для самого большого стопора.

## Индикатор

Чтобы добавить индикатор вверху контроллера, установите `.prefersGrabberVisible` в `true`. По умолчанию индикатор спрятан. Индикатор не влияет на safe area и layout margins.

![Пример grabber-индикатора на sheet-контроллере.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/grabber.png)

## Corner Radius

Можно управлять закруглением краёв у контроллера. Установите значение для `.preferredCornerRadius`. Закругление меняется не только у презентуемого контроллера, но и у родителя.

![Пример выставленного corner-радиуса на sheet-контроллере.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/corner-radius.png)

На скриншоте я установил corner-радиус в `22`. Радиус сохраняется и для `.medium`-стопора. На этом всё. Напишите в [комментариях к посту](https://t.me/sparrowcode/71), будете ли использовать в своих проектах sheet-контроллеры.

