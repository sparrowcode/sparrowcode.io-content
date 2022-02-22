Начиная с iOS 15 и SwiftUI 3 поисковый бар вызывается модификатором [.searchable()](https://developer.apple.com/documentation/swiftui/form/searchable(text:placement:)).

## Инициализация

Добавим модификатор `.searchable()` к `NavigationView()`:

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

[Searchable init](https://cdn.ivanvorobei.by/websites/sparrowcode.io/searchable-swiftui/searchable_init.mov)

Для изменения плейсхолдера в поисковой строке укажем `prompt`:

```swift
.searchable(text: $searchQuery, prompt: "Нажмите для поиска…")
```

## Расположение

Инициализатор `searchable()` принимает `placement`. Есть четыре варианта:    `automatic`, `navigationBarDrawer`, `sidebar` и `toolbar`. Параметр указывает **предпочтительное** размещение - в зависимости от иерархии вью и платформы, размещение может не сработать:

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

![Searchable Diff Placement](https://cdn.ivanvorobei.by/websites/sparrowcode.io/searchable-swiftui/searchable_diff_placement.png)

Применили модификатор к `SecondaryView()` и изменили расположение на `.navigationBarDrawer`. За размещение поля поиска отвечает структура `SearchFieldPlacement()`. По умолчанию `placement` установлено в `.automatic`.

[Searchable Placement](https://cdn.ivanvorobei.by/websites/sparrowcode.io/searchable-swiftui/searchable_placement.mov)

## Поиск

Сделаем поиск и выдачу результата. Создадим приложение, показывающее список авторов статей, в котором пользователь может найти определенного автора. Подготовим структуру:

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

Имеем одно проперти `name` и массив данных `placeholder`. Перейдем в `ContentView()`:

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

[Searchable Author Run](https://cdn.ivanvorobei.by/websites/sparrowcode.io/searchable-swiftui/searchable_author_run.mov)

Создадим `NavigationView` с `List`, который принимает массив авторов  и фильтрует его:

```swift
authors.filter { $0.name.contains(searchQuery) }
```

По умолчанию бар поиска появляется внутри списка - поэтому он скрыт. Чтобы поиск появился - скрольте список вниз. В расширение вью я вынес `authorsResult` проперти, чтобы разделить логику от интерфейса и улучшить читаемость кода.

## Предложения (Suggestions)

Для продвинутого использования, модификатор позволяет нам показать список вариантов авторов.

```swift
.searchable(text: $searchQuery, prompt: "Search author") {
    Text("Vanya").searchCompletion("Ivan Vorobei")
    Text("Somenkov").searchCompletion("Nikita Somenkov")
    Text("Nicola").searchCompletion("Nikolay Pelevin")
    Text("?").searchCompletion("Unknown author")
}
```

[Searchable suggestions](https://cdn.ivanvorobei.by/websites/sparrowcode.io/searchable-swiftui/searchable_suggestions.mov)

Полезно будет знать, что предложения накладываются на основную вью.

![Searchable overlay](https://cdn.ivanvorobei.by/websites/sparrowcode.io/searchable-swiftui/searchable_overlay.png)

Параметр `suggestions` принимает `@ViewBuilder`, поэтому можно сделать кастомную View и комбинировать варианты для поискового предложения. Код текущего проекта:

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

Приложение упадет, если мы введем символы или цифры. Я оставил этот код, чтобы продемонстрировать комбинированные варианты предложений для поиска:

```swift
.searchCompletion(authorsResult.first!.name)
```

## Кастомизация

Если вам нужно больше контроля, будь то отслеживание поисковых запросов, поиск в локальной базе данных и т.д., то используйте модификатор `.onSubmit(of: SubmitTriggers)`. Он определяет различные триггеры для старта действия. Доступно 2 проперти: `text` и `search`.

```swift
.onSubmit(of: .search) { 
    print("Sending a search request: \(searchQuery)")
}
```

[Searchable onSubmit](https://cdn.ivanvorobei.by/websites/sparrowcode.io/searchable-swiftui/searсhable_onsubmit.mov)

Модификатор `.onSubmit()` сработает, когда будет отправлен поисковый запрос:

1. По нажатию предполагаемого варианта.
2. По нажатию ввода (`return`).
3. По нажатию ввода (`return`) на физической клавиатуре.

## Environment

Доступно 2 значения: `\.isSearching` и `\.dismissSearch`.

`isSearching` - взаимодействует ли пользователь в данный момент с полем поиска. `dismissSearch` требует от системы завершить текущее взаимодействие с полем поиска.
Оба значения среды работают только в вью, где вызывается модификатор `.searchable()`:

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

## Вывод

Сегодня мы познакомились с новым модификатором. Потребовалось всего пару строк кода и в вашем приложении появилась функция поиска. Сам по себе searchable прост, но таит сложное поведение.
Надеюсь после прочтения статьи вам станет более понятна организация и работа поиска в SwiftUI.
Спасибо, что дочитали и увидимся в следующих статьях.
