The library in Xcode provides access to the SwiftUI `View`, `modifiers`, images, etc. You can drag the selected item or double-click it to add the `View` to the code.

![Screenshot of `Views` library in Xcode.](https://cdn.sparrowcode.io/tutorials/how-add-view-to-swiftui-library/xcode_library.png)

Let's make a custom `view` to be added to the library. I will create a user profile. Example model:

```swift
struct User {
    
    let name: String
    let imageName: String
    let githubProfile: String
}
```

And this is what the `view` will look like:

```swift
struct UserProfileView: View {

    let user: User
    
    var body: some View {
        HStack {
            Image(user.imageName)
                .resizable()
                .frame(width: 40, height: 40)
                .clipShape(Circle())
            
            VStack(alignment: .leading) {
                Text(user.name)
                Text(user.githubProfile)
                    .foregroundColor(.gray)
            }
        }
        .padding(.all)
    }
}
```

And here's the result:

![What `UserProfileView` will look like.](https://cdn.sparrowcode.io/tutorials/how-add-view-to-swiftui-library/user_profile_preview.png)

Create the file `UserProfileLibrary.swift`. First, let's define a structure that inherits from [LibraryContentProvider](https://developer.apple.com/documentation/developertoolssupport/librarycontentprovider?changes=latest_minor).

```swift
//filename: UserProfileLibrary.swift

struct UserProfileLibrary: LibraryContentProvider {
    
    @LibraryContentBuilder
    var views: [LibraryItem] {
        LibraryItem(
            UserProfileView(
                user: User(
                    name: "Nikita",
                    imageName: "Nikita",
                    githubProfile: "wmorgue"
                )
            ),
            visible: true, // whether our `View` will be available in the library
            title: "User Profile", // title to be displayed
            category: .control, // several categories are available to choose from
            matchingSignature: "UserProfile" // signature for the auto-complete
        )
    }
}
```

Then use `LibraryContentProvider` to add custom views to the Xcode library.
And now let's go to the `ContentView.swift` file and add a user.

[Getting custom `view` from `UserProfileLibrary`.](https://cdn.sparrowcode.io/tutorials/how-add-view-to-swiftui-library/user_profile_library.mov)

There are limitations:
- You can't add a description to your `View`, so the box on the right stays blank - **No Details**.
- You can't add an icon.
- When we add a `View` to the code, we also add a _prescribed_ value. In our case this is the `User()` structure:

```swift
UserProfileView(
    user: User(
        name: "Nikita",
        imageName: "Nikita",
        githubProfile: "wmorgue
    )
)
```

Hopefully, in future versions we will be able to add a description and icon.
You can [download](https://cdn.sparrowcode.io/tutorials/how-add-view-to-swiftui-library/MyApp.zip) the project from the tutorial.
