SwiftUI был спроектирован таким образом, чтобы View можно было легко переиспользовать.

## Библиотека View

Библиотека в Xcode предоставляет доступ ко всем возможным SwiftUI View, модификаторам (modifiers), изображениям и т.д. Вы можете перетянуть или кликнуть дважды по выбранному элементу, чтобы добавить View в свой код.

![Xcode View Library](https://cdn.ivanvorobei.by/websites/sparrowcode.io/how-add-view-to-swiftui-library/xcode_library.png)

## Кастомная View

Первым делом, реализуем свою View, которая будет отвечать за профиль пользователя.

```swift
//filename: UserProfileView.swift

struct User {
	let name: String
	let imageName: String
	let githubProfile: String
}

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

![UserProfile_Preview](https://cdn.ivanvorobei.by/websites/sparrowcode.io/how-add-view-to-swiftui-library/user_profile_preview.png)

Так выглядит профиль.


## Добавляем нашу View в библиотеку

Для этого создадим отдельный файл `UserProfileLibrary.swift`, определим структуру, которая наследуется от [LibraryContentProvider](https://developer.apple.com/documentation/developertoolssupport/librarycontentprovider?changes=latest_minor).

```swift
//filename: UserProfileLibrary.swift

struct UserProfileLibrary: LibraryContentProvider {
	
	@LibraryContentBuilder
	var views: [LibraryItem] {
		LibraryItem(
			UserProfileView(user: User(name: "Nikita", imageName: "Nikita", githubProfile: "wmorgue")),
			visible: true,	// будет ли доступна наша View в библиотеке
			title: "User Profile", // заголовок, который будет отображаться
			category: .control, // доступно несколько категорий на выбор
			matchingSignature: "UserProfile" // сигнатура для автокомплита
		)
	}
}
```
Именно `LibraryContentProvider` предоставляет возможность добавлять кастомные View в библиотеку Xcode.
После добавления, перейдем в `ContentView.swift` файл и добавим пользователя.

![UserProfileLibrary](https://cdn.ivanvorobei.by/websites/sparrowcode.io/how-add-view-to-swiftui-library/user_profile_library.mov)

Существуют некоторые ограничения:

1. Нельзя добавить описание к своей View, поэтому поле справа пустое — **No Details**.
2. Нельзя добавить иконку.
3. При добавлении View в код, добавляется заранее _прописанное_ значение. В нашем случае это структура `User()`:

```swift
UserProfileView(user: User(name: "Nikita", imageName: "Nikita", githubProfile: "wmorgue"))
```

Остается ждать изменений в будущих версиях, чтобы появилась возможность добавить описание и иконку.
Проект доступен для [скачивания](https://cdn.ivanvorobei.by/websites/sparrowcode.io/how-add-view-to-swiftui-library/MyApp.zip).
