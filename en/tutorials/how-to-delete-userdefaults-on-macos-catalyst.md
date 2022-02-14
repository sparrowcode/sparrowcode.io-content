To reset a macOS Catalyst application you need to know the name of the user folder, the application bundle, the AppGroup, and the suit for UserDefaults (if you use it). In the tutorial I will use the following examples:

User folder `ivanvorobei`, app bundle `by.ivanvorobei.apps.debts`, AppGroup identifier `group.by.ivanvorobei.apps.debts`.

Be careful to use the values from your application.

## Clean up UserDefaults

If you want to delete the default `UserDefaults` open a terminal and enter the command:

```swift
// Delete `UserDefaults` completely
defaults delete by.ivanvorobei.apps.debts

// Delete from `UserDefaults` by key
defaults delete by.ivanvorobei.apps.debts key
```

For custom domain use the following command:

```swift
// Created like this
// UserDefaults(suiteName: "Custom")
defaults delete suit.name
```

## AppGroup

If you use an `AppGroup` you need to delete the following folders:

```swift
/Users/ivanvorobei/Library/Group Containers/group.by.ivanvorobei.apps.debts
/Users/ivanvorobei/Library/Application Scripts/group.by.ivanvorobei.apps.debts
```

If you store in the default path, it will be the following directory:

```swift
/Users/ivanvorobei/Library/Containers/by.ivanvorobei.apps.debts
```

## Realm mobile database

The `Realm` database files are stored as regular files. They are either in the AppGroup or in the default directory. By performing the steps above, the database will be deleted.

## Other directories

I found more folders, but I don't know what they are for. I will leave the paths here:

```swift
/Users/ivanvorobei/Library/Application Scripts/group.by.ivanvorobei.apps.debts
/Users/ivanvorobei/Library/Developer/Xcode/Products/by.ivanvorobei.apps.debts (macOS)
```

If you know what they are for or know more folders, let me know - I'll update the tutorial.
