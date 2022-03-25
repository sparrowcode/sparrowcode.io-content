With iOS 15 and SwiftUI 3 the search bar is called by the [.searchable()](https://developer.apple.com/documentation/swiftui/form/searchable(text:placement:)) modifier.

## Init

Add the modifier `.searchable()` to `NavigationView()`:

```swift
struct ContentView: View {
    
    @State private var searchQuery: String = ""
    
    var body: some View {
        NavigationView {
            Text("Search \(searchQuery)")
                .navigationTitle("Searchable Sample")
                .navigationBarTitleDisplayMode(.inline)
            
        }
        .searchable(text: $searchQuery)
    }
}
```

[Searchable init](https://cdn.sparrowcode.io/articles/searchable-swiftui/searchable_init.mov)

To change the placeholder, in the search field we will add `prompt`:

```swift
.searchable(text: $searchQuery, prompt: "Tap to search…")
```

## Placement

Initializer `searchable()` get `placement` parameter. There are four selections:    `automatic`, `navigationBarDrawer`, `sidebar` and `toolbar`. The parameter provides the **preferred** placement - depending on the view hierarchy and platform, the placement may not work:

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

![Searchable Diff Placement](https://cdn.sparrowcode.io/articles/searchable-swiftui/searchable_diff_placement.png)

Apply a modifier to `SecondaryView()` and change the location to `.navigationBarDrawer`. The `SearchFieldPlacement()` structure is responsible for the position of the search field. By default `placement` is `.automatic`.

[Searchable Placement](https://cdn.sparrowcode.io/articles/searchable-swiftui/searchable_placement.mov)

## Search

Let's perform a search and output the result. Create an application that shows a list of authors of articles in which the user can find a particular author. Prepare the structure:

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

Have a single `name` property and a data `placeholder` array. Move to `ContentView()`:

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

[Searchable Author Run](https://cdn.sparrowcode.io/articles/searchable-swiftui/searchable_author_run.mov)

Create a `NavigationView` with `List` that takes an array of authors and filters it:

```swift
authors.filter { $0.name.contains(searchQuery) }
```

By default, the search bar appears inside the list, so is hidden. To search appear - scroll down the list. Put `authorsResult` into `ContentView` extension to split logic from interface.

## Suggestions

The modifier will show a list of different authors:

```swift
.searchable(text: $searchQuery, prompt: "Search author") {
    Text("Vanya").searchCompletion("Ivan Vorobei")
    Text("Somenkov").searchCompletion("Nikita Somenkov")
    Text("Nicola").searchCompletion("Nikolay Pelevin")
    Text("?").searchCompletion("Unknown author")
}
```

[Searchable suggestions](https://cdn.sparrowcode.io/articles/searchable-swiftui/searchable_suggestions.mov)

Search suggestions will overlay your main view:

![Searchable overlay](https://cdn.sparrowcode.io/articles/searchable-swiftui/searchable_overlay.png)

The `suggestions` parameter takes `@ViewBuilder`, so you can make a custom View and combine options for a search suggestion. The code of the current project:

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

The app will crash if we enter symbols or digits. I kept this code to demonstrate the combined options of the search suggestions:

```swift
.searchCompletion(authorsResult.first!.name)
```

## Control

If you need more control - tracking searches, searching the local database, etc., use the modifier `.onSubmit(of: SubmitTriggers)`. It defines different triggers to start an action. There are 2 properties available: `text` and `search`.

```swift
.onSubmit(of: .search) { 
    print("Sending a search request: \(searchQuery)")
}
```

[Searchable onSubmit](https://cdn.sparrowcode.io/articles/searchable-swiftui/searchable_onsubmit.mov)

Modifier `.onSubmit()` will trigger when a search query is submitted:

1. User tap on search suggestion.
2. User tap on the return key on the software keyboard.
3. User tap on the return key on the physical hardware keyboard.

## Environment

We have two environment values: `\.isSearching` and `\.dismissSearch`.

`isSearching` - value that indicated whether the user is currently interacting with the search bar that has been placed by a surrounding searchable modifier. `dismissSearch` asks the system to dismiss the current search interaction.
Both environment values work only in the views surrounded by the `.searchable()` modifier:

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

Adding search to the app is easy. But setting up the behavior is more difficult.
