Напишем приложение с использованием фреймворка MapKit. Научимся добавлять карту, гео-метки,  описание и оверлеи. Познакомимся с основными понятиями, знание и понимание которых необходимо для работы с карточными API.

- [API](#api)
- [Подключение](#подключение)
    - [Map View](#map-view)
    - [Типы карт](#типы-карт)
    - [Проекции](#проекции)
    - [Подложки](#подложки)
    - [Уровни](#уровни)
    - [Вес](#вес)
- [Метки](#метки)
    - [Location](#location)
    - [GeoMarker](#geomarker)
- [Камера](#камера)
    - [Boundary](#boundary)
    - [ZoomRange](#zoomRange)
    - [MKMapCamera](#mkmapcamera)
- [Данные](#данные)
    - [GeoJSON](#geojson)
    - [Описание](#описание)
- [MKOverlay](#mkoverlay)
    - [MKCircle](#mkcircle)
    - [MKPolyline](#mkpolyline)
    - [MKPolygon](#mkpolygon)
- [Маршрут](#маршрут)
- [Поиск](#поиск)

## API
Для создания приложения с картой нам потребуется встроенное или стороннее `API`. Под «API» (Application Programming Interface) будем понимать способ структурного взаимодействия с фреймворком или библиотекой.

`Apple` предоставляет свой собственный фреймворк для работы с картами - `MapKit`. Помимо карт от `Apple` существует множество других. Самыми популярными считаются `Google Maps` и `Open Street Maps`. Они также предоставляют `API` для `Swift`. 

Посмотрим [официальную документацию](https://developer.apple.com/documentation/mapkit/) `MapKit`. Все эти представленные наборы структур, классов и протоколов являются `API` для работы с фреймворком. Для начала работы достаточно импортировать `MapKit` в свой проект:

```swift
import MapKit
```

Подключить `Google Maps` можно несколькими методами, наиболее удобным является использование одного из пакетных менеджеров: `CocoaPods` или `Carthage`. Полное руководство можно посмотреть на [официальном сайте](https://developers.google.com/maps/documentation/ios-sdk/config).

`Open Street Maps` не предоставляют единого фреймворка. Есть набор `iOS`-[библиотек](https://wiki.openstreetmap.org/wiki/Apple_iOS#Libraries_for_developers) с картами `OSM`.

Можно использовать `MapKit`, а в качестве сервера с картами выбрать `Google Maps`, `OSM` или другой. Всё зависит от ваших нужд, детальности карт, частоты их обновления, качества и веса.

Для примера посмотрим на отображение Лондона на картах от `Apple`, `Google` и `OSM`.

**Apple Maps**

![Apple Maps](https://cdn.sparrowcode.io/tutorials/mapkit/london-apple.png)

**Google Maps**

![Google Maps](https://cdn.sparrowcode.io/tutorials/mapkit/london-g-maps.png)

**Open Street Maps**

![OSM Maps](https://cdn.sparrowcode.io/tutorials/mapkit/london-osm.png)

## Подключение
### Map View
Карта в проект добавляется аналогично любой другой `View`. Для `UIKit` предусмотрен класс `MKMapView`, а для `SwiftUI` - структура `Map`. В этом туториале мы будем работать с `UIKit`.

Создадим проект с названием `MapKitTutorial`. Выберите `Storyboard`. `Storyboard`-файл мы трогать не будем, всё сделаем через код.

Проект имеет стандартную начальную файловую структуру:

```
├── MapKitTutorial
│   ├── AppDelegate
│   ├── SceneDelegate
│   ├── ViewController
│   ├── Main
│   ├── Assets
│   ├── LaunchScreen
│   ├── Info
```

Переходим в файл `ViewController `. Импортируем `MapKit`.  В теле класса создаём постоянную `mapView` типа `MKMapView`. В качестве значения укажем ей сомовызывающуюся функцию, возвращающую экземпляр `MKMapView`.

```swift
import UIKit
import MapKit
class ViewController: UIViewController {

    let mapView: MKMapView = {
        let map = MKMapView()
        map.translatesAutoresizingMaskIntoConstraints = false
        
        return map
    }()
}
```

Этой строкой мы включили возможность выставлять `anchors` для `mapView`:

```swift
map.translatesAutoresizingMaskIntoConstraints = false
```

Создадим новый `Swift File` с названием `Helper`.  В этом файле будут вспомогательные объекты, так мы не будем захламлять класс `ViewController`.

Переходим в `Helper`. Создадим структуру `AnchorsSetter` со `static` методом `setAllSides(for view: UIView)`, который выставит `view` в размер его `superview` с учётом верхней `safeArea`.

```swift
struct AnchorsSetter {
    
    static func setAllSides(for view: UIView) {
    
        if let superview = view.superview {
            NSLayoutConstraint.activate([
                view.topAnchor.constraint(equalTo: superview.safeAreaLayoutGuide.topAnchor),
                view.rightAnchor.constraint(equalTo: superview.rightAnchor),
                view.bottomAnchor.constraint(equalTo: superview.bottomAnchor),
                view.leftAnchor.constraint(equalTo: superview.leftAnchor)
            ])
        }
    }
}
```

Переключаемся на `ViewController`. Во `viewDidLoad()` добавляем нашу карту (`mapView`) в основной `view` и позиционируем её.

```swift
override func viewDidLoad() {

    super.viewDidLoad()
    
    view.addSubview(mapView)
    AnchorsSetter.setAllSides(for: mapView)
}
```

Запускаем симулятор и видим нашу карту.

![Базовая карта](https://cdn.sparrowcode.io/tutorials/mapkit/simple-mapview.png)

### Типы карт
По типу отображения карты можно разделить на:
- `Спутник` - карта составлена из совокупности снимков со спутника
- `Схема` - карта составлена схематическим образом
- `Гибрид` - объекты схематически нанесены на совокупность спутниковых снимков, иными словами - одновременное отображение `Спутника` и `Схемы`

Пользователям обычно не требуется спутниковая карта без отображения на ней дорог, объектов, границ и названий. Поэтому разработчики делят карты на два типа для пользователей: `Схему` и `Спутник`, называя спутником именно гибридную карту. Вы неоднократно могли видеть именно эти два типа в навигаторах. Посмотрим на них.

**Схема**

![Схема](https://cdn.sparrowcode.io/tutorials/mapkit/scheme-map.png)

**Спутник**

![Спутник](https://cdn.sparrowcode.io/tutorials/mapkit/satellite-map.png)

В нашем приложении мы видим именно схематическую карту.

За изменение типа отображаемой карты в `MapKit` отвечает свойство `mapType`, принимающее значения типа `MKMapType`. `MKMapType` - перечисление, содержащее следующие кейсы:

- `standard` - карта улиц, показывающая расположение всех дорог и названия некоторых дорог
- `satellite` - спутниковые снимки местности
- `hybrid` - спутниковые снимки местности с информацией о дорогах и названиями, расположенной поверх снимков
- `satelliteFlyover` - спутниковый снимок местности с данными облёта, если они имеются
- `hybridFlyover` - гибридный спутниковый снимок с данными облёта, если они имеются
- `mutedStandard` - карта улиц, на которой данные выделены поверх основных деталей карты

Изменим тип нашей карты и посмотрим разницу.

```swift
override func viewDidLoad() {
    
    // ...
    
    mapView.mapType = .satellite
}
```

![mapView Satellite](https://cdn.sparrowcode.io/tutorials/mapkit/mapview-satellite.png)

```swift
override func viewDidLoad() {
    
    // ...
    
    mapView.mapType = .hybrid
}
```

![mapView Hybrid](https://cdn.sparrowcode.io/tutorials/mapkit/mapview-hybrid.png)

Карты делятся на множество категорий в зависимости от применения. Вот некоторые из них:
- автомобильные навигационные;
- географические;
- геологические;
- гидрогеологические;
- ландшафтные;
- морские навигационные;
- тектонические;
- топографические;
- цифровые;
- электронные.

Карта в нашем приложении относится к электронным. Каждая такая категория может представлять отдельный слой на электронной карте. Их можно отображать совместно или по отдельности. 

Карта представляет собой изображение, сформированное на основе набора геоданных. Эти данные собираются, обрабатываются и подготавливаются специалистами. Итоговые карты выставляются на продажу. Основными поставщиками карт являются разработчики ГИС (геоинформационных систем). Стоимость таких карт для рядового разработчика довольно высока. Есть множество бесплатных карт, таких как `OSM`, но стоит принять во внимание точность и частоту обновления данных.

### Проекции

Привычные нам карты - плоские, но мы знаем, что Земля имеет форму геоида. Когда мы смотрим на глобус, то видим все объекты в правильных пропорциях. На картах же мы видим проекцию геоида на плоскость. Таких проекций очень много. В привычной нам проекции материки выглядят иначе, чем они есть на самом деле.

Посмотрим на схематичное и спутниковое изображение Земли.

**Схема**

![Схема геоид](https://cdn.sparrowcode.io/tutorials/mapkit/globe-scheme.png)

**Спутник**

![Спутник геоид](https://cdn.sparrowcode.io/tutorials/mapkit/globe-satellite.png)

Самыми распространёнными проекциями являются:
- Меркатора;
- Азимутальная;
- Каврайского;
- Пирса;
- Робинсона.

`Apple Maps`, `Google Maps` и `OSM` предоставляют свои карты в проекции `Меркатора`. Мы будем работать с ней.

Посмотрим на соотношения между площадью каждой страны в проекции `Меркатора` (полупрозрачные цвета) и истинной площадью (яркие цвета):

![Соотношение площадей по Меркатору. Автор Гифки: Jakub Nowosad - собственная работа, CC BY-SA 4.0, https://commons.wikimedia.org/w/index.php?curid=73955926)](https://cdn.sparrowcode.io/tutorials/mapkit/mer-dif.png)

Такая проекция не сохраняет площади, поскольку имеет разный масштаб на разных участках. Больше всего разница в масштабе у тех объектов, что расположены ближе к полюсам (дальше от экватора), потому что там геоид сужается.

В `MapKit` это учитывается при различных расчётах, однако, необходимо понимание основных принципов. В дальнейшем мы рассмотрим это более детально.

### Подложки

"Подложка" - термин, означающий базовую карту или карту-основу, использующуюся в качестве информационного фона. Мы уже рассмотрели карты по типам и категориям, уделим внимание форматам. 

Рассмотрим на примере [`Google Earth`](https://earth.google.com/web/). Первое, что можно отметить - время загрузки. Обычно, когда вы открываете карты, то подгружается только её часть, затем участки в этой области, пока она полностью не будет загружена. В `Google Earth` же происходит подгрузка так, что глаз не успевает заметить разделения на тайлы. "Тайлами" называют квадратные (плиточные) изображения, на которые разбиваются карты. В совокупности тайлы дают впечатление большой единой картинки.

Мы видим глобус, по сути - планету Земля.

![Google Earth](https://cdn.sparrowcode.io/tutorials/mapkit/g-earth.png)

С точки зрения разработки это математически посчитанная фигура - геоид, с координатной разметкой, на которую натянули картинку. Это картинка - подложка. При увеличении, объекты будут отображаться поверх неё. Подложка может представлять собой как 2D-изображение, так и 3D. В отличие от 2D-изображения 3D-изображение помимо широт и долгот хранит информацию о высоте в каждой точке. Такая подложка называется `terrain`. Информация о высотах также может идти совместно с 2D-изображением формата `GeoTiff`, но по отображению будет отличаться от `terrain`.

Мы можем переключиться и посмотреть разницу отображений 2D и 3D.

**2D**

![Google Earth 2D](https://cdn.sparrowcode.io/tutorials/mapkit/g-earth-2d.png)

**3D**

![Google Earth 3D](https://cdn.sparrowcode.io/tutorials/mapkit/g-earth-3d.png)

Может показаться, что сильной разницы нет. Для явного различия добавим измерение расстояния. 

**Измерение 2D**

![Google Earth Measure 2D](https://cdn.sparrowcode.io/tutorials/mapkit/g-earth-measure-2d.png)

**Измерение 3D**

![Google Earth Measure 3D](https://cdn.sparrowcode.io/tutorials/mapkit/g-earth-measure-3d.png)

Обратите внимание, что при разных отображениях мы получаем одинаковое расстояние измерений, это происходит из-за учёта высоты в обоих случаях.

### Уровни

Мы выяснили, что карта разбивается на тайлы. Это позволяет увеличить скорость загрузки, так как нет необходимости грузить полное изображение, достаточно загрузить только необходимую область.

Для удобства масштабирования и скорости просмотра используют специальный механизм - карта представляется в виде пирамиды тайлов.

![Пирамида тайлов](https://cdn.sparrowcode.io/tutorials/mapkit/pyramid-tiles.png)

Самая большая область помещается в самое маленькое изображение - один тайл. Каждое последующее увеличение области представляет собой новый уровень, в котором эта область разделяется на большее число тайлов и т.д. Тайлы имеют одинаковый размер. Уровни также могут называться `zoom`, `level` и `zoom level`. Не у всех `maps API` эти уровни сопадают. Так 10-й уровень одной ГИС может соответсвовать 12-му уровню другой.

![Zoom Levels](https://cdn.sparrowcode.io/tutorials/mapkit/zoom-levels.png)

Упорядоченная совокупность тайлов представляет собой матрицу. У каждого тайла есть своё название по позиции в матрице. Тайл также обладает координатными границами. При поиске области по координатам, алгоритм ищет тайл, в который попадает эта область, обращается к нему по матричной разметке и подгружает. 

Давайте посмотрим, как это выглядит в динамике.

![Video Tiles Loading](https://cdn.sparrowcode.io/tutorials/mapkit/tiles-loading.mov)

### Вес

Важно учитывать, что совокупность тайлов даёт нам изображение высокого качества, размер которого довольно велик. Чем больше область, которую необходимо исследовать, тем больше тайлов и уровней требуется, соответственно возрастает и вес карты. На вес влияет и сопутствующая информация, он может достигать нескольких десятков, а порой и сотен гигабайт. Поэтому подгрузка по областям очень удобна.

Есть несколько способов загрузки, хранения и очищения кэша геоданных. 

Первый наиболее распространён и удобен, когда важна скорость отображения и размер оперативной памяти небольшой. Уровень загружается и сохраняется в кеш. При зуме подгружается следующий уровень, а предыдущий очищается из кеша. Так, при зуме одной и той же области в плюс и минус каждый раз будет происходить загрузка уровня и очистка предыдущего. Используется в мобильных приложениях.

Другой способ подразумевает сохранение в кеше загруженных уровней, но требует достаточного объёма оперативной памяти, потому применяется в основном на ПК-платформах в специальных ГИС.

Можно скачивать карты на определённый район на устройство, чтоб не загружать каждый раз и иметь возможность трекинга даже при слабом интернете. Такой режим называют "оффлайн картами".

## Метки

Само по себе изображение местности бесполезно обычному пользователю без дополнительных опознавательных знаков. Это могут быть подписи, метки, цветовые и схематические выделения объектов, областей, геопозиции, маршрута и т.д. Для нанесения подобных обозначений и поиска на местности используют системы координат. Чаще всего используют градусы или прямоугольные координаты.

Основные системы координат `maps API`:
- градусы (геодезические координаты `WGS84` (`EPSG:4326`))
- прямоугольные (метры, сферическая проекция Меркатора (`EPSG:3857`))
- пиксели (`XY` координаты пикселей экрана в уровне (`zoom`))
- координаты тайлов (Tile Map Service (`ZXY`))

`MapKit` использует градусы (`WGS84`).

Мы разделим метки на три типа и подробнее рассмотрим каждый из них.

### Location

Локацией принято считать определение местоположения чего-либо. Также в обиходе можно встретить определение локации, как некоторой географической области. Мы будем использовать `location` для того, чтоб указать местонахождение некоторого объекта и обозначить координаты отображаемой области.

Сейчас в нашем приложении отображается местоположение устройства. При этом уровень отображения один из начальных. Мы хотим, чтобы при открытии загружалась определённая область.

В `MapKit` есть структура: 

```swift
struct CLLocationCoordinate2D {

    var latitude: CLLocationDegrees // широта в градусах (WGS84)
    var longitude: CLLocationDegrees // долгота в градусах (WGS84)

    // ...
}
```  

Мы воспользуемся ею для создания объекта на основе координат широты и долготы. Координаты должны быть нам известны. Воспользуемся поиском через `Google Maps`. Введём в запрос что-нибудь необычное, например, "Памятник почтальону Печкину". Жмём на предложенную достопримечательность. 

![Google Maps поиск локации](https://cdn.sparrowcode.io/tutorials/mapkit/g-location-search.png)

То, что нужно. 

![Google Maps отображаемая локация](https://cdn.sparrowcode.io/tutorials/mapkit/g-location-view.png)

Теперь обратите внимание на `url`-адрес:

```
https://www.google.ru/maps/place/.../@54.9502529,39.0187517,17z/data=...
```

Нас интересует:

- `54.9502529` - широта
- `39.0187517` - долгота
- `17z` - `zoom = 17`

Благодаря пометке `17z` мы видим отображение карты в более информативном и удобном для восприятия виде. Во `viewDidLoad()` вернём обратно `mapType` в схематичный вид и добавим `location`. 

```swift
override func viewDidLoad() {
    
    // ...
    
    mapView.mapType = .standard
    let location = CLLocationCoordinate2D(latitude: 54.9502529 , longitude: 39.0187517)
}
```

Для отображения заданного региона воспользуемся методом `setRegion(_ region: MKCoordinateRegion, animated: Bool)`. Он переместит отображение в указанную локацию при помощи встроенной анимации масштабирования.

Нам потребуется создать объект типа `MKCoordinateRegion(center centerCoordinate: CLLocationCoordinate2D, latitudinalMeters: CLLocationDistance, longitudinalMeters: CLLocationDistance)`, который представляет собой прямоугольный географический регион с центром вокруг указанной широты и долготы.

`location` будет являться центральной точкой нашей карты. `regionRadius` отвечает за размер дистанции с севера на юг и с востока на запад.

```swift
override func viewDidLoad() {
    
    // ...
    
    mapView.mapType = .standard
    let location = CLLocationCoordinate2D(latitude: 54.9502529 , longitude: 39.0187517)
    let regionRadius: CLLocationDistance = 1000
    let coordinateRegion = MKCoordinateRegion(center: location, latitudinalMeters: regionRadius, longitudinalMeters: regionRadius)
    mapView.setRegion(coordinateRegion, animated: true)
}
```

Запустим и посмотрим, что получилось.

![Отображение location](https://cdn.sparrowcode.io/tutorials/mapkit/zoom-to-location.png)

Изменим `regionRadius`, чтоб немного увеличить отображение.

```swift
override func viewDidLoad() {
    
    // ...
    
    let regionRadius: CLLocationDistance = 500
}
```

![Отображение location 500](https://cdn.sparrowcode.io/tutorials/mapkit/zoom-to-location-500.png)

>Для зумирования в симуляторе удерживайте клавишу `option` и, зажав левую кнопку мыши, перемещайте курсор. 

Преобразуем наш код так, чтобы расчистить `viewDidLoad()`. Вынесем наши константы в `extension`, для этого сделаем их вычисляемыми свойствами.

```swift
extension UIViewController {

    var location: CLLocationCoordinate2D {
        CLLocationCoordinate2D(latitude: 54.9502529 , longitude: 39.0187517)
    }
    var regionRadius: CLLocationDistance { 500 }
    var coordinateRegion: MKCoordinateRegion {
        MKCoordinateRegion(center: location, latitudinalMeters: regionRadius, longitudinalMeters: regionRadius)
    }
}
```

Теперь `viewDidLoad` выглядит аккуратно:

```swift
override func viewDidLoad() {

    super.viewDidLoad()
    
    view.addSubview(mapView)
    AnchorsSetter.setAllSides(for: mapView)
    mapView.mapType = .standard
    mapView.setRegion(coordinateRegion, animated: true)
}
```

### GeoMarker

Теперь нам необходимо отметить на карте, где конкретно находится интересующий нас объект. По сути это точка на карте, но в картографии она называется "геоточка". Геоточку с опознавательными знаками, подписями или иной уточняющей информацией называют "геомаркером".

Геомаркер на карту можно нанести множествами способов, но все они сводятся к тому, что такие объекты должны соответствовать протоколу `MKAnnotation`. Т.е. такой объект является интерфейсом для связывания данных с определенным местоположением на карте.

Мы можем воспользоваться `MapKit Overlays` - оверлеями для выделения географических регионов или путей. Создадим экземпляр класса `MKPlacemark`, который отвечает за удобное описание местоположения.

```swift
extension UIViewController {

    // ...
    
    var geoPoint: MKPlacemark {
        MKPlacemark(coordinate: location)
    }
}
```

Объекты `MKPlacemark` соответствуют протоколу `MKAnnotation`, поэтому мы можем добавить их при помощи метода `addAnnotation(_ annotation: MKAnnotation)`.

```swift
override func viewDidLoad() {

    // ...
    
    mapView.addAnnotation(geoPoint)
}
```

Запускаем симулятор.

![GeoPoint](https://cdn.sparrowcode.io/tutorials/mapkit/geo-point.png)

Прекрасная минутка юмора от `Apple`. У нас появился геомаркер с некоторым дефолтным описанием, так как сами мы никакое описание не указали. В предыдущих версиях `MapKit` это добавляло пустой геомаркер.

Добавим описание, но теперь воспользуемся другим, наиболее оптимальным способом для добавления геомаркера. Теперь вместо `geoPoint` создадим экземпляр `MKPointAnnotation`, добавим в описание данные о координатах, заголовке и подзаголовке.

```swift
extension UIViewController {

    // ...
    
    var annotation: MKPointAnnotation {
        let ann = MKPointAnnotation()
        ann.coordinate = location
        ann.title = "Памятник почтальону Печкину"
        ann.subtitle = "Достопримечательность"
        
        return ann
    }
}
```

В `mapView.addAnnotation` заменяем `geoPoint` на `annotation`.

```swift
override func viewDidLoad() {

    // ...
    
    mapView.addAnnotation(annotation)
}
```

![GeoPoint Annotation](https://cdn.sparrowcode.io/tutorials/mapkit/geo-point-annotation.png)

Нажмём на геомаркер.

![GeoPoint Annotation Full](https://cdn.sparrowcode.io/tutorials/mapkit/geo-point-annotation-full.png)

Для удобства рассмотрим ещё один способ, завязанный на протоколе `MKAnnotation`, который удобно использовать при отображении множества данных.

Создадим новый `swift`-файл `Landmark` с соответствующим классом. Класс `Landmark` должен соответствовать протоколу `MKAnnotation`, а значит должен наследоваться от `NSObject`, потому что `MKAnnotation` является `NSObjectProtocol`.

`MKAnnotation` требует обязательное свойство `coordinate` типа `CLLocation` или `CLLocationCoordinate2D`.

```swift
import Foundation
import MapKit

class Landmark: NSObject, MKAnnotation {
    let coordinate: CLLocationCoordinate2D
    let title: String?
    let subtitle: String?
    
    init(coordinate: CLLocationCoordinate2D, title: String?, subtitle: String?) {
        self.coordinate = coordinate
        self.title = title
        self.subtitle = subtitle
        
        super.init()
    }
}
```

`title` и `subtitle` мы сделали `String?` потому, что координата у геоточки есть всегда, а вот заголовка и подзаголовка может и не быть, как мы не добавляли его в `geoPoint`.

Экземпляр `Landmark` заменит `annotation`. Возвращаемся к `UIViewController`. Мы не можем создать экземпляр и передать в него `location` в расширении до инициализации класса, поэтому сделаем это во `viewDidLoad()`.

```swift
override func viewDidLoad() {
    
    // ...
    
    let landmark = Landmark(coordinate: location, title: "Памятник почтальону Печкину", subtitle: "Достопримечательность")
    mapView.addAnnotation(landmark)
}
```

Запустите симулятор. Вы увидите, что разницы в отображении между `annotation` и `landmark` нет.

## Камера

`MapKit` может задать ограничения панорамирования и масштабирования карты в указанной области. Это полезно, когда необходимо сосредоточить пользователя на конкретном месте.

### Boundary

Воспользуемся методом `setCameraBoundary(_ cameraBoundary: MKMapView.CameraBoundary?, animated: Bool)`. Он устанавливает границу камеры для представления карты с возможностью использования встроенной анимации. Параметр типа `CameraBoundary` отвечает за границу области, в пределах которой должен оставаться центр карты.

```swift
override func viewDidLoad() {
    
    // ...
    
    mapView.setCameraBoundary(MKMapView.CameraBoundary(coordinateRegion: coordinateRegion), animated: true)
}
```

Запустите симулятор и попробуйте передвигаться по карте. Вы увидите, что она не прогружается дальше небольшой области.

### ZoomRange

Также нам потребуется метод `setCameraZoomRange(_ cameraZoomRange: MKMapView.CameraZoomRange?, animated: Bool)`. С его помощью мы установим диапазон масштабирования камеры для просмотра карты.

В `extension` добавим вычисляемое свойство `zoomRange`.

```swift
extension UIViewController {

    // ...

    var zoomRange: MKMapView.CameraZoomRange? {
        MKMapView.CameraZoomRange(maxCenterCoordinateDistance: 1000)
    }
}
```

`maxCenterCoordinateDistance` - максимальное расстояние от центральной координаты представления карты, измеряемое в метрах.


```swift
override func viewDidLoad() {
    
    // ...
    
    mapView.setCameraZoomRange(zoomRange, animated: true)
}
```

Запускаем и видим, что теперь нельзя отдалить карту более, чем на заданное нами расстояние. Можно также задать ограничение на приближение, для этого используется `MKMapView.CameraZoomRange(minCenterCoordinateDistance: CLLocationDistance)`.

### MKMapCamera

`MKMapCamera` - виртуальная камера. С её помощью задаётся точка обзора, угол обзора, направление компаса, шаг относительно перпендикуляра карты и высота над ней.

Воспользуемся инициализатором `MKMapCamera(lookingAtCenter centerCoordinate: CLLocationCoordinate2D, fromEyeCoordinate eyeCoordinate: CLLocationCoordinate2D, eyeAltitude: CLLocationDistance)`, - возвращает новый объект камеры, используя указанную информацию об угле обзора.

`centerCoordinate` - геоточка, по которой центрируется карта

`eyeCoordinate` - геоточка, в которой размещается камера. Если `centerCoordinate` равен `eyeCoordinate`, то карта отображается так, будто камера смотрит вниз; если их значения разные, то карта отображается с соответствующим углом наклона и направлением

`eyeAltitude` - высота (в метрах) над землей, на которой нужно разместить камеру

Зададим новую геоточку `location2`, немного изменив координаты имеющейся (`location`). По `location` будем центрировать карту, а из `location2` направим камеру. Саму камеру разместим на высоте `500` метров.

```swift
extension UIViewController {

    // ...

    var location2: CLLocationCoordinate2D {
        CLLocationCoordinate2D(latitude: 54.9502700 , longitude: 39.0187900)
    }
    
    var camera: MKMapCamera {
        MKMapCamera(lookingAtCenter: location, fromEyeCoordinate: location2, eyeAltitude: 500)
    }
}
```

Для установки камеры используем метод `setCamera(_ camera: MKMapCamera, animated: Bool)`.

```swift
override func viewDidLoad() {
    
    // ...
    
    mapView.setCamera(camera, animated: true)
}
```

![MKMapCamera](https://cdn.sparrowcode.io/tutorials/mapkit/map-camera.png)

Мы видим, что карта по прежнему центрируется в заданной нами точке, но изменился угол поворота и появился компас.

## Данные

В нашем примере у нас всего обдин объект, который мы отображаем пользователю. На деле же таких объектов очень много, вы могли видеть их на карте (магазины, например). Геоинформационные данные обычно загружаются с сервера и хранятся в специальном формате.

Мы запишем свои данные и научимся отображать их.

### GeoJSON

`JSON` - текстовый формат для обмена данными. Он хранит набор пар `ключ-значение` или упорядоченный `набор значений`. Использование единого формата позволяет унифицировать протоколы взаимодействия с данными.

Пример `JSON`-объекта:

```json
{
    "key-1": "value-1",
    "key-2": {
       "key-2-1": "value-2-1",
       "key-2-2": "value-2-2"
    }
}
```

`GeoJSON` — такой же `JSON` с определённой структурой, который хранит данные о местоположении и географических объектах.

Пример объекта `GeoJSON`:

```json
{
    "type": "Feature",
    "properties": {},
    "geometry": {
        "type": "Point",
        "coordinates": [10.000078, 80.454676]
    }
}
```

Рассмотрим ключи подробнее.

**Coordinates**

Хранит массив координат долготы и широты. В данном случае важен порядок, в котором они указаны. Долгота указывается первой, затем - широта.

```json
"coordinates": [10.000001, 20.000001]
```

**Geometry и Type**

У каждой геометрии есть ключ `type`, значения которого - специальные типы геометрии с учётом регистра. Основные:

- `Point`
- `Line`
- `Polygon`

Все типы можно посмотреть в [GeoJSON RFC](https://tools.ietf.org/html/rfc7946#page-6).

```json
"geometry": {
    "type": "Point",
    "coordinates": [10.000001, 20.000001]
}
```

```json
"geometry": {
    "type": "Polygon",
    "coordinates": [
        [
            [10.000001, 20.000001],
            [20.000001, 30.000001],
            [30.000001, 40.000001],
            [10.000001, 20.000001]
        ]
    ]
}
```

Есть некоторые типы геометрии, которые используются для хранения других типов геометрии. Это `Feature` и `FeatureCollection`.

**Properties**

Используется для дополнительной информации. Например, вместе с локацией `"coordinates": [longitude, latitude]` мы можем передавать данные о городе, погоде, количестве населения и т.д.

```json
{
    "type": "Feature",
    "properties": {
        "townName": "Funny City",
        "population": "2000000"
    },
    "geometry": {
        "type": "Point",
        "coordinates": [10.000001, 20.000001]
    }
}
```

Теперь рассмотрим подробнее типы геометрии.

**Point**

`Point` - геоточка или геомаркер с единственной координатой. Используется для хранения информации о конкретном месте.

```json
"geometry": {
    "type": "Point",
    "coordinates": [10.000001, 20.000001]
}
```

**MultiPoint**

`MultiPoint` содержит информацию о наборе независимых геоточек. Массив значений хранит набор координат.

```json
"geometry": {
    "type": "MultiPoint",
    "coordinates": [
        [10.000001, 20.000001],
        [20.000001, 30.000001],
        [30.000001, 40.000001]
    ]
}
```

**LineString**

В отличие от набора независимых точек `MultiPoint`, `LineString` содержит набор связанных точек, представляющих собой линию. Структура `coordinates` такая же, как и у `MultiPoint`.

```json
"geometry": {
    "type": "LineString",
    "coordinates": [
        [10.000001, 20.000001],
        [20.000001, 30.000001],
        [30.000001, 40.000001]
    ]
}
```

**MultiLineString**

Содержит информацию о нескольких `LineString` (линиях). В `coordinates` записывается массив из набора координат `LineString`.

```json
"geometry": {
    "type": "MultiLineString",
    "coordinates" : [
        [
            [10.000001, 20.000001],
            [20.000001, 30.000001],
            [30.000001, 40.000001]  
        ],
        [
            [50.000001, 40.000001],
            [60.000001, 30.000001],
            [70.000001, 20.000001]  
        ]
    ]
}
```

**Polygon**

`Polygon` - многоугольник, любая замкнутая фигура. Полигоны используют для записи информации о некоторой области. В `coordinates` хранится набор координат вершин многоугольника.

```json
"geometry": {
    "type": "Polygon",
    "coordinates": [
        [
            [10.000001, 20.000001],
            [20.000001, 30.000001],
            [30.000001, 40.000001],
            [10.000001, 20.000001]
        ]
    ]
}
```

**Feature и FeatureCollection**

Для записи полной информации используется тип `Feature` - геометрия геометрии, по сути.

```json
{
    "type": "Feature",
    "geometry": {
        "type": "Point",
        "coordinates": [10.000001, 20.000001]
    },
    "properties": {
        "area": "20000 sq meters",
        "city": "Funny City",
        "description": "Very funny city"
    }
}
```

`FeatureCollection` содержит набор `Features`.

```json
{
    "type": "FeatureCollection",
    "features": [
    {
        "type": "Feature",
        "properties": {},
        "geometry": {
            "type": "Point",
            "coordinates": [10.000001, 20.000001]
        }
    },
    {
        "type": "Feature",
        "properties": {},
        "geometry": {
            "type": "LineString",
            "coordinates": [
                [10.000001, 20.000001],
                [20.000001, 30.000001],
                [30.000001, 40.000001]
            ]
        }
    }
  ]
}
```

### Описание

В проекте создадим файл `data.geojson` и запишем  в него информацию о нескольких геоточках. В `properties` мы можем задавать любую необходимую нам информацию, в том числе `url`-адреса изображений. Мы укажем только необходимый минимум.

```json
{
    "type": "FeatureCollection",
    "features": [
    {
        "type": "Feature",
        "properties": {
            "title": "Памятник почтальону Печкину",
            "subtitle": "Достопримечательность"
        },
        "geometry": {
            "type": "Point",
            "coordinates": [39.0187517, 54.9502529]
        }
    },
    {
        "type": "Feature",
        "properties": {
            "title": "Почта",
            "subtitle": "Услуги"
        },
        "geometry": {
            "type": "Point",
            "coordinates": [39.0210369, 54.9500234]
        }
    }
  ]
}
```

Обратите внимание, что при записи координат первой указывается долгота.

>Если вы не знаете, как создать файл с нужным расширением в проекте, то создайте его вне проекта и добавьте туда.

Проверьте, что структура вашего проекта соответствует этой:

```
├── MapKitTutorial
│   ├── AppDelegate
│   ├── SceneDelegate
│   ├── ViewController
│   ├── Main
│   ├── Assets
│   ├── LaunchScreen
│   ├── Info
│   ├── Helper
│   ├── Landmark
│   ├── data
```

Получение данных из `JSON` называют "декодированием" или "парсингом". Мы воспользуемся объектом класса `MKGeoJSONDecoder`, который декодирует объекты `GeoJSON` в типы `MapKit` при помощи метода `decode(_ data: Data) throws -> [MKGeoJSONObject]`. При этом он возвращает массив объектов соответсвующих протоколу `MKGeoJSONObject`. Этот протокол реализует класс `MKGeoJSONFeature`.

Перейдём в `Landmark` и напишем ещё один инициализатор. Нам нужна заготовка под декодированные данные.

```swift
init? (feature: MKGeoJSONFeature) {

    guard let geoPoint = feature.geometry.first as? MKPointAnnotation,
        let properties = feature.properties,
        let json = try? JSONSerialization.jsonObject(with: properties),
        let props = json as? [String: Any] 
    else { return nil }

    coordinate = geoPoint.coordinate
    title = props["title"] as? String
    subtitle = props["subtitle"] as? String

    super.init()
}
```

Вернёмся в `UIViewController`. Создадим свойство под массив декодированных объектов.

```swift
    var landmarks: [Landmark] = []
```

Добавим метод `getData()`, где и будем декодировать `data.geojson`. Полученные объекты будем сразу добавлять в массив `landmarks`.

```swift
func getData() {
    guard let file = Bundle.main.url(forResource: "data", withExtension: "geojson"),
        let data = try? Data(contentsOf: file) 
    else { return }

    do {
        let features = try MKGeoJSONDecoder()
            .decode(data)
            .compactMap { $0 as? MKGeoJSONFeature }
        let mapedData = features.compactMap(Landmark.init)
        landmarks.append(contentsOf: mapedData)
    } catch {
        print("Error MKGeoJSONDecoder")
    }
}
```

Теперь необходимо вызвать метод `getData()` и добавить массив с данными на карту. Постоянная `landmark` более не нужна, её можно удалить.

```swift
override func viewDidLoad() {

    // ...
    
    getData()
    mapView.addAnnotations(landmarks)
}
```

![Отображение геоданных](https://cdn.sparrowcode.io/tutorials/mapkit/geodata.png)

Чтобы увидеть вторую геометку потребуется немного передвинуть карту. Для удобства изменим параметр `eyeAltitude` камеры на `1000`, так обе геометки будут видны на экране.

```swift
extension UIViewController {    
    var camera: MKMapCamera {
        MKMapCamera(lookingAtCenter: location, fromEyeCoordinate: location2, eyeAltitude: 1000)
    }
}
```

## MKOverlay

Помимо геоточек часто возникает потребность в отображении другого рода данных. При работе с `GeoJSON` мы узнали, что также есть геометрии линий и полигонов. Получать данные мы уже научились, уделим внимание именно отображению.

Мы воспользуемся `MapKit Overlays` - специальными наложениями для выделения географических данных. Нам потребуется класс нужного оверлея (`MKCircle`, `MKPolyline`, `MKPolygon`), его отрисовщика (`MKCircleRenderer`, `MKPolylineRenderer`, `MKPolygonRenderer`) и делегат `mapView`.

### MKCircle

`MKCircle` - оверлей в форме круга с изменяемым радиусом в метрах, центром которого является переданная географическая пара координат. Удобен как для отображения геоточек, так и для конкретных областей, зон покрытий и т.д.

Сперва укажем классу `ViewController` соответствие протоколу делегата `MKMapViewDelegate`. Это позволит нам использовать некоторые опциональные методы `MapKit`.

```swift
class ViewController: UIViewController, MKMapViewDelegate { // ... }
```

Для удобства восприятия отключим отрисовку геомаркеров, сосредоточимся на оверлеях.

```swift
override func viewDidLoad() {
    
    // ...
    
    // mapView.addAnnotations(landmarks)
}
```

Создадим вычисляемое свойство типа `MKCircle`. Это будет круг с центром `location` и радиусом в `10` метров.

```swift
extension UIViewController {
    var circle: MKCircle {
        MKCircle(center: location, radius: 10)
    }
}
```

Во `viewDidLoad()` укажем, что делегатом для `mapView` выступает `UIViewController`. Добавим `circle` при помощи метода `addOverlay(_ overlay: MKOverlay)` на карту.

```swift
override func viewDidLoad() {

    super.viewDidLoad()
    
    mapView.delegate = self
    
    // ...
    
    mapView.addOverlay(circle)
}
```

Теперь нужен обработчик, который будет отрисовывать объекты типа `MKOverlay`. Соответствие протоколу делегата `MKMapViewDelegate` позволяет нам использовать метод `mapView(_ mapView: MKMapView, rendererFor overlay: MKOverlay) -> MKOverlayRenderer`. Добавим его в `UIViewController`. В теле метода будем проверять есть ли наложения типа `MKCircle`. Если есть, то создаём экземпляр визуального представления, можно называть его отрисовщиком, которому указываем параметры отрисовки. Т.е. при создании объекта `MKOverlay` мы указываем только необходимые параметры геометрии (количество точек и их координаты), а `MKOverlayRenderer` отвечает за визуальные параметры (цвет, толщина линий и т.д.).

Можно возвращать ошибку, например, `fatalError("Наложений нет")`, в случае отсутствия соответсвующих оверлеев, но мы будем возвращать объект `MKOverlayRenderer`. Зададим нашему кругу только `strokeColor`, так его центр не будет залит.

```swift
    func mapView(_ mapView: MKMapView, rendererFor overlay: MKOverlay) -> MKOverlayRenderer {
        if let circle = overlay as? MKCircle {
            let renderer = MKCircleRenderer(circle: circle)
            renderer.strokeColor = .red
            
            return renderer
        }
        
        return  `MKOverlayRenderer`.(overlay: overlay)
    }
```

Запускаем и видим, что круг отображается под зданиями. Чтобы было более отчётливо, давайте изменим параметры круга добавив заливку, прозрачность, толщину обводки и сменим цвет.

![MKCircle Red](https://cdn.sparrowcode.io/tutorials/mapkit/circle-red.png)

```swift
func mapView(_ mapView: MKMapView, rendererFor overlay: MKOverlay) -> MKOverlayRenderer {
    if let circle = overlay as? MKCircle {
        let renderer = MKCircleRenderer(circle: circle)
        renderer.fillColor = .blue.withAlphaComponent(0.3)
        renderer.strokeColor = .blue
        renderer.lineWidth = 1
        
        return renderer
    }
    
    return MKOverlayRenderer(overlay: overlay)
}
```

Теперь любой объект типа `MKCircle` будет отображаться с такими визуальными параметрами. Также для самого `circle` изменим радиус на `1000`. **CHECK RADIUS**

```swift
extension UIViewController {
    var circle: MKCircle {
        MKCircle(center: location, radius: 1000)
    }
}
```

![MKCircle Blue Below Buildings](https://cdn.sparrowcode.io/tutorials/mapkit/circle-blue-below.png)

Теперь нам более отчётливо видно, что `circle` отображается под слоем `buildings`. Такого быть не должно. В документации на этот счёт сказано, что такое происходит лишь с `3D-buildings`. Но у нас `2D`-карта. В данном случае на это влияет наша камера `MKMapCamera`. Закомментируем эту строчку, вернув настройки обзора к стандартным.

```swift
override func viewDidLoad() {
    
    // ...
    
    // mapView.setCamera(camera, animated: true)
}
```

Теперь `circle` отображается как задумано. Такое отображение удобно для указания на области, распределение, зоны покрытия и досягаемости, и т.д.

![MKCircle Blue](https://cdn.sparrowcode.io/tutorials/mapkit/circle-blue.png)

Мы можем одновременно отображать все наши данные. Именно совокупность данных даёт наиболее информативную картину.

![MKCircle Blue & GeoMarkers](https://cdn.sparrowcode.io/tutorials/mapkit/circle-blue-marker.png)

### MKPolyline

Теперь отрисуем линию. Как мы знаем, линии состоят из совокупности точек. Нам достаточно двух. Изменим координаты `location2`, чтоб расстояние между `location` и `location2` было заметным. Можем взять координаты второго геомаркера. Также добавим свойство `polyline` типа `MKPolyline`. При инициализации `MKPolyline` принимает на вход массив координат геоточек и количество этих точек.

```swift
extension UIViewController {
    
    // ...
    
    var location2: CLLocationCoordinate2D {
        CLLocationCoordinate2D(latitude: 54.9500234 , longitude: 39.0210369)
    }
    
    var polyline: MKPolyline {
        MKPolyline(coordinates: [location, location2], count: 2)
    }
```

Обновим `mapView(_ mapView: MKMapView, rendererFor overlay: MKOverlay) -> MKOverlayRenderer`, добавив проверку на `MKPolyline` и задав всем таким линиям ширину `5` и зелёный цвет.

```swift
func mapView(_ mapView: MKMapView, rendererFor overlay: MKOverlay) -> MKOverlayRenderer {
    
    // ...
    
    if let polyline = overlay as? MKPolyline {
        let renderer = MKPolylineRenderer(polyline: polyline)
        renderer.strokeColor = .green
        renderer.lineWidth = 5
        
        return renderer
    }
    
    return MKOverlayRenderer(overlay: overlay)
}
```

Добавляем оверлей линии на карту.

```swift
override func viewDidLoad() {
    
    // ...
    
    mapView.addOverlay(polyline)
}
```

![MKPolyline](https://cdn.sparrowcode.io/tutorials/mapkit/circle-line.png)

Если мы включим отображение маркеров, то можно сказать, что мы нарисовали отображение кратчайшего расстояния между объектами. Но в случае отрисовки на карте маршрутов и дистанций важно учитывать форму Земли, и не всегда расстояние между двумя объектами на `2D`-карте будет выглядеть как прямая.

![MKPolyline & GeoMarkers](https://cdn.sparrowcode.io/tutorials/mapkit/circle-line-markers.png)

### MKPolygon

Для полигона - многоугольника, нужны минимум три точки. Когда мы разбирали структуру `GeoJSON`, то указывали первую и последнюю точку одинаковыми. Так принято по стандарту, это указывает на закрытый полигон. В `MapKit` же при создании объекта типа `MKPolygon` достаточно указать вершины без повтора, чтобы получился замкнутый многоугольник.

Зададим координаты третьей геоточки и создадим полигон, как делали это с линией.

```swift
extension UIViewController {
    
    // ...
    
    var location3: CLLocationCoordinate2D {
        CLLocationCoordinate2D(latitude: 54.9484931, longitude: 39.0170369)
    }
    
    var polygon: MKPolygon {
        MKPolygon(coordinates: [location, location2, location3], count: 3)
    }
```

Укажем параметры отрисовки полигонов. Пусть будут оранжевые с прозрачной заливкой и толщиной обводки `1`.

```swift
func mapView(_ mapView: MKMapView, rendererFor overlay: MKOverlay) -> MKOverlayRenderer {
    
    // ...
    
    if let polygon = overlay as? MKPolygon {
        let renderer = MKPolygonRenderer(polygon: polygon)
        renderer.fillColor = .orange.withAlphaComponent(0.3)
        renderer.strokeColor = .orange
        renderer.lineWidth = 1
        
        return renderer
    }
    
    return MKOverlayRenderer(overlay: overlay)
}
```

Добавляем наш полигон на карту.

```swift
override func viewDidLoad() {
    
    // ...
    
    mapView.addOverlay(polygon)
}
```

![MKPolygon](https://cdn.sparrowcode.io/tutorials/mapkit/circle-line-triangle.png)

## Маршрут

Одна из наиболее востребованных функуций любого карточного сервиса - построение маршрута. Нам не придётся рассчитывать маршрут самостоятельно, это делает сервис `Apple`, мы лишь отправляем запрос и получаем в ответ возможные варианты маршрута. Нам потребуется класс `MKDirections` и связанные с ним. Он вычисляет направления и информацию о времени в пути на основе предоставленной информации (геоточки, способ перемещения и т.д.).

Вернём отображение геомаркеров. Будем строить маршрут от `location` до `location2`. Также скроем отображение оверлеев. Наш маршрут также строится на основе оверлея `MKPolyline`, поэтому он отобразится с теми же параметрами, что и линия.

```swift
override func viewDidLoad() {

    // ...
    
    mapView.addAnnotations(landmarks)

    // mapView.addOverlay(circle)
    // mapView.addOverlay(polyline)
    // mapView.addOverlay(polygon)
}
```

Напишем метод `createPath(sourceCLL: CLLocationCoordinate2D, destinationCLL: CLLocationCoordinate2D)`. 

- `sourceCLL` - координаты геоточки, начальная точка маршрута
- `destinationCLL` - координаты геоточки, конечная точка маршрута

Нам потребуется экземпляр `MKDirections.Request()`. С его помощью мы будем делать запрос на сервер `Apple` о маршрутах. В ответ придёт массив маршрутов или ошибка. 

Прежде чем сделать запрос нужно указать значения для свойств `source`, `destination` и `transportType`. `transportType` отвечает за тип передвижения по маршруту и принимает значения типа `MKDirectionsTransportType`. Можно передать одно из четырёх значений:

- `automobile` - на автомобиле
- `walking` - пешком
- `transit` - общественным транспортом
- `any` - для любого транспорта

При добавлении оверлея на карту укажем отображение поверх дорог.

```swift
func createPath(sourceCLL: CLLocationCoordinate2D, destinationCLL: CLLocationCoordinate2D) {
    let source = MKPlacemark(coordinate: sourceCLL, addressDictionary: nil)
    let destination = MKPlacemark(coordinate: destinationCLL, addressDictionary: nil)

    let directionRequest = MKDirections.Request()
    directionRequest.source = MKMapItem(placemark: source)
    directionRequest.destination = MKMapItem(placemark: destination)
    directionRequest.transportType = .automobile
    
    let direction = MKDirections(request: directionRequest)

    direction.calculate { (response, error) in
        guard let response = response else {
            if let err = error {
                print("Error: \(err.localizedDescription)")
            }
            return
        }
        
        let route = response.routes[0]
        self.mapView.addOverlay(route.polyline, level: MKOverlayLevel.aboveRoads)
    }
}
```

Вызываем метод `createPath(sourceCLL: CLLocationCoordinate2D, destinationCLL: CLLocationCoordinate2D)`.

```swift
override func viewDidLoad() {
    
    // ...
    
    createPath(sourceCLL: location, destinationCLL: location2)
}
```

![Route Automobile](https://cdn.sparrowcode.io/tutorials/mapkit/route-automobile.png)

Изменим тип передвижения по маршруту.

```swift
func createPath(sourceCLL: CLLocationCoordinate2D, destinationCLL: CLLocationCoordinate2D) {
    
    // ...
    
    directionRequest.transportType = .walking
}
```

![Route Walking](https://cdn.sparrowcode.io/tutorials/mapkit/route-walking.png)

## Поиск
