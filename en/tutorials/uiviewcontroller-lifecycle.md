> View controller is not created after controller initialization

A system needs a reason to create a view. The lifecycle concept is built around this feature. Just keep in mind that a view is created out of necessity.

## Initializing the UIViewController

Consider the `UIViewController`. Two initializers are available:

```swift
override init(nibName nibNameOrNil: String?, bundle nibBundleOrNil: Bundle?) {
    super.init(nibName: nibNameOrNil, bundle: nibBundleOrNil)
}
    
required init?(coder: NSCoder) {
    super.init(coder: coder)
}
```

There is also an initializer without parameters `init()`, but this is a wrapper over the first initializer.

At this point, the controller initializes the property and fills the initializer body. View is not loaded, outlets are not active. Only file name is saved in initializer with nib, but file itself is not loaded.

## Loading View

When a developer presents a controller, it is a reason for the system to load a view. The controller has lifecycle methods with which we monitor the process and add our logic.

```swift
override func loadView() {}
```

The `loadView()` method is called by the system. It doesn't need to be called manually. But you can override it to override the root view. If you need to load the view manually (and you're sure you need to), hold the red `loadViewIfNeeded()` button. The `isViewLoaded` flag shows whether the view is loaded or not.

The second method is called when the view has finished loading.

```swift
override viewDidLoad() {
    super.viewDidLoad()
}
```

Developers don't just set up the controller and view-his in the `viewDidLoad()` method. Before this method is called, the root view doesn't exist, and after, the controller is ready to appear on the screen. In `viewDidLoad()` the memory for the view is allocated, the view is loaded and ready to be configured.

> View cannot be configured in the initializer: if you call `controller.view` - it will load. But the controller is not visible now, and maybe it will never show up at all. You will waste memory and occupy the main thread.

This will not destroy the project, but the interface elements consume memory - you don't want to waste them before they are needed. Do it as needed.

I used to make the controller's proprietary views this way:

```swift
class ViewController: UIViewController {
    
    let redView = UIView()
}
```

But when I was preparing the article I realized my mistake. The property is initialized together with the controller, which means the memory for the view will be allocated immediately. The right thing to do is to defer this to the requirement, mark the property as `lazy`.

In the `viewDidLoad()` method, the view dimensions are wrong - you can't bind to height and width. Make a setting that doesn't depend on dimensions.

There is a method `viewDidUnload()`. The root view can unload from memory, which means something incredible!

> The `viewDidLoad()` method can be called several times.

If the modal controller is closed, the view is unloaded from memory, but the controller is alive. Outlets are active here, but no longer meaningful - they can be reset. If you show the controller again, the view will load again. If the system unloaded the view, then it must have had a reason. You don't need to refer to the root view in this method - it will load the view.

Nothing will break in your project, `viewDidLoad()` is rarely called multiple times. Separate the data and view setup in the next project.

## Show and Hide View

The appearance of the controller starts with the `viewWillAppear` method:

```swift
override func viewWillAppear(_ animated: Bool) {
    super.viewWillAppear(animated)
}
    
override func viewDidAppear(_ animated: Bool) {
    super.viewDidAppear(animated)
}
```

The appearance of the controller in the modal window or the transition in `UINavigationController`-e will call `viewWillAppear` before the animation and `viewDidAppear` after it. When `viewWillAppear` is called, the view is already in the hierarchy.

Both methods are bundled. You don't need to do any customization here, but you can hide or show view-highs, or add uncomplicated behavior. In the `viewDidAppear()` method, start a network request or spin the load indicator. Both methods can be called multiple times.

There are methods that report that the view disappears from the screen. Here's a schematic:

![Lifecycle scheme of the `ViewController'.](https://cdn.sparrowcode.io/tutorials/uiviewcontroller-lifecycle/header-en.jpg)

Note the pair of antagonists `viewWillDisappear()` and `viewDidDisappear()`. They are called when the view is removed from the view hierarchy. If you show another controller on top, the methods are not called.

## Layout

Layout methods are tied to the view lifecycle. Three methods are available:

```swift
override func viewWillLayoutSubviews() {
    super.viewWillLayoutSubviews()
}
    
override func viewDidLayoutSubviews() {
    super.viewDidLayoutSubviews()
}
```

The first method is called before `layoutSubviews()` of the root view, the second method is called after. In the second method, the dimensions are correct and the view is placed correctly - you can link to the dimensions of the root view.

There is a separate method for resizing the view. It is also called to rotate the device:

```swift
override func viewWillTransition(to size: CGSize, with coordinator: UIViewControllerTransitionCoordinator) {
    super.viewWillTransition(to: size, with: coordinator)
}
```

The `viewWillLayoutSubviews()` and `viewDidLayoutSubviews()` methods are called after it.

## Memory is out

If you don't clear the objects that cause it to happen, iOS will forcibly crash the app. This method is a warning, you have a chance to free up some memory.

```swift
override func didReceiveMemoryWarning() {
    super.didReceiveMemoryWarning()
}
```
