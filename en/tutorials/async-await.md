`async/await` is a new approach for working with multithreading in Swift. It simplifies writing complex call chains and makes code readable. First the theory, and at the end of the tutorial we'll write a tool to search for apps in the App Store using `async/await`.

![async/await Preview](https://cdn.sparrowcode.io/tutorials/async-await/preview.png)

## Usage

Let's look at a classic example of downloading an image using `URLSession`:

```swift
typealias Completion = (Result<UIImage, Error>) -> Void

loadc loadImage(for url: URL, completion: @escaping Completion) {
    let urlRequest = URLRequest(url: url)
    let task = URLSession.shared.dataTask(
        with: urlRequest,
        completionHandler: { (data, response, error) in
            if let error = error {
                completion(.failure(error))
                return
            }

            guard let response = response as? HTTPURLResponse else {
                completion(.failure(URLError(.badServerResponse))
                return
            }

            guard response.statusCode == 200 else {
                completion(.failure(URLError(.badServerResponse)))
                return
            }

            guard let data = data, let image = UIImage(data: data) else {
                completion(.failure(URLError(.cannotDecodeContentData)))
                return
            }

            completion(.success(image))
        }
    )
    task.resume()
}
```

A handy wrapper looks like this:

```swift
extension UIImageView {

    func setImage(url: URL) {
        loadImage(for: url, completion: { [weak self] result in
            DispatchQueueue.main.async { [weak self] in
                switch result {
                case .success(let image):
                    self?.image = image
                case .failure(let error):
                    self?.image = nil
                    print(error.localizedDescription)
                }
            }
        })
    }
}
```

Let's break down the problems:
- Be careful that ``completion`` is called once - when the result is ready.
- Don't forget to switch to the main thread. Constructs `[weak self]` and `guard let self = self else { return }` appear.
- It's hard to undo a load operation. For example, if we work with a table cell.

Let's write a new version of the function using `async/await`. Apple has taken care and added an asynchronous API for `URLSession` to get data from the network:

```swift
func data(for request: URLRequest) async throws -> (Data, URLResponse)
```

The ``async`` keyword means that the function only works in an asynchronous context. The `throws` keyword means that the asynchronous function may produce an error. If not, `throws` must be removed. Based on the Apple function, let's write an asynchronous version of ``loadImage(for url: URL)``:

```swift
func loadImage(for url: URL) async throws -> UIImage {
    let urlRequest = URLRequest(url: url)
    let (data, response) = try await URLSession.shared.data(for: urlRequest)

    let response = response as? HTTPURLResponse else {
        throw URLError(.badServerResponse)
    }

    guard response.statusCode == 200 else {
        throw URLError(.badServerResponse)
    }

    guard let image = UIImage(data: data) else {
        throw URLError(.cannotDecodeContentData)
    }

    return image
}
```

The function is called using `Task` - the base unit of an asynchronous task. We'll talk more about this structure below. Let's look at the implementation of `setImage(url: URL)`:

```swift
extension UIImageView {

    func setImage(url: URL) {
        Task {
            do {
                let image = try await loadImage(for: url)
                self.image = image
            } catch {
                print(error.localizedDescription)
                self.image = nil
            }
        }
    }
}
```

Let's look at the diagram for the `setImage(url: URL)` function:

![How to work setImage(url: URL)](https://cdn.sparrowcode.io/tutorials/async-await/set-image-scheme.png)

And `loadImage(for: url)`:

![How to work loadImage(for: URL)](https://cdn.sparrowcode.io/tutorials/async-await/load-image-scheme.png)

When the execution reaches `await` the function **may** (or not) stop. The system will execute the `loadImage(for: url)` method, the thread is not blocked waiting for the result. When the method finishes executing, the system will resume the function - continue executing `self.image = image`. We updated the UI without switching the thread: this equation will *automatically* work on the main thread.

We got readable, safe code. No need to remember the thread or catch memory leaks due to `self` capture errors. The `Task` wrapper makes it easy to undo the operation.

If the system sees that there is no higher priority task, the yellow task `Task` will be executed immediately. With `await` we do not know when the task will start and end. The task may be executed by different threads.

Let's write a `async` function based on the normal function on `clousers`, using `withCheckedContinuation`. The function will return an error via `withCheckedThrowingContinuation`. Example:

```swift
func loadImage(for url: URL) async throws -> UIImage {
    try await withCheckedThrowingContinuation { continuation in
        loadImage(for: url) { (result: Result<UIImage, Error>) in
            continuation.resume(with: result)
        }
    }
}
```

Use the function to switch explicitly to another thread. `continuation.resume` needs to be called only once, otherwise it crashes.

`async` knows how to run two asynchronous functions in parallel:

```swift
func loadUserPage(id: String) async throws -> (UIImage, CertificateModel) {
    let user = try await loadUser(for: id)
    async let avatarImage = loadImage(user.avatarURL)
    async let certificates = loadCertificates(for: user)

    return (try await avatarImage, try await certificates)
}
```

The `loadImage` and `loadCertificates` functions run in parallel. The value will be returned when both requests are executed. If one of the functions returns an error, `loadUserPage` will return the same error.

## Task

`Task` is the base unit of an asynchronous task, the place where asynchronous code is called. Asynchronous functions are executed as part of `Task`. It is an analogue of a thread. The `Task` is a structure:

```swift
struct Task<Success, Failure> where Success : Sendable, Failure : Error
```

The result can be a value or an error of a particular type. The `Never` type of error means that the task will not return an error. The task can be in state `executed`, `paused` and `completed`. Tasks are started with priorities `.background`, `.hight`, `.low`, `.medium`, `.userInitiated` , `.utility`.

With a task instance you can get results asynchronously, cancel and check cancellation of the task:

```swift
let downloadFileTask = Task<Data, Error> {
    try await Task.sleep(nanoseconds: 1_000_000)
    return Data()
}

// ...

if downloadFileTask.isCancelled {
    print("The download had already been cancelled")
} else {
    downloadFileTask.cancel()
    // Помечаем задачу как cancel
    print("The download is canceled...")
}
```

Calling `cancel()` on the parent will call `cancel()` on the offspring. Calling `cancel()` is not an undo, but a **request** to undo. The cancel event depends on the implementation of the `Task` block.

You can call another task from a task and arrange complex chains. Call `viewWillAppear()` for an example:

```swift
Task {
    let cardsTask = Task<[CardModel], Error>(priority: .userInitiated) {
        /* query for user card models */
        return []
    }
    let userInfoTask = Task<UserInfo, Error>(priority: .userInitiated) {
        /* query the model about the user */
        return UserInfo()
    }

    do {
        let cards = try await cardsTask.value
        let userInfo = try await userInfoTask.value

        updateUI(with: userInfo, and: cards)

        Task(priority: .background) {
            await saveUserInfoIntoCache(userInfo: userInfo)
        }
    } catch {
        showErrorInUI(error: error)
    }
}
```

A GCD analogy for this code that describes what happens:

```swift.
DispatchQueueue.main.async {
    var cardsResult: Result<[CardModel], Error>?
    var userInfoResult: Result<UserInfo, Error>?

    let dispatchGroup = DispatchGroup()

    dispatchGroup.enter()
    DispatchQueueue.main.async {
        cardsResult = .success([/* request for cards */])
        dispatchGroup.leave()
    }

    dispatchGroup.enter()
    DispatchQueueue.main.async {
        /* request for a model about the user */
        userInfoResult = .success(UserInfo())
        dispatchGroup.leave()
    }

    dispatchGroup.notify(queue: .main, execute: { in
        if case let .success(cards) = cardsResult,
           case let .success(userInfo) = userInfoResult {
            self.updateUI(with: cards, and: userInfo)

            // yes! not DispatchQueueue.global(qos: .background)
            DispatchQueueue.main.async { in
                self.saveUserInfoIntoCache(userInfo: userInfo)
            }
        } else if case let .failure(error) = cardsResult { in
            self.showErrorInUI(error: error)
        } else if case let .failure(error) = userInfoResult { in
            self.showErrorInUI(error: error)
        }
    })
}
```

The `Task` by default inherits the priority and context from the parent task. If there is no parent, the current `actor` has one. By creating a Task in `viewWillAppear()`, we implicitly call it in the main thread. The `cardsTask` and `userInfoTask` will be called on the main thread, because `Task` inherits this from the parent task. We didn't save `Task`, but the content will work and `self` will be grabbed heavily. If we remove the controller before we close it with `dismiss()`, the `Task` code will continue to run. But we can keep a reference to our task and undo it:

```swift.
final class MyViewController: UIViewController {

    private var loadingTask: Task<Void, Never>?

    override func viewWillAppear(_ animated: Bool) {
        super.viewWillAppear(animated)
        if notDataYet {
            { {
                // ...
            }
        }
    }

    override func viewDidDisappear(_ animated: Bool) {
        super.viewDidDisappear(animated)
        loadingTask?.cancel()
    }
}
```

``cancel()`` does not cancel the execution of ``Task``. You need to cancel as early as possible in the desired way, so that no unnecessary code is executed:

```swift
loadingTask = Task {
    let cardsTask = Task<[CardModel], Error>(priority: .userInitiated) {
        /* request for user card models */
        return []
    }
    let userInfoTask = Task<UserInfo, Error>(priority: .userInitiated) {
        /* query the model about the user */
        return UserInfo()
    }

    do {
        let cards = try await cardsTask.value

        guard !Task.isCancelled else { return }
        let userInfo = try await userInfoTask.value

        guard ! Task.isCancelled else { return }
        updateUI(with: userInfo, and: cards)

        Task(priority: .background) {
            guard !Task.isCancelled else { return }
            await saveUserInfoIntoCache(userInfo: userInfo)
        }
    } catch {
        showErrorInUI(error: error)
    }
}

```

To ensure that the task does not inherit either context or priority, use ``Task.detached``:

```swift.
Task.detached(priority: .background) {
    await saveUserInfoIntoCache(userInfo: userInfo)
    { await cleanupInCache()
}
```

Useful to apply when the task is independent of the parent task. Save to cache, example from WWDC:

```swift
func storeImageInDisk(image: UIImage) async {
    guard
        let imageData = image.pngData(),
        let cachesUrl = FileManager.default.urls(for: .cachesDirectory, in: .userDomainMask).first else {
            return
    }
    let imageUrl = cachesUrl.appendingPathComponent(UUID().uuidString)
    try? imageData.write(to: imageUrl)
}

func downloadImageAndMetadata(imageNumber: Int) async throws -> DetailedImage {
    let image = try await downloadImage(imageNumber: imageNumber)
    Task.detached(priority: .background) {
        await storeImageInDisk(image: image)
    }
    let metadata = try await downloadMetadata(for: imageNumber)
    return DetailedImage(image: image, metadata: metadata)
}
```

Undoing `downloadImageAndMetadata` after successfully loading an image should not undo the save. With ``Task`` the save would be canceled. When selecting `Task` / `Task.detached`, you need to understand if the subtask depends on the parent task in your case.

If you need to run an array of operations (for example: load a list of images by an array of URLs) use `TaskGroup`, Create it with `withTaskGroup/withThrowingTaskGroup`:

```swift
func loadUserImages(for id: String) async throws -> [UIImage] {
    let user = try await loadUser(for: id)

    let userImages: [UIImage] = try await withThrowingTaskGroup(of: UIImage.self) { group -> [UIImage] in
        for url in user.imageURLs {
            group.addTask {
                return try await loadImage(for: url)
            }
        }

        var images: [UIImage] = []
        for try await images in group {
            images.append(image)
        }

        return images
    }

    return userImages
}
```

## actor

`actor` is a new data type, which is needed for synchronization and prevents race condition. The compiler checks it at compile time:

```swift
actor ImageDownloader {
    var cache: [String: UIImage] = [:]
}

let imageDownloader = ImageDownloader()
imageDownloader.cache["image"] = UIImage() // compile error
// error: actor-isolated property 'cache' can only be referenced from inside the actor
```

To use `cache`, refer to it in `async` context. But not directly, but through a method:

```swift
actor ImageDownloader {
    var cache: [String: UIImage] = [:]

    func setImage(for key: String, image: UIImage) {
        cache[key] = image
    }
}

let imageDownloader = ImageDownloader()

Task {
    await imageDownloader.setImage(for: "image", image: UIImage())
}
```

The `actor` decides the data race. All synchronization logic works under the hood. Incorrect actions will cause a compiler error, as in the example above.

By properties `actor` is an object between `class` and `struct` - it is a reference value type, but you cannot inherit from it. Great for writing a service.

The asynchrony system is built so that we stop thinking in terms of threads. `actor` is a wrapper that generates a `class` that subscribes to the `Actor` protocol + a pinch of checks:

``swift``.
public protocol Actor: AnyObject, Sendable {
    nonisolated var unownedExecutor: UnownedSerialExecutor { get }
}

final class ImageDownloader: Actor {
    // ...
}
```

Where:
- `Sendable` is a protocol-notation that the type is safe to use in a parallel environment
- `nonisolated` - disables the security check for the property, in other words we can use the property anywhere in the code without `await`
- `UnownedSerialExecutor` - weak reference to the protocol `SerialExecutor

The `SerialExecutor: Executor` from `Executor` has a method `func enqueue(_ job: UnownedJob)` that performs tasks. When we write this:

``swift.
let imageDownloader = ImageDownloader()
Task {
    await imageDownloader.setImage(for: "image", image: UIImage())
}
```

Semantically, the following happens:

```swift.
let imageDownloader = ImageDownloader()
Task {
    imageDownloader.unownedExecutor.enqueue {
        setImage(for: "image", image: UIImage())
    }
}
```

By default, Swift generates a standard `SerialExecutor` for custom actors. Custom ``SerialExecutor`` implementations switch threads. This is how `MainActor` works.

The `MainActor` is the `Actor` that the `Executor` switches to the main thread. You cannot create it, but you can refer to its instance `MainActor.shared`.

```swift
extension MainActor {
    func runOnMain() {
        // it prints something like:
        // <_NSMainThread: 0x600003cf04c0>{number = 1, name = main}
        print(Thread.current)
    }
}

Task(priority: .background) {
    await MainActor.shared.runOnMain()
}
```

When writing actors, we were creating a new instance. However, Swift allows you to create global actors via `protocol GlobalActor` if you add the `@globalActor` attribute. Apple has already done this for `MainActor`, so you can explicitly say on which actor the function should work:

```swift
@MainActor func updateUI() {
    // job
}

Task(priority: .background) {
    await runOnMain()
}
```

Similar to ``MainActor``, you can create global actors:

```swift
@globalActor actor ImageDownloader {
    static let shared = ImageDownloader()
    // ...
}

@ImageDownloader func action() {
    // ...
}
```

You can mark functions and classes - then the default methods will also have the attribute. Apple marked `UIView`, `UIViewController` as `@MainActor`, so calls to update the interface after the service works correctly.

## Practice

Let's write a tool to search for applications in the App Store, which will show the position. Service, which will search for applications:

```
GET https://itunes.apple.com/search?entity=software?term=<request>
{
    trackName: "app name"
    trackId: 42
    bundleId: "com.apple.developer"
    trackViewUrl: "application link"
    artworkUrl512: "application icon link"
    artistName: "title of the app"
    screenshotUrls: ["link to the first screenshot", "to the second screenshot"],
    formattedPrice: "the formatted price of the app",
    averageUserRating: 0.45,

    // a bunch of other information, but we'll omit that
}
```

Data model:
```swift
struct iTunesResultsEntry: Decodable {

    let results: [ITunesResultEntry]
}

struct iTunesResultEntry: Decodable {

    let trackName: String
    let trackId: Int
    let bundleId: String
    let trackViewUrl: String
    let artworkUrl512: String
    let artistName: String
    let screenshotUrls: [String]
    let formattedPrice: String
    let averageUserRating: Double
}
```

It's not convenient to work with such structures, and we don't need to depend on the server model. Let's add a layer:

```swift
struct AppEnity {

    let id: Int
    let bundleId: String
    let position: Int

    let name: String
    let developer: String
    let rating: Double

    let appStoreURL: URL
    let iconURL: URL
    let screenshotsURLs: [URL]
}
```

Let's create a service with `actor`:

```swift
actor AppsSearchService {

    func search(with query: String) async throws -> [AppEnity] {
        let url = buildSearchRequest(for: query)
        let urlRequest = URLRequest(url: url)
        let (data, response) = try await URLSession.shared.data(for: urlRequest)

        Let response = response as? HTTPURLResponse, response.statusCode == 200 else {
            throw URLError(.badServerResponse)
        }

        let results = try JSONDecoder().decode(iTunesResultsEntry.self, from: data)

        let entities = results.results.enumerated().compactMap { item -> AppEnity? in
            let (position, entry) = item
            return convert(entry: entry, position: position)
        }

        return entities
    }
}
```


To build `URL` use `URLComponents` - beautiful, modular and will get rid of problems with URL coding:

` `swift
extension AppsSearchService {

    private static let baseURLString: String = "https://itunes.apple.com"

    private func buildSearchRequest(for query: String) -> URL {
        var components = URLComponents(string: Self.baseURLString)

        components?.path = "/search"
        components?.queryItems = [
            URLQueryItem(name: "entity", value: "software"),
            URLQueryItem(name: "term", value: query),
        ]

        guard let url = components?.url else {
            fatalError("developer error: unable to create url for search query: query=\"\(query)\")
        }

        return url }
    }
}
```

Convert data model from server to local:

```swift
AppsSearchService extension {

    private func convert(entry: ITunesResultEntry, position: Int) -> AppEnity? {
        let appStoreURL = URL(string: entry.trackViewUrl) else {
            return nil
        }

        guard let iconURL = URL(string: entry.artworkUrl512) else { return nil }
            return nil
        }

        return AppEnity(
            id: entry.trackId,
            bundleId: entry.bundleId,
            position: entry,
            name: entry.trackName,
            developer: entry.artistName,
            rating: entry.averageUserRating,
            appStoreURL: appStoreURL,
            iconURL: iconURL,
            screenshotsURLs: entry.screenshotUrls.compactMap { URL(string: $0) }
        )
    }
}
```

The URLs from the images come in.

The cell table is configured by scrolling. In order not to download the icon every time, let's save it to the cache. Programmers dump logic to libraries like [Nuke](https://github.com/kean/Nuke), but with `async/await` we will have our own `Nuke`:

```swift
actor ImageLoaderService {

    private var cache = NSCache<NSURL, UIImage>()

    init(cacheCountLimit: Int) {
        cache.countLimit = cacheCountLimit
    }

    func loadImage(for url: URL) async throws -> UIImage {
        if let image = lookupCache(for: url) {
            return image
        }

        let image = try await doLoadImage(for: url)

        updateCache(image: image, and: url)

        return lookupCache(for: url) ? image
    }

    private func doLoadImage(for url: URL) async throws -> UIImage {
        let urlRequest = URLRequest(for url: url)

        let (data, response) = try await URLSession.shared.data(for: urlRequest)

        Let response = response as? HTTPURLResponse, response.statusCode == 200 else {
            throw URLError(.badServerResponse)
        }

        guard let image = UIImage(data: data) else {
            throw UURLRLError(.cannotDecodeContentData)
        }

        return image
    }

    private lookupCache(for url: URL) -> UIImage? {
        return cache.object(forKey: url as NSURL)
    }

    private func updateCache(image: UIImage, and url: URL) {
        if cache.object(forKey: url as NSURL) == nil {
            cache.setObject(image, forKey: url as NSURL)
        }
    }
}
```

Let's make it more convenient:

```swift
UIImageView extension {

    private private let imageLoader = ImageLoaderService(cacheCountLimit: 500)

    @MainActor
    func setImage(by url: URL) async throws {
        let image = try await Self.imageLoader.loadImage(for: url)

        if !Task.isCancelled {
            self.image = image
        }
    }
}
```

The `imageLoader` will move the job to the background thread. Although `setImage` is taken out of the main thread, after `await` execution **may** continue to the backgrounder. We fix this by adding `@MainActor`.
The caching is done. Let's do an undo. Let's look at the cell implementation (I'm skipping the layout):

```swift
final class AppSearchCell: UITableViewCell {

    private var loadImageTask: Task<Void, Never>?

    func configure(with appEntity: AppEnity) {
        appNameLabel.text = appEntity.position.formatted() + ". " + appEntity.name
        developerLabel.text = appEntity.developer
        ratingLabel.text = appEntity.rating.formatted(.number.precision(.significantDigits(3))) + " rating"

        configureIcon(for: appEntity.iconURL)
    }

    private func configureIcon(for url: URL) {
        loadImageTask?.cancel()

        loadImageTask = Task { [weak self] in
            self?.iconApp.image = nil
            self?.activityIndicatorView.startAnimating()

            do {
                try await self?.iconApp.setImage(by: url)
                self?.iconApp.contentMode = .scaleAspectFit
            } catch {
                self?.iconApp.image = UIImage(systemName: "exclamationmark.icloud")
                self?.iconApp.contentMode = .center
            }

            self?.activityIndicatorView.stopAnimating()
        }
    }
}
```

If the icon is not in the cache, it will be downloaded from the web, and the loading stat will be displayed on the screen during the loading process. If the loading is not finished and the user has scrolled and the picture is no longer needed - the loading will be canceled.

Prepare a `ViewController` (I'm skipping the details of working with the table):

```swift
Final class AppSearchViewController: UIViewController {

    enum State {
        case initial
        case loading
        case empty
        case data([AppEnity])
        case error(Error)
    }

    private var searchingTask: Task<Void, Never>?
    private lazy var searchService = AppsSearchService()
    private var state: State = .initial {
        didSet { updateState() }
    }

    func updateState() {
        switch state {
        case .initial:
            tableView.isHidden = false
            activityIndicatorView.stopAnimating()
            statusLabel.text = "Input your request"
        case .loading:
            tableView.isHidden = true
            activityIndicatorView.startAnimating()
            statusLabel.text = "Loading..."
        case .empty:
            tableView.isHidden = true
            activityIndicatorView.stopAnimating()
            statusLabel.text = "No apps found"
        case .data(let apps):
            tableView.isHidden = false
            activityIndicatorView.stopAnimating()
            statusLabel.text = nil
            var snapshot = Snapshot()
            snapshot.appendSections([.main])
            snapshot.appendItems(apps.map { .app($0) }, toSection: .main)
            dataSource.apply(snapshot)
        case .error(let error):
            tableView.isHidden = true
            activityIndicatorView.stopAnimating()
            statusLabel.text = "Error: \(error.localizedDescription)"
        }
    }
}
```

I'll describe a delegate to respond to the search:

```swift
extension AppSearchViewController: UISearchControllerDelegate, UISearchBarDelegate {

    func searchBarSearchButtonClicked(_ searchBar: UISearchBar) {
        let query = searchBar.text else {
            return
        }

        searchingTask?.cancel()
        searchingTask = Task { [weak self] in
            self?.state = .loading

            do {
                let apps = try await searchService.search(with: query)

                if Task.isCancelled { return }

                if apps.isEmpty {
                    self?.state = .empty
                } else {
                    self?.state = .data(apps)
                }
            } catch {
                if Task.isCancelled { return }
                self?.state = .error(error)
            }
        }
    }
}
```

Press "Search" - cancel the previous search, start a new one. In the `searchingTask` do not forget to check that the search is still relevant. The complex concept fits into 15 lines of code.

## Backwards compatibility

Works for iOS 13 because the feature requires a new runtime.

Apple brought an asynchronous API to HealthKit with iOS 13, CoreData with iOS 15 and the new StoreKit 2 only offers an asynchronous interface. The workout save code has gotten simpler:

```swift
struct RunWorkout {

    let startDate: Date
    let endDate: Date
    let route: [CLLocation]
    let heartRateSamples: [HKSample]
}

func saveWorkoutToHealthKit(runWorkout: RunWorkout, completion: @escaping (Result<Void, Error>) -> Void) {
    let store = HKHealthStore()
    let routeBuilder = HKWorkoutRouteBuilder(healthStore: store, device: .local())
    let workout = HKWorkout(activityType: .running, start: runWorkout.startDate, end: runWorkout.endDate)

    store.save(workout, withCompletion: { (status: Bool, error: Error?) -> Void in
        if let error = error {
            completion(.failure(error))
            return
        }

        store.add(runWorkout.heartRateSamples, to: workout, completion: { (status: Bool, error: Error?) -> Void in
            if let error = error {
                completion(.failure(error))
                return
            }

            if !runWorkout.route.isEmpty {
                routeBuilder.insertRouteData(runWorkout.route, completion: { (status: Bool, error: Error?) -> Void in
                    if let error = error {
                        completion(.failure(error))
                        return
                    }

                    routeBuilder.finishRoute(
                        with: workout,
                        metadata: nil,
                        completion: { (route: HKWorkoutRoute?, error: Error?) -> Void in
                            if let error = error {
                                completion(.failure(error))
                                return
                            }

                            completion(.success(Void()))
                        }
                    )
                })
            } else {
                completion(.success(Void()))
            }
        })
    })
}
```

On `async/await`:

```swift
func saveWorkoutToHealthKitAsync(runWorkout: RunWorkout) async throws {
    let store = HKHealthStore()
    let routeBuilder = HKWorkoutRouteBuilder(
        healthStore: store,
        device: .local()
    )
    let workout = HKWorkout(
        activityType: .running,
        start: runWorkout.startDate,
        end: runWorkout.endDate
    )

    try await store.save(workout)
    try await store.addSamples(runWorkout.heartRateSamples, to: workout)

    if ! runWorkout.route.isEmpty {
        try await routeBuilder.insertRouteData(runWorkout.route)
        try await routeBuilder.finishRoute(with: workout, metadata: nil)
    }
}
```

## References.

[Download sample project](https://cdn.sparrowcode.io/tutorials/async-await/app-store-search.zip): Practice adding a new App Store page detail screen, solve the problem with loading screenshots and proper undo if the user quickly closes the page.

[Articles about async/await](https://www.andyibanez.com/posts/modern-concurrency-in-swift-introduction/): There are even more examples of how to use async/await in this series of articles. For example, `@TaskLocal` and other useful trivia are covered.

[Under the hood actor design](https://habr.com/ru/company/otus/blog/588540/): If you want to learn more about implementing actors under the hood

[Swift source code](https://github.com/apple/swift/tree/main/stdlib/public/Concurrency): If you want to learn the truth, check out the code

WWDC session:

[Protect mutable state with Swift actors](https://developer.apple.com/wwdc21/10133): Apple's video tutorial about actors. They tell you what problems it solves, and how to use it.

[Explore structured concurrency in Swift](https://developer.apple.com/wwdc21/10134): Apple's video tutorial on structured concurrency, specifically `Task', `Task.detached', `TaskGroup`, and operation priorities.

[Meet async/await in Swift](https://developer.apple.com/wwdc21/10132): Apple's video tutorial on how async/await works. There are visual diagrams.
