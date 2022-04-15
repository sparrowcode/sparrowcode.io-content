Библиотека в Xcode предоставляет доступ к SwiftUI View, модификаторам `modifiers`, изображениям и т. д. Вы можете перетянуть выбранный элемент или кликнуть по нему дважды, чтобы добавить `View` в код.

![Библиотека `Views` в Xcode.](https://cdn.sparrowcode.io/tutorials/how-add-view-to-swiftui-library/xcode_library.png)

Сделаем кастомную вью, которую будем добавлять в библиотеку. Я создам профиль пользователя. Пример модели:

```swift
struct User {
    
    let name: String
    let imageName: String
    let githubProfile: String
}
```

А так будет выглядеть вью:

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

А вот результат:

![Как будет выглядеть `UserProfileView`.](https://cdn.sparrowcode.io/tutorials/how-add-view-to-swiftui-library/user_profile_preview.png)

Создаём файл `UserProfileLibrary.swift`. Сначала определим структуру, которая наследуется от [LibraryContentProvider](https://developer.apple.com/documentation/developertoolssupport/librarycontentprovider?changes=latest_minor).

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
            visible: true,    // будет ли доступна наша View в библиотеке
            title: "User Profile", // заголовок, который будет отображаться
            category: .control, // доступно несколько категорий на выбор
            matchingSignature: "UserProfile" // сигнатура для автокомплита
        )
    }
}
```

Потом с помощью `LibraryContentProvider` добавляем кастомные View в библиотеку Xcode.
И теперь перейдём в `ContentView.swift` файл и добавим пользователя.

[Получение кастомной `view` из `UserProfileLibrary`.](https://cdn.sparrowcode.io/tutorials/how-add-view-to-swiftui-library/user_profile_library.mov)

Есть ограничения:
- Нельзя добавить описание к своей View, поэтому поле справа остаётся пустым — **No Details**.
- Нельзя добавить иконку.
- Когда добавляем View в код, добавляется также заранее _прописанное_ значение. В нашем случае это структура `User()`:

```swift
UserProfileView(
    user: User(
        name: "Nikita",
        imageName: "Nikita",
        githubProfile: "wmorgue
    )
)
```

Надеюсь, в будущих версиях мы сможем добавлять описание и иконку.
Проект из туториала можно [скачать](https://cdn.sparrowcode.io/tutorials/how-add-view-to-swiftui-library/MyApp.zip).
