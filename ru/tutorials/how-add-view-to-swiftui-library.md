Библиотека в Xcode предоставляет доступ к SwiftUI View, модификаторам (modifiers), изображениям и т.д. Вы можете перетянуть или кликнуть дважды по выбранному элементу, чтобы добавить View в свой код.

![Xcode View Library](https://cdn.ivanvorobei.io/websites/sparrowcode.io/how-add-view-to-swiftui-library/xcode_library.png)

## Кастомная View

Сделаем кастомную вью, которую будем добавлять в библиотеку. Я сделаю профиль пользователя. Пример модели:

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

Результат:

![UserProfile_Preview](https://cdn.ivanvorobei.io/websites/sparrowcode.io/how-add-view-to-swiftui-library/user_profile_preview.png)

## Добавляем в библиотеку

Создаем файл `UserProfileLibrary.swift`. Определим структуру, которая наследуется от [LibraryContentProvider](https://developer.apple.com/documentation/developertoolssupport/librarycontentprovider?changes=latest_minor).

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

C помощью `LibraryContentProvider` добавляем кастомные View в библиотеку Xcode.
Перейдем в `ContentView.swift` файл и добавим пользователя.

[UserProfileLibrary](https://cdn.ivanvorobei.io/websites/sparrowcode.io/how-add-view-to-swiftui-library/user_profile_library.mov)

Есть ограничения:

1. Нельзя добавить описание к своей View, поэтому поле справа пустое — **No Details**.
2. Нельзя добавить иконку.
3. При добавлении View в код, добавляется заранее _прописанное_ значение. В нашем случае это структура `User()`:

```swift
UserProfileView(
   user: User(
      name: "Nikita", 
      imageName: "Nikita", 
      githubProfile: "wmorgue
   )
)
```

Надеюсь в будущих версиях можно будет добавить описание и иконку.
Проект из туториала можно [скачать](https://cdn.ivanvorobei.io/websites/sparrowcode.io/how-add-view-to-swiftui-library/MyApp.zip).
