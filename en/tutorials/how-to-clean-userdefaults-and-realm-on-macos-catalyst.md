To reset a macOS Catalyst application, you need to know these values:

- User folder `ivanvorobei`
- Application Bundle `io.ivanvorobei.apps.debts`
- AppGroup `group.io.ivanvorobei.apps.debts`.

Be careful, use the values from your application.

# Clear UserDefaults

To remove the default `UserDefaults`, open a terminal and type the command:

```
// Delete `UserDefaults` entirely 
defaults delete io.ivanvorobei.apps.debts

// Remove from `UserDefaults` by key 
defaults delete io.ivanvorobei.apps.debts key
```

If you used a custom domain, call the command:

```
// Created like this: 
UserDefaults(suiteName: "Custom")

// Deleted like this:
defaults delete Custom
```

# AppGroup

If you use an `AppGroup`, delete these folders:

```
/Users/ivanvorobei/Library/Group Containers/group.io.ivanvorobei.apps.debts
/Users/ivanvorobei/Library/Application Scripts/group.io.ivanvorobei.apps.debts
```

If stored in the default path, delete that folder:

```
/Users/ivanvorobei/Library/Containers/io.ivanvorobei.apps.debts
```

# Realm database

The `Realm` database files are stored as normal files. They are either in the AppGroup or in the default folder. If you follow the steps above, the database is deleted.
