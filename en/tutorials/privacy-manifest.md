If you use User Defaults or collect user data, you need to fill out a manifest. Everything you specify will appear on the application page.

> The library's authors also add a manifest. But if they didn’t do this, then the developer himself adds it inside the project.

If the library has a manifest, it doesn't need to be duplicated into your manifest. When you archive a project, all manifests are merged into one.

# Adding Manifest

Press `⌘+N` and select `App Privacy` file.

![Create an `App Privacy` file](https://cdn.sparrowcode.io/tutorials/privacy-manifest/app-privacy.png?v=1)

Each target has its own manifest, so be careful to checkmark the right target. If the manifest is the same for all targets, you can specify several targets at once.

![Specifying the target for the manifest](https://cdn.sparrowcode.io/tutorials/privacy-manifest/enable-target.png?v=1)

# Structure of Manifest

The manifest is a plist file with the extension `.xcprivacy`.

![Example of a completed Privacy Manifest](https://cdn.sparrowcode.io/tutorials/privacy-manifest/base-app-manifest.png?v=1)

The manifest consists of three fields. The first is about tracking - you fill it out when you collect mail or name. The second is responsible for system API, for example, User Defaults. The third party is responsible for `IDFA`.

Let's break down each field in more detail.

## User tracking

The `Privacy Nutrition Label Types` field describes what data collect about the user. Anything specify in the manifest will be visible in the App Privacy field on the application page:

![Information about what data we collect on the App Store page](https://cdn.sparrowcode.io/tutorials/privacy-manifest/nutrition-label-app-store.png?v=1)

**Collected Data Type** — is the type of data collect about the user. For example, contacts or payment information. All types are on the [official website](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_data_use_in_privacy_manifests#4250555), you cannot add your own. Add a line from `Data type` to the plist-file.

![Contact data types for Manifest](https://cdn.sparrowcode.io/tutorials/privacy-manifest/collected-data-type.png?v=1)

For each data type, create a new Item. The fields below must be specified for each data type:

**Linked to User** — if you collect data related to the user's identity, put `YES`.

**Used for Tracking** — if the data is used for tracking, put `YES`.

**Collection Purposes** — here specify the reasons why are collecting the data. For example, analytics, advertising or authentication. Choose from the available [list of reasons](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_data_use_in_privacy_manifests#4250556), you can't list your own..

![Reasons in Manifest why we collect data](https://cdn.sparrowcode.io/tutorials/privacy-manifest/collection-purposes.png?v=1)

## System API

There is a separate `Privacy Accessed API Types` field for APIs. The error message from Apple comes from this because of field. In this field we specify which API we are using and why.

![The type of API and the reason for its use](https://cdn.sparrowcode.io/tutorials/privacy-manifest/privacy-accessed-api-reasons.png?v=1)

These are the system APIs that need to be specified in the manifest:

[File Timestamp](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278393): Get the time when the file was created or modified
[System Boot Time](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278394): Information about application startup and OS runtime
[Disk Space](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278397): Available storage space on the device
[Active Keyboard](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278400): Access to the list of active keypads
[User Defaults](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278401): If used User Defaults

For each API, the link will also list the available reasons.  Cannot specify your own reasons.

> If more than one reason is appropriate, all reasons should be given

## IDFA

If you are using IDFA, add the **Privacy Tracking Enabled** field put `YES`. Immediately add the **Privacy Tracking Domains** field, here you need to specify all domains that work in IDFA.

![Fields for IDFA in Manifest](https://cdn.sparrowcode.io/tutorials/privacy-manifest/tracking-enabled-tracking-domains.png?v=1)

> If you set `Privacy Tracking Enabled`, be sure to specify at least one domain.

To get which domains are used for IDFA, open the `Product` → `Profile` profiler. Now select Network in the window:

![Profiler window](https://cdn.sparrowcode.io/tutorials/privacy-manifest/profile-network.png?v=1)

In the upper left corner, click Start Recording. Select the **Points of Interest** tab, this will list all the domains. The **Start Message** column shows the domain and indicates that it has not been added to the manifest.

![How to collect IDFA domains](https://cdn.sparrowcode.io/tutorials/privacy-manifest/points-of-interest.png?v=1)

The profile sometimes fails if **Points of Interest** doesn't show anything or disappears altogether, here's the second way. Select your application tab, and can see all domains in the sessions.

![All domains in application sessions](https://cdn.sparrowcode.io/tutorials/privacy-manifest/app-sessions.png?v=1)

Now you will have to check each domain to see if it participates in IDFA. You will have to do it yourself.

# Manifest in libraries

> Library authors add the manifest too. But if they haven't done so, the developer adds it internally

If the library author has not added a manifest, the developer must fill in the manifest themselves.

If there is a manifest in the library and it is complete, there is no need to duplicate the information in the main manifest. All manifests are merged into one when we collect the archive.

If there are errors in the manifest, the developer will have to complete the manifest himself within the project. For example, Firebase Crashlytics uses the domain **firebase-settings.crashlytics.com**. They didn't specify this in their manifest:

![Firebase manifest error](https://cdn.sparrowcode.io/tutorials/privacy-manifest/firebase-manifest.png?v=1)

We found it with the help of a [profiler](https://beta.sparrowcode.io/ru/tutorials/privacy-manifest#idfa). In this situation add the domain to your manifest, this will override the problem field in the Firebase manifest.

Library manifests make mistakes - be sure to double-check.

# If the error in Manifest

> Errors will come to mail only when send the application for checking. If you just unload the project, there will be no errors

Only errors about the system API will come to the mail:

![A letter with errors in the manifest](https://cdn.sparrowcode.io/tutorials/privacy-manifest/privacy-manifest-email.png)

To quickly find the keys, type `NS` in the search. These are the ones that are missing from your Manifest. Even if you don't use this API, it can be used by libraries that you have added to your project.

Here are the NS keys, and links to the key and the reason on Apple's site:

- [NSPrivacyAccessedAPICategoryFileTimestamp](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278393)
- [NSPrivacyAccessedAPICategorySystemBootTime](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278394)
- [NSPrivacyAccessedAPICategoryDiskSpace](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278397)
- [NSPrivacyAccessedAPICategoryActiveKeyboards](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278400)
- [NSPrivacyAccessedAPICategoryUserDefaults](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278401)

# Final Manifest

Collect the archive Product -> Archive. Right click on the archive, select Generate Privacy Report.

![Exporting the final manifest](https://cdn.sparrowcode.io/tutorials/privacy-manifest/generate-privacy-report.png?v=1)

In the export PDF-file. All manifests merged into the final one:

![PDF report with all manifests](https://cdn.sparrowcode.io/tutorials/privacy-manifest/pdf-report.png?v=1)

All fields with `.app` extension are from your manifest. Other fields are third-party libraries in your project.