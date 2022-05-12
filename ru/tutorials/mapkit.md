Напишем приложение с использованием фреймворка MapKit. Научимся добавлять карту, геомаркеры,  описание и оверлеи. Познакомимся с основными понятиями для работы с карточными API.

## API
Для создания приложения с картой нам потребуется встроенное или стороннее `API` для структурного взаимодействия с фреймворком или библиотекой.

Apple сделала собственный фреймворк для работы с картами - `MapKit`. Помимо него можно использовать `Google Maps`, `Open Street Maps` и другие фреймворки с `API` для `Swift`.

Посмотрим [официальную документацию](https://developer.apple.com/documentation/mapkit/) `MapKit`. Эти наборы структур, классов и протоколов являются `API` для работы с фреймворком. 

Для начала работы достаточно импортировать `MapKit` в свой проект:

```swift
import MapKit
```

В отличие от `Google Maps` у `Open Street Maps` нет единого фреймворка, но есть набор `iOS`-[библиотек](https://wiki.openstreetmap.org/wiki/Apple_iOS#Libraries_for_developers) с картами `OSM`.

Можно использовать `MapKit`, а в качестве сервера с картами выбрать `Google Maps`, `OSM` или другой. Всё зависит от ваших нужд, детальности карт, частоты их обновления, качества и веса.

## Подключение

### Map View

Карта добавляется в проект аналогично любой другой `View`. Для `UIKit` предусмотрен класс `MKMapView`, а для `SwiftUI` - структура `Map`. В этом туториале мы будем работать с `UIKit`.

Создадим проект с названием `MapKitTutorial`.

Структура проекта должна выглядеть так:

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

Переходим в файл `ViewController` и импортируем `MapKit`. В теле класса создаём постоянную `mapView` типа `MKMapView`. В качестве значения укажем ей сомовызывающуюся функцию, возвращающую экземпляр `MKMapView`.

```swift
import UIKit
import MapKit

class ViewController: UIViewController {

    let mapView: MKMapView = {
        let map = MKMapView()
        map.translatesAutoresizingMaskIntoConstraints = false // Возможность выставлять `anchors` для `mapView`
        
        return map
    }()
}
```

Создадим и перейдём в новый файл с названием `Helper`. В нём будут вспомогательные объекты, что бы не засорять `ViewController`.

Создадим структуру `AnchorsSetter` со `static` методом `setAllSides(for view: UIView)`, который выставит `view` в размер его `superview` с учётом верхней `safeArea`.

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

Переключаемся на `ViewController`. Во `viewDidLoad()` добавляем `mapView` на основную `view` и позиционируем её.

```swift
override func viewDidLoad() {
    super.viewDidLoad()
    
    view.addSubview(mapView)
    AnchorsSetter.setAllSides(for: mapView)
}
```

Запускаем симулятор и видим нашу карту.

![Базовая карта.](https://cdn.sparrowcode.io/tutorials/mapkit/simple-mapview.jpg)

### Типы карт

Карты можно разделить на 3 типа отображения:

- **Спутник** - карта составлена из совокупности снимков со спутника.
- **Схема** - карта составлена схематическим образом.
- **Гибрид** - объекты схематически нанесены на совокупность спутниковых снимков, иными словами - одновременное отображение *cпутника* и *cхемы*.

Обычно пользователям не требуется спутниковая карта без отображения на ней дорог, объектов, границ и названий. Для них разработчики делят карты на два типа: схему и спутник, называя спутником именно гибридную карту. Вы могли видеть эти типы в навигаторах.

![Типы карт.](https://cdn.sparrowcode.io/tutorials/mapkit/map-types.jpg)

В нашем приложении мы видим именно схематическую карту.

За изменение типа отображаемой карты отвечает свойство `mapType`, принимающее значения типа `MKMapType` - перечисление, содержащее следующие кейсы:

- `standard` - карта улиц, показывающая расположение всех дорог и названия некоторых дорог.
- `satellite` - спутниковые снимки местности.
- `hybrid` - спутниковые снимки местности с информацией о дорогах и названиями, расположенной поверх снимков.
- `satelliteFlyover` - спутниковый снимок местности с данными облёта, если они имеются.
- `hybridFlyover` - гибридный спутниковый снимок с данными облёта, если они имеются.
- `mutedStandard` - карта улиц, на которой данные выделены поверх основных деталей карты.

Изменим тип нашей карты и посмотрим разницу.

```swift
mapView.mapType = .satellite
```

![Отображение `.satellite`.](https://cdn.sparrowcode.io/tutorials/mapkit/mapview-satellite.png)

```swift
mapView.mapType = .hybrid
```

![Отображение `.hybrid `.](https://cdn.sparrowcode.io/tutorials/mapkit/mapview-hybrid.png)

Карты делятся на категории в зависимости от применения. В нашем приложении используем электронную. Каждая категория может представлять отдельный слой на такой карте, их можно отображать совместно или по отдельности. 

Карта представляет собой изображение, сформированное на основе набора геоданных, которые предоставляют разработчики геоинформационных систем (ГИС).

### Проекции

Привычные нам карты - плоские, но мы знаем, что Земля имеет форму геоида. Когда мы смотрим на глобус, то видим все объекты в правильных пропорциях. На картах же мы видим проекцию геоида на плоскость. Таких проекций очень много, а в привычной нам - материки выглядят иначе, чем есть на самом деле.

Посмотрим на схематичное и спутниковое изображение Земли.

![Сравнение изображений Земли.](https://cdn.sparrowcode.io/tutorials/mapkit/globe-types.jpg)

`Apple Maps`, `Google Maps` и `OSM` предоставляют свои карты в проекции Меркатора. Мы будем работать с ней.

Посмотрим на соотношения между площадью каждой страны в проекции Меркатора (полупрозрачные цвета) и истинной площадью (яркие цвета):

![Соотношение площадей по Меркатору.](https://cdn.sparrowcode.io/tutorials/mapkit/mer-dif.jpg)

Такая проекция не сохраняет площади, поскольку имеет разный масштаб на разных участках. Больше всего разница в масштабе у тех объектов, что расположены ближе к полюсам (дальше от экватора), потому что там геоид сужается.

В `MapKit` это учитывается при различных расчётах.

### Подложки

Базовые карты или карты-основы, использующиеся в качестве информационного фона.

Рассмотрим на примере [`Google Earth`](https://earth.google.com/web/). 

Первое, что можно отметить - время загрузки. Обычно, когда вы открываете карты, то подгружается только её часть, затем участки в этой области, пока она полностью не будет загружена. Подгрузка в `Google Earth` же происходит так, что глаз не успевает заметить разделения на «тайлы» - плиточные изображения, которые в совокупности создают впечатление единой картинки.

Мы видим глобус, по сути - планету Земля.

![Земля в Google Earth.](https://cdn.sparrowcode.io/tutorials/mapkit/g-earth.jpg)

С точки зрения разработки это математически посчитанная фигура - геоид, с координатной разметкой, на которую натянули картинку. Это картинка - подложка. При увеличении объекты будут отображаться поверх неё. Подложка может представлять собой как 2D, так и 3D-изображение. В отличие от 2D, 3D-изображение помимо широт и долгот хранит информацию о высоте в каждой точке. Такая подложка называется `terrain`. Информация о высотах также может идти совместно с 2D-изображением формата `GeoTiff`, но по отображению будет отличаться от `terrain`.

Посмотрим разницу в отображении 2D и 3D с измерением расстояния.

**Измерение 2D**

![2D Земля в Google Earth с измерением расстояния.](https://cdn.sparrowcode.io/tutorials/mapkit/g-earth-measure-2d.jpg)

**Измерение 3D**

![3D Земля в Google Earth с измерением расстояния.](https://cdn.sparrowcode.io/tutorials/mapkit/g-earth-measure-3d.jpg)

> При разных отображениях мы получаем одинаковое расстояние измерений. Это происходит из-за учёта высоты в обоих случаях.

### Уровни

Для удобства масштабирования и скорости просмотра используют специальный механизм - карта представляется в виде пирамиды тайлов.

![Пирамида тайлов.](https://cdn.sparrowcode.io/tutorials/mapkit/pyramid-tiles.jpg)

Самая большая область помещается в самое маленькое изображение - один тайл. Каждое последующее увеличение области представляет собой новый уровень, в котором она разделяется на большее число тайлов и т.д. Тайлы имеют одинаковый размер. Уровни также могут называться `zoom`, `level` и `zoom level`.

Эти уровни совпадают не во всех API. Так 10-й уровень одной ГИС может соответствовать 12-му уровню другой.

![Zoom Levels](https://cdn.sparrowcode.io/tutorials/mapkit/zoom-levels.jpg)

Упорядоченная совокупность тайлов представляет собой матрицу, в которой у каждого есть своё название по позиции в ней и координатные границы. При поиске области по координатам, алгоритм ищет тайл, в который попадает эта область, обращается к нему по матричной разметке и подгружает. 

Давайте посмотрим, как это выглядит в динамике.

[Прогрузка тайлов при зуме.](https://cdn.sparrowcode.io/tutorials/mapkit/tiles-loading.mp4)

### Вес

Совокупность тайлов даёт нам изображение высокого качества. Чем больше область, которую необходимо исследовать - тем больше тайлов и уровней требуется, соответственно возрастает и вес карты. На него влияет и сопутствующая информация, он может достигать нескольких десятков, а порой и сотен гигабайт - поэтому подгрузка по областям очень удобна.

Есть несколько способов загрузки, хранения и очищения кэша геоданных. 

Первый подойдет, когда важна скорость отображения, а размер оперативной памяти небольшой. Уровень загружается и сохраняется в кеш. При зуме подгружается следующий уровень, а предыдущий очищается из кеша. Так, при зуме одной и той же области в плюс и минус каждый раз будет происходить загрузка уровня и очистка предыдущего. Используется в мобильных приложениях.

Другой способ подразумевает сохранение в кеше загруженных уровней, но требует большого объёма оперативной памяти, потому применяется в основном на ПК-платформах в специальных ГИС.

Можно скачивать карты определённого района на устройство, что бы не загружать уровни каждый раз и иметь возможность трекинга даже при слабом интернете. Такой режим называют "оффлайн картами".

## Метки

Изображение местности бесполезно обычному пользователю без дополнительных опознавательных знаков. Это могут быть подписи, метки, цветовые и схематические выделения объектов, областей, геопозиции, маршрута и т.д. Для нанесения подобных обозначений и поиска на местности используют системы координат. Чаще всего используют градусы или прямоугольные координаты.

Основные системы координат в API:
- Градусы (геодезические координаты `WGS84` (`EPSG:4326`)).
- Прямоугольные (метры, сферическая проекция Меркатора (`EPSG:3857`)).
- Пиксели (`XY` координаты пикселей экрана в уровне (`zoom`)).
- Координаты тайлов (Tile Map Service (`ZXY`)).

`MapKit` использует градусы `WGS84`.

### Location

Локацией принято считать определение местоположения. Также можно встретить определение локации, как географической области. Мы будем использовать `location` для того, чтобы указать местонахождение объекта и обозначить координаты отображаемой области.

Сейчас в нашем приложении отображается местоположение устройства, при этом отображается один из начальных уровней. Мы хотим, что бы при открытии загружалась определённая область.

В `MapKit` есть структура: 

```swift
struct CLLocationCoordinate2D {

    var latitude: CLLocationDegrees // широта в градусах (WGS84)
    var longitude: CLLocationDegrees // долгота в градусах (WGS84)
}
```  

Используем её для создания объекта на основе координат широты и долготы. Воспользуемся поиском через `Google Maps`. Введём в запрос что-нибудь необычное, например, "Памятник почтальону Печкину". Жмём на предложенную достопримечательность. 

![Поиск локации в Google Maps.](https://cdn.sparrowcode.io/tutorials/mapkit/g-location-search.jpg)

То, что нужно. 

![Отображение найденной локации в Google Maps.](https://cdn.sparrowcode.io/tutorials/mapkit/g-location-view.jpg)

Теперь обратим внимание на `url`-адрес:

```
https://www.google.ru/maps/place/.../@54.9502529,39.0187517,17z/data=...
```

Нас интересует:

- `54.9502529` - широта.
- `39.0187517` - долгота.
- `17z` - `zoom = 17`.

Благодаря пометке `17z` мы видим отображение карты в более информативном и удобном для восприятия виде. Вернём `mapType` обратно в схематичный вид и добавим `location`. 

```swift
override func viewDidLoad() {
    super.viewDidLoad()
    
    view.addSubview(mapView)
    AnchorsSetter.setAllSides(for: mapView)
    
    mapView.mapType = .standard
    let location = CLLocationCoordinate2D(latitude: 54.9502529 , longitude: 39.0187517)
}
```

Для отображения заданного региона используем метод `setRegion(_ region: MKCoordinateRegion, animated: Bool)`. Он переместит отображение в указанную локацию при помощи встроенной анимации масштабирования.

Нам потребуется создать объект типа `MKCoordinateRegion`, который представляет собой прямоугольный географический регион с центром вокруг указанной широты и долготы.

`location` будет являться центральной точкой нашей карты. `regionRadius` отвечает за размер дистанции с севера на юг и с востока на запад.

```swift
mapView.mapType = .standard
let location = CLLocationCoordinate2D(latitude: 54.9502529 , longitude: 39.0187517)
let regionRadius: CLLocationDistance = 1000
let coordinateRegion = MKCoordinateRegion(center: location, latitudinalMeters: regionRadius, longitudinalMeters: regionRadius)
mapView.setRegion(coordinateRegion, animated: true)
```

Запустим и посмотрим, что получилось.

![](https://cdn.sparrowcode.io/tutorials/mapkit/zoom-to-location.jpg)

Изменим `regionRadius`, что бы немного увеличить отображение.

```swift
let regionRadius: CLLocationDistance = 500
```

![Отображение локации c радиусом 500.](https://cdn.sparrowcode.io/tutorials/mapkit/zoom-to-location-500.jpg)

> Для зумирования в симуляторе удерживайте клавишу `option`, и зажав левую кнопку мыши, перемещайте курсор. 

Вынесем наши константы в `extension`, что бы очистить `viewDidLoad()`. Для этого сделаем их вычисляемыми свойствами.

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

Отметим на карте, где находится интересующий нас объект. Это точка, но в картографии она называется *геоточкой*. С опознавательными знаками, подписями или иной уточняющей информацией её называют *геомаркером*.

Геомаркеры должны соответствовать протоколу `MKAnnotation`. Такой объект является интерфейсом для связывания данных с определенным местоположением на карте.

Воспользуемся `MapKit Overlays` для выделения географических регионов или путей. Добавим в `extension UIViewController` экземпляр класса `MKPlacemark`, который отвечает за описание местоположения.

```swift
var geoPoint: MKPlacemark {
    MKPlacemark(coordinate: location)
}
```

Объекты `MKPlacemark` соответствуют протоколу `MKAnnotation`, поэтому мы можем добавить их при помощи метода `addAnnotation(_ annotation: MKAnnotation)`.

```swift
mapView.addAnnotation(geoPoint)
```

Запускаем симулятор.

![GeoPoint](https://cdn.sparrowcode.io/tutorials/mapkit/geo-point.jpg)

Минутка юмора от Apple. У нас появился геомаркер с дефолтным описанием, так как сами мы его не указывали. В предыдущих версиях `MapKit` это добавляло геомаркер без подписей.

Добавим описание, но теперь воспользуемся другим, наиболее оптимальным способом для добавления геомаркера. Вместо `geoPoint` создадим экземпляр `MKPointAnnotation`, в описание которого добавим данные о координатах, заголовке и подзаголовке.

```swift
var annotation: MKPointAnnotation {
    let ann = MKPointAnnotation()
    ann.coordinate = location
    ann.title = "Памятник почтальону Печкину"
    ann.subtitle = "Достопримечательность"
    
    return ann
}
```

В `mapView.addAnnotation` заменяем `geoPoint` на `annotation`.

```swift
mapView.addAnnotation(annotation)
```

![Геомаркер с коротким описанием.](https://cdn.sparrowcode.io/tutorials/mapkit/geo-point-annotation.jpg)

Нажмём на геомаркер.

![Геомаркер с полным описанием.](https://cdn.sparrowcode.io/tutorials/mapkit/geo-point-annotation-full.jpg)

Для удобства рассмотрим ещё один способ, завязанный на протоколе `MKAnnotation`, который удобно использовать при отображении множества данных.

Создадим новый файл `Landmark` с соответствующим классом. Он должен соответствовать протоколу `MKAnnotation`, а значит должен наследоваться от `NSObject`, потому что `MKAnnotation` является `NSObjectProtocol`.

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

`title` и `subtitle` делаем `String?`, потому что координата у геоточки есть всегда, а заголовка и подзаголовка может не быть, как мы не добавляли его в `geoPoint`.

Экземпляр `Landmark` заменит `annotation`. Возвращаемся к `UIViewController`. Мы не можем создать экземпляр и передать в него `location` в расширении до инициализации класса, поэтому сделаем это во `viewDidLoad()`.

```swift
let landmark = Landmark(coordinate: location, title: "Памятник почтальону Печкину", subtitle: "Достопримечательность")
mapView.addAnnotation(landmark)
```

Запустите симулятор. Вы увидите, что разницы в отображении между `annotation` и `landmark` нет.

## Камера

`MapKit` может задать ограничения панорамирования и масштабирования карты в указанной области. Это полезно, когда необходимо сосредоточить пользователя на конкретном месте.

### Boundary

Воспользуемся методом `setCameraBoundary(_ cameraBoundary: MKMapView.CameraBoundary?, animated: Bool)`, что бы установить границу камеры для представления карты с возможностью использования встроенной анимации. Параметр типа `CameraBoundary` отвечает за границу области, в пределах которой должен оставаться центр карты.

```swift
mapView.setCameraBoundary(MKMapView.CameraBoundary(coordinateRegion: coordinateRegion), animated: true)
```

Запустите симулятор и попробуйте передвигаться по карте. Вы увидите, что она не прогружается дальше небольшой области.

### ZoomRange

С помощью метода `setCameraZoomRange(_ cameraZoomRange: MKMapView.CameraZoomRange?, animated: Bool)` установим диапазон масштабирования камеры для просмотра карты. 

В `extension UIViewController` добавим вычисляемое свойство `zoomRange`.

```swift
var zoomRange: MKMapView.CameraZoomRange? {
    MKMapView.CameraZoomRange(maxCenterCoordinateDistance: 1000)
}
```

`maxCenterCoordinateDistance` - максимальное расстояние от центральной координаты представления карты, измеряемое в метрах.

```swift
mapView.setCameraZoomRange(zoomRange, animated: true)
```

Запускаем и видим, что теперь нельзя отдалить карту дальше, чем мы указали.

Можно также задать ограничение на приближение с помощью `MKMapView.CameraZoomRange(minCenterCoordinateDistance: CLLocationDistance)`.

### MKMapCamera

Виртуальная камера, с помощью которой задаётся точка и угол обзора, направление компаса, шаг относительно перпендикуляра карты и высота над ней.

Воспользуемся инициализатором `MKMapCamera(lookingAtCenter centerCoordinate: CLLocationCoordinate2D, fromEyeCoordinate eyeCoordinate: CLLocationCoordinate2D, eyeAltitude: CLLocationDistance)`, который вернёт новый объект камеры, используя указанную информацию об угле обзора.

`centerCoordinate` - геоточка, по которой центрируется карта.

`eyeCoordinate` - геоточка, в которой размещается камера. Если `centerCoordinate` равен `eyeCoordinate`, то карта отображается так, будто камера смотрит вниз; если их значения разные, то карта отображается с соответствующим углом наклона и направлением.

`eyeAltitude` - высота над землей в метрах, на которой нужно разместить камеру.

Зададим новую геоточку `location2`, немного изменив координаты имеющейся (`location`). По `location` будем центрировать карту, а из `location2` направим камеру. Саму камеру разместим на высоте 500 метров.

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

Используем метод `setCamera(_ camera: MKMapCamera, animated: Bool)` для установки камеры.

```swift
mapView.setCamera(camera, animated: true)
```

![Пример отображения.](https://cdn.sparrowcode.io/tutorials/mapkit/map-camera.jpg)

Мы видим, что карта по-прежнему центрируется в заданной нами точке, но изменился угол поворота и появился компас.

## Данные

В нашем примере мы отображаем пользователю только один объект. На деле их очень много, например, магазины или рестораны. Геоинформационные данные обычно загружаются с сервера и хранятся в специальном формате.

Запишем и отобразим свои данные.

### GeoJSON

`JSON` - текстовый формат для обмена данными. Он хранит набор пар `ключ-значение` или упорядоченный набор значений. Использование единого формата позволяет унифицировать протоколы взаимодействия с данными.

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
- `LineString`
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

> Если вы не знаете, как создать файл с нужным расширением в проекте, то создайте его вне проекта и добавьте туда.

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

Получение данных из `JSON` называют "декодированием" или "парсингом". Мы воспользуемся объектом класса `MKGeoJSONDecoder`, который декодирует объекты `GeoJSON` в типы `MapKit` при помощи метода `decode(_ data: Data) throws -> [MKGeoJSONObject]`. Он возвращает массив объектов, соответствующих протоколу `MKGeoJSONObject`, который реализует класс `MKGeoJSONFeature`.

Перейдём в `Landmark` и напишем ещё один инициализатор, сделаем заготовку под декодированные данные.

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

Теперь необходимо вызвать метод `getData()` и добавить массив с данными на карту. Постоянная `landmark` больше не нужна, её можно удалить. Метод `addAnnotation()` заменяем на `addAnnotations()`.

```swift
override func viewDidLoad() {
    super.viewDidLoad()
    
    view.addSubview(mapView)
    AnchorsSetter.setAllSides(for: mapView)
    mapView.mapType = .standard
    mapView.setRegion(coordinateRegion, animated: true)
    mapView.setCameraBoundary(MKMapView.CameraBoundary(coordinateRegion: coordinateRegion), animated: true)
    mapView.setCameraZoomRange(zoomRange, animated: true)
    mapView.setCamera(camera, animated: true)
    
    getData()
    mapView.addAnnotations(landmarks)
}
```

![Отображение геоданных.](https://cdn.sparrowcode.io/tutorials/mapkit/geodata.jpg)

Чтобы увидеть вторую геометку потребуется немного передвинуть карту. Для удобства изменим параметр `eyeAltitude` камеры на `1000`, так будут видны обе геометки.

```swift
var camera: MKMapCamera {
    MKMapCamera(lookingAtCenter: location, fromEyeCoordinate: location2, eyeAltitude: 1000)
}
```

## MKOverlay

Используется для отображения данных, например геометрии линий и полигонов.

Воспользуемся `MapKit Overlays` - специальными наложениями для выделения географических данных. Нам потребуется класс нужного оверлея (`MKCircle`, `MKPolyline`, `MKPolygon`), его отрисовщика (`MKCircleRenderer`, `MKPolylineRenderer`, `MKPolygonRenderer`) и делегат `mapView`.

### MKCircle

Оверлей в форме круга с изменяемым радиусом в метрах, центром которого является переданная географическая пара координат. Удобен как для отображения геоточек, так и для конкретных областей, зон покрытий и т.д.

Сперва укажем классу `UIViewController` соответствие протоколу делегата `MKMapViewDelegate`. Это позволит нам использовать опциональные методы `MapKit`.

```swift
class ViewController: UIViewController, MKMapViewDelegate { // ... }
```

Для удобства восприятия отключим отрисовку геомаркеров и сосредоточимся на оверлеях.

```swift
// mapView.addAnnotations(landmarks)
```

Создадим вычисляемое свойство типа `MKCircle`. Это будет круг с центром `location` и радиусом в 10 метров.

```swift
var circle: MKCircle {
    MKCircle(center: location, radius: 10)
}
```

Во `viewDidLoad()` укажем, что делегатом для `mapView` выступает `UIViewController`. При помощи метода `addOverlay(_ overlay: MKOverlay)` добавим `circle` на карту.

```swift
mapView.delegate = self
mapView.addOverlay(circle)
```

Теперь нужен обработчик, который будет отрисовывать объекты типа `MKOverlay`. 

Соответствие протоколу делегата `MKMapViewDelegate` позволяет нам использовать метод `mapView(_ mapView: MKMapView, rendererFor overlay: MKOverlay) -> MKOverlayRenderer`. Добавим его в `UIViewController`. В теле метода будем проверять есть ли наложения типа `MKCircle`. Если есть, то создаём экземпляр визуального представления, можно называть его отрисовщиком, которому указываем параметры отрисовки.

То есть при создании объекта `MKOverlay` мы указываем только необходимые параметры геометрии (количество точек и их координаты), а `MKOverlayRenderer` отвечает за визуальные параметры (цвет, толщина линий и т.д.).

Можно возвращать ошибку, например `fatalError("Наложений нет")` в случае отсутствия соответствующих оверлеев, но мы будем возвращать объект `MKOverlayRenderer`. Зададим нашему кругу только `strokeColor`, так его центр не будет залит.

```swift
func mapView(_ mapView: MKMapView, rendererFor overlay: MKOverlay) -> MKOverlayRenderer {
    if let circle = overlay as? MKCircle {
        let renderer = MKCircleRenderer(circle: circle)
        renderer.strokeColor = .red
            
        return renderer
    }
        
    return  MKOverlayRenderer(overlay: overlay)
}
```

Запускаем и видим, что круг отображается под зданиями. Изменим параметры круга, добавив заливку, прозрачность, толщину обводки, и сменим цвет. Так будет видно детальнее.

![`MKCircle` красного цвета.](https://cdn.sparrowcode.io/tutorials/mapkit/circle-red.jpg)

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

Теперь любой объект типа `MKCircle` будет отображаться с такими визуальными параметрами. Для самого `circle` изменим радиус на 100.

```swift
var circle: MKCircle {
    MKCircle(center: location, radius: 100)
}
```

![Синий `MKCircle` под слоем `buildings`.](https://cdn.sparrowcode.io/tutorials/mapkit/circle-blue-below.jpg)

Теперь нам более отчётливо видно, что `circle` отображается под слоем `buildings` - такого быть не должно. В документации сказано, что такое происходит лишь с `3D-buildings`. Но у нас `2D`-карта. В данном случае на это влияет наша камера `MKMapCamera`. Закомментируем эту строчку, вернув настройки обзора к стандартным.

```swift
// mapView.setCamera(camera, animated: true)
```

Теперь `circle` отображается как задумано. Такое отображение удобно для указания на области, распределение, зоны покрытия и досягаемости.

![Синий `MKCircle`.](https://cdn.sparrowcode.io/tutorials/mapkit/circle-blue.jpg)

Мы можем одновременно отображать все наши данные. Именно совокупность данных даёт наиболее информативную картину.

![Синий `MKCircle` с геомаркерами.](https://cdn.sparrowcode.io/tutorials/mapkit/circle-blue-marker.jpg)

### MKPolyline

Отрисуем линию. Она состоит из совокупности точек, нам достаточно двух. Изменим координаты `location2`, что бы расстояние между `location` и `location2` было заметным. Можем взять координаты второго геомаркера. Также добавим свойство `polyline` типа `MKPolyline`. При инициализации `MKPolyline` принимает на вход массив координат геоточек и их количество.

```swift
var location2: CLLocationCoordinate2D {
    CLLocationCoordinate2D(latitude: 54.9500234 , longitude: 39.0210369)
}

var polyline: MKPolyline {
    MKPolyline(coordinates: [location, location2], count: 2)
}
```

Обновим `mapView(_ mapView: MKMapView, rendererFor overlay: MKOverlay) -> MKOverlayRenderer`, добавив проверку на `MKPolyline`, задав всем таким линиям ширину 5 и зелёный цвет.

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

Во `viewDidLoad()` добавляем оверлей линии на карту.

```swift
mapView.addOverlay(polyline)
```

![Пример `MKPolyline`.](https://cdn.sparrowcode.io/tutorials/mapkit/circle-line.jpg)

Если мы включим отображение маркеров - получится, что мы нарисовали отображение кратчайшего расстояния между объектами. Но в случае отрисовки на карте маршрутов и дистанций важно учитывать форму Земли, и не всегда расстояние между двумя объектами на `2D`-карте будет выглядеть как прямая.

![MKPolyline с геомаркерами.](https://cdn.sparrowcode.io/tutorials/mapkit/circle-line-markers.jpg)

### MKPolygon

При создании объекта достаточно указать вершины без повтора, чтобы получился замкнутый многоугольник.

Зададим координаты третьей геоточки и создадим полигон, как делали это с линией.

```swift
var location3: CLLocationCoordinate2D {
    CLLocationCoordinate2D(latitude: 54.9484931, longitude: 39.0170369)
}

var polygon: MKPolygon {
    MKPolygon(coordinates: [location, location2, location3], count: 3)
}
```

Укажем параметры отрисовки полигонов. Пусть будут оранжевые с прозрачной заливкой и толщиной обводки 1.

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

Во `viewDidLoad()` добавляем полигон на карту.

```swift
mapView.addOverlay(polygon)
```

![Пример `MKPolygon`.](https://cdn.sparrowcode.io/tutorials/mapkit/circle-line-triangle.jpg)

### Маршрут

Apple сделала все за нас - мы лишь отправляем запрос и получаем в ответ возможные варианты маршрута. Потребуется класс `MKDirections` и связанные с ним. Он вычисляет направления и информацию о времени в пути на основе предоставленной информации (геоточки, способ перемещения и т.д.).

Вернём отображение геомаркеров. Будем строить маршрут от `location` до `location2`. Скроем отображение оверлеев. Наш маршрут также строится на основе `MKPolyline`, поэтому он отобразится с теми же параметрами, что и линия.

```swift
mapView.addAnnotations(landmarks)

// mapView.addOverlay(circle)
// mapView.addOverlay(polyline)
// mapView.addOverlay(polygon)
```

Напишем метод `createPath(sourceCLL: CLLocationCoordinate2D, destinationCLL: CLLocationCoordinate2D)`. 

- `sourceCLL` - координаты геоточки, начальная точка маршрута.
- `destinationCLL` - координаты геоточки, конечная точка маршрута.

С помощью `MKDirections.Request()` будем делать запрос на сервер Apple, в ответ придёт массив маршрутов или ошибка. 

Прежде чем сделать запрос нужно указать значения для свойств `source`, `destination` и `transportType`. `transportType` отвечает за тип передвижения по маршруту и принимает значения типа `MKDirectionsTransportType`. Можно передать одно из четырёх значений:

- `automobile` - на автомобиле.
- `walking` - пешком.
- `transit` - общественным транспортом.
- `any` - для любого транспорта.

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

Во `viewDidLoad()` вызываем метод `createPath(sourceCLL: CLLocationCoordinate2D, destinationCLL: CLLocationCoordinate2D)`.

```swift
createPath(sourceCLL: location, destinationCLL: location2)
```

![Маршрут для автомобиля.](https://cdn.sparrowcode.io/tutorials/mapkit/route-automobile.jpg)

Изменим тип передвижения по маршруту.

```swift
directionRequest.transportType = .walking
```

![Пеший маршрут.](https://cdn.sparrowcode.io/tutorials/mapkit/route-walking.jpg)

## Поиск

Для поиска по карте потребуются классы `MKLocalSearch` и `MKLocalSearch.Request`. 

`MKLocalSearch` используется для одного поискового запроса, в роли которого может выступать адрес, тип или названия интересующих объектов и мест. Результаты передаются в указанный нами обработчик. Используем инициализатор `init(request: MKLocalSearch.Request)`. `MKLocalSearch.Request` используется для поиска местоположения на карте на основе строки на естественном языке (`naturalLanguageQuery`). Включение региона карты при поиске сузит результаты поиска до указанной географической области.

Переходим в `Landmark.swift` и добавляем ещё один инициализатор. Он потребуется, потому что координаты найденных мест приходят с типом `CLLocation`.

```swift
init? (location: CLLocation, title: String?) {
    
    self.coordinate = CLLocationCoordinate2D(latitude: location.coordinate.latitude, longitude: location.coordinate.longitude)
    self.title = title
    self.subtitle = ""
    
    super.init()
}
```

Добавим в `UIViewController` метод `search(place: String)`. `place` - место, которое мы собираемся искать. Создадим запрос `request` типа `MKLocalSearch.Request()`, на его основе сделаем поиск `search` типа `MKLocalSearch`, в обработчике которого будем создавать экземпляры `Landmark` на основе полученных результатов и сразу добавлять их на карту.

```swift
func search(place: String) {

    let request = MKLocalSearch.Request()
    request.naturalLanguageQuery = place
    request.region = MKCoordinateRegion(center: location, latitudinalMeters: regionRadius, longitudinalMeters: regionRadius)

    let search = MKLocalSearch(request: request)
    search.start(completionHandler: {(response, error) in
            
        for item in response!.mapItems {
            let landmark = Landmark(location: item.placemark.location!, title: item.name)
            self.mapView.addAnnotation(landmark!)
        }
    })
}
```

Теперь можно вызвать метод `search(place: String)` во `viewDidLoad()`, запустить симулятор и посмотреть результаты поиска. Также снимем ограничение на панорамирование и масштабирование. 

```swift
// mapView.addAnnotations(landmarks)
// mapView.setCameraBoundary(MKMapView.CameraBoundary(coordinateRegion: coordinateRegion), animated: true)
// mapView.setCameraZoomRange(zoomRange, animated: true)
search(place: "Почта")
```

![Приближенный почтовый офис.](https://cdn.sparrowcode.io/tutorials/mapkit/postoffice.jpg)

Немного отдалим карту.

![Отдалённый почтовый офис.](https://cdn.sparrowcode.io/tutorials/mapkit/postoffices.jpg)

Изменим запрос поиска.

```swift
search(place: "Магазин")
```

![Магазины.](https://cdn.sparrowcode.io/tutorials/mapkit/shops.jpg)
