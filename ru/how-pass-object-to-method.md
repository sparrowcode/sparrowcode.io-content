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

