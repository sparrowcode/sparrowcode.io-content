You control three indents - `imageEdgeInsets`, `titleEdgeInsets` and `contentEdgeInsets`. Most often, the task comes down to setting symmetric-opposite values, I'll explain below this confusion.

Before diving into the process, take a look at [example project](https://cdn.sparrowcode.io/tutorials/edge-insets-uibutton/example-project.zip). Each slider is responsible for a specific indent - you can combine them. The settings in the video are as follows: background color - red, icon color - yellow, and title - blue.

[An example of controlling the indentations in `UIButton`.](https://cdn.sparrowcode.io/tutorials/edge-insets-uibutton/edge-insets-uibutton-example-preview.mov)

Indent the header and icon by `10pt`. When you get it, see if you can control the result or if it's random. At the end of the tutorial you will know how it works.

## `contentEdgeInsets`

The property behaves predictably and adds indents around the header and icon. If you set negative values, the indentation will decrease. Code:

```swift
// I know about the abbreviated entry
previewButton.contentEdgeInsets.left = 10
previewButton.contentEdgeInsets.right = 10
previewButton.contentEdgeInsets.top = 5
previewButton.contentEdgeInsets.bottom = 5
```

![Example `contentEdgeInsets` indents.](https://cdn.sparrowcode.io/tutorials/edge-insets-uibutton/content-edge-insets.png)

Indentations appeared around the content. They are added proportionally and affect only the size of the button. They are needed to expand the clickable area if the button is small.

## `imageEdgeInsets` and `titleEdgeInsets`

I put them in one section for a reason. More often than not, the task will boil down to adding indents symmetrically on one side and reducing them on the other. This sounds complicated, but we'll figure it out.

Let's add an indent between the picture and the header `10pt`. The first idea is to add an indent through the property `imageEdgeInsets`:

[Example of `imageEdgeInsets` indentation between icon and text.](https://cdn.sparrowcode.io/tutorials/edge-insets-uibutton/image-edge-insets-space-icon-title.mov)

The indentation is added, but it doesn't affect the size of the button and the icon goes behind the button. The partner `titleEdgeInsets` works the same way - it doesn't change button size. Let's add indent for title, but opposite to the icon indent. It will look like this:

```swift
previewButton.imageEdgeInsets.left = -10
previewButton.titleEdgeInsets.left = 10
```

This is the symmetry I wrote about above.

>`imageEdgeInsets` and `titleEdgeInsets` do not change the size of the button. But `contentEdgeInsets` does. Remember that, and you won't have any problems with proper indentation.

Let's complicate the task by putting an icon to the right of the header.

```swift
let buttonWidth = previewButton.frame.width
let imageWidth = previewButton.imageView?.frame.width ?? .zero

// Shift the header to the left edge. 
// The indent on the left was `imageWidth`. If you decrease by this value, you get the left edge.
previewButton.titleEdgeInsets = UIEdgeInsets(
    top: 0, 
    left: -imageWidth, 
    bottom: 0, 
    right: imageWidth
)

// Move the icon to the right edge.
// The default indent was 0, so the new Y point will have the width of the icon.
previewButton.imageEdgeInsets = UIEdgeInsets(
    top: 0, 
    left: buttonWidth - imageWidth, 
    bottom: 0, 
    right: 0
)
```

## A ready-made class

My library [SparrowKit](https://github.com/ivanvorobei/SparrowKit) already has a ready-made button class [`SPButton`](https://github.com/ivanvorobei/SparrowKit/blob/main/Sources/SparrowKit/UIKit/Classes/Buttons/SPButton.swift) with support for indenting between picture and text.

```swift
button.titleImageInset = 8
```

Works for RTL localization. If there is no image, no indent is added. The developer only needs to set the indent value.

## Deprecated

Note, with iOS 15 our friends are marked `deprecated`.

![Screenshot from Apple Developer website.](https://cdn.sparrowcode.io/tutorials/edge-insets-uibutton/depricated.png)

Properties will work for several years. Apple recommends using the configuration. Let's see what survives - the configuration or good old `padding`.

That's all for now. For a visual dabble, download [sample project](https://cdn.sparrowcode.io/tutorials/edge-insets-uibutton/example-project.zip).
