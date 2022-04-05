To reset a macOS Catalyst app, you need to know the name of the user folder, the app bundle, the AppGroup and the suit for UserDefaults - if using. In the tutorial I will use these examples: user folder `ivanvorobei`, app bundle `by.ivanvorobei.apps.debts`, AppGroup identifier `group.by.ivanvorobei.apps.debts`.

Be careful to use the values from your application.

## Clear UserDefaults

If you want to remove the default `UserDefaults`, open a terminal and type the command:

```swift
// Delete `UserDefaults` entirely 
defaults delete by.ivanvorobei.apps.debts

// Remove from `UserDefaults` by key 
defaults delete by.ivanvorobei.apps.debts key
```

If you used a custom domain, call the command:

```swift
// Created like this
// UserDefaults(suiteName: "Custom")
defaults delete Custom
```

## AppGroup

If you use an `AppGroup`, delete these folders:

```swift
/Users/ivanvorobei/Library/Group Containers/group.by.ivanvorobei.apps.debts
/Users/ivanvorobei/Library/Application Scripts/group.by.ivanvorobei.apps.debts
```

If stored in the default path, delete that folder:

```swift
/Users/ivanvorobei/Library/Containers/by.ivanvorobei.apps.debts
```

## Realm Database

The `Realm` database files are stored as normal files. They are either in the AppGroup or in the default folder. If you perform the steps above, the database is deleted.

## More folders

I found more folders, but I don't know what they are for. I'll leave the paths here:

```swift
/Users/ivanvorobei/Library/Application Scripts/group.by.ivanvorobei.apps.debts
/Users/ivanvorobei/Library/Developer/Xcode/Products/by.ivanvorobei.apps.debts (macOS)
```

If you know what they're for, or know more folders, let me know - I'll update the tutorial.
