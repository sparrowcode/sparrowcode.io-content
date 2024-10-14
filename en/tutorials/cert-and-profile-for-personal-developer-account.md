You want to add a developer to the account so that they can upload apps. If you have a company account, everything works out of the box.

But if you have an individual account, a third-party developer will be able to upload applications only with a special profile.

> It's not safe to pass your Apple ID username-password, don't do that

Сертификаты можно сделать вручную или через API. В этой статье разберем ручной способ.

Step by step what we are going to do:
- First, request a signature for the certificate
- Create the certificate
- Combine this certificate with the key
- Register the app (you may already have it registered).
- Create a profile based on the certificate — it is the one we need to upload app

# Certificate Request

We make a special request for a certificate — this is a file with the extension `.certSigningRequest`.

Open *Keychain Access* and create the file `CertificateSigningRequest.certSigningRequest`:

![Inquiry at the certification center](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/keychain-request.png)

Enter your email, name and select *Saved to disk*. In the next window, just save the file:

![Save the certificate request](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/keychain-sert-info.png?v=2)

You'll have a file, it'll still come in handy:

![Ready `.certSigningRequest` file](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/keychain-sert-created.png?v=2)

> If the account holder doesn't have macOS, the request-file is made by the developer and sent to the account holder

# Making a Certificate

The certificate confirms that the app is yours. The extension of the certificate file is `.cer`.

Open the Certificates tab in *Developer Account*:

![Certificate tab](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/main-sert.png)

To make a new certificate, click the plus sign:

![Adding a Certificate](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/add-sert.png)

Select *Apple Distribution* and click *Continue*:

![Apple Distribution](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/new-sert.png)

This page will ask for the `.certSigningRequest` certificate request file we made above. Select the file:

![Add `.certSigningRequest`.](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/select-new-sert.png)

The certificate is ready — download it, it will still come in handy:

![Download the certificate](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/download-sert.png)

# Merge certificate and key

Next we need a file with the extension `.p12`. It stores the certificate-key mapping.

Double-click on the `distribution.cer` file and it will open *Keychain Access*.

> If nothing happens, just search for the last downloaded *Apple Distribution* certificate by date. The expiration date will be one year from now

![Apple Distribution Certificate](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/distribution-sert.png)

Expand the drop-down box (to the left of the certificate), highlight the certificate and private key. Next, right-click and select `Export 2 items...`.

![Export Certificate with key](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/export-distribution-sert.png)

Save the file:

![Name for the Certificate](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/create-sert-p12.png)

Set a password for the certificate, you can leave it blank:

![Password for Certificate](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/sert-p12-non-pass.png)

It will ask for your mac password - enter it and click *Always Allow*:

![Enter your mac's password](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/sert-p12-system-pass.png)

Get the file `Certificates.p12`:

![Certificate `.p12'.](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/save-sert-p12.png)

# Register the App

> If you already have an application, skip this step

The `App ID` is a unique identifier for an app. It links apps to Apple services such as Push Notifications, iCloud, Game Center, etc.

Go to *Developer Account* under the *Identifiers* tab and click the plus sign:

![Identifiers Tab](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/identifiers.png)

Select *App IDs*, then *App*:

![App IDs & App](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/register-identifier-app-id.png)

Here in *Description* enter the name of the app, and in *Bundle ID* enter the bundle. `Explicit` - used to sign only one application. `Wildcard` - used to sign multiple apps.

> Learn more about Explicit and Wildcard [at link](https://developer.apple.com/library/archive/qa/qa1713/_index.html):

![App ID registration](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/register-app-id.png)

When you have filled in the fields, click *Register*:

> If you get an error, check the Bundle ID field

![Registering an App ID](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/end-register-app-id.png)

The *Identifiers* page will display the ID of the new app:

![Application Identifier](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/identifiers-list.png)

# Provisioning Profile

The `Provisioning Profile' ties everything together: Apple Developer Account, App ID, certificates, and devices.

This is a file with the extension `.mobileprovision`.

Go to the *Profiles* tab, click the *Generate a profile* button:

![Profiles Tab](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/profiles.png)

Select *App Store Connect*:

![App Store Connect](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/new-profile.png)

In `App ID` select the desired `Bundle ID` from the list:

![Select App ID](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/generate-profile-app-id.png)

Select the newly created certificate (check the date when it expires):

![Adding a certificate](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/generate-profile-select-sert.png)

Fill in the *Provisioning Profile Name* and click *Generate*:

![Name for Provisioning Profile](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/generate-profile-name.png)

All that's left is to download the file:

![Downloading Provisioning Profile](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/download-profile.png)

We get a file with your name and extension `.mobileprovision`:

![Provision Profile](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/created-profile.png)

# Transfer files to the developer

Pass the `.p12` file and `Provision Profile` to the developer. Next, the developer needs to double-click the `.p12` file or import it into *Keychain Access*:

![Import `.p12`](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/add-p12.png)

Now the developer goes to Xcode-project - Project Settings and selects the target. On the *Signing & Capabilities* tab disable `Automatically manage signing`, select Team ID and import Provisioning Profile:

![Importing a Provision Profile](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/add-profile-xcode.png)

Done! The developer will be able to upload apps to an individual account.

> Repeat the steps only if the Profile is changed. It does not need to be repeated for each app