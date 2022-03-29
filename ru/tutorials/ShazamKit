## Что такое ShazamKit

ShazamKit был представлен на WWDC в 2021 году, это фреймворк от Apple, который помогает разработчику интегрировать распознавание музыки или звуков в приложение. Это может быть либо распознавание песен из каталога самого Shazam (который Apple купила еще в 2017), либо распознавание звуков на основании своей собственной базы аудио.

Фреймворк работает на iOS, iPadOS, macOS, tvOS и watchOS. Кроме того, ShazamKit SDK также доступен для Android.

## Обзор

ShazamKit создает уникальную акустическую сигнатуру аудиозаписи, чтобы найти совпадение в своей базе данных. Эта сигнатура фиксирует частотно-временное распределение энергии звукового сигнала в исходном звуке. Фактически, ShazamKit выполняет одностороннее хэширование аудио, поэтому невозможно отпечаток превратить обратно в звук.

При поиске сигнатура запроса, которую ShazamKit создает для полученного аудио, сравнивается с эталонными сигнатурами в базе данных. Совпадения возникают, когда сигнатура запроса в определенной степени совпадает с частью эталонной. Таким образом совпадения могут происходить, даже если полученный звук шумный, например, при частичной записи фоновой музыки, играющей в ресторане.

На рисунке ниже показано сопоставление сигнатуры запроса с эталонной сигнатурой в каталоге. Информация о совпадении включает временной код в эталонной записи.

![Сопоставление сигнатуры запроса с эталонной сигнатурой в каталоге](https://cdn.sparrowcode.io/tutorials/shazamkit/signature-match.png)

Например, собственное приложение Shazam преобразует звуковой поток с микрофона устройства в сигнатуру и ищет совпадение в каталоге Shazam Music. При совпадение база отдает метаданные, такие как название песни, имя исполнителя и пр.

Вы можете создать собственный каталог со своими собственными сигнатурами и связанными с ними метаданными. Например, каталог приложения для виртуального обучения может содержать референсные сигнатуры для обучающих видео и связанные с ними метаданные, включающие тайм-коды для вопросов. Используя ShazamKit, приложение может идентифицировать текущий вопрос и показать видео с ответом. 

## Начинаем работу

Чтобы начать работать с ShazamKit и общаться с сервисами, нужно получить идентификатор для  приложения. Перейдите на портал разработчиков Apple. В разделе “Certificates, Identifiers, and Profiles” выберите вкладку “Identifiers ” на боковой панели и щелкните значок “Add”, чтобы создать новый идентификатор приложения.

![Добавление идентификатора в App Store Connect](https://cdn.sparrowcode.io/tutorials/shazamkit/register-new-id.png)

Нажмите «Continue», задайте Bundle ID. В разделе «App Services» включите ShazamKit, чтобы добавить его возможности.

![Добавляем ShazamKit](https://cdn.sparrowcode.io/tutorials/shazamkit/register-app-id.png)

## Механизма сопоставления Shazam

Прежде чем писать код и использовать ShazamKit API, давайте еще раз по пунктам разберемся, как работает Shazam:

1. Приложение начинает использовать микрофон для записи потока с предварительно заданным размером буфера.
2. Библиотека ShazamKit для аудиобуфера генерирует сигнатуру (хэш, подпись) только что записанного аудио.
3. Затем ShazamKit отправляет запрос с этой звуковой подписью в Shazam API. Сервис Shazam сопоставляет подпись с эталонными подписями музыки в каталоге.
5. Если есть совпадение, API возвращает метаданные трека в ShazamKit.
6. ShazamKit вызывает нужного делегата и передает метаданные для показа в приложении.

## Сопоставление музыки с каталогом Shazam

Пришло время реализовать упрощенный клон Shazam. Вот первый код:
```swift
import AVFAudio
import Foundation
import ShazamKit
    
class MatchingHelper: NSObject {
    private var session: SHSession?
    private let audioEngine = AVAudioEngine()
    
    private var matchHandler: ((SHMatchedMediaItem?, Error?) -> Void)?
    
    init(matchHandler handler: ((SHMatchedMediaItem?, Error?) -> Void)?) {
        matchHandler = handler
     }
}
```
Это вспомогательный класс, который управляет микрофоном и использует ShazamKit для идентификации звука. С самого начала код импортирует ShazamKit вместе с AVFAudio. Вам понадобится AVFAudio, чтобы использовать микрофон и записывать звук.

`MatchingHelper` также является подклассом NSObject, поскольку это требуется для любого класса, соответствующего `SHSessionDelegate`.

Взгляните на свойства MatchingHelper:

* `session`: сеанс ShazamKit, который вы будете использовать для связи со службой Shazam.
* `audioEngine`: экземпляр AVAudioEngine, который вы будете использовать для получения звука с микрофона.
* `matchHandler`: блок обработчика, который будут реализовывать представление результатов в  приложении. Он вызывается, когда процесс идентификации заканчивается.

Инициализатор гарантирует, что matchHandler установлен при создании экземпляра класса.

Добавьте следующий метод после инициализатора:
```swift
func match(catalog: SHCustomCatalog? = nil) throws {
  // 1. Instantiate SHSession
  if let catalog = catalog {
    session = SHSession(catalog: catalog)
  } else {
    session = SHSession()
  }

  // 2. Set SHSession delegate
  session?.delegate = self

  // 3. Prepare to capture audio
  let audioFormat = AVAudioFormat(
    standardFormatWithSampleRate: 
      audioEngine.inputNode.outputFormat(forBus: 0).sampleRate,
    channels: 1)
  audioEngine.inputNode.installTap(
    onBus: 0,
    bufferSize: 2048,
    format: audioFormat
  ) { [weak session] buffer, audioTime in 
    // callback with the captured audio buffer
    session?.matchStreamingBuffer(buffer, at: audioTime)
  }

  // 4. Start capture audio using AVAudioEngine
  try AVAudioSession.sharedInstance().setCategory(.record)
  AVAudioSession.sharedInstance()
    .requestRecordPermission { [weak self] success in
      guard
        success,
        let self = self
      else { return }
      try? self.audioEngine.start()
    }
}
```

`match(catalog:)` — это метод, который остальная часть кода приложения будет использовать для идентификации звука с помощью ShazamKit. Он принимает один необязательный параметр типа SHCustomCatalog, если нужно сопоставлять звуки со своей кастомной БД.

Давайте пройдемся по шагам:

1. Сначала мы создаем сеанс `SHSession` и передаем ему каталог, если используете наш собственный. `SHSession` по умолчанию использует каталог Shazam, если вы не предоставите собственную библиотеку звуков.
2. Устанавливаем делегат `SHSession`, который вскоре реализуем.
3. Вызываем метод AVAudioEngine `AVAudioNode.installTap(onBus:bufferSize:format:block:)`, который подготавливает ноду ввода аудио. В колбеке, которому передается захваченный звуковой буфер, вы вызываете `SHSession.matchStreamingBuffer(_:at:)`. Это преобразует звук в буфере в сигнатуру Shazam и сопоставляет ее с эталонными сигнатурами в выбранном каталоге.
4. Устанавливаем категорию или режим AVAudioSession для записи. Затем запрашиваем разрешение на запись с микрофона, вызывая AVAudioSession `requestRecordPermission(_:)`, чтобы запросить у пользователя разрешение на использование микрофона при первом запуске приложения.
5. Наконец, начинаем запись, вызывая `AVAudioEngine.start()`.

**Примечание**. Разрешение NSMicrophoneUsageDescription должно быть уже задано в Info.plist проекта.

`matchStreamingBuffer(_:at:)` обрабатывает звук и передает его в ShazamKit. Кроме того, можно использовать `SHSignatureGenerator` для создания сигнатуры и передачи ее в `match` у `SHSession`. Однако `matchStreamingBuffer(_:at:)` подходит для непрерывного звука и, следовательно, соответствует нашему варианту использования.

Далее мы реализуем делегат сессий Shazam.

## Сессии ShazamKit

Осталось два шага. Во-первых, нужно реализовать SHSessionDelegate для обработки полученных данных сопоставления.

Добавьте следующее расширение класса в конец:

```swift
extension MatchingHelper: SHSessionDelegate {
  func session(_ session: SHSession, didFind match: SHMatch) {
    DispatchQueue.main.async { [weak self] in
      guard let self = self else {
        return
      }

      if let handler = self.matchHandler {
        handler(match.mediaItems.first, nil)
        // stop capturing audio
      }
    }
  }
}  
```

В этом расширении реализуем `SHSessionDelegate`. SHSession вызывает `session(_:didFind:)`, когда записанная подпись соответствует песне в каталоге. У него есть два параметра: сеанс `SHSession`, из которого он был вызван, и объект `SHMatch`, содержащий результаты.

Здесь вы проверяете, установлен ли `matchHandler`, и вызываете его, передавая следующие параметры:

1. Первый `SHMatchedMediaItem` из возвращенных `mediaItem` в `SHMatch`: ShazamKit может возвращать несколько совпадений, если сигнатура запроса соответствует нескольким песням в каталоге. Они упорядочены по качеству совпадения, первое из которых имеет самое высокое качество.
2. Тип ошибки: поскольку мы обрабатываем совпадение, передаем `nil`.

Вы реализуете этот блок обработчика в SwiftUI в следующем разделе.

Сразу после `session(_:didFind:)` добавим:

```swift
func session(
  _ session: SHSession, 
  didNotFindMatchFor signature: SHSignature, 
  error: Error?
) {
  DispatchQueue.main.async { [weak self] in
    guard let self = self else {
      return
    }

    if let handler = self.matchHandler {
      handler(nil, error)
      // stop capturing audio
    }                          
  }
}
```

`session(_:didNotFindMatchFor:error:)` — это метод делегата, который SHSession вызывает, когда в каталоге нет песни, соответствующей сигнатуре запроса, или когда возникает ошибка, препятствующая сопоставлению. Он возвращает ошибку в третьем параметре или `nil`, если в каталоге Shazam не было совпадения для запроса. Подобно тому, что мы делали в `session(_:didFind:)`, вызываем тот же блок обработчика и передаем ошибку.

Наконец, чтобы придерживаться рекомендаций Apple по использованию микрофона и защитить конфиденциальность пользователей, необходимо прекратить захват звука при вызове любого из двух методов делегирования.

Добавим следующий метод сразу после `match(catalog:)` в основную часть `MatchingHelper`:

```swift
func stopListening() {
  audioEngine.stop()
  audioEngine.inputNode.removeTap(onBus: 0)
}
```

Затем вызовем stopListening() в обоих методах делегата выше. Замените следующий комментарий:

```swift
// stop capturing audio
```

на

```swift
self.stopListening()
```

## Показ совпадения

Последняя часть клона Shazam — это пользовательский интерфейс. Сделаем макет, примерно такой макет:

![Вид приложения](https://cdn.sparrowcode.io/tutorials/shazamkit/app-preview.png)

Представление состоит из двух частей. В верхней части с закругленным зеленым квадратом будет информация о песне. В нижней части - кнопка Match, которая запускает процесс сопоставления.

Во-первых, нужен объект MatchHelper. В верхней части контроллера добавьте:

```swift
@State var matcher: MatchingHelper?
```

Затем в конце struct, сразу после body, добавьте:

```swift
func songMatched(item: SHMatchedMediaItem?, error: Error?) {
  isListening = false
  if error != nil {
    status = "Cannot match the audio :("
    print(String(describing: error.debugDescription))
  } else {
    status = "Song matched!"
    print("Found song!")
    title = item?.title
    subtitle = item?.subtitle
    artist = item?.artist
    coverUrl = item?.artworkURL
  }
}
```

`songMatched(item:error:)` — это метод, который `MatchingHelper` вызывает после завершения сопоставления. Он:

* Устанавливает `isListening` в `false`. В результате пользовательский интерфейс обновляется, чтобы показать пользователю, что приложение больше не записывает, и скрывает индикатор активности.
* Проверяет параметр ошибки. Если это не ноль, произошла ошибка, поэтому он обновляет статус, который видит пользователь, и записывает ошибку в консоль.
* Если ошибки не было, он сообщает пользователю, что нашел совпадение, и обновляет метаданные песни.

**Примечание**. `SHMatchedMediaItem` является подклассом `SHMediaItem`. Он наследует свойства метаданных, такие как название песни, исполнитель, жанр, URL-адрес обложки и URL-адрес видео. Он также имеет другие свойства, специфичные для поиска элементов, такие как FrequencySkew, разница в частоте между совпадающим звуком и звуком запроса.

В конце NavigationView добавим:

```swift
.onAppear {
  if matcher == nil {
    matcher = MatchingHelper(matchHandler: songMatched)
  }
}
.onDisappear {
  isListening = false
  matcher?.stopListening()
  status = ""
}
```

После создадим экземпляр `MatchHelper`, передавая обработчик, который вы только что добавили, в момент появления View. Когда представление исчезает, например, когда вы переключаетесь на другую вкладку, вы останавливаете процесс идентификации, вызывая `stopListening()`.

Наконец, делаем кнопку Match, как показано ниже:

```swift
Button("Match") {
}
.font(.title)
```

Добавляем обработку нажатия:

```swift
status = "Listening..."
isListening = true
do {
  try matcher?.match()
} catch {
  status = "Error matching the song"
}
```

Здесь и начинается волшебство. Вы меняете статус, чтобы сообщить пользователю, что приложение слушает, и вызываете `match()`, чтобы начать процесс сопоставления. Когда `SHSession` возвращает результат `MatchingHelper`, он вызывает `songMatched(item:error:)`.

## Тестирование

Сейчас вы можете протестировать ShazamKit только на физическом устройстве. Попробуйте определить любую музыку, которая у вас играет. 

## Дополнительно

* [https://developer.apple.com/shazamkit/](https://developer.apple.com/shazamkit/): Официальная страница
* [https://developer.apple.com/documentation/shazamkit](https://developer.apple.com/documentation/shazamkit): Документация
* [https://developer.apple.com/shazamkit/android/](https://developer.apple.com/shazamkit/android/): Документация для Android
* [https://developer.apple.com/videos/play/wwdc2021/10044/](https://developer.apple.com/videos/play/wwdc2021/10044/): Сессия WWDC21, посвященная ShazamKit
