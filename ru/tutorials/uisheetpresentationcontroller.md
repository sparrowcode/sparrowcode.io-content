Когда я был молодым, сделал [либу](https://github.com/ivanvorobei/SPStorkController) для управления высотой контроллера на снепшотах. Новые модальные контроллеры частично решили проблему нативно, а с iOS 15 управлять высотой можно из коробки:

[Пример работы UISheetPresentationController со сторами посередине и вверху.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/header.mov)

Выглядит круто, кейсов много. Чтобы показать дефолтный `sheet`-controller, используйте код:

```swift
let controller = UIViewController()
if let sheetController = controller.sheetPresentationController {
    sheetController.detents = [.medium(), .large()]
}
present(controller, animated: true)
```

Это обычный модальный контроллер, которому добавили сложное поведение. Можно оборачивать в навигационный контроллер, добавлять заголовок и бар-кнопки. Если проект поддерживает предыдущие версии iOS, оберните код с `sheetController` в `if #available(iOS 15.0, *) {}`.

## Что такое detents (стопоры)

Стопор — высота, к которой стремится контроллер. Похоже на ситуации с пейджингом скролла или когда электрон не на своём энергетическом уровне.

Доступно два стопора: `.medium()` с размером на половину экрана и `.large()`, который повторяет большой модальный контроллер. Если оставить только `.medium()`-стопор, то контроллер откроется на половину экрана и подниматься выше не будет. Установить свою высоту в пикселях нельзя, выбираем только из доступных стопоров. По умолчанию контроллер показывается со стопором `.large()`.

Доступные стопоры указываются так:

```swift
sheetController.detents = [.medium(), .large()]
```

Если укажите только один стопор, то переключиться жестом не получится.

### Как переключаться между стопорами

Чтобы перейти из одного стопора в другой, используйте код:

```swift
sheetController.animateChanges {
    sheetController.selectedDetentIdentifier = .medium
}
```

Можно вызывать без блока анимации. Ещё можно переключать стопор без возможности изменять его, для этого меняем доступные стопоры:

```swift
sheetController.animateChanges {
    sheetController.detents = [.large()]
}
```

Контроллер переключится в `.large()` стопор и не даст переключится жестом в `.medium()`.

## Dismiss

Если вы хотите зафиксировать контроллер в одном стопоре без возможности закрыть его, установите `isModalInPresentation` в `true` родителю:

```swift
navigationController.isModalInPresentation = true
if let sheetController = nav.sheetPresentationController {
    sheetController.detents = [.medium()]
    sheetController.largestUndimmedDetentIdentifier = .medium
}
```

[Пример работы sheet-контроллера с запретом на закрытие.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/prevent-dismiss.mov)

## Scroll контента

Если активен `.medium()`-стопор и контент контроллера скролится, то при скролле вверх модальный контроллер перейдёт в `.large()`-стопор, а контент останется на месте.

[Пример стандартного скрола на sheet-контроллере.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/scrolling-expands-true.mov)

Чтобы сначала скролить контент, укажите такие параметры:

```swift
sheetController.prefersScrollingExpandsWhenScrolledToEdge = false
```

[Пример скрола на sheet-контроллере с `prefersScrollingExpandsWhenScrolledToEdge = false`.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/scrolling-expands-false.mov)

Теперь при скроле вверх будет отрабатываться скрол контента. Чтобы перейти в большой стопор, потяните за navigation-бар.

## Альбомная ориентация

По умолчанию `sheet`-контроллер в альбомной ориентации выглядит как обычный контроллер. Дело в том, что `.medium()`-стопор недоступен, а `.large()` - дефолтный режим модального контроллера. Но можно добавить отступы по краям.

```swift
sheetController.prefersEdgeAttachedInCompactHeight = true
```

Вот как это выглядит:

![Пример sheet-контроллера в альбомной ориентации с отступами по краям.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/edge-attached.png)

Чтобы контроллер учитывал prefered-размер, установите `widthFollowsPreferredContentSizeWhenEdgeAttached` в `true`.

## Как затемнять фон

Если фон затемнён, кнопка за модальным контроллером будет не кликабельная. Чтобы разрешить взаимодействие с фоном, уберите затемнение. Сначала укажите самый большой стопор, который не нужно затемнять. Вот код:

```swift
sheetController.largestUndimmedDetentIdentifier = .medium
```

[Пример отключения затемнения для `.medium` стопора на sheet-контроллере.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/undimmed-detent.mov)

Указано, что `.medium` затемняться не будет, а всё, что больше, будет. Можно убрать затемнение и для самого большого стопора.

## Как добавить индикатор

Чтобы добавить индикатор вверху контроллера, установите `.prefersGrabberVisible` в `true`. По умолчанию индикатор спрятан. Индикатор не влияет на safe area и layout margins.

![Пример grabber-индикатора на sheet-контроллере.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/grabber.png)

## Corner Radius

Можно управлять закруглением краёв у контроллера. Установите значение для `.preferredCornerRadius`. Закругление меняется не только у презентуемого контроллера, но и у родителя.

![Пример выставленного corner-радиуса на sheet-контроллере.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/corner-radius.png)

На скриншоте я установил corner-радиус в `22`. Радиус сохраняется и для `.medium`-стопора. 

На этом всё. Напишите в [комментариях к посту](https://t.me/sparrowcode/71), будете ли использовать в своих проектах sheet-контроллеры.
