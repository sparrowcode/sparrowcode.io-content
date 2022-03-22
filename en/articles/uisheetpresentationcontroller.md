Attempts to control the height of modal controllers have been bothering developers for 4 years. [The libraries turn out to be bad](https://github.com/ivanvorobei/SPStorkController). They work ugly or don't work at all. The lead engineer of `UIKit` was thrown out of the window for trying to discuss this topic at the meeting. By iOS 15 Tim Cook took pity and discovered secret knowledge.

[UISheetPresentationController Preview](https://cdn.sparrowcode.io/articles/uisheetpresentationcontroller/uisheetpresentationcontroller.mov)

That looks cool and there are a lot of use cases. To show the default `sheet` controller use the code below:

```swift
let controller = UIViewController()
if let sheetController = controller.sheetPresentationController {
    sheetController.detents = [.medium(), .large()]
}
present(controller, animated: true)
```

That's a modal controller that has been added to advanced behavior. You can wrap it into a navigation controller add a header and buttons. Wrap the code with `sheetController` to `if #available(iOS 15.0, *) {}` if the project supports previous versions of iOS.

## Detents

The detent is the height to which the controller reaches. Just like in scroll paging or when the electron is not at its energy level.

Two detents are available: `.medium()` with a size of about half the screen and `.large()`, which replicates a large modal controller. If you leave only `.medium()` detents, the controller opens at half the screen and won't go any higher. It's not possible to set its height.

## Switching between detents

To switch from one detent to another use the code below:

```swift
sheetController.animateChanges {
    sheetController.selectedDetentIdentifier = .medium
}
```

You can use it without the animation.

## Landscape orientation

By default, the `sheet` controller in landscape orientation looks like a usual controller. The thing is that `.medium()` detent is not available and `.large()` is the default mode of the modal controller. Also, you can add indentation around the edges.

```swift
sheetController.prefersEdgeAttachedInCompactHeight = true
```

Here's how it looks:

![Landscape for UISheetPresentationController](https://cdn.sparrowcode.io/articles/uisheetpresentationcontroller/landscape.jpg)

Set `.widthFollowsPreferredContentSizeWhenEdgeAttached` to `true` to let the controller consider the preferred size.

## Indicator

If you wanna add an indicator on top of the controller, set `.prefersGrabberVisible` to `true`. By default, the indicator is hidden. The indicator does not affect the safe area and layout margins, at least at the time of this article.

![Grabber for UISheetPresentationController](https://cdn.sparrowcode.io/articles/uisheetpresentationcontroller/prefers-grabber-visible.jpg)

## Dimmed background

Specify the largest detent that does not need to be dimmed. Anything larger than this detent will be dimmed. The code below:

```swift
sheetController.largestUndimmedDetentIdentifier = .medium
```

It says that the `.medium` will not dim, but anything larger will. You can remove the dimming for the largest detent.

## Corner Radius

You can control the corner radius of the controller. To do this, set `.preferredCornerRadius`. Note that the rounding changes not only for the presented controller but also for the parent.

![Grabber for UISheetPresentationController](https://cdn.sparrowcode.io/articles/uisheetpresentationcontroller/preferred-corner-radius.jpg)

On the screenshot, I set the corner radius to `22`. The radius is set for `.medium`. That's all. [Comment on the post](https://t.me/sparrowcode/71), if you will use sheet controllers in your projects.
