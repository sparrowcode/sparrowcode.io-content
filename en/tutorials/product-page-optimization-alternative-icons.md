With [Product Page Optimization](https://developer.apple.com/app-store/product-page-optimization/) you can create variants of screenshots, promo texts, and icons. Screenshots and text are added to App Store Connect, but icons are added by the developer in the Xcode project.

The documentation says "put the icons in Asset Catalog, send the binary to App Store Connect and use the SDK". But how to upload icons and what kind of SDK - did not say. Let's figure it out, the steps are supported by screenshots.

## Adding icons to Assets

The alternative icon is done in multiple resolutions, just like the main icon. I use [AppIconBuilder](https://apps.apple.com/app/id1294179975). Naming should be whatever you want, but it will show up on App Store Connect.

![Adding icons to Assets](https://cdn.ivanvorobei.by/websites/sparrowcode.io/product-page-optimization-alternative-icons/adding-icons-to-assets.png)

## Settings in Target.

You need Xcode 13 or higher. Select the app targetet and go to the `Build Settings` tab. In the search, type `App Icon` and you will see the `Asset Catalog Compiler` section.

![Settings in target](https://cdn.ivanvorobei.by/websites/sparrowcode.io/product-page-optimization-alternative-icons/adding-settings-to-target.png)

We are interested in 3 parameters:

`Alternate App Icons Sets` - listing the names of the icons you added to the catalog.

`Include All App Icon Assets` - set to `true` to include alternative icons in the assembly.

`Primary App Icon Set Name` - default icon name. Not checked, but most likely the alternate icon can be made the primary icon.

## Assembly.

It remains to build the application and send it for testing.

>Alternative icons will be available after the review.

Now you can build different pages of the app and create links for A/B tests.
