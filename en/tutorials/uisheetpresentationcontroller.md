When I was young, I made [library](https://github.com/ivanvorobei/SPStorkController) to control controller height on snapshots. The new modal controllers partially solved the problem natively. And with iOS 15 you can control height out of the box:

[UISheetPresentationController example with tables in the middle and at the top.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/header.mov)

It looks cool, there are a lot of cases. To show the default `sheet`-controller, use the code:

```swift
let controller = UIViewController()
if let sheetController = controller.sheetPresentationController {
    sheetController.detents = [.medium(), .large()]
}
present(controller, animated: true)
```

This is a regular modal controller that has been added complex behavior. You can wrap it into a navigation controller, add a header and bar buttons. Wrap the code with `sheetController` to `if #available(iOS 15.0, *) {}` if the project supports previous versions of iOS.

## Detents (stoppers)

A stopper is the height to which the controller aspires. Just like in scroll paging or when the electron is not at its energy level.

Two stops are available: `.medium()` which is half the size of the screen and `.large()` which replicates a large modal controller. If you leave only the `.medium()` stopper, the controller will open at half the screen and will not go any higher. You can't set your own height in pixels, you choose only from the available stoppers. By default, the controller is shown with the `.large()` stopper.

The available stoppers are specified as follows:

```swift
sheetController.detents = [.medium(), .large()]
```

If you specify only one stopper, you cannot switch with a gesture.

### Switching between stoppers

To switch from one stopper to another, use the code:

```swift
sheetController.animateChanges {
    sheetController.selectedDetentIdentifier = .medium
}
```

You can call it without the animation block. It is also possible to switch a stopper without being able to change it, to do this, change the available stoppers:

```swift
sheetController.animateChanges {
    sheetController.detents = [.large()]
}
```

The controller will switch to a `.large()` stop and won't let the gesture switch to `.medium()`.

## Dismiss

If you want to lock the controller in a single stop without being able to close it, set `isModalInPresentation` to `true` parent:

```swift
navigationController.isModalInPresentation = true
if let sheetController = nav.sheetPresentationController {
    sheetController.detents = [.medium()]
    sheetController.largestUndimmedDetentIdentifier = .medium
}
```

[Example of sheet-controller operation with prohibition of closing.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/prevent-dismiss.mov)

## Scroll Content

If the `.medium()` stopper is active and the controller content is scrolling, then if you scroll up, the modal counterroller will go to the `.large()` stopper. The content will remain in place.

[Example of a standard scroll on a sheet-controller.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/scrolling-expands-true.mov)

To scroll content first, specify:

```swift
sheetController.prefersScrollingExpandsWhenScrolledToEdge = false
```

[An example of scrolling on a sheet-controller with `prefersScrollingExpandsWhenScrolledToEdge = false`.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/scrolling-expands-false.mov)

Now when scrolling up will work content scrolling. To go to the big stop, you need to pull the navigation-bar.

## Album orientation

By default, the `sheet` controller in landscape orientation looks like a normal controller. The thing is that `.medium()` -stop is not available, and `.large()` is the default mode of the modal controller. But you can add indentation along the edges.

```swift
sheetController.prefersEdgeAttachedInCompactHeight = true
```

This is what it looks like:

![An example of a sheet-controller in landscape orientation with edge indentation.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/edge-attached.png)

To make the controller take the prefered size into account, set `widthFollowsPreferredContentSizeWhenEdgeAttached` to `true`.

## Dimmed Background

If the background is dimmed, the button behind the modal controller will not be clickable. To allow interaction with the background, you must remove the dimming. Specify the largest stop that you don't want to darken. Code:

```swift
sheetController.largestUndimmedDetentIdentifier = .medium
```

[Example of disabling dimming for a `.medium' stopper on a sheet-controller.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/undimmed-detent.mov)

It is specified that the `.medium' will not dim, but anything larger will. It is possible to remove the dimming for the largest stopper as well.

## Indicator

To add an indicator on top of the controller, set `.prefersGrabberVisible` to `true`. By default, the indicator is hidden. The indicator has no effect on safe area and layout margins.

![Example of grabber indicator on sheet-controller.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/grabber.png)

## Corner Radius

You can control the edge rounding of the controller. Set a value for `.preferredCornerRadius`. The rounding changes not only for the presented controller, but also for the parent.

![An example of a corner radius set on a sheet-controller.](https://cdn.sparrowcode.io/tutorials/uisheetpresentationcontroller/corner-radius.png)

In the screenshot I set the corner radius to `22`. The radius remains the same for the `.medium` stop.