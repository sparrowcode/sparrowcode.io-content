# How to drop LaunchScreen.storyboard

By default `LaunchScreen.storyboard` file is created only for UIKit projects. Delete it first:

![How to drop `LaunchScreen.storyboard`.](https://cdn.sparrowcode.io/tutorials/set-launch-screen-via-plist/delete-launchscreen-storyboard-file.jpg)

Now select the app target and go to the `Info` tab. Here you need to remove the key "Launch screen interface file base name" or `UILaunchStoryboardName`:

![Delete the `UILaunchStoryboardName` key.](https://cdn.sparrowcode.io/tutorials/set-launch-screen-via-plist/delete-launch-screen-interface-file-base-name-key.jpg)

Now add the `UILaunchScreen` dictionary here as well:

![Add `UILaunchScreen` dictionary.](https://cdn.sparrowcode.io/tutorials/set-launch-screen-via-plist/add-uilaunchscreen-key.jpg)

The dictionary can be left blank, then the background will be the color `.systemBackground`.

# Set Launch Screen via `.plist`

Available for UIKit and SwiftUI starting with iOS 14.

You can add Tab/Nav/Tool-bar placeholders to make the transition between Launch Screen and Root Controller smooth. You can also set the background color and put an image. For all this we specify special keys in plist-file.

> You can combine keys, for example, set background, image and Tab bar.

Let's check all six keys:

## Background color

In Assets add a new color, you can choose different colors for dark and light theme:

![New color in Assets.](https://cdn.sparrowcode.io/tutorials/set-launch-screen-via-plist/add-color-to-assets.jpg)

In the 'Launch Screen dictionary', add the `UIColorName` key with the name of the color:

![Add the `UIColorName` key.](https://cdn.sparrowcode.io/tutorials/set-launch-screen-via-plist/add-background-color-launch-screen-key.jpg)

The Launch Screen will now be filled with color:

![Result with `UIColorName`.](https://cdn.sparrowcode.io/tutorials/set-launch-screen-via-plist/with-uicolorname-result.jpg)

## Image name

You can set the image to the center of the Launch Screen. Add the picture to Assets, and then add the `UIImageName` key and specify the name of the picture. Result:

![Result with `UIImageName`.](https://cdn.sparrowcode.io/tutorials/set-launch-screen-via-plist/with-uiimagename-result.jpg)

> Launch Screen is cached, so if you changed the image - the simulator should be reset via `Device` → `Erase All Content and Settings...`.

## Image respects safe area insets

The `UIImageRespectsSafeAreaInsets` key should affect the size of the picture and fit it into the Safe Area. I've put different images, but the key doesn't affect anything. I checked on iOS 17.2. Maybe it's a bug and will be fixed in the future.

## Show Tab Bar

To show the Tab bar placeholder, add an empty `UITabBar` dictionary:

![Add `UITabBar` dictionary.](https://cdn.sparrowcode.io/tutorials/set-launch-screen-via-plist/add-uitabbar-key.jpg)

The Tab bar placeholder will appear at the bottom:

![Result with `UITabBar`.](https://cdn.sparrowcode.io/tutorials/set-launch-screen-via-plist/with-uitabbar-result.jpg)

> The height of Tab-bar on Launch Screen is higher than it should be. This is a bug. For now, I recommend to use `Toolbar`, about it below.

## Show Toolbar

Similarly, you can show the Tool-bar placeholder by adding an empty `UIToolbar` dictionary:

![Result with `UIToolbar`.](https://cdn.sparrowcode.io/tutorials/set-launch-screen-via-plist/with-uitoolbar-result.jpg)

## Navigation bar

To add a Navigation-bar, add the `UINavigationBar` dictionary. By default, Navigation-bar with a large header has no background, so when you set the key, nothing will change.



