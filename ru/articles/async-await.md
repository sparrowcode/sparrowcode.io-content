`async/await` — новый поход для работы с многопоточностью в Swift. Он упрощает написание сложных цепочек вызовов и делает код читаемым. Сначала разберёмся с теорией, а в конце туториала напишем инструмент для поиска приложений в App Store с использованием `async/await`.

![async/await Preview](https://cdn.sparrowcode.io/articles/async-await/preview.png)

## Как пользоваться

Взлянем на классический пример скачивания изображения с использованием `URLSession`:

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

Удобная обёртка выглядит так:

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

Что держим в уме: 
- `completion` должен вызываться один раз - когда результат готов.
- Не забываем переключаться на главный поток. Появляются конструкции `[weak self]` и `guard let self = self else { return }`
- Иногда бывает сложно отменить операцию загрузки. Например, если мы работаем с ячейкой таблицы.

Напишем новую версию функции, используя `async/await`. Apple позаботилась о нас и добавила асинхронный API для `URLSession`, чтобы получать данные из сети:

```swift
func data(for request: URLRequest) async throws -> (Data, URLResponse)
```

Ключевое слово `async` означает, что функция работает только в асинхронном контексте. Ключевое слово `throws` означает, что асинхронная функция может выдавать ошибку. Если нет - `throws` нужно убрать. Возьмём эпловскую функцию и на её основе напишем асинхронный вариант `loadImage(for url: URL)`:

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

Функцию вызываем с помощью `Task` - базового юнита асинхронной задачи. О нём мы поговорим позже, а сейчас посмотрим на реализацию `setImage(url: URL)`:

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

Теперь взглянем на схему для функции `setImage(url: URL)`:

![How to work setImage(url: URL)](https://cdn.sparrowcode.io/articles/async-await/set-image-scheme.png)

и `loadImage(for: url)`:

![How to work loadImage(for: URL)](https://cdn.sparrowcode.io/articles/async-await/load-image-scheme.png)

Когда выполнение дойдёт до `await`, функция **может** остановиться, а может и нет. Система выполнит метод `loadImage(for: url)`, поток не заблокируется в ожидании результата. Когда метод закончит выполняться, система возобновит работу функции - продолжится выполнение `self.image = image`. Мы обновили UI, не переключая поток: это приравнивание автоматически сработает на главном потоке.

Вот так получился читаемый, безопасный код. Не нужно помнить про поток или беспокоиться о возможной утечке памяти из-за ошибок захвата `self`. Благодаря обёртке `Task` операцию легко отменить.

Если система увидит, что приоритетнее задач нет, жёлтая задача `Task` выполнится немедленно. При использовании `await` мы не знаем, когда начнётся и закончится выполнение задачи. Задачу могут выполнять разные потоки.

Напишем `async` функцию на основе обычной функции на `clousers`, используя `withCheckedContinuation`. Функция вернёт ошибку через `withCheckedThrowingContinuation`. Пример:

```swift
func loadImage(for url: URL) async throws -> UIImage {
    try await withCheckedThrowingContinuation { continuation in
        loadImage(for: url) { (result: Result<UIImage, Error>) in
            continuation.resume(with: result)
        }
    }
}
```

Используйте функцию для явного переключения на другой поток. Вызвать `continuation.resume` можно только один раз, иначе - краш.

`async` умеет запускать две асинхронные функции параллельно:

```swift
func loadUserPage(id: String) async throws -> (UIImage, CertificateModel) {
    let user = try await loadUser(for: id)
    async let avatarImage = loadImage(user.avatarURL)
    async let certificates = loadCertificates(for: user)

    return (try await avatarImage, try await certificates)
}
```

Функции `loadImage` и `loadCertificates` запускаются параллельно. Значение вернётся, когда оба запроса выполнятся. Если одна из функций вернёт ошибку, `loadUserPage` вернёт эту же ошибку.

## Теперь про Task

`Task` - базовый юнит асинхронной задачи, место вызова асинхронного кода. Асинхронные функции выполняются как часть `Task`. Это аналог потока. `Task` — структура:

```swift
struct Task<Success, Failure> where Success : Sendable, Failure : Error
```

Результатом может быть значение или ошибка конкретного типа. Тип ошибки `Never` означает, что задача не вернёт ошибку. У задачи могут быть разные состояния: `выполняется`, `приостановлена` и `завершена`, а запускаются они с приоритетами `.background`, `.hight`, `.low`, `.medium `, `.userInitiated` , `.utility`.

С помощью экземпляра задачи можно получать результат асинхронно, отменять и проверять отмену задачи:

```swift
let downloadFileTask = Task<Data, Error> {
    try await Task.sleep(nanoseconds: 1_000_000)
    return Data()
}

// ...

if downloadFileTask.isCancelled {
    print("Загрузка была уже отменена")
} else {
    downloadFileTask.cancel()
    // Помечаем задачу как cancel
    print("Загрука отменяется...")
}
```

Вызов `cancel()` у родителя вызовет `cancel()` у потомков. При этом вызов `cancel()` это не отмена, а **просьба** об отмене. Событие отмены зависит от реализации блока `Task`.

Из задачи можно вызывать другую задачу и организовывать сложные цепочки. Вызываем во `viewWillAppear()` для примера:

```swift
Task {
    let cardsTask = Task<[CardModel], Error>(priority: .userInitiated) {
        /* запрос на модели карт пользователя */
        return []
    }
    let userInfoTask = Task<UserInfo, Error>(priority: .userInitiated) {
        /* запрос на модель о пользователе */
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

Аналогия на GCD для этого кода, которая описывает, что происходит:

```swift
DispatchQueue.main.async {
    var cardsResult: Result<[CardModel], Error>?
    var userInfoResult: Result<UserInfo, Error>?

    let dispatchGroup = DispatchGroup()

    dispatchGroup.enter()
    DispatchQueue.main.async {
        cardsResult = .success([/* запрос на карты */])
        dispatchGroup.leave()
    }

    dispatchGroup.enter()
    DispatchQueue.main.async {
        /* запрос на модель о пользователе */
        userInfoResult = .success(UserInfo())
        dispatchGroup.leave()
    }

    dispatchGroup.notify(queue: .main, execute: { in
        if case let .success(cards) = cardsResult,
           case let .success(userInfo) = userInfoResult {
            self.updateUI(with: cards, and: userInfo)

            // да! не DispatchQueue.global(qos: .background)
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

`Task` по умолчанию наследует приоритет и контекст у задачи родителя, а если родителя нет, то наследует у текущего `actor`. Создавая Task в `viewWillAppear()`, мы неявно вызываем его в главном потоке. `cardsTask` и `userInfoTask` вызовутся на главном потоке, потому что `Task` наследует это из родительской задачи. Мы не сохранили `Task`, но содержимое отработает и `self` захватится сильно. Если удалили контроллер до того, как закроем его с помощью `dismiss()`, код `Task` продолжит выполняться. Но можно сохранить ссылку на на нашу задачу и отменить её:

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

`cancel()` не отменяет выполнение `Task`. Нужно как можно раньше отменять желаемым образом, чтобы не выполнялся лишний код:

```swift
loadingTask = Task {
    let cardsTask = Task<[CardModel], Error>(priority: .userInitiated) {
        /* запрос на модели карт пользователя */
        return []
    }
    let userInfoTask = Task<UserInfo, Error>(priority: .userInitiated) {
        /* запрос на модель о пользователе */
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

Чтобы задача не наследовала ни контекст, ни приоритет, используйте `Task.detached`:

```swift
Task.detached(priority: .background) {
    await saveUserInfoIntoCache(userInfo: userInfo)
    await cleanupInCache()
}
```

Полезно применять, когда задача не зависит от родительской. Вот пример сохранения в кеш от  WWDC:

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

Отмена `downloadImageAndMetadata` после успешной загрузки изображения не должна отменять сохранение. С `Task` сохранение бы отменилось. При выборе `Task`/`Task.detached` нужно понять, зависит ли подзадача от задачи родителя в вашем кейсе.

Если нужно запустить массив операций (например, загрузить список изображений по массиву URL), используйте `TaskGroup`. Создавайте его с помощью `withTaskGroup/withThrowingTaskGroup`:

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

`actor` - новый тип данных. Он нужен для синхронизации, а ещё предотвращает состояние гонки. Компилятор проверяет его на стадии компиляции:

```swift
actor ImageDownloader {
    var cache: [String: UIImage] = [:]
}

let imageDownloader = ImageDownloader()
imageDownloader.cache["image"] = UIImage() // ошибка компиляции
// error: actor-isolated property 'cache' can only be referenced from inside the actor
```

Чтобы использовать `cache`, обратитесь к нему в `async`-контексте. Но не напрямую, а через такой метод:

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

`actor` решает гонку данных. Вся логика по синхронизации работает под капотом. Неверные действия вызовут ошибку компилятора, как в примере выше.

По свойствам `actor` — объект между `class` и `struct`. Это ссылочный тип значений, но наследоваться от него нельзя. Он отлично подходит для написания сервиса.

Система асинхронности построена так, чтобы мы перестали думать потоками. `actor` - обёртка, генерирующая `class`, который подписывается под протокол `Actor`, а ещё щепотка проверок:

```swift
public protocol Actor: AnyObject, Sendable {
    nonisolated var unownedExecutor: UnownedSerialExecutor { get }
}

final class ImageDownloader: Actor {
    // ...
}
```

Полезно знать:
-  `Sendable` — протокол-пометка, что тип безопасен для работы в параллельной среде
- `nonisolated` отключает проверку безопасности для свойства, то есть мы можем использовать в любом месте кода свойство без `await`
- `UnownedSerialExecutor` - слабая ссылка на протокол `SerialExecutor`

У `SerialExecutor: Executor` от `Executor` есть метод `func enqueue(_ job: UnownedJob)`, который выполняет задачи. Сначала пишем это:

```swift
let imageDownloader = ImageDownloader()
Task {
    await imageDownloader.setImage(for: "image", image: UIImage())
}
```

А потом семантически происходит следующее:

```swift
let imageDownloader = ImageDownloader()
Task {
    imageDownloader.unownedExecutor.enqueue {
        setImage(for: "image", image: UIImage())
    }
}
```

По умолчанию Swift генерирует стандартный `SerialExecutor` для кастомных акторов. Кастомные реализации `SerialExecutor` переключают потоки. Так работает `MainActor`.

`MainActor` - `Actor`, у которого `Executor` переводит в главный поток. Создать его нельзя, но можно обратиться к его экземпляру `MainActor.shared`.

```swift
extension MainActor {
    func runOnMain() {
        // напечается что-то вроде:
        // <_NSMainThread: 0x600003cf04c0>{number = 1, name = main}
        print(Thread.current)
    }
}

Task(priority: .background) {
    await MainActor.shared.runOnMain()
}
```

Когда писали акторы, мы создавали новый инстанс. Однако Swift позволяет создавать глобальные акторы через `protocol GlobalActor`, если добавить атрибут `@globalActor`. Apple уже сделала это для `MainActor`, поэтому можно явно сказать, на каком акторе должна работать функция:

```swift
@MainActor func updateUI() {
    // job
}

Task(priority: .background) {
    await runOnMain()
}
```

По аналогии с `MainActor` можно создавать глобальные акторы:

```swift
@globalActor actor ImageDownloader {
    static let shared = ImageDownloader()
    // ...
}

@ImageDownloader func action() {
    // ...
}
```

Можно помечать функции и классы - тогда у методов по умолчанию будут атрибуты. `UIView`, `UIViewController` Apple пометила как `@MainActor`, поэтому вызовы на обновление интерфейса после работы сервиса работают корректно.

## Практика

Напишем инструмент для поиска приложений в App Store. Он будет показывать позицию сервиса для поиска приложений:

```
GET https://itunes.apple.com/search?entity=software?term=<запрос>
{
    trackName: "Имя приложения"
    trackId: 42
    bundleId: "com.apple.developer"
    trackViewUrl: "ссылка на приложение"
    artworkUrl512: "ссылка на иконку приложения"
    artistName: "название приложения"
    screenshotUrls: ["ссылка на первый скриншот", "на второй"],
    formattedPrice: "отформатированная цена приложения",
    averageUserRating: 0.45,

    // ещё куча другой информации, но мы это опустим.
}
```

Модель данных:
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

C такими структурами работать неудобно, да и не хочется зависеть от модельки сервера. Добавим прослойку:

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

Создадим сервис через `actor`:

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


Для построения `URL` используем `URLComponents` - он красивый, модульный и избавит от проблем с URL-encoding:

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

Конвертируем модель данных с сервера в локальную:

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

Приходят URL от изображений.

Ячейка таблица конфигурируется при скролле. Чтобы не качать иконку каждый раз, сохраним в кеш. Программисты скидывают логику на библиотеки типа [Nuke](https://github.com/kean/Nuke), но с `async/await` у нас будет свой `Nuke`:

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

Сделаем удобнее:

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

`imageLoader` переведёт работу на бекграунд-поток. Хотя `setImage` вывозится из главного потока, после `await` выполнение **может** продолжиться на бекграунд. Исправим это, добавив `@MainActor`.
Кеширование готово. Сделаем отмену. Глянем на реализацию ячейки (layout пропускаю):

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

Если иконка отсутствует в кеше, она будет загружаться из сети, а в процессе загрузки на экране будет отображаться loading стейт. Если загрузка не закончилась, а пользователь проскроллил и картинка больше не нужна, загрузка отменится.

Подготовим `ViewController` (layout и детали работы с таблицей пропускаю):

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

Опишу делегат, чтобы реагировать на поиск:

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

Нажимаем «Search» - отменяем предыдущий поиск, запускаем новый. В задаче `searchingTask` не забываем проверить, что поиск ещё актуален. Сложная концепция умещается в 15 строк кода.

## Обратная совместимость

Работает iOS 13 из-за того, что фича требует нового рантайма.

Apple принесла асинхронный API в HealthKit с iOS 13, CoreData c iOS 15, а новый StoreKit 2 предлагает только асинхронный интерфейс. Код сохранения тренировки стал проще:

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

На `async/await`:

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

## Ссылки

[Скачать проект-пример](https://cdn.sparrowcode.io/articles/async-await/app-store-search.zip): Попрактикуйтесь, добавив новый экран детали страницы App Store, решите проблему с загрузкой скриншотов и правильной отменой, если пользователь быстро закрыл страницу

[Серия статей о async/await](https://www.andyibanez.com/posts/modern-concurrency-in-swift-introduction/): Множество примеров использования async/await. Например, раскрыта тема `@TaskLocal`, есть и другие полезные мелочи.

[Устройство акторов под капотом](https://habr.com/ru/company/otus/blog/588540/): Если хотите больше узнать о реализации акторов под капотом

[Исходный код swift](https://github.com/apple/swift/tree/main/stdlib/public/Concurrency): Если хотите познать истину, то обратитесь к коду

WWDC-сессии:

[Protect mutable state with Swift actors](https://developer.apple.com/wwdc21/10133): Видео-туториал от Apple об actor. Рассказывают, какие проблемы он решает и как им пользоваться.

[Explore structured concurrency in Swift](https://developer.apple.com/wwdc21/10134): Видео-туториал от Apple о структурном параллелизме, в частности, о `Task`, `Task.detached`, `TaskGroup` и приоритетах операции

[Meet async/await in Swift](https://developer.apple.com/wwdc21/10132): Видео-туториал от Apple о том, как работает async/await. Есть наглядные схемы.
