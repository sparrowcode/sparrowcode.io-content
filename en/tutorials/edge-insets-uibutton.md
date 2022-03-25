You control three indentations - `imageEdgeInsets`, `titleEdgeInsets` and `contentEdgeInsets`. More often than not, your task comes down to setting symmetrical-opposite values.

Before we dive in, take a look at [example project](https://cdn.sparrowcode.io/articles/edge-insets-uibutton/example-project.zip). Each slider is responsible for a specific indent and you can combine them. In the video I set the background color to red, the icon color to yellow, and the title color to blue.

[Edge Insets UIButton Example Project Preview](https://cdn.sparrowcode.io/articles/edge-insets-uibutton/edge-insets-uibutton-example-preview.mov)

Indent between the header and the icon `10pt`. When you get it, make sure you control the result or it's random. At the end of the tutorial you'll know how it works.

## contentEdgeInsets

It behaves predictably. It adds indents around the header and icon. If you set negative values, the indentation will decrease. Code:

```swift
// I know about the abbreviated entry
previewButton.contentEdgeInsets.left = 10
previewButton.contentEdgeInsets.right = 10
previewButton.contentEdgeInsets.top = 5
previewButton.contentEdgeInsets.bottom = 5
```

![contentEdgeInsets](https://cdn.sparrowcode.io/articles/edge-insets-uibutton/content-edge-insets.png)

Indentations have been added around the content. They are added proportionally and affect only the size of the button. The practical sense is to expand the clickable area if the button is small.

## imageEdgeInsets and titleEdgeInsets

I put them in one section for a reason. More often than not, the task will boil down to adding indents symmetrically on one side, and reducing them on the other. That sounds complicated, but we'll figure it out.

Let's add an indent between the picture and the header, let's say `10pt`. The first idea is to add an indent through the property `imageEdgeInsets`:

[imageEdgeInsets space between icon and title](https://cdn.sparrowcode.io/articles/edge-insets-uibutton/image-edge-insets-space-icon-title.mov)

The behavior is more complicated. The indentation is added, but it doesn't affect the size of the button. If it did, the problem would be solved.

The `titleEdgeInsets` partner works the same way - it doesn't change button size. It makes sense to add an indent for the header, but the opposite value. It will look like this:

```swift
previewButton.imageEdgeInsets.left = -10
previewButton.titleEdgeInsets.left = 10
```

This is the symmetry I wrote about above.

>`imageEdgeInsets` and `titleEdgeInsets` do not change the size of the button. But `contentEdgeInsets` does.

Keep this in mind and you won't have any more problems with correct indentation. Let's complicate the task by putting an icon to the right of the header.

```swift
let buttonWidth = previewButton.frame.width
let imageWidth = previewButton.imageView?.frame.width ?? .zero

// Shift the header to the left edge. 
// The indent on the left was `imageWidth`, so reducing by this value will get the left edge.
previewButton.titleEdgeInsets = UIEdgeInsets(
    top: 0, 
    left: -imageWidth, 
    bottom: 0, 
    right: imageWidth
)

// We move the icon to the right edge.
// The default indent was 0, so the new Y-point will be the width - width of the icon.
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

Works for RTL localization. If there is no picture, no indentation is added. The developer only needs to set the indent value.

![Deprecated imageEdgeInsets и titleEdgeInsets](https://cdn.sparrowcode.io/articles/edge-insets-uibutton/depricated.png)

## Deprecated

I should point out, with iOS 15 our friends are labeled `derritated`.

A few years of property will work. Apple recommends using the configuration. Let's see what survives - the configuration, or good old `padding`.

That's all for now. For a visual dabble, download [example project](https://cdn.sparrowcode.io/articles/edge-insets-uibutton/example-project.zip).

