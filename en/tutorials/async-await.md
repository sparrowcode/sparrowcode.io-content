`async/await` - a new approach for working with multithreading in Swift. It simplifies writing complex call chains and makes code readable. First we'll cover the theory, and at the end of the tutorial we'll write a tool to search for apps in the App Store using `async/await`.

![async/await Preview](https://cdn.sparrowcode.io/tutorials/async-await/preview.png)

## How it works

Code for downloading an image from `URLSession`:

```swift
typealias Completion = (Result<UIImage, Error>) -> Void

func loadImage(for url: URL, completion: @escaping Completion) {
    let urlRequest = URLRequest(url: url)
    let task = URLSession.shared.dataTask(
        with: urlRequest,
        completionHandler: { (data, response, error) in
            if let error = error {
                completion(.failure(error))
                return
            }

            guard let response = response as? HTTPURLResponse else {
                completion(.failure(URLError(.badServerResponse)))
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
            DispatchQueue.main.async { [weak self] in
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

What we keep in mind: 
- The `completion` should be called once - when the result is ready.
- Don't forget to switch to the main thread. The constructs `[weak self]` and `guard let self = self else { return }` appear.
- It's hard to undo the load operation if we're working with a table cell.

Let's write a new function with `async/await`. Apple took care of us and added an asynchronous API for `URLSession` to get data from the network:

```swift
func data(for request: URLRequest) async throws -> (Data, URLResponse)
```

The `async` keyword means that the function only works in asynchronous context. The keyword `throws` means that the asynchronous function may produce an error. If not, `throws` needs to be removed. Let's take an Apple function and use it to write an asynchronous version of `loadImage(for url: URL)`:

```swift
func loadImage(for url: URL) async throws -> UIImage {
    let urlRequest = URLRequest(url: url)
    let (data, response) = try await URLSession.shared.data(for: urlRequest)

    guard let response = response as? HTTPURLResponse else {
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

The function is called with `Task` - the basic unit of an asynchronous task. We'll talk about it later, but for now let's look at the implementation of `setImage(url: URL)`:

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

Now let's look at the scheme for the `setImage(url: URL)` function:

![How to work setImage(url: URL)](https://cdn.sparrowcode.io/tutorials/async-await/set-image-scheme.png)

and `loadImage(for: url)`:

![How to work loadImage(for: URL)](https://cdn.sparrowcode.io/tutorials/async-await/load-image-scheme.png)

When execution reaches `await`, the function **may** or may not stop. The system will execute the `loadImage(for: url)` method, the thread will not be blocked waiting for the result. When the method finishes executing, the system will resume the function - continue executing `self.image = image`. We have updated the UI without switching the thread: this equation will automatically work on the main thread.

That's how we got readable and safe code. No need to remember the thread or worry about possible memory leaks due to `self` capture errors. Thanks to the `Task` wrapper, the operation is easy to undo.

If the system sees that there are no higher priority tasks, the yellow `Task` will be executed immediately. With `await` we don't know when the task will start and end. The task may be executed by different threads.

Let's write an `async` function based on the normal function on `clousers`, using `withCheckedContinuation`. The function will return an error through `withCheckedThrowingContinuation`. Example:

```swift
func loadImage(for url: URL) async throws -> UIImage {
    try await withCheckedThrowingContinuation { continuation in
        loadImage(for: url) { (result: Result<UIImage, Error>) in
            continuation.resume(with: result)
        }
    }
}
```

Use the function to switch explicitly to another thread. You can only call `continuation.resume` once, otherwise it crashes.

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

`Task` is the basic unit of an asynchronous task, the place where asynchronous code is called. Asynchronous functions are executed as part of `Task`. It is analogous to a thread. `Task` is a structure:

```swift
struct Task<Success, Failure> where Success : Sendable, Failure : Error
```

The result can be a value or an error of a particular type. The error type `Never` means that the task will not return an error. The task can have different states: `Running`, `Paused` and `Finished`, and they are started with priorities `.background`, `.hight`, `.low`, `.medium`, `.userInitiated` , `.utility`.

With a task instance, you can get results asynchronously, undo and check the undo of a task:

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
    // Mark the task as cancel
    print("The download is canceled...")
}
```

Calling `cancel()` on the parent will call `cancel()` on the offspring. Calling `cancel()` is not a cancellation, but a **request** to cancel. The cancel event depends on the implementation of the `Task` block.

You can call another task from a task and organize complex chains. Let's call `viewWillAppear()` for an example:

```swift
Task {
    let cardsTask = Task<[CardModel], Error>(priority: .userInitiated) {
        /* request for user card models */
        return []
    }
    let userInfoTask = Task<UserInfo, Error>(priority: .userInitiated) {
        /* query for a model about a user */
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

The analogy on the GCD for this code, which describes what happens:

```swift
DispatchQueue.main.async {
    var cardsResult: Result<[CardModel], Error>?
    var userInfoResult: Result<UserInfo, Error>?

    let dispatchGroup = DispatchGroup()

    dispatchGroup.enter()
    DispatchQueue.main.async {
        cardsResult = .success([/* card request */])
        dispatchGroup.leave()
    }

    dispatchGroup.enter()
    DispatchQueue.main.async {
        /* query for a model about a user */
        userInfoResult = .success(UserInfo())
        dispatchGroup.leave()
    }

    dispatchGroup.notify(queue: .main, execute: { in
        if case let .success(cards) = cardsResult,
           case let .success(userInfo) = userInfoResult {
            self.updateUI(with: cards, and: userInfo)

            // yes! Not DispatchQueue.global(qos: .background)
            DispatchQueue.main.async { in
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

The `Task` by default inherits the priority and context from the parent task, and if there is no parent, it inherits from the current `actor`. By creating a Task in `viewWillAppear()`, we implicitly call it in the main thread. The `cardsTask` and `userInfoTask` will be called on the main thread because `Task` inherits this from the parent task. We didn't save the `Task`, but the content will work and `self` will be captured heavily. If we remove the controller before we close it with `dismiss()`, the `Task` code will continue to run. But we can keep a reference to our task and undo it:

```swift
final class MyViewController: UIViewController {

    private var loadingTask: Task<Void, Never>?

    override func viewWillAppear(_ animated: Bool) {
        super.viewWillAppear(animated)
        if notDataYet {
            loadingTask = Task {
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

`cancel()` does not cancel execution of `Task`. You need to cancel as early as possible in the desired way, so that unnecessary code is not executed:

```swift
loadingTask = Task {
    let cardsTask = Task<[CardModel], Error>(priority: .userInitiated) {
        /* request for user card models */
        return []
    }
    let userInfoTask = Task<UserInfo, Error>(priority: .userInitiated) {
        /* query for a model about a user */
        return UserInfo()
    }

    do {
        let cards = try await cardsTask.value

        guard !Task.isCancelled else { return }
        let userInfo = try await userInfoTask.value

        guard !Task.isCancelled else { return }
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

To ensure that the task does not inherit either context or priority, use `Task.detached`:

```swift
Task.detached(priority: .background) {
    await saveUserInfoIntoCache(userInfo: userInfo)
    await cleanupInCache()
}
```

Useful when the task is independent of the parent task. Here is an example of cache saving from  WWDC:

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

Canceling `downloadImageAndMetadata` after successfully loading an image should not cancel the save. With `Task` the save would be canceled. When selecting `Task` / `Task.detached`, you need to understand whether the subtask depends on the parent task in your case.

If you need to run an array of operations (e.g. load a list of images by an array of URLs), use `TaskGroup`. Create it with `withTaskGroup/withThrowingTaskGroup`:

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
        for try await image in group {
            images.append(image)
        }

        return images
    }

    return userImages
}
```

## actor

`actor` is a new data type. It is needed for synchronization and prevents race condition. The compiler checks it at compile time:

```swift
actor ImageDownloader {
    var cache: [String: UIImage] = [:]
}

let imageDownloader = ImageDownloader()
imageDownloader.cache["image"] = UIImage() // compilation error
// error: actor-isolated property 'cache' can only be referenced from inside the actor
```

To use `cache`, refer to it in the `async` context. But not directly, but through a method like this:

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

By properties, `actor` is an object between `class` and `struct`. It's a reference value type, but you can't inherit from it. It's great for writing a service.

The asynchrony system is built so that we stop thinking in terms of threads. `actor` is a wrapper that generates `class`, which subscribes to the `Actor` protocol, and a pinch of checks:

```swift
public protocol Actor: AnyObject, Sendable {
    nonisolated var unownedExecutor: UnownedSerialExecutor { get }
}

final class ImageDownloader: Actor {
    // ...
}
```

Useful to know:
- `Sendable` - protocol-marking that the type is safe to work in a parallel environment
- `nonisolated` disables the security check for the property, meaning we can use the property anywhere in the code without `await`
- The `UnownedSerialExecutor` is a weak reference to the `SerialExecutor` protocol

The `SerialExecutor: Executor` from `Executor` has a method `func enqueue(_ job: UnownedJob)` that performs tasks. First we write this:

```swift
let imageDownloader = ImageDownloader()
Task {
    await imageDownloader.setImage(for: "image", image: UIImage())
}
```

And then semantically the following happens:

```swift
let imageDownloader = ImageDownloader()
Task {
    imageDownloader.unownedExecutor.enqueue {
        setImage(for: "image", image: UIImage())
    }
}
```

By default, Swift generates a standard `SerialExecutor` for custom actors. Custom implementations of `SerialExecutor` switch threads. This is how the `MainActor` works.

The `MainActor` is the `Actor` with the `Executor` switching to the main thread. You cannot create it, but you can refer to its instance `MainActor.shared`.

```swift
extension MainActor {
    func runOnMain() {
        // prints something like:
        // <_NSMainThread: 0x600003cf04c0>{number = 1, name = main}
        print(Thread.current)
    }
}

Task(priority: .background) {
    await MainActor.shared.runOnMain()
}
```

When writing actors, we created a new instance. However, Swift allows you to create global actors via `protocol GlobalActor` if you add the `@globalActor` attribute. Apple has already done this for `MainActor`, so you can explicitly tell which actor the function should work on:

```swift
@MainActor func updateUI() {
    // job
}

Task(priority: .background) {
    await runOnMain()
}
```

Similar to `MainActor`, you can create global actors:

```swift
@globalActor actor ImageDownloader {
    static let shared = ImageDownloader()
    // ...
}

@ImageDownloader func action() {
    // ...
}
```

You can mark functions and classes - then methods will have attributes by default. Apple marked `UIView`, `UIViewController` as `@MainActor`, so calls to update the interface after the service works correctly.

## Practice

Let's write a tool to search for applications in the App Store. It will show the position of the service to search for applications:

```
GET https://itunes.apple.com/search?entity=software?term=<query>
{
    trackName: "Application name"
    trackId: 42
    bundleId: "com.apple.developer"
    trackViewUrl: "application link"
    artworkUrl512: "link to the application icon"
    artistName: "application name"
    screenshotUrls: ["link to the first screenshot", "to the second one"],
    formattedPrice: "formatted application price",
    averageUserRating: 0.45,

    // There's a lot of other information, but we'll skip that.
}
```

Data model:

```swift
struct ITunesResultsEntry: Decodable {

    let results: [ITunesResultEntry]
}

struct ITunesResultEntry: Decodable {

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

It's inconvenient to work with such structures, and you don't want to depend on the server model. Let's add a layer:

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

Let's create a service through `actor`:

```swift
actor AppsSearchService {

    func search(with query: String) async throws -> [AppEnity]  {
        let url = buildSearchRequest(for: query)
        let urlRequest = URLRequest(url: url)
        let (data, response) = try await URLSession.shared.data(for: urlRequest)

        guard let response = response as? HTTPURLResponse, response.statusCode == 200 else {
            throw URLError(.badServerResponse)
        }

        let results = try JSONDecoder().decode(ITunesResultsEntry.self, from: data)

        let entities = results.results.enumerated().compactMap { item -> AppEnity? in
            let (position, entry) = item
            return convert(entry: entry, position: position)
        }

        return entities
    }
}
```


To build `URL` use `URLComponents` - it is beautiful, modular and avoid problems with the URL-encoding:

```swift
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
            fatalError("developer error: cannot build url for search request: query=\"\(query)\"")
        }

        return url
    }
}
```

Convert data model from server to local:

```swift
extension AppsSearchService {

    private func convert(entry: ITunesResultEntry, position: Int) -> AppEnity? {
        guard let appStoreURL = URL(string: entry.trackViewUrl) else {
            return nil
        }

        guard let iconURL = URL(string: entry.artworkUrl512) else {
            return nil
        }

        return AppEnity(
            id: entry.trackId,
            bundleId: entry.bundleId,
            position: position,
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

URLs from images are coming in.

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

        return lookupCache(for: url) ?? image
    }

    private func doLoadImage(for url: URL) async throws -> UIImage {
        let urlRequest = URLRequest(url: url)

        let (data, response) = try await URLSession.shared.data(for: urlRequest)

        guard let response = response as? HTTPURLResponse, response.statusCode == 200 else {
            throw URLError(.badServerResponse)
        }

        guard let image = UIImage(data: data) else {
            throw URLError(.cannotDecodeContentData)
        }

        return image
    }

    private func lookupCache(for url: URL) -> UIImage? {
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
extension UIImageView {

    private static let imageLoader = ImageLoaderService(cacheCountLimit: 500)

    @MainActor
    func setImage(by url: URL) async throws {
        let image = try await Self.imageLoader.loadImage(for: url)

        if !Task.isCancelled {
            self.image = image
        }
    }
}
```

The `imageLoader` will move the job to the backgroud thread. Although `setImage` is taken out of the main thread, after `await` execution **may** continue to the backgrounder. We fix this by adding `@MainActor`.
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

If the icon is not in the cache, it will be downloaded from the network and the loading state will be displayed on the screen during the download. If the loading is not finished and the user has scrolled and the picture is no longer needed, the loading will be canceled.

Let's prepare a `ViewController` (I'm skipping the details of working with the table):

```swift
final class AppSearchViewController: UIViewController {

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

I will describe a delegate to respond to a search:

```swift
extension AppSearchViewController: UISearchControllerDelegate, UISearchBarDelegate {

    func searchBarSearchButtonClicked(_ searchBar: UISearchBar) {
        guard let query = searchBar.text else {
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

Press "Search" - cancel the previous search, start a new one. In the `searchingTask`, don't forget to check that the search is still relevant. A complex concept fits into 15 lines of code.

## Backwards Compatibility.

Works for iOS 13 because the chip requires a new runtime.

Apple brought asynchronous API to HealthKit with iOS 13, CoreData with iOS 15 and the new StoreKit 2 offers only asynchronous interface. The workout save code has gotten simpler:

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

At `async/await`:

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

    if !runWorkout.route.isEmpty {
        try await routeBuilder.insertRouteData(runWorkout.route)
        try await routeBuilder.finishRoute(with: workout, metadata: nil)
    }
}
```

## Helpful materials

[Download sample project](https://cdn.sparrowcode.io/tutorials/async-await/app-store-search.zip): Practice adding a new App Store page detail screen, solve the problem with loading screenshots and proper undo if the user quickly closes the page

[Async/await article series](https://www.andyibanez.com/posts/modern-concurrency-in-swift-introduction/): Lots of examples of how to use async/await. For example, `@TaskLocal` is covered, there are other useful trivia as well.

[How Actors Work](https://habr.com/ru/company/otus/blog/588540/): If you want to know more about implementing actors under the hood

[Swift source code](https://github.com/apple/swift/tree/main/stdlib/public/Concurrency): If you want to learn the truth, check out the code

WWDC session:

[Protect mutable state with Swift actors](https://developer.apple.com/wwdc21/10133): Apple's video tutorial about actors. They tell you what problems it solves and how to use it.

[Explore structured concurrency in Swift](https://developer.apple.com/wwdc21/10134): Apple's video tutorial on structured concurrency, specifically `Task', `Task.detached', `TaskGroup', and operation priorities

[Meet async/await in Swift](https://developer.apple.com/wwdc21/10132): A video tutorial from Apple on how async/await works. There are illustrative diagrams.
