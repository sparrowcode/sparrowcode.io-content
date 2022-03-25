In this article let's look at the life cycle of a ViewController. We'll see when methods are called and what you can do inside them. We'll also look at common errors.

Let's start with the `UIView`. The memory is allocated during initialization, so the behavior is predictable. Now the properties have values and the object can be used.

The controller has a view. But just because the controller is created, does not mean that the view is created too. The system is waiting for a reason to create it. The lifecycle concept is built around this feature. Just keep in mind that the view is created by necessity.

## Initializing

Consider the basic `UIViewController` which has two initializers:

```swift
override init(nibName nibNameOrNil: String?, bundle nibBundleOrNil: Bundle?) {
    super.init(nibName: nibNameOrNil, bundle: nibBundleOrNil)
}
    
required init?(coder: NSCoder) {
    super.init(coder: coder)
}
```

There is also an initializer without parameters `init()`, but this is a wrapper over the first initializer.

At this point, the controller behaves like a class: it initializes the property and handles the initializer body. The controller may be in a condition without a loaded view for a long time, or it may never even load one. The view will load as soon as the system or the developer accesses the `.view` property.

## Loading

The developer presents the controller. The memory is allocated because the system loads the view. We can follow the process and even intervene. Let's see what methods are available:

```swift
override func loadView() {}
```

The `loadView()` method is called by the system. You don't need to call it manually but you can override it to replace the root view. If you need to load the view manually (and you know what you're doing), hold down the red `loadViewIfNeeded()` button.

> `super.loadView()` не нужно.

The second method is legendary like Steve Jobs. It is called when the view has finished loading.

```swift
override viewDidLoad() {
    super.viewDidLoad()
}
```

There is a reason why developers set up the controller and views in the `viewDidLoad()` method. Before this method is called, the root view doesn't exist yet, and afterward, the controller is ready to appear on the screen. The `viewDidLoad()` is a great place. The memory for the view is allocated, the view is loaded and ready to be set up.

The view cannot be configured in the initializer. When you invoke `.view`, it will load, but the controller won't show up on the screen now (or may not show up at all). The project will not crash from this, but the interface elements consume a lot of memory and it will be spent earlier than necessary. It is better to do this as needed.

Previously I made property views of the controller just by creating them:

```swift
class ViewController: UIViewController {
    
    var redView = UIView()
}
```

The property is initialized with the controller, which means the memory for the view is allocated immediately. To hold off this you need to mark the property as `lazy`.

In the `viewDidLoad()` method, the size of the view is wrong, so you can't bind to height and width. Do a setting that does not depend on size.

I wanna focus on `viewDidUnload()`. The root view can be unloaded from memory, which means something incredible:

>The `viewDidLoad()` method can be called several times.

For example, if you close the modal controller, the view will be deallocated from memory, but the controller object will still be alive. If you show the controller again, the view will load again. If the system dumped the view, it means there was a reason. There is no need to refer to the root view in this method - it will cause it to load. Outlets are still available here, but are no longer meaningful - you can reset them.

You don't have to rush to take off-hours and spend all weekend redoing your VPN. Nothing will break, `viewDidLoad()` is rarely called multiple times. Keep in mind that you need to split the configuration of data and views in your next project.

## Showing

The appearance of the controller begins with the `viewWillAppear` method:

```swift
override func viewWillAppear(_ animated: Bool) {
    super.viewWillAppear(animated)
}
    
override func viewDidAppear(_ animated: Bool) {
    super.viewDidAppear(animated)
}
```

Both methods are paired. You don't need to do any customization here, but you can hide/show views or add some simple behavior. In the `viewDidAppear()` method, start a network request or spin the load indicator. Both methods can be called multiple times.

Some methods report that the view disappears from the screen. See the schematic:

![ViewController LifeCycle](https://cdn.sparrowcode.io/tutorials/uiviewcontroller-lifecycle/header.jpg)

Note the two antagonists `viewWillDisappear()` and `viewDidDisappear`. They are called when the view is removed from the view hierarchy. If you show another controller on top, the methods are not called.

## Layout

Layout methods, similar to the methods above, are tied to the life cycle of the view. Three methods are available:

```swift
override func viewWillLayoutSubviews() {
    super.viewWillLayoutSubviews()
}
    
override func viewDidLayoutSubviews() {
    super.viewDidLayoutSubviews()
}
```

The first method is called before `layoutSubviews()` of the root view, the second method is called after. In the second method, the size is correct and the views are placed correctly - you can link to the size of the root view.

There is a special method for resizing a view. With this method you can adjust the rotation of the device:

```swift
override func viewWillTransition(to size: CGSize, with coordinator: UIViewControllerTransitionCoordinator) {
    super.viewWillTransition(to: size, with: coordinator)
}
```

The `viewWillLayoutSubviews()` and `viewDidLayoutSubviews()` methods will then be called.

## Out of memory

This method is called if the memory overflows. If you don't clear the objects that cause it, iOS will force the application to shut down (to the user, it will look like a crash).

```swift
override func didReceiveMemoryWarning() {
    super.didReceiveMemoryWarning()
}
```

That's all. Controller lifecycle is a big topic I might have missed something. Let me know if you find something or have a good example for an article.
