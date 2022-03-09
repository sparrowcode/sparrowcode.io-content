The difficulty of the first version of StoreKit was so overwhelming that it produced a huge number of SAS solutions of varying degrees of lousiness and quality. You definitely know a couple and probably don't know how to work with native StoreKit. That's fine. I don't know too.

The new StoreKit looks like a sip of cold water in the desert. Let's dive in.

![Introducing StoreKit 2](https://cdn.sparrowcode.io/articles/meet-storekit-2/header.jpg)

## What's new

The models representing purchases and operations on them have been replaced. The names now have no SK prefixes and it is generally intuitive to see which data represent the models. We will not dwell on each one the list is below:

![StoreKit 2 Modes](https://cdn.sparrowcode.io/articles/meet-storekit-2/models.jpg)

## Request for products and purchase

Before you had to create a `SKProductsRequest` become its delegate, make the request and be sure to keep a strong reference to it so that the system doesn't kill it before it's completed.

Currently:

```swift
// Get products
let storeProducts = try await Product.request(with: identifiers)

// Purchase
let result = try await product.purchase()
switch result {
case .success(let verification):
    // handle success
    return result
case .userCancelled, .pending:
    // handle if needed
default: break
```

Check out the processing statuses. You can add your data to the purchase:

```swift
let result = try await product.purchase(options:[.appAccountToken(yourAppToken))])
```

For communication between accounts and analytics, it's great.

## Subscriptions

If the user has used the trial on one of the group subscriptions, the trial is no longer available to him. There is no easy way to find out if a user is allowed the trial or not. You had to query all transactions and look them up manually. Now it has been simplified to a single line of code.

```swift
static func isEligibleForIntroOffer(for groupID: String) async -> Bool
```

Added auto-renewal subscription state, which was previously only available in the receipt:

- <b>subscribed</b> - subscription is active<br>
- <b>expired</b> - subscription expired<br>
- <b>inBillingRetryPeriod</b> - there was an error when trying to pay<br>
- <b>inGracePeriod</b> - deferred payment by subscription. If your subscription has a grace period enabled and a payment error has occurred, the user will have some more time while the subscription is alive, although the payment has not yet been made. The number of days of the grace period can be from 6 to 16, depending on the length of the subscription itself.<br>
- <b>revoked</b> - access to all subscriptions of this group is denied by the AppStore.

![Subscription information](https://cdn.sparrowcode.io/articles/meet-storekit-2/subscription-information.jpg)

The `Renewal Info` entity contains information about auto-renewal subscriptions. For example:

- <b>willAutoRenew</b> - key that tells you whether the subscription will automatically renew. If not, it's somewhat likely that the user doesn't plan to continue using the subscription in your app. It's a good time to think about how to hold on to the user.<br>
- <b>autoRenewPreference</b> - The ID of the subscription to which the auto-renewal will happen. You can check if the user has made a downgrade and wants to use the cheaper version of your subscription. In this case, you can try to offer him a discount and keep them on the more premium version if you want to.<br>
- <b>expirationReason</b> - here you can read more about the reasons for the expiration of a subscription.

There are even more goodies. Purchases are restored automatically, async support, improved API with naming functions and models, subscription status, availability of the offerer.  Looks like the beginning of death for SAS solutions (it's more complicated there, but the update is still a killer).

## Backwards compatibility

Purchases from the first version will work in the second. The new StoreKit is available only since iOS 15. Most projects for some reason keep support for iOS 6, so the real use we will see only in indie projects.

Thanks to author of the [article](https://habr.com/ru/post/563280/), find out more in the original Russian version.
