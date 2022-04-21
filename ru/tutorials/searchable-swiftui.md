С iOS 15 и SwiftUI 3 поисковый бар вызывается модификатором [.searchable()](https://developer.apple.com/documentation/swiftui/form/searchable(text:placement:)).

## Инициализация

Сначала добавим модификатор `.searchable(text:)` к `NavigationView()`:

```swift
struct ContentView: View {
    
    @State private var searchQuery: String = ""
    
    var body: some View {
        NavigationView {
            Text("Поиск \(searchQuery)")
                .navigationTitle("Searchable Sample")
                .navigationBarTitleDisplayMode(.inline)
            
        }
        .searchable(text: $searchQuery)
    }
}
```

[Работа `Searchable`.](https://cdn.sparrowcode.io/tutorials/searchable-swiftui/searchable_init.mov)

Чтобы изменить плейсхолдер в поисковой строке, укажем `prompt`:

```swift
.searchable(text: $searchQuery, prompt: "Нажмите для поиска…")
```

## Расположение

Инициализатор `searchable()` принимает `placement`. Есть четыре варианта: `automatic`, `navigationBarDrawer`, `sidebar` и `toolbar`. Параметр указывает **предпочтительное** размещение, при этом в зависимости от иерархии вью и платформы размещение может не сработать:

```swift
struct PrimaryView: View {

    var body: some View {
        Text("Primary View")
    }
}

struct SecondaryView: View {

    var body: some View {
        Text("Secondary View")
    }
}

struct ContentView: View {

    @State private var searchQuery: String = ""
    
    var body: some View {
        NavigationView {
            PrimaryView()
                .navigationTitle("Primary")
            
            SecondaryView()
                .navigationTitle("Secondary")
                .searchable(text: $searchQuery, placement: .navigationBarDrawer)
        }
    }
}
```

![Варианты расположения.](https://cdn.sparrowcode.io/tutorials/searchable-swiftui/searchable_diff_placement.png)

Мы применили модификатор к `SecondaryView()` и изменили расположение на `.navigationBarDrawer`. За положение поля ввода отвечает структура `SearchFieldPlacement()`. По умолчанию `placement` установлено в `.automatic`.

[Изменяем `Searchable Placement`.](https://cdn.sparrowcode.io/tutorials/searchable-swiftui/searchable_placement.mov)

## Поиск

Сделаем поиск и выдачу результата. Создадим приложение, показывающее список авторов статей, в котором пользователь может найти определённого автора. Сперва подготовим структуру:

```swift
struct Author {
    let name: String
}

extension Author: Identifiable {

    var id: UUID { UUID() }
    
    static let placeholder = [
        Author(name: "Ivan Vorobei"),
        Author(name: "Nikita Rossik"),
        Author(name: "Nikita Somenkov"),
        Author(name: "Nikolay Pelevin")
    ]
}
```

У нас есть одно проперти `name` и массив данных `placeholder`. Перейдём в `ContentView()`:

```swift
struct ContentView: View {

    let authors: [Author] = Author.placeholder
    @State private var searchQuery: String = ""
    
    var body: some View {
        NavigationView {
            List(authorsResult) { author in
                NavigationLink(author.name, destination: Text(author.name))
            }
            .navigationTitle("Authors")
            .navigationBarTitleDisplayMode(.inline)
        }
        .searchable(text: $searchQuery, prompt: "Search author")
    }
}

extension ContentView {

    var authorsResult: [Author] {
        guard searchQuery.isEmpty else {
            return authors.filter { $0.name.contains(searchQuery) }
        }
        return authors
    }
}
```

[Поиск автора статьи через `Searchable`.](https://cdn.sparrowcode.io/tutorials/searchable-swiftui/searchable_author_run.mov)

Создадим `NavigationView` с `List`, который принимает массив авторов и фильтрует его:

```swift
authors.filter { $0.name.contains(searchQuery) }
```

По умолчанию бар поиска появляется внутри списка, поэтому он скрыт. Чтобы поиск появился, скрольте список вниз. Я вынес `authorsResult` в расширение `ContentView`, чтобы отделить логику от интерфейса.

## Предложения Suggestions

Модификатор покажет список вариантов авторов:

```swift
.searchable(text: $searchQuery, prompt: "Search author") {
    Text("Vanya").searchCompletion("Ivan Vorobei")
    Text("Somenkov").searchCompletion("Nikita Somenkov")
    Text("Nicola").searchCompletion("Nikolay Pelevin")
    Text("?").searchCompletion("Unknown author")
}
```

[Подсказки `Searchable`.](https://cdn.sparrowcode.io/tutorials/searchable-swiftui/searchable_suggestions.mov)

Предложения накладываются на основную вью:

![Интерфейс `Searchable`.](https://cdn.sparrowcode.io/tutorials/searchable-swiftui/searchable_overlay.png)

Параметр `suggestions` принимает `@ViewBuilder`, поэтому можно сделать кастомную View и комбинировать варианты для поискового предложения. Вот код текущего проекта:

```swift
struct ContentView: View {

    let authors: [Author] = Author.placeholder
    @State private var searchQuery: String = ""
    
    var body: some View {
        NavigationView {
            List(authorsResult) { author in
                NavigationLink(author.name, destination: Text(author.name))
            }
            .navigationTitle("Authors")
            .navigationBarTitleDisplayMode(.inline)
        }
        .searchable(text: $searchQuery, prompt: "Search author") {
            Text("Vanya")
                .searchCompletion(authorsResult.first!.name)
            searchableSuggestions
        }
    }
}

extension ContentView {

    var authorsResult: [Author] {
        guard searchQuery.isEmpty else {
            return authors.filter { $0.name.contains(searchQuery) }
        }
        return authors
    }
    
    private var searchableSuggestions: some View {
        ForEach(authorsResult) { suggestion in
            Text(suggestion.name)
                .searchCompletion(suggestion.name)
        }
    }
}
```

Приложение упадёт, если мы введём символы или цифры. Я оставил этот код, чтобы продемонстрировать комбинированные варианты предложений для поиска:

```swift
.searchCompletion(authorsResult.first!.name)
```

## Кастомизация

Если вам нужно больше контроля, например, отслеживание поисковых запросов, поиск в локальной базе данных и т. д., используйте модификатор `.onSubmit(of: SubmitTriggers)`. Он определяет различные триггеры для старта действия. Есть 2 проперти: `text` и `search`.

```swift
.onSubmit(of: .search) { 
    print("Sending a search request: \(searchQuery)")
}
```

[Работа `onSubmit` триггера.](https://cdn.sparrowcode.io/tutorials/searchable-swiftui/searchable_onsubmit.mov)

Модификатор `.onSubmit()` сработает, когда отправите поисковый запрос по нажатию:

1. предполагаемого варианта,
2. ввода (`return`),
3. ввода (`return`) на физической клавиатуре.

## Environment

Доступно 2 значения: `\.isSearching` и `\.dismissSearch`.

`isSearching` помогает понять, взаимодействует ли пользователь в данный момент с полем поиска. `dismissSearch` требует от системы завершить текущее взаимодействие с полем поиска. Оба значения среды работают только во вью, где вызывается модификатор `.searchable()`:

```swift
struct ContentView: View {

    @StateObject var viewModel = SearchViewModel()
    @Environment(\.isSearching) private var isSearching
    @Environment(\.dismissSearch) private var dismissSearch
    
    let query: String
    
    var body: some View {
        List(viewModel.repos) { repo in
            RepoView(repo: repo)
        }.overlay {
            if isSearching && !query.isEmpty {
                VStack {
                    Button("Dismiss search") {
                        dismissSearch()
                    }
                    SearchResultView(query: query)
                        .environmentObject(viewModel)
                }
            }
        }
    }
}
```

Добавить поиск в приложение просто, а вот настроить поведение сложнее.
