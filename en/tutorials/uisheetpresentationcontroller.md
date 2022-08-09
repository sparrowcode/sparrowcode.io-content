When I was young, I made [package](https://github.com/ivanvorobei/SPStorkController) with similar behavior on snapshots. In iOS 13 Apple introduced updated modal controllers, and with iOS 15 you can control their height:

[Sheet controller with detents in the middle and at the top.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/header.mov)

## Quick Start 

To show the default sheet-controller, use the code:

```swift
let controller = UIViewController()
if let sheetController = controller.sheetPresentationController {
    sheetController.detents = [.medium(), .large()]
}
present(controller, animated: true)
```

This is a regular modal controller that has been added complex behavior. You can wrap the sheet-controller into a navigation controller, add a header and bar buttons. If the project supports previous versions of iOS, wrap the code with `sheetController` in `if #available(iOS 15.0, *) {}`.

## Detents

Detent - the height to which the controller aspires. Similar to situations with scroll paging or when the electron is not at its energy level.

There are two detents available:
- `.medium()` half the size of the screen 
- `.large()` repeats the large modal controller. 

If you leave only `.medium()`, the controller will open at half of the screen and will not rise higher. You can't set your own height in pixels, you choose only from the available detents. By default, the controller is shown with the `.large()` detent.

The available detents are indicated as follows:

```swift
sheetController.detents = [.medium(), .large()]
```

If you specify only one detent, you cannot switch between them with a gesture.

### Switching between detents by code

To go from one detent to another, use the code:

```swift
sheetController.animateChanges {
    sheetController.selectedDetentIdentifier = .medium
}
```

It is possible to call without animation block. It is also possible to switch the detent without being able to change it, to do this, change the available detents:

```swift
sheetController.animateChanges {
    sheetController.detents = [.large()]
}
```

The controller will switch to `.large()`-detent and will no longer allow the gesture to switch to `.medium()`.

## Lock Dismiss

If you want to lock a controller in one detent without being able to close it, set `isModalInPresentation` to `true` for the parent. In the example, the parent is the navigation controller:

```swift
navigationController.isModalInPresentation = true
if let sheetController = nav.sheetPresentationController {
    sheetController.detents = [.medium()]
    sheetController.largestUndimmedDetentIdentifier = .medium
}
```

[Sheet controller with a prohibition to close.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/prevent-dismiss.mov)

## Content scrolling

If `.medium()`-detent is active and the controller content is scrolling, the modal controller will go to `.large()`-detent when scrolling up and the content will stay in place.

[Standard scroll on the sheet controller.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/scrolling-expands-true.mov)

To scroll content without changing the detent, specify these parameters:

```swift
sheetController.prefersScrollingExpandsWhenScrolledToEdge = false
```

[Scroll on a sheet controller with `prefersScrollingExpandsWhenScrolledToEdge = false`.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/scrolling-expands-false.mov)

Scrolling up will now work for content scrolling. 

> To go to the big detent, pull the navigation bar.

## Album orientation

By default, the sheet-controller in landscape orientation looks like a normal controller. The point is that `.medium()`-detent is not available, and `.large()` is the default mode of the modal controller. But you can add edge indentation.

```swift
sheetController.prefersEdgeAttachedInCompactHeight = true
```

This is what it looks like:

![Sheet-controller in landscape orientation with edge indentation.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/edge-attached.png)

To make the controller take the prefered size, set `widthFollowsPreferredContentSizeWhenEdgeAttached` to `true`.

## Darken the background

If the background is dimmed, the buttons behind the modal controller will not be clickable. To allow interaction with the background, you must remove the dimming. Specify the largest detent that doesn't need to be dimmed. Here's the code:

```swift
sheetController.largestUndimmedDetentIdentifier = .medium
```

[Sheet controller with disabled dimming for the `.medium` stopper.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/undimmed-detent.mov)

It is specified that the `.medium' will not dim, but anything larger will. It is possible to remove the dimming for the largest detent as well.

## Indicator

To add an indicator on top of the controller, set `.prefersGrabberVisible` to `true`. By default the indicator is hidden. The indicator has no effect on safe area and layout margins.

![Grabber indicator on the sheet-controller.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/grabber.png)

## Corner Radius

You can control the edge rounding of the controller. Set a value for `.preferredCornerRadius`. The rounding changes not only for the presented controller, but also for the parent.

![Corner radius at the sheet-controller.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/corner-radius.png)

In the screenshot I set the corner radius to `22`. The radius remains the same for the `.medium` detent.
