Научимся изменять порядок ячеек, перетаскивать несколько ячеек, перемещать ячейки между коллекциями и даже между приложениями.

В этой части разберём перетаскивание для коллекции и таблицы. В следующей части расскажем, как перетаскивать любые вьюхи куда угодно и обрабатывать их сброс. Перед погружением в код разберём, как устроен жизненный цикл драга и дропа.

![preview](https://cdn.ivanvorobei.by/websites/sparrowcode.io/drag-and-drop-part-1/preview.jpg)

## Модели

Драг отвечает за перемещение объекта, дроп — за сброс объекта и его новое положение. Сервиса, отвечающего за начало драга, нет. Когда палец с ячейкой ползёт по экрану, вызывается метод делегата. Очень похоже на `UIScrollViewDelegate` с методом `scrollViewDidScroll`.

`UIDragSession` и `UIDropSession` становятся доступны, когда вызываются методы делегата. Это объекты-обёртки с информацией о положении пальца, объектов, для которых совершали действия, кастомного context и других. Перед началом драга предоставьте объект `UIDragItem`, это обёртка данных. В буквальном смысле это то, что мы хотим перетянуть.

```swift
let itemProvider = NSItemProvider.init(object: yourObject)
let dragItem = UIDragItem(itemProvider: itemProvider)
dragItem.localObject = action
return dragItem
```

Чтобы провайдер смог скушать любой объект, реализуйте протокол `NSItemProviderWriting`:

```swift
extension YourClass: NSItemProviderWriting {
    
    public static var writableTypeIdentifiersForItemProvider: [String] {
        return ["YourClass"]
    }
    
    public func loadData(withTypeIdentifier typeIdentifier: String, forItemProviderCompletionHandler completionHandler: @escaping (Data?, Error?) -> Void) -> Progress? {
        return nil
    }
}
```

Мы готовы. Потянули.

## Drag

Мучать будем коллекцию. Лучше использовать `UICollectionViewController`, из коробки он умеет больше. Но и простая вьюха подойдёт.

Установим драг делегат:

```swift
class CollectionController: UICollectionViewController {
    
    func viewDidLoad() {
        super.viewDidLoad()
        collectionView.dragDelegate = self
    }
}
```

Реализуем протокол `UICollectionViewDragDelegate`. Первым будет метод `itemsForBeginning`:

```swift
func collectionView(_ collectionView: UICollectionView, itemsForBeginning session: UIDragSession, at indexPath: IndexPath) -> [UIDragItem] {
        let itemProvider = NSItemProvider.init(object: yourObject)
        let dragItem = UIDragItem(itemProvider: itemProvider)
        dragItem.localObject = action
        return dragItem
    }
```

Вы уже видели этот код выше. Он оборачивает наш объект в `UIDragItem`. Метод вызывается при подозрении, что пользователь хочет начать драг. Не используйте этот метод как начало драга, его вызов только предполагает, что драг начнётся.

Добавим ещё два метода — `dragSessionWillBegin` и `dragSessionDidEnd`:

```swift
extension CollectionController: UICollectionViewDragDelegate {
   
   func collectionView(_ collectionView: UICollectionView, itemsForBeginning session: UIDragSession, at indexPath: IndexPath) -> [UIDragItem] {
        let itemProvider = NSItemProvider.init(object: yourObject)
        let dragItem = UIDragItem(itemProvider: itemProvider)
        dragItem.localObject = action
        return dragItem
    }
    
    func collectionView(_ collectionView: UICollectionView, dragSessionWillBegin session: UIDragSession) {
    
    }
    
    func collectionView(_ collectionView: UICollectionView, dragSessionDidEnd session: UIDragSession) {
    
    }
}
```

Первый метод вызывается, когда драг начался. Второй - когда драг закончился. Перед `dragSessionWillBegin` вызывается `itemsForBeginning`. Но не факт, что если вызвался `itemsForBeginning`, вызовется метод `dragSessionWillBegin`.

Если нужно обновить интерфейс на время драга (например, спрятать кнопки удаления), это правильное место. Давайте посмотрим, что получается на этом этапе.

[Drag Preview](https://cdn.ivanvorobei.by/websites/sparrowcode.io/drag-and-drop-part-1/drag-delegate.mov)

Ячейка возвращается на место. Дроп реализуем дальше.

## Drop

Драг - половина дела. Теперь научимся сбрасывать ячейку в нужное положение. Реализуем протокол `UICollectionViewDropDelegate`:

```swift
extension CollectionController: UICollectionViewDropDelegate {
    
    func collectionView(_ collectionView: UICollectionView, dropSessionDidUpdate session: UIDropSession, withDestinationIndexPath destinationIndexPath: IndexPath?) -> UICollectionViewDropProposal {
        
    }
    
    func collectionView(_ collectionView: UICollectionView, performDropWith coordinator: UICollectionViewDropCoordinator) {
    
    }
    
    func collectionView(_ collectionView: UICollectionView, dropSessionDidEnd session: UIDropSession) {
    
    }
}
```

Первый метод требует вернуть объект `UICollectionViewDropProposal`. Этот метод отвечает за превью и обновление интерфейса, он подсказывает пользователю, что произойдёт, если дроп сделать сейчас.

Вернуть можно один из нескольких статусов, разберём каждый.

```swift
// Ячейка вернётся на место, визуальные индикаторы не появятся. Действие не смещает другие ячейки.
return .init(operation: .cancel)
// Появится серая иконка. Это значит, что операция запрещена.
return .init(operation: .forbidden)
// Произойдёт полезное действие, визуальные индикаторы не появятся.
return .init(operation: .move)
// Ячейки смещаются для предлагаемого места дропа, визуальные индикаторы не появятся, .
return .init(operation: .move, intent: .insertAtDestinationIndexPath)
// Появляется зелёный плюс — как индикатор копирования.
return .init(operation: .copy)
```

В нашем примере сделаем так - если есть прогнозируемый IndexPath, то разрешаем сброс. Если нет - то запрещаем. Лучше поставить отмену, но так будет нагляднее.

```swift
func collectionView(_ collectionView: UICollectionView, dropSessionDidUpdate session: UIDropSession, withDestinationIndexPath destinationIndexPath: IndexPath?) -> UICollectionViewDropProposal {

    guard let _ = destinationIndexPath else { return .init(operation: .forbidden) }
    return .init(operation: .move, intent: .insertAtDestinationIndexPath)
}
```

`destinationIndexPath` — это системный расчёт, куда ячейку можно дропнуть. Он ни к чему не обязывает, более того, дропнуть мы можем в другое место. Перейдём к следующему методу `performDropWith`.

Здесь решаем самые главные дела. Меняем данные, переставляем ячейки и уведомляем систему, куда дропнули вьюху, чтобы система отрисовала анимацию.

```swift
func collectionView(_ collectionView: UICollectionView, performDropWith coordinator: UICollectionViewDropCoordinator) {
    
    // Если система не смогла определить IndexPath, то останавливаем выполнение. 
    // Дальше мы научимся определять индекс самостоятельно, но пока оставим так.
    guard let destinationIndexPath = coordinator.destinationIndexPath else { return }
    
    for item in coordinator.items {
        // Получаем доступ к нашему объекту, приводим тип.
        guard let yourObject = item.dragItem.localObject as? YourClass else { continue }
        // Объект перемещаем из одного места в другое. Я использую псевдофункцию, подразумевая кастомную логику:
        move(object: yourObject, to: destinationIndexPath)
    }
    
    // Не забудьте обновить коллекцию.
    // Если используете классический data source, изменения вносите в блоке `performBatchUpdates`.
    // Если у вас diffable data source, используйте обновление снепшота.
    // Функция для примера, такой функции нет.
    collectionView.reloadAnimatable()
    
    // Уведомляем, куда сбросили элемент.
    // Самостоятельно реализуйте функцию `getIndexPath`.
    for item in coordinator.items {
        guard let yourObject = item.dragItem.localObject as? YourClass else { continue }
        if let indexPath = getIndexPath(for: yourObject) {
            coordinator.drop(item.dragItem, toItemAt: indexPath)
        }
    }
}
```

Теперь коллекция и data source обновляются при перемещении, ячейка дропается по новому индексу. Глянем, что получилось:

[Drag Preview](https://cdn.ivanvorobei.by/websites/sparrowcode.io/drag-and-drop-part-1/drop-delegate.mov)

Чтобы ячейки расступались для дропа другой ячейки, используйте Drop Proposal c `.insertAtDestinationIndexPath`. Любой другой интент не будет этого делать. Иногда багует с коллекцией, будьте осторожны.

## Drag нескольких ячеек

В протоколе `UICollectionViewDragDelegate` мы реализовывали метод `itemsForBeginning`. Он возвращал объект драга. Чтобы к текущему драгу добавить ещё объекты, реализуйте метод `itemsForAddingTo`:

```swift
func collectionView(_ collectionView: UICollectionView, itemsForAddingTo session: UIDragSession, at indexPath: IndexPath, point: CGPoint) -> [UIDragItem] {
    // Код аналогичен.
    // Создаём `UIDragItem` на основе нашего объекта.
    let itemProvider = NSItemProvider.init(object: yourObject)
    let dragItem = UIDragItem(itemProvider: itemProvider)
    dragItem.localObject = action
    return dragItem
}
```

Теперь ячейки будут собираться в стопку, можно перемещать группу.

[Drag Stack](https://cdn.ivanvorobei.by/websites/sparrowcode.io/drag-and-drop-part-1/drag-stack.mov)

## Table View

Для таблицы есть аналогичные протоколы `UITableViewDragDelegate` и `UITableViewDropDelegate`. Методы повторяются с оговоркой на таблицу.

```swift
public protocol UITableViewDragDelegate: NSObjectProtocol {

    optional func tableView(_ tableView: UITableView, itemsForAddingTo session: UIDragSession, at indexPath: IndexPath, point: CGPoint) -> [UIDragItem]

    optional func tableView(_ tableView: UITableView, dragSessionWillBegin session: UIDragSession)

    optional func tableView(_ tableView: UITableView, dragSessionDidEnd session: UIDragSession)
}
```

Дроп работает аналогично. Отмечу, что дроп стабильнее именно в таблице, сказывается отсутствие лейаута.

Редактирование таблицы никак не влияет на вызовы методов дропа.

```swift
tableView.isEditing = true
```

То есть у вас может быть системный реодер ячеек и дроп, к примеру, внутрь ячеек.

[Table Drop](https://cdn.ivanvorobei.by/websites/sparrowcode.io/drag-and-drop-part-1/table-drop.mov)

## DestinationIndexPath

Системный параметр `DestinationIndexPath` не всегда идеально определяет положение. Например, если вы выйдете за края контента коллекции, то система не предложит сбросить ячейку как последнюю.

Давайте напишем функцию, которая сможет предложить свой индекс, если системное предложение равно `nil`.

```swift
// В качестве входных параметров используем системный индекс и сессию дропа.
// Если системный индекс будет равен `nil`, то у нас появятся две системы расчёта.
private func getDestinationIndexPath(system passedIndexPath: IndexPath?, session: UIDropSession) -> IndexPath? {

            // Здесь попытаемся получить индекс по локации дропа.
            // Чаще всего результат будет совпадать с системным, но когда системного нет, может вернуть хорошее значение.
            let systemByLocationIndexPath = collectionView.indexPathForItem(at: session.location(in: collectionView))
            
            // Здесь хардкор. Берём локацию и ищем в радиусе 100 точек ближайшую ячейку.
            var customByLocationIndexPath: IndexPath? = nil
            if systemByLocationIndexPath == nil {
                var closetCell: UICollectionViewCell? = nil
                var closetCellVerticalDistance: CGFloat = 100
                let tapLocation = session.location(in: collectionView)
                
                for indexPath in collectionView.indexPathsForVisibleItems {
                    guard let cell = collectionView.cellForItem(at: indexPath) else { continue }
                    let cellCenterLocation = collectionView.convert(cell.center, to: collectionView)
                    let verticalDistance = abs(cellCenterLocation.y - tapLocation.y)
                    if closetCellVerticalDistance > verticalDistance {
                        closetCellVerticalDistance = verticalDistance
                        closetCell = cell
                    }
                }
                
                if let cell = closetCell {
                    customByLocationIndexPath = collectionView.indexPath(for: cell)
                }
            }
            
            // Вернём значение в порядке приоритета.
            return passedIndexPath ?? systemByLocationIndexPath ?? customByLocationIndexPath
}
```

Можем улучшить код для обновления интерфейса:

```swift
func collectionView(_ collectionView: UICollectionView, dropSessionDidUpdate session: UIDropSession, withDestinationIndexPath destinationIndexPath: IndexPath?) -> UICollectionViewDropProposal {

    guard let _ = getDestinationIndexPath(system: destinationIndexPath, session: session) else { return .init(operation: .forbidden) }
    return .init(operation: .move, intent: .insertAtDestinationIndexPath)
}
```

Обратите внимание: метод поможет только с дропом. Если используете `.insertAtDestinationIndexPath`, не получится переопределить, как будут расступаться ячейки.

## Проблемы

Большинство проблем связано с коллекцией, а именно с лейаутом. Из известных проблем - при попытке сбросить ячейку последней FlowLayout запросит несуществующие атрибуты ячейки. Когда ячейки расступаются, лейаут рисует ячейку внутри, а при дропе получается ячеек больше, чем моделей в Data Source. Это можно решить переопределением метода в `UICollectionViewFlowLayout`:

```swift
override func layoutAttributesForItem(at indexPath: IndexPath) -> UICollectionViewLayoutAttributes? {
   if let countItems = collectionView?.numberOfItems(inSection: indexPath.section) {
       if countItems == indexPath.row {
            // If ask layout cell which not isset,
            // shouldn't call super.
            return nil
       }
   }
   return super.layoutAttributesForItem(at: indexPath)
}
```

`.insertAtDestinationIndexPath` работает плохо, если тянуть ячейку из одной коллекции в другую. Приложение крашнется при драге за пределы первой секции, это связано с лейаутом. У таблиц проблем не ловил.

Мы закончили первую часть. Когда будет готова вторая, добавлю на неё ссылку. Если нужен ролик по теме или остались вопросы - пишите в комментариях к посту в [телеграм-канале](https://t.me/sparrowcode/55).

Вы управляете тремя отступами - `imageEdgeInsets`, `titleEdgeInsets` и `contentEdgeInsets`. Чаще всего ваша задача сводится к выставлению симметрично-противоположных значений.

Перед тем как начнем погружаться, гляньтье [проект-пример](https://cdn.ivanvorobei.by/websites/sparrowcode.io/edge-insets-uibutton/example-project.zip). Каждый ползунок отвечает за конкретный отсуп и вы можете их комбинировать. На видео я выставил цвет фона - красный, цвет иконки - желтый, а цвет тайтла - синий.

[Edge Insets UIButton Example Project Preview](https://cdn.ivanvorobei.by/websites/sparrowcode.io/edge-insets-uibutton/edge-insets-uibutton-example-preview.mov)

Сделайте отступ между заголовоком и иконкой `10pt`. Когда получится, убедитесь, контролируете результат или получилось наугад. В конце туториала вы будете знать как это работает.

## contentEdgeInsets

Ведёт себя предсказуемо. Он добавляет отступы вокруг заголовка и иконки. Если поставите отрицательные значения - то отступ будет уменьшаться. Код:

```swift
// Я знаю про сокращенную запись
previewButton.contentEdgeInsets.left = 10
previewButton.contentEdgeInsets.right = 10
previewButton.contentEdgeInsets.top = 5
previewButton.contentEdgeInsets.bottom = 5
```

![contentEdgeInsets](https://cdn.ivanvorobei.by/websites/sparrowcode.io/edge-insets-uibutton/content-edge-insets.png)

Вокруг контента добавились отступы. Они добавляются пропорционально и влияют только на размер кнопки. Практический смысл - расширить область нажатия, если кнопка маленькая.

## imageEdgeInsets и titleEdgeInsets

Я вынес их в одну секцию не просто так. Чаще всего задача будет сводится к симметричному добавлению отсупов с одной стороны, и уменьшению с другой. Звучит сложно, сейчас разрулим.

Добавим отступ между картинкой и заголовоком, пускай `10pt`. Первая мысль - добавить отступ через проперти `imageEdgeInsets`:

[imageEdgeInsets space between icon and title](https://cdn.ivanvorobei.by/websites/sparrowcode.io/edge-insets-uibutton/image-edge-insets-space-icon-title.mov)

Поведение сложнее. Отступ добавляется, но не влияет на размер кнопки. Если бы влиял - проблема была решена.

Напарник `titleEdgeInsets` работает так же - не меняет размер кнопки. Логично добавить отступ для заголовка, но противоположный по значению. Выглядеть это будет так:

```swift
previewButton.imageEdgeInsets.left = -10
previewButton.titleEdgeInsets.left = 10
```

Это та симметрия, про которую писал выше.

***`imageEdgeInsets` и `titleEdgeInsets` не меняют размер кнопки. А вот `contentEdgeInsets` - меняет.***

Запомните это, и больше не будет проблем с правильными отступами. Давайте усложним задачу - поставим иконку справа от заголовка.

```swift
let buttonWidth = previewButton.frame.width
let imageWidth = previewButton.imageView?.frame.width ?? .zero

// Смещаем заголовок к левому краю. 
// Отступ слева был `imageWidth`, значит уменьшив на это значение получим левый край.
previewButton.titleEdgeInsets = UIEdgeInsets(
    top: 0, 
    left: -imageWidth, 
    bottom: 0, 
    right: imageWidth
)

// Перемещаем иконку к правому краю.
// Дефолтный отступ был 0,значит новая точка Y будет ширина - ширина иконки.
previewButton.imageEdgeInsets = UIEdgeInsets(
    top: 0, 
    left: buttonWidth - imageWidth, 
    bottom: 0, 
    right: 0
)
```

## Готовый класс

В моей библиотеке [SparrowKit](https://github.com/ivanvorobei/SparrowKit) уже есть готовый класс кнопки [`SPButton`](https://github.com/ivanvorobei/SparrowKit/blob/main/Sources/SparrowKit/UIKit/Classes/Buttons/SPButton.swift) с поддержкой отсупа между картинкой и текстом.

```swift
button.titleImageInset = 8
```

Работает для RTL локализации. Если картинки нет, отступ не добавляется. Разработчку нужно только выставить значение отступа.

![Deprecated imageEdgeInsets и titleEdgeInsets](https://cdn.ivanvorobei.by/websites/sparrowcode.io/edge-insets-uibutton/depricated.png)

## Deprecated

Я должен обратить внимание, с iOS 15 наши друзья помечены `depriсated`.

Несколько лет проперти будут работать. Apple рекомендуют использовать конфигурацию. Посмотрим, что останется в живых - конфигурация, или старый добрый `padding`.

На этом всё. Чтобы наглядно побаловаться, качайте [проект-пример](https://cdn.ivanvorobei.by/websites/sparrowcode.io/edge-insets-uibutton/example-project.zip). Задать вопросы можно в комментариях [к посту](https://t.me/sparrowcode/99).

## Проблема

У меня небыло проблем с выбором - подсознательно я знал, что здесь нужно передать объект, а здесь только некоторые поля объекта. Это даже не туториал, а цепочка рассуждений для того чтобы объяснить свой выбор.

Представьте метод, вычисляющий возраст у объекта `Car`:

```swift
/*
Обратите внимание, класс `Car` имеет несколько проперти.
*/
class Car {

    var id: String
    var model: String
    var birthday: Date
}

static func age(/* Проблема здесь */) -> Int {
    return Calendar.current.dateComponents([.year], from: self, to: *Объект даты*).year!
}
```

Методу `age` для работы нужна только дата, остальные проперти класса `Car` не важны. Есть два конкурирующих подхода.

Сторонники первого утверждают что в метод нужно передавать только требуемые данные. Это поддерживает сопряжение, облегчает повторное использование (например когда файл кидайте в другие схемы). И конечно инкапсюляция - метод сможет использовать все остальные проперти, а это плохо.

```swift
static func age(from birthday: Date) -> Int { ... }
```

Сторонники второго подхода утверждают что в метод нужно передавать весь объект. Это сохраняет обстракцию и предупреждает будущие изменения. Например, появится дата списания в утиль - считать возраст машины придется не до текущей даты, а до даты списания.

```swift
static func age(for car: Car) -> Int { ... }
```

Если проблема кажется несущественной, значит вы не страдали со схемами и шарингом файлов между ними. Примеры выше отражают крайности, поэтому появляются споры.

## Решение

Оба подхода не нарушают код-стайл и делают свою работу. А значит по этим пунктам отбросить неработающий вариант не получится. Считать количество символов - такая же глупость, как спорить про Swift и SwiftUI.

Давайте взглянем на абстрацию. Если метод ожидает конкретные проперти, которые случайно оказались в одном объекте - передавайте их в метод по отдельности. Если же элементы данных принадлежат конкретному объекту и неотделимы, а функция при этом не универсальная - то раскрывая элементы вы нарушаете абстрацию. В этом случае передавайте объект целиком.

Решение для примера выше вариант можете написать в комментариях к посту [телеграм канале](https://t.me/sparrowcode/43).

Чтобы ресетнуть приложение для macOS Catalyst, нужно знать имя папки пользователя, бандл приложения, AppGroup и suit для UserDefaults (если используете). В туториале я буду использовать следующие примеры:

Папка пользователя `ivanvorobei`, bundle приложения `by.ivanvorobei.apps.debts`, идентификатор AppGroup `group.by.ivanvorobei.apps.debts`.

Будьте внимательны, используйте значения от вашего приложения.

## Очистить UserDefaults

Если вы хотите удалить дефолтный `UserDefaults`, откройте терминал и введите команду:

```swift
// Удаляем `UserDefaults` целиком 
defaults delete by.ivanvorobei.apps.debts

// Удаляем из `UserDefaults` по ключу 
defaults delete by.ivanvorobei.apps.debts key
```

Если вы использовали кастомный домен, вызывайте эту команду:

```swift
// Создается вот так
// UserDefaults(suiteName: "Custom")
defaults delete suit.name
```

## AppGroup

Если вы используйте `AppGroup`, нужно удалить следующие папки:

```swift
/Users/ivanvorobei/Library/Group Containers/group.by.ivanvorobei.apps.debts
/Users/ivanvorobei/Library/Application Scripts/group.by.ivanvorobei.apps.debts
```

Если хранили в дефолтном пути, то эта папка:

```swift
/Users/ivanvorobei/Library/Containers/by.ivanvorobei.apps.debts
```

## База данных Realm

Файлы базы данных `Realm` хранятся как обычные файлы. Они находятся либо в AppGroup, либо в дефолтной папке. Выполнив пункты выше, база данных будет удалена.

## Ещё папки

Мне удалось найти еще папки, но для чего они не знаю. Оставлю пути здесь:

```swift
/Users/ivanvorobei/Library/Application Scripts/group.by.ivanvorobei.apps.debts
/Users/ivanvorobei/Library/Developer/Xcode/Products/by.ivanvorobei.apps.debts (macOS)
```

Если вы знаете для чего они или знаете еще папки, дайте мне знать - я обновлю туториал.

Сложность первой версии StoreKit была настолько запредельной, что породила огромное количество SAS-решений разной степени паршивости и качества. Ты точно знаешь парочку, и скорее всего не умеешь работать с нативным StoreKit. Это нормально. Я тоже не умею.

Новый StoreKit выглядит как глоток холодной воды в пустыне. Давайте погружаться.

![Introducing StoreKit 2](https://cdn.ivanvorobei.by/websites/sparrowcode.io/meet-storekit-2/header.jpg)

## Что нового

Заменили модели, представляющие покупки и операции над ними. Теперь названия без префиксов SK, и в целом интуитивно понятно какие данные репрезентуют модели. Останавливаться на каждом не будем, картинка cо списком:

![StoreKit 2 Modes](https://cdn.ivanvorobei.by/websites/sparrowcode.io/meet-storekit-2/models.jpg)

Запрос продуктов и покупка

Раньше нужно было создать `SKProductsRequest`, стать его делегатом, запустить этот request и обязательно сохранить на него сильную ссылку, чтобы система не убила его до завершения.

Теперь круче:

```swift
// Получение продуктов
let storeProducts = try await Product.request(with: identifiers)

// Покупка
let result = try await product.purchase()
switch result {
case .success(let verification):
    // handle success
    return result
case .userCancelled, .pending:
    // handle if needed
default: break
```

Зацените статусы обработки результата. К покупке можно крепить свои данные:

```swift
let result = try await product.purchase(options:[.appAccountToken(yourAppToken))])
```

Для связаности между аккаунтами и аналитики чумовая штука.

## Подписки

Если пользователь использовал триал в группе на одной из подписок, триал ему больше не доступен. Нет простого способа узнать пользователю разрешен триал или нет. Нужно было запросить все транзакции и посмотреть вручную. Сейчас упростилось до одной строчки кода.

```swift
static func isEligibleForIntroOffer(for groupID: String) async -> Bool
```

Добавили состояние автообновления подписки, которое раньше было доступно только в чеке:

- <b>subscribed</b> - подписка активна<br>
- <b>expired</b> - подписка истекла<br>
- <b>inBillingRetryPeriod</b> - была ошибка при попытке оплаты<br>
- <b>inGracePeriod</b> - отсрочка платежа по подписке. Если grace period у вашей подписки включен и произошла ошибка при оплате, то у пользователя будет ещё какое-то время, пока подписка работает, хотя оплаты ещё не было. Количество дней отсрочки может быть от 6 до 16 в зависимости от длительности самой подписки.<br>
- <b>revoked</b> - доступ ко всем подпискам этой группы отклонён AppStore.

![Subscription information](https://cdn.ivanvorobei.by/websites/sparrowcode.io/meet-storekit-2/subscription-information.jpg)

Объект `Renewal Info` содержит информацию об автообновлением подписки. Например:

- <b>willAutoRenew</b> - флаг, который подскажет, будет ли подписка автопродлена. Если нет, то с какой-то долей вероятности пользователь не планирует дальше использовать подписку в вашем приложении. Самое время подумать о том, как его удержать.<br>
- <b>autoRenewPreference</b> - ID подписки, на которую произойдет автообновление. Например, вы можете проверить, что пользователь сделал downgrade и планирует пользоваться более дешевой версией вашей подписки. В таком случае при желании можете попробовать предложить ему скидку и удержать его на более премиальной версии.<br>
- <b>expirationReason</b> - а здесь вы можете более подробно посмотреть причины истечения срока подписки.

Плюшек еще больше. Восстанавливаться покупки будут автоматически, поддержка async, нормальное API с неймингом функций и моделей, статус подписок, доступность оффера. Выглядит как начало смерти SAS-решений (там всё сложнее, но апдейт всё таки киллер).

## Обратная совместимость

Покупки из первой версии будут работать во второй. Новый StoreKit доступен только с iOS 15. Большинство проектов зачем-то держат поддержку iOS 6, так что реальное использование увидим только в инди-проектах.

Спасибо автору  [статьи](https://habr.com/ru/post/563280/), почитайте - там подробнее и на русском.

С помощью  [Product Page Optimization](https://developer.apple.com/app-store/product-page-optimization/) вы можете создавать варианты скриншотов, промо-текстов и иконок. Скриншоты и текст добавляются в App Store Connect, а вот иконки добавляет разработчик в Xcode-проект.

В документации сказано «поместите иконки в Asset Catalog, отправьте бинарный файл в App Store Connect и используйте SDK». Но как закинуть иконки и что за SDK - не сказали. Давайте разбираться, шаги подкрепил скриншотами.

## Добавляем иконки в Assets

Алтернативную иконку делаем в нескольких разрешениях, как и основную. Я использую приложение [AppIconBuilder](https://apps.apple.com/app/id1294179975). Неймнг пишем любой, но учтите - имя отобразится в App Store Connect.

![Добавляем иконки в Assets](https://cdn.ivanvorobei.by/websites/sparrowcode.io/product-page-optimization-alternative-icons/adding-icons-to-assets.png)

## Настройки в таргете

Нужен Xcode 13 и выше. Выберите таргет приложения и перейдите на вкладку `Build Settings`. В поиск вставьте `App Icon` и вы увидите секцию `Asset Catalog Compiler`.

![Настройки в таргете](https://cdn.ivanvorobei.by/websites/sparrowcode.io/product-page-optimization-alternative-icons/adding-settings-to-target.png)

Нас интересуют 3 параметра:

`Alternate App Icons Sets` - перечисление названий иконок, которые добавили в каталог.

`Include All App Icon Assets` - установите в `true`, что бы включить альтернативные иконки в сборку.

`Primary App Icon Set Name` - название иконки по умолчанию. Не проверял, но скорее всего альтернативную иконку можно сделать основной.

## Cборка

Остается собрать приложение и отправить на проверку.

***Альтернативные иконки будут доступны после прохождения ревью.***

Теперь можно собирать разные страницы приложения и создавать ссылки для A/B тестов.

## Что Нового

Добавил поддержу SPM. Появился новый статус у разрешений `.notDetermined`. Добавил локализацию, на помент написания статьи SPPermissions поддерживает русский, английский и арабский. Добавил поддержку RTL для арабских языков. Изменил струкрутуру проекта.

## Установка через Swift Package Manager

Главной мотивацией была поддержка SPM. Импортируйте пакет:

```swift
https://github.com/ivanvorobei/SPPermissions
```

Выберите только нужные разрешения:

![Swift Package Manager Install Xcode Preivew](https://cdn.ivanvorobei.by/websites/sparrowcode.io/release-sppermissions-v6/spm-install-preview.png)

SPM требует определенный импорт файлов. Вы должны импортировать базовый модуль, он отвечает за интерфейсы, логику и локализацию. Следом импортируйте модели разрещений, которые нужны:

```swift
import SPPermissions
import SPPermissionsCamera
import SPPermissionsContacts
```

Не нужно испортировать все разрешения. Библиотека разбита на модули, потому что если вы добавите весь код в проект - эпл отклонит приложение. Добавляете только используемые разрешения.

Для Cocoapods без изменений.

## Синтаксис

Теперь разрешения это не enum, а проперти внутри класса `SPPermissions.Permission`

```swift
SPPermissions.Permission.camera
```

Это не должно повлиять на синтаксис, если вы использовали сокращенную форму.

## DataSource и Delegate

Метод для передачи текста для алерта (когда разрешение заблокировано) переехал в Data Source.

```swift
extension Controller: SPPermissionsDataSource {
    
    func configure(_ cell: SPPermissionsTableViewCell, for permission: SPPermissions.Permission) -> SPPermissionsTableViewCell {
    
        // Here you can customise cell, like texts or colors.
        
        return cell
    }
    
    func deniedAlertTexts(for permission: SPPermissions.Permission) -> SPPermissionDeniedAlertTexts? {
    
        // Вы можете вернуть кастомные текста. 
        // Если вернете `nil`, то алерт не покажется.
        // Я вернул дефолтные.
        
        return .default
    }
}
```

Методы делегата остались теми же, но переименованы. Если вы использовали эти протоколы в версии 5.x, обновите их сейчас. Методы являются опциональными, вы не увидите ошибку об изменении синтаксиса.

```swift
extension Controller: SPPermissionsDelegate {
    
    func didHidePermissions(_ permissions: [SPPermissions.Permission]) {}
    func didAllowPermission(_ permission: SPPermissions.Permission) {}
    func didDeniedPermission(_ permission: SPPermissions.Permission) {}
}
```

Подробную документацию можно найти на [странице библиотеки](https://github.com/ivanvorobei/SPPermissions).

Примеры кода будут для `SwiftUI` и `UIKit`. Внимательно следите за совместимостью символов - не все доступны для 14 и предудыщих iOS. Глянуть с какой версии доступен символ можно [в приложении](https://developer.apple.com/sf-symbols/).

## Render Modes

Render Modes - это отрисовка иконки в цветовой схеме. Доступны монохром, иерархический, палетка и мульти-цвет. Наглядное превью:

![SFSymbols Render Modes Preview](https://cdn.ivanvorobei.by/websites/sparrowcode.io/sf-symbols-3/render-modes-preview.jpg)

Рендеры доступны для каждого символа, но возможны ситуации когда результат для разных рендеров будет совпадать и иконка не изменит внешнего вида. Лучше выбирать [в приложении](https://developer.apple.com/sf-symbols/), предварительно установив нужный рендер.

Monochrome Render

Иконка целиком красится в указанный цвет. Цвет управляется через `tintColor`.

```swift
// UIKit
let image = UIImage(systemName: "doc")
let imageView = UIImageView(image: image)
imageView.tintColor = .systemRed

// SwiftUI
Image(systemName: "doc")
    .foregroundColor(.red)
```

Способ работает для любых изображений, не только для SF Symbols.

Hierarchical Render

Отрисовывает иконку в одном цвете, но создает глубину с помощью прозрачности для элементов символа.

```swift
// UIKit
let config = UIImage.SymbolConfiguration(hierarchicalColor: .systemIndigo)
let image = UIImage(systemName: "square.stack.3d.down.right.fill", withConfiguration: config)

// SwiftUI
Image(systemName: "square.stack.3d.down.right.fill")
    .symbolRenderingMode(.hierarchical)
    .foregroundColor(.indigo)
```

Обратите внимание, иногда рендер с моно-цветом совпадает с иерархическим.

![SFSymbols Hierarchical Render](https://cdn.ivanvorobei.by/websites/sparrowcode.io/sf-symbols-3/hierarchical-render.jpg)

Palette Render

Отрисовывает иконку в кастомных цветах. Каждому символу нужно опредленное количество цветов.

```swift
// UIKit
let config = UIImage.SymbolConfiguration(paletteColors: [.systemRed, .systemGreen, .systemBlue])
let image = UIImage(systemName: "person.3.sequence.fill", withConfiguration: config)

// SwiftUI
Image(systemName: "person.3.sequence.fill")
    .symbolRenderingMode(.palette)
    .foregroundStyle(.red, .green, .blue)
```

Если у символа 1 сегмент для цвета, он будет использовать первый указанный цвет. Если у символа 2 сегмента, но будет указан 1 цвет, он будет использоваться для обоих сегментов. Если укажете 2 цвета - они применятся соотвественно. Если указать 3 цвета, третий игнорируется.

![SFSymbols Palette Render](https://cdn.ivanvorobei.by/websites/sparrowcode.io/sf-symbols-3/palette-render.jpg)

Multicolor Render

Важные элементы будут иметь фиксированный цвет, для заполняющего можно указать кастомный.

```swift
// UIKit
let config = UIImage.SymbolConfiguration.configurationPreferringMulticolor()
let image = UIImage(systemName: "externaldrive.badge.plus", withConfiguration: config)

// SwiftUI
Image(systemName: "externaldrive.badge.plus")
    .symbolRenderingMode(.multicolor)
```

Изображения, у которых нет многоцветного варианта, будут автоматически отображаться в моно-цвете. На превью заполняющий цвет `.systemCyan`:

![SFSymbols Multicolor Render](https://cdn.ivanvorobei.by/websites/sparrowcode.io/sf-symbols-3/multicolor-render.jpg)

## Symbol Variant

Некоторые символы имеют поддержку форм, например колокольчик `bell` можно вписать в квадарт или круг. В `UIKit` нужно вызывать их по имени - например, `bell.square`, но в SwiftUI есть модификатор `.symbolVariant()`:

```swift
// Колокльчик перечеркнут
Image(systemName: 'bell')
    .symbolVariant(.slash)

// Вписывает в квадарт
Image(systemName: 'bell')
    .symbolVariant(.square)

// Можно комбинировать
Image(systemName: 'bell')
    .symbolVariant(.fill.slash)
```

Обратите внимание, в последнем примере можно комбинировать варианты символов.

Адаптация

SwiftUI умеет отображать символы соотвественно контексту. Для iOS Apple использует залитые иконки, но в macOS иконки без заливки, только линии. Если вы используете SF Symbols для Side Bar, то не нужно указывать, залитый символ или нет - он будет автоматически адаптироваться в зависимости от системы.

```swift
Label('Home', systemImage: 'person')
    .symbolVariant(.none)
```

Это все изменения в новой версии. Напишите [в коментариях к посту](https://t.me/sparrowcode/82) была ли полезна статья, и используете ли SF Symbols в проектах.

