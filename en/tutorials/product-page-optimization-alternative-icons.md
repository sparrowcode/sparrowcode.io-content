With [Product Page Optimization](https://developer.apple.com/app-store/product-page-optimization/) you can create variants of screenshots, promotional texts, and icons. Screenshots and text are added to App Store Connect, but icons are added by the developer to the Xcode project.

The documentation says: "Put the icons in Asset Catalog, send the binary to App Store Connect and use the SDK. It does not say how to add icons and what kind of SDK it is. Let's figure it out.

## Adding icons to Assets

The alternative icon is made in several resolutions, just like the main icon. I use the [AppIconBuilder](https://apps.apple.com/app/id1294179975) application. The name of the icon pack is visible in App Store Connect.

![Adding icons to Assets.](https://cdn.sparrowcode.io/tutorials/product-page-optimization-alternative-icons/adding-icons-to-assets.png)

## Setting up targeting

We need Xcode 13 or higher. Select the application target and go to the `Build Settings` tab. In the search for `App Icon` - you will see the section `Asset Catalog Compiler`.

![Screenshot of the necessary settings in the project targets.](https://cdn.sparrowcode.io/tutorials/product-page-optimization-alternative-icons/adding-settings-to-target.png)

We are interested in 3 parameters:

`Alternate App Icons Sets` - list the names of the icons you have added to the catalog.

`Include All App Icon Assets` - set to `true` to include alternative icons in the assembly.

`Primary App Icon Set Name` - default icon name. Most likely, the alternate icon can be made the primary icon. Did not check.

## Unloading

It remains to assemble the application and send it in for review.

>Alternative icons will be available after the review.

Now you can assemble different pages of the app and create links for A/B tests.
