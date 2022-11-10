![Сравнение кастового контроллера с `UISheetPresentationController`.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/preview.png)

Когда я был молодым, то сделал [либу](https://github.com/ivanvorobei/SPStorkController) с походим поведением на снепшотах. В iOS 13 Apple представила обновленные модальные контроллеры, а с iOS 15 можно управлять их высотой:

[Sheet-контроллер со стопорами посередине и сверху.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/header.mov)

## Быстрый старт 

Чтобы показать дефолтный sheet-controller, используйте код:

```swift
let controller = UIViewController()
if let sheetController = controller.sheetPresentationController {
    sheetController.detents = [.medium(), .large()]
}
present(controller, animated: true)
```

Это обычный модальный контроллер, которому добавили сложное поведение. Sheet-контроллер можно оборачивать в навигационный контроллер, добавлять заголовок и бар-кнопки. Если проект поддерживает предыдущие версии iOS, оберните код с `sheetController` в `if #available(iOS 15.0, *) {}`.

## Cтопоры (Detents)

Стопор — высота, к которой стремится контроллер. Похоже на ситуации с пейджингом скролла или когда электрон не на своём энергетическом уровне.

Доступно два стопора:
- `.medium()` с размером на половину экрана 
- `.large()` повторяет большой модальный контроллер. 

Если оставить только `.medium()`, то контроллер откроется на половину экрана и подниматься выше не будет. Установить свою высоту в пикселях нельзя, выбираем только из доступных стопоров. По умолчанию контроллер показывается со стопором `.large()`.

Доступные стопоры указываются так:

```swift
sheetController.detents = [.medium(), .large()]
```

Если укажите только один стопор, то переключиться между ними жестом не получится.

### Переключение между стопорами кодом

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

Контроллер переключиться в `.large()`-стопор и больше не даст переключиться жестом в `.medium()`.

## Заблокировать Dismiss

Если вы хотите зафиксировать контроллер в одном стопоре без возможности закрыть его, установите `isModalInPresentation` в `true` родителю. В примере родитель это навигационный контроллер:

```swift
navigationController.isModalInPresentation = true
if let sheetController = nav.sheetPresentationController {
    sheetController.detents = [.medium()]
    sheetController.largestUndimmedDetentIdentifier = .medium
}
```

[Sheet-контроллер с запретом на закрытие.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/prevent-dismiss.mov)

## Скроллинг контента

Если активен `.medium()`-стопор и контент контроллера скролится, то при скролле вверх модальный контроллер перейдёт в `.large()`-стопор, а контент останется на месте.

[Стандартный скролл на sheet-контроллере.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/scrolling-expands-true.mov)

Чтобы скролить контент без изменения стопора, укажите такие параметры:

```swift
sheetController.prefersScrollingExpandsWhenScrolledToEdge = false
```

[Скролл на sheet-контроллере с `prefersScrollingExpandsWhenScrolledToEdge = false`.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/scrolling-expands-false.mov)

Теперь при скролле вверх будет отрабатываться скролл контента. 

> Чтобы перейти в большой стопор, потяните за navigation-бар.

## Альбомная ориентация

По умолчанию sheet-контроллер в альбомной ориентации выглядит как обычный контроллер. Дело в том, что `.medium()`-стопор недоступен, а `.large()` — дефолтный режим модального контроллера. Но можно добавить отступы по краям.

```swift
sheetController.prefersEdgeAttachedInCompactHeight = true
```

Вот как это выглядит:

![Sheet-контроллер в альбомной ориентации с отступами по краям.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/edge-attached.png)

Чтобы контроллер учитывал prefered-размер, установите `widthFollowsPreferredContentSizeWhenEdgeAttached` в `true`.

## Затемнить фон

Если фон затемнён, кнопки за модальным контроллером будут не кликабельные. Чтобы разрешить взаимодействие с фоном, нужно убрать затемнение. Укажите самый большой стопор, который не нужно затемнять. Вот код:

```swift
sheetController.largestUndimmedDetentIdentifier = .medium
```

[Sheet-контроллер с отключенным затемнением для `.medium` стопора.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/undimmed-detent.mov)

Указано, что `.medium` затемняться не будет, а всё, что больше - будет. Можно убрать затемнение и для самого большого стопора.

## Индикатор

Чтобы добавить индикатор вверху контроллера, установите `.prefersGrabberVisible` в `true`. По умолчанию индикатор спрятан. Индикатор не влияет на safe area и layout margins.

![Grabber-индикатора на sheet-контроллере.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/grabber.png)

## Corner Radius

Можно управлять закруглением краёв у контроллера. Установите значение для `.preferredCornerRadius`. Закругление меняется не только у презентуемого контроллера, но и у родителя.

![Corner-радиус у sheet-контроллера.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/corner-radius.png)

На скриншоте я установил corner-радиус в `22`. Радиус сохраняется и для `.medium`-стопора. 

На этом всё. Напишите в [комментариях к посту](https://t.me/sparrowcode/71), будете ли использовать в своих проектах sheet-контроллеры.
