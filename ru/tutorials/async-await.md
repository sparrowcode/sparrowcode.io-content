`async/await` это новый поход для работы с многопоточностью в Swift, который позволяет почти на нет свести гонку данных, упростить написание сложных цепочек вызовов, и сделать более читаемым код. Рассмотрим теорию, а в конце туториала напишем небольшой инструмент для поиска приложений в App Store с использованием `async/await`.

![async/await preview](https://cdn.ivanvorobei.by/websites/sparrowcode.io/async-await/preview.png)

## Использование

Чтобы продемонстрировать красоту `async/await` для начала глянем на классический пример для скачивания изображения из сети используя `URLSession`:

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

Разберем проблемы этого кода:
- Необходимо внимательно следить за тем, чтобы `completion` вызывался и при этом только один раз, когда результат готов.
- Чаще всего после получения данных с сервера мы отображаем данные в интерфейсе, поэтому нам надо не забыть перейти на главный поток. Одновременно с этим появляются типичные конструкции `[weak self]` и `guard let self = self else { return }`
- Без специальных достаточно сложных наворотов мы не можем легко и быстро отменить операцию загрузки, например, если мы работаем с ячейкой таблицы.

А теперь напишем новую версию этой функции, используя `async/await`.

Apple уже позаботилась о нас и добавила новый асинхронный API для `URLSession`, чтобы получать данные из сети:

```swift
func data(for request: URLRequest) async throws -> (Data, URLResponse)
```

Ключевое слово `async` в объявлении означает, что функция может работать только в асинхронном контексте. Ключевое слово `throws` работает по таким же правилам как и в обычной функции и является способом обозначить, что ваша асинхронная функция может выдавать ошибку. Если не может выдавать ошибку - `throws` нужно убрать. Давайте на основе эпловской функции напишем асинхронный вариант `loadImage(for url: URL)`.

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

Теперь нашу функцию надо вызывать с помощью `Task` - базового юнита асинхронной задачи. Мы поговорим подробней о этой структуре ниже, сейчас же посмотрим на одну из реализаций `setImage(url: URL)`

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

Для наглядности посмотрим на общую схему работы функции `setImage(url: URL)` и `loadImage(for: url)`:

![How to work setImage(url: URL)](https://cdn.ivanvorobei.by/websites/sparrowcode.io/async-await/set-image-scheme.png)
![How to work loadImage(for: URL)](https://cdn.ivanvorobei.by/websites/sparrowcode.io/async-await/load-image-scheme.png)

Немного пояснений: когда выполнение дойдет до `await` функция **может** приостановиться. Система возьмет на себя исполнение тела метода `loadImage(for: url)`, при этом поток не заблокируется в ожидании результата. После того, как метод закончит свою работу, система возобновит работу функции то есть продолжится выполнение `self.image = image` . Заметим, что мы не переключая поток обновляем UI, но на самом деле это приравнивание автоматически сработает на главном потоке. В итоге мы получили более читаемый код, который к тому же значительно безопасней, так как мы не можем забыть поменять поток или же случайно получить утечку памяти из-за ошибок захвата `self`. К тому же за счет обертки `Task` мы сможет легко отменить операцию.

Отмечу, что желтая задача “Task” вполне может выполниться немедленно, если система увидит, что более приоритетных задач сейчас нет. При использовании `await` мы не можем точно сказать, когда именно начнется и закончится выполнение задачи, а также не гарантируется, что нашу асинхронную функцию будет обрабатывать всегда один и тот же поток. Это надо держать в голове.

Мы можем также написать `async` функцию на основе обычной функции на `clousers`, используя`withCheckedContinuation`, или, если функция может вернуть ошибку `withCheckedThrowingContinuation`. Глянем на пример:

```swift
func loadImage(for url: URL) async throws -> UIImage {
    try await withCheckedThrowingContinuation { continuation in
        loadImage(for: url) { (result: Result<UIImage, Error>) in
            continuation.resume(with: result)
        }
    }
}
```

С помощью этого инструмента можно очень быстро адаптировать функцию под этот API. К тому же это можно использовать для явного переключения на другой поток. Важно помнить, что `continuation.resume` должен быть вызван только один раз, иначе - краш.

А что если хотим запустить, к примеру, две асинхронные функции параллельно? Используя `async` - легко:

```swift
func loadUserPage(id: String) async throws -> (UIImage, CertificateModel) {
    let user = try await loadUser(for: id)

    async let avatarImage = loadImage(user.avatarURL)
    async let certificates = loadCertificates(for: user)

    return (try await avatarImage, try await certificates)
}
```

 Здесь функции `loadImage` и `loadCertificates` запускаются параллельно, когда оба запроса выполнятся, мы просто вернем значения. Если одна из функций вернет ошибку - `loadUserPage` вернет эту же ошибку.

### Task

`Task` - базовый юнит асинхронной задачи, место вызова вашего асинхронного кода. Все асинхронные функции выполняются как часть некоторого `Task`. Является аналогом потока.  `Task` это структура:

```swift
struct Task<Success, Failure> where Success : Sendable, Failure : Error
```

Как можно заметить из сигнатуры, результатом работы может быть какое-то значение или ошибка определенного типа. Тип ошибки `Never` означает, что задача не вернет ошибку.  Задача может быть в состоянии “выполняется”, “приостановлена”, “завершена”.

Задачи можно запускать с разными приоритетами `.background`, `.hight`, `.low`, `.medium `, `.userInitiated` , `.utility`, чтобы сначала попытаться выполнить задачи с более высоким приоритетом, а затем продолжить обслуживать задачи с более низким приоритетом. Это не означает “перейти на поток .global(qos:)”. Так как async/await может работать не только под платформы Apple, то реальное следование этим приоритетам будет зависеть от реализации на платформе.

С помощью экземпляра задачи мы можем получать асинхронно результат, отменять, и проверять отменена ли была задача:

```swift
let downloadFileTask = Task<Data, Error> {
    try await Task.sleep(nanoseconds: 1_000_000)
    return Data()
}

...

if downloadFileTask.isCancelled {
    print("Загрузка была уже отменена")
} else {
    downloadFileTask.cancel()
    // Помечаем задачу как cancel
    print("Загрука отменяется...")
}
```

Вызов `cancel()` у родителя приводит к вызову `cancel()` у его потомков. Также стоит упомянуть, что вызов `cancel()` это не отмена, а просьба об отмене. Реальная отмена зависит от реализации блока `Task`.

Из задачи можно вызывать другую задачу и с помощью этого организовывать сложные цепочки, например:

```swift
// вызываем во viewWillAppear() для примера
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

Попробую привести аналогию на GCD для этого кода, которая приблизительно описывает что происходит:

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

`Task` по умолчанию наследует приоритет и контекст у задачи родителя а если нет родителя, то у текущего `actor`. То есть, например, создавая Task как в нашем примере в viewWillAppear(), мы неявно вызываем его на главном потоке, когда дойдем до `actor` станет понятно почему именно так. `cardsTask` и `userInfoTask` вызовутся также на главном потоке, из-за того, что `Task` наследует это из родительской задачи. Обратите внимание также на то, что мы не сохранили `Task`, но содержимое отработает, `self` захватиться сильно, это надо учитывать при использовании Task. Если вы решим удалить контроллер, до того, например, закроем его с помощью `dismiss()`, код `Task` продолжит выполняться дальше, однако мы можем сохранить ссылку на на нашу задачу и отменить ее в нужное время:

```swift
final class MyViewController: UIViewController {

    private var loadingTask: Task<Void, Never>?

    override func viewWillAppear(_ animated: Bool) {
        super.viewWillAppear(animated)
        if notDataYet {
            loadingTask = Task {
                ...
            }
        }
    }

    override func viewDidDisappear(_ animated: Bool) {
        super.viewDidDisappear(animated)
        loadingTask?.cancel()
    }

}
```

Однако, как было сказано выше, `cancel()` вовсе не отменяет выполнение тела `Task`. Мы должны как можно раньше реагировать на отмену желаемым образом, чтобы не исполнялся лишний код:

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

Задачу можно создать таким образом, чтобы она не наследовала ни контекст, ни приоритет, в таком случае надо использовать `Task.detached`:

```swift
Task.detached(priority: .background) {
    await saveUserInfoIntoCache(userInfo: userInfo)
    await cleanupInCache()
}
```

Очень полезно, когда наша задача не зависит от родительской, например, сохранение в кеш, пример от  WWDC:

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

Глобальная разница почему не надо использовать просто Task в данном случае - отмена `downloadImageAndMetadata` после успешной загрузки `image` уже не должна отменять сохранение на диск, а при обычной `Task` это бы и произошло. При выборе detached/не detached нужно просто понять, зависит ли подзадача от задачи родителя в вашем кейсе.

Если вам необходимо запустить целый массив операций, например, загрузить список изображений по массиву URL, имеется `TaskGroup` который можно создать с помощью `withTaskGroup/withThrowingTaskGroup`. Использование:

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

### actor

`actor` - новый тип данных, который необходим для синхронизации и предназначен для того, чтобы предотвратить состояние гонки. Благодаря тому, что это особый тип данных компилятор может делать проверки о безопасности еще на стадии компиляции, например:

```swift
actor ImageDownloader {
    var cache: [String: UIImage] = [:]
}

let imageDownloader = ImageDownloader()
imageDownloader.cache["image"] = UIImage() // ошибка компиляции
```

Чтобы теперь использовать `cache` необходимо обращаться к нему в `async` контексте, но не напрямую, а через метод.

```swift
actor ImageDownloader {
    var cache: [String: UIImage] = [:]

    func setImage(for key: String, image: UIImage) {
        cache["image"] = image
    }
}

let imageDownloader = ImageDownloader()

Task {
    await imageDownloader.setImage(for: "image", image: UIImage())
}
```

Таким образом `actor` может решать проблемы гонки данных, вся логика по синхронизации работает под капотом. При этом неверные действия вызовут ошибку компилятора, как в примере выше. По свойствам `actor` это что-то между `class` и `struct` - является ссылочным типом значений, но наследоваться от него нельзя. Отлично подходит для написания сервиса.

Новая система асинхронности построена так, чтобы мы перестали думать потоками. Если без фанатизма углубиться в то как работают акторы, можно увидеть, что `actor` - это удобная обертка, которая генерирует `class`, который подписывается под протокол `Actor` + щепотка проверок:

```swift
public protocol Actor: AnyObject, Sendable {
	nonisolated var unownedExecutor: UnownedSerialExecutor { get }
}

final class ImageDownloader: Actor {
    ...
}
```

Где:
-  `Sendable` это протокол-пометка, что тип безопасен для работы в параллельной среде,
- `nonisolated` - отключает проверку безопасности для свойства, другими словами мы можем использовать в любом месте кода свойство без `await`,
- `UnownedSerialExecutor` - слабая ссылка на протокол `SerialExecutor`.
В свою очередь `SerialExecutor: Executor` от `Executor` имеет метод `func enqueue(_ job: UnownedJob)`, который и выполняет наши задачи. То есть когда мы пишем следующим образом:

```swift
let imageDownloader = ImageDownloader()
Task {
    await imageDownloader.setImage(for: "image", image: UIImage())
}
```

Семантически происходит следующее:

```swift
let imageDownloader = ImageDownloader()
Task {
    imageDownloader.unownedExecutor.enqueue {
        setImage(for: "image", image: UIImage())
    }
}
```

По умолчанию swift генерирует стандартный `SerialExecutor` для наших кастомных акторов. За счет кастомных реализаций `SerialExecutor` можно переключать потоки. На этой основе работает `MainActor`.

`MainActor` - это `Actor`, у которого `Executor` переводит нас на главный поток и выполняет на нем код. Создать его нельзя, но можно обратиться к его экземпляру `MainActor.shared`.

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

Когда мы писали свои акторы, мы создавали новый инстанс, и использовали его. Однако swift позволяет создавать глобальные акторы через `protocol GlobalActor`, если добавить атрибут `@globalActor`. Apple уже сделала это за нас для `MainActor`, поэтому мы можем явно сказать на каком акторе должна работать функция:

```swift
@MainActor func updateUI() {
    // job
}

Task(priority: .background) {
    await runOnMain()
}
```

По аналогии с `MainActor`, мы тоже можем создавать свои глобальные акторы:

```swift
@globalActor actor ImageDownloader {
    static let shared = ImageDownloader()
    ...
}

@ImageDownloader func action() {
    ...
}
```

Помечать мы можем не только функции, но и классы, тогда все методы по умолчанию будут также иметь этот атрибут. `UIView`, `UIViewController` Apple уже пометила как `@MainActor`, поэтому вызовы на обновление интерфейса после работы какого-нибудь сервиса работают корректно.

### Практика

Перейдем к практике и напишем небольшой инструмент для поиска приложений в App Store, который будет показывать номер позиции в поиске.

Начнем с сервиса, который будет непосредственно искать приложения. API у нас простой:

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
    // еще куча другой информации, но мы это опустим
}
```

Создадим модель данных:
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

C такими структурами работать в приложении будет не удобно, да и вообще не очень правильно зависеть от модельки сервера, поэтому добавим прослойку:
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

У нас есть все, чтобы создать сервис:

```swift
actor AppsSearchService {

    private static let baseURLString: String = "https://itunes.apple.com"

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

Да, с использованием `async` получается очень понятный линейный код. Чтобы функция вернула ошибку используем `throw`, любые ошибки декодирования или сетевого запроса будут также проброшены наверх по аналогии с обычной функцией, которая может вернуть ошибку.

Как видно нам приходят не сами изображения, а URL до них. Как известно, когда мы будет выводить ячейку таблицы она будет переконфигурироваться при скролле. Чтобы не качать иконку на каждый чих, нам стоит сохранять ее в кеш, а также отменять загрузку, если иконка уже не требуется. Часто программисты скидывают это логику на библиотеки типа Nuke. Но с `async/await` мы можем написать упрощенный Nuke:

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

И чтобы было удобно пользоваться, напишем дополнительно:

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

Но это решает только лишь проблему с кешем, а как же с отменой? Глянем на реализацию ячейки (layout намерено упущен):

```swift
final class AppSearchCell: UITableViewCell {

    private lazy var iconApp = UIImageView()
    private lazy var activityIndicatorView = UIActivityIndicatorView()
    private lazy var appNameLabel = UILabel()
    private lazy var developerLabel = UILabel()
    private lazy var ratingLabel = UILabel()

    private var loadImageTask: Task<Void, Never>?

}

extension AppSearchCell {

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

Теперь, если иконка отсутствует в кеше, она будет загружаться, а в процессе загрузки на экране будет отображаться loading стейт. А в случае если загрузка еще не закончилась, а пользователь проскроллил далее и ячейка переконфигурировалась, предыдущая загрузка отмениться, начнется новая.

Наш `ViewController` выглядит так (layout намерено упущен):

```swift
final class AppSearchViewController: UIViewController {

    // MARK: - Neested Types

    enum Section: Hashable {
        case main
    }

    enum Cell: Hashable {
        case app(AppEnity)
    }

    enum State {
        case initial
        case loading
        case empty
        case data([AppEnity])
        case error(Error)
    }

    typealias Snapshot = NSDiffableDataSourceSnapshot<Section, Cell>
    typealias DataSource = UITableViewDiffableDataSource<Section, Cell>

    // MARK: - Subviews

    private lazy var tableView = UITableView(frame: .zero, style: .insetGrouped)
    private lazy var searchController = UISearchController(searchResultsController: nil)

    private lazy var activityIndicatorView = UIActivityIndicatorView()
    private lazy var statusLabel = UILabel()

    // MARK: - Private Properties

    private lazy var dataSource = createDataSource()

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

                if apps.isEmpty {
                    self?.state = .empty
                } else {
                    self?.state = .data(apps)
                }
            } catch {
                self?.state = .error(error)
            }
        }
    }

}
```

На каждое нажатие Search мы также отменяем поиск, и запускаем новую задачу. В нашем случае еще необходимо правильно отреагировать на отмену `searchingTask` - выйти из функции, если задача была отменена пока мы загружали информацию. В целом это все, вот так легко можно теперь писать всякого рода асинхронности.

### Обратная совместимость

Можно начинать использовать с iOS 13 из-за того, что фича требует нового рантайма, который не раскатали ниже 13.

С iOS 15 Apple принесла асинхронный API в HealthKit, CoreData, а новый StoreKit 2 предлагает только асинхронный интерфейс. Посмотрите как он упрощает код в задаче сохранения беговой тренировки в HealthKit!

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

А вот на `async/await` получается:

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

### Ссылки

Полезные ссылки:
- [Скачать проект-пример](https://cdn.ivanvorobei.by/websites/sparrowcode.io/async-await/app-store-search.zip)
- [Хорошая серия статей о async/await](https://www.andyibanez.com/posts/modern-concurrency-in-swift-introduction/)
- [Больше информации о устройстве акторов под капотом на Хабре](https://habr.com/ru/company/otus/blog/588540/)
- [Исходный код: для тех кто хочет узнать познать истину](https://github.com/apple/swift/tree/main/stdlib/public/Concurrency)

WWDC-сессии:
- [Protect mutable state with Swift actors](https://developer.apple.com/wwdc21/10133)
- [Explore structured concurrency in Swift](https://developer.apple.com/wwdc21/10134)
- [Meet async/await in Swift](https://developer.apple.com/wwdc21/10132)
