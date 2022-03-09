SwiftUI is designed to make its view easy to be reuse.

## View Library

Library provides access to available SwiftUI View, modifiers, images, etc. You can DnD or double-click the selected item to add the View into your code.


![Xcode View Library](https://cdn.sparrowcode.io/articles/how-add-view-to-swiftui-library/xcode_library.png)

## Custom View

First of all, let's implement our custom View, which will be responsible for the user's profile.

```swift
struct User {

    let name: String
    let imageName: String
    let githubProfile: String
}
```

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

![UserProfile_Preview](https://cdn.sparrowcode.io/articles/how-add-view-to-swiftui-library/user_profile_preview.png)


Here is how it looks like.


## Add to View Library

For this step create a `UserProfileLibrary.swift` file, define `UserProfileLibrary()` structure which inherits from [LibraryContentProvider](https://developer.apple.com/documentation/developertoolssupport/librarycontentprovider?changes=latest_minor).


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
            visible: true,    // whether it's visible in the Xcode library
            title: "User Profile", // the custom name shown in the library
            category: .control, // a category to find you custom views faster
            matchingSignature: "UserProfile" // the signature for code completion
        )
    }
}
```

The way we add a view to View Library is quite similar to how we make our view support preview function. 
The `LibraryContentProvider` protocol provides an ability to add custom views to the Xcode library.
After that, we go to the `ContentView.swift` file and add the user view.

[UserProfileLibrary](https://cdn.sparrowcode.io/articles/how-add-view-to-swiftui-library/user_profile_library.mov)

Caveat:

1. There are no ways to add a description right now, so the field on the right is empty - **No Details**.
2. There are no ways to add an image for a thumbnail that shows up in the View Library.
3. When you use the system component, it will prefill all the parameters with usable default values. In our case, we get a user as our default value `User()`:

```swift
UserProfileView(
    user: User(
        name: "Nikita",
        imageName: "Nikita",
        githubProfile: "wmorgue
    )
)
```

Just waiting for changes in future versions to be able to add a description and icon.
This project is available for [download](https://cdn.sparrowcode.io/articles/how-add-view-to-swiftui-library/MyApp.zip).
