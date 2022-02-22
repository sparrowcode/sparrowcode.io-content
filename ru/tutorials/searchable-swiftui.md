С появлением iOS 15 и SwiftUI 3 появилась возможность вызвать поисковый бар с помощь модификатора [.searchable()](https://developer.apple.com/documentation/swiftui/form/searchable(text:placement:)).

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


Для изменения приглашения в поисковой строке добавим параметр `prompt`:

```swift
.searchable(text: $searchQuery, prompt: "Нажмите для поиска…")
```


## Расположение

Инициализатор `searchable()` принимает `placement` в качестве одного из параметров. На выбор доступно четыре варианта:    `automatic`, `navigationBarDrawer`, `sidebar` и `toolbar`. Обратите внимание, что этот параметр позволяет указать **предпочтительное** размещение. В зависимости от иерархии вью и платформы, размещение может не сработать:

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
В примере выше, мы применили модификатор к `SecondaryView()` и изменили расположение на `.navigationBarDrawer`.
За это отвечает структура `SearchFieldPlacement()`. По умолчанию `placement` установлено в `.automatic`.

[Searchable placement](https://cdn.ivanvorobei.by/websites/sparrowcode.io/searchable-swiftui/searchable_placement.mov)


## Поиск

Рассмотрим как можно выполнить сам поиск и выдачу результата.
Создадим приложение, показывающее список авторов статей, в котором пользователь может искать определенного автора. 

Подготовим структуру:

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

Имеем одно проперти: `name` и массив данных: `placeholder`. Далее переходим в `ContentView()`:

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

[Searchable Author run](https://cdn.ivanvorobei.by/websites/sparrowcode.io/searchable-swiftui/searchable_author_run.mov)


Создаем `NavigationView` и внутри него создаем `List`, который принимает массив авторов c фильтром:

```swift
authors.filter { $0.name.contains(searchQuery) }
```
По умолчанию поисковый бар появляется внутри списка и поэтому он скрыт. Необходимо потянуть список вниз, чтобы поле поиска появилось.
В расширение нашей вью я вынес `authorsResult` проперти.

## Предполагаемые варианты (Suggestions)

Для более продвинутого использования, модификатор позволяет нам показывать список вариантов для наших авторов.

```swift
.searchable(text: $searchQuery, prompt: "Search author") {
    Text("Vanya").searchCompletion("Ivan Vorobei")
    Text("Somenkov").searchCompletion("Nikita Somenkov")
    Text("Nicola").searchCompletion("Nikolay Pelevin")
    Text("?").searchCompletion("Unknown author")
}
```

[Searchable suggestions](https://cdn.ivanvorobei.by/websites/sparrowcode.io/searchable-swiftui/searchable_suggestions.mov)


Параметр `suggestions` принимает `@ViewBuilder`, поэтому мы можем сделать кастомную View, а так же комбинировать варианты.
Код текущего проекта:

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

Обратите внимание, приложение упадет, если мы начнем вводить символы или цифры. Я оставил этот код умышленно, чтобы продемонстрировать комбинированные варианты поиска:

```swift
.searchCompletion(authorsResult.first!.name)
```

## Больше контроля

Если вам необходимо больше контроля, будь то отслеживание поисковых запросов, поиск в локальной базе данных и т.д., то вы можете использовать модификатор `.onSubmit(of: SubmitTriggers)`.

`SubmitTriggers()` — тип, который определяет различные триггеры приводящие к выполнению действия. Доступно 2 проперти: `text` и `search`.

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

`isSearching` показывает, взаимодействует ли пользователь в данный момент с полем поиска.
`dismissSearch` требует от системы завершить текущее взаимодействие с полем поиска.
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
