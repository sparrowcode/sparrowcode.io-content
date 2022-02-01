<?php

use App\HTMLElements;
use App\TutorialModel;
use App\ButtonModel;

/** @var TutorialModel $tutorial */

HTMLElements::tutorialHeader(
    $tutorial,
    [
        new ButtonModel(
            'WWDC17',
            'https://developer.apple.com/videos/play/wwdc2017/203/',
            true
        ),
        new ButtonModel(
            'developer.apple.com',
            'https://developer.apple.com/documentation/uikit/drag_and_drop',
            true
        )
    ],
    [
        "https://cdn.ivanvorobei.by/websites/sparrowcode.io/drag-and-drop-part-1/google-structured-data/article_1_1.jpg",
        "https://cdn.ivanvorobei.by/websites/sparrowcode.io/drag-and-drop-part-1/google-structured-data/article_1_1.jpg",
        "https://cdn.ivanvorobei.by/websites/sparrowcode.io/drag-and-drop-part-1/google-structured-data/article_1_1.jpg"
    ]
);

HTMLElements::text(
    'Научимся изменять порядок ячеек, перетаскивать несколько ячеек, перемещать ячейки между коллекциями и даже между приложениями.'
);

HTMLElements::text(
    'В этой части разберём перетаскивание для коллекции и таблицы. В следующей части расскажем, как перетаскивать любые вьюхи куда угодно и обрабатывать их сброс. Перед погружением в код разберём, как устроен жизненный цикл драга и дропа.'
);

HTMLElements::image('preview', 'https://cdn.ivanvorobei.by/websites/sparrowcode.io/drag-and-drop-part-1/preview.jpg', 100);

HTMLElements::titleSection(
    'Модели'
);

HTMLElements::text(
    'Драг отвечает за перемещение объекта, дроп — за сброс объекта и его новое положение. Сервиса, отвечающего за начало драга, нет. Когда палец с ячейкой ползёт по экрану, вызывается метод делегата. Очень похоже на `UIScrollViewDelegate` с методом `scrollViewDidScroll`.'
);

HTMLElements::text(
    '`UIDragSession` и `UIDropSession` становятся доступны, когда вызываются методы делегата. Это объекты-обёртки с информацией о положении пальца, объектов, для которых совершали действия, кастомного context и других. Перед началом драга предоставьте объект `UIDragItem`, это обёртка данных. В буквальном смысле это то, что мы хотим перетянуть.'
);

HTMLElements::blockCode('
let itemProvider = NSItemProvider.init(object: yourObject)
let dragItem = UIDragItem(itemProvider: itemProvider)
dragItem.localObject = action
return dragItem
');

HTMLElements::text(
    'Чтобы провайдер смог скушать любой объект, реализуйте протокол `NSItemProviderWriting`:'
);

HTMLElements::blockCode('
extension YourClass: NSItemProviderWriting {
    
    public static var writableTypeIdentifiersForItemProvider: [String] {
        return ["YourClass"]
    }
    
    public func loadData(withTypeIdentifier typeIdentifier: String, forItemProviderCompletionHandler completionHandler: @escaping (Data?, Error?) -> Void) -> Progress? {
        return nil
    }
}
');

HTMLElements::text('Мы готовы. Потянули.');

HTMLElements::titleSection(
    'Drag'
);

HTMLElements::text(
    'Мучать будем коллекцию. Лучше использовать `UICollectionViewController`, из коробки он умеет больше. Но и простая вьюха подойдёт.'
);

HTMLElements::text(
    'Установим драг делегат:'
);

HTMLElements::blockCode('
class CollectionController: UICollectionViewController {
    
    func viewDidLoad() {
        super.viewDidLoad()
        collectionView.dragDelegate = self
    }
}
');

HTMLElements::text(
    'Реализуем протокол `UICollectionViewDragDelegate`. Первым будет метод `itemsForBeginning`:'
);

HTMLElements::blockCode('
func collectionView(_ collectionView: UICollectionView, itemsForBeginning session: UIDragSession, at indexPath: IndexPath) -> [UIDragItem] {
        let itemProvider = NSItemProvider.init(object: yourObject)
        let dragItem = UIDragItem(itemProvider: itemProvider)
        dragItem.localObject = action
        return dragItem
    }
');

HTMLElements::text(
    'Вы уже видели этот код выше. Он оборачивает наш объект в `UIDragItem`. Метод вызывается при подозрении, что пользователь хочет начать драг. Не используйте этот метод как начало драга, его вызов только предполагает, что драг начнётся.'
);

HTMLElements::text('Добавим ещё два метода — `dragSessionWillBegin` и `dragSessionDidEnd`:');

HTMLElements::blockCode('
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
');

HTMLElements::text(
    'Первый метод вызывается, когда драг начался. Второй - когда драг закончился. Перед `dragSessionWillBegin` вызывается `itemsForBeginning`. Но не факт, что если вызвался `itemsForBeginning`, вызовется метод `dragSessionWillBegin`.'
);

HTMLElements::text(
    'Если нужно обновить интерфейс на время драга (например, спрятать кнопки удаления), это правильное место. Давайте посмотрим, что получается на этом этапе.'
);

HTMLElements::video(
    'Drag Preview',
    'https://cdn.ivanvorobei.by/websites/sparrowcode.io/drag-and-drop-part-1/drag-delegate.mov',
    100
);

HTMLElements::text(
    'Ячейка возвращается на место. Дроп реализуем дальше.'
);

HTMLElements::titleSection(
    'Drop'
);

HTMLElements::text(
    'Драг - половина дела. Теперь научимся сбрасывать ячейку в нужное положение. Реализуем протокол `UICollectionViewDropDelegate`:'
);

HTMLElements::blockCode('
extension CollectionController: UICollectionViewDropDelegate {
    
    func collectionView(_ collectionView: UICollectionView, dropSessionDidUpdate session: UIDropSession, withDestinationIndexPath destinationIndexPath: IndexPath?) -> UICollectionViewDropProposal {
        
    }
    
    func collectionView(_ collectionView: UICollectionView, performDropWith coordinator: UICollectionViewDropCoordinator) {
    
    }
    
    func collectionView(_ collectionView: UICollectionView, dropSessionDidEnd session: UIDropSession) {
    
    }
}
');

HTMLElements::text(
    'Первый метод требует вернуть объект `UICollectionViewDropProposal`. Этот метод отвечает за превью и обновление интерфейса, он подсказывает пользователю, что произойдёт, если дроп сделать сейчас.'
);

HTMLElements::text(
    'Вернуть можно один из нескольких статусов, разберём каждый.'
);

HTMLElements::blockCode('
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
');

HTMLElements::text(
    'В нашем примере сделаем так - если есть прогнозируемый IndexPath, то разрешаем сброс. Если нет - то запрещаем. Лучше поставить отмену, но так будет нагляднее.'
);

HTMLElements::blockCode('
func collectionView(_ collectionView: UICollectionView, dropSessionDidUpdate session: UIDropSession, withDestinationIndexPath destinationIndexPath: IndexPath?) -> UICollectionViewDropProposal {

    guard let _ = destinationIndexPath else { return .init(operation: .forbidden) }
    return .init(operation: .move, intent: .insertAtDestinationIndexPath)
}
');

HTMLElements::text(
    '`destinationIndexPath` — это системный расчёт, куда ячейку можно дропнуть. Он ни к чему не обязывает, более того, дропнуть мы можем в другое место. Перейдём к следующему методу `performDropWith`.'
);

HTMLElements::text(
    'Здесь решаем самые главные дела. Меняем данные, переставляем ячейки и уведомляем систему, куда дропнули вьюху, чтобы система отрисовала анимацию.'
);

HTMLElements::blockCode('
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
');

HTMLElements::text(
    'Теперь коллекция и data source обновляются при перемещении, ячейка дропается по новому индексу. Глянем, что получилось:'
);

HTMLElements::video(
    'Drag Preview',
    'https://cdn.ivanvorobei.by/websites/sparrowcode.io/drag-and-drop-part-1/drop-delegate.mov',
    100
);

HTMLElements::text(
    'Чтобы ячейки расступались для дропа другой ячейки, используйте Drop Proposal c `.insertAtDestinationIndexPath`. Любой другой интент не будет этого делать. Иногда багует с коллекцией, будьте осторожны.'
);

HTMLElements::titleSection(
    'Drag нескольких ячеек'
);

HTMLElements::text('В протоколе `UICollectionViewDragDelegate` мы реализовывали метод `itemsForBeginning`. Он возвращал объект драга. Чтобы к текущему драгу добавить ещё объекты, реализуйте метод `itemsForAddingTo`:');

HTMLElements::blockCode('
func collectionView(_ collectionView: UICollectionView, itemsForAddingTo session: UIDragSession, at indexPath: IndexPath, point: CGPoint) -> [UIDragItem] {
    // Код аналогичен.
    // Создаём `UIDragItem` на основе нашего объекта.
    let itemProvider = NSItemProvider.init(object: yourObject)
    let dragItem = UIDragItem(itemProvider: itemProvider)
    dragItem.localObject = action
    return dragItem
}
');

HTMLElements::text('Теперь ячейки будут собираться в стопку, можно перемещать группу.');

HTMLElements::video(
    'Drag Stack',
    'https://cdn.ivanvorobei.by/websites/sparrowcode.io/drag-and-drop-part-1/drag-stack.mov',
    100
);

HTMLElements::titleSection(
    'Table View'
);

HTMLElements::text(
    'Для таблицы есть аналогичные протоколы `UITableViewDragDelegate` и `UITableViewDropDelegate`. Методы повторяются с оговоркой на таблицу.'
);

HTMLElements::blockCode('
public protocol UITableViewDragDelegate: NSObjectProtocol {

    optional func tableView(_ tableView: UITableView, itemsForAddingTo session: UIDragSession, at indexPath: IndexPath, point: CGPoint) -> [UIDragItem]

    optional func tableView(_ tableView: UITableView, dragSessionWillBegin session: UIDragSession)

    optional func tableView(_ tableView: UITableView, dragSessionDidEnd session: UIDragSession)
}
');

HTMLElements::text(
    'Дроп работает аналогично. Отмечу, что дроп стабильнее именно в таблице, сказывается отсутствие лейаута.'
);

HTMLElements::text(
    'Редактирование таблицы никак не влияет на вызовы методов дропа.'
);

HTMLElements::blockCode('
tableView.isEditing = true
');

HTMLElements::text('То есть у вас может быть системный реодер ячеек и дроп, к примеру, внутрь ячеек.');

HTMLElements::video(
    'Table Drop',
    'https://cdn.ivanvorobei.by/websites/sparrowcode.io/drag-and-drop-part-1/table-drop.mov',
    100
);

HTMLElements::titleSection(
    'DestinationIndexPath'
);

HTMLElements::text(
    'Системный параметр `DestinationIndexPath` не всегда идеально определяет положение. Например, если вы выйдете за края контента коллекции, то система не предложит сбросить ячейку как последнюю.'
);

HTMLElements::text(
    'Давайте напишем функцию, которая сможет предложить свой индекс, если системное предложение равно `nil`.'
);

HTMLElements::blockCode('
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
');

HTMLElements::text(
    'Можем улучшить код для обновления интерфейса:'
);

HTMLElements::blockCode('
func collectionView(_ collectionView: UICollectionView, dropSessionDidUpdate session: UIDropSession, withDestinationIndexPath destinationIndexPath: IndexPath?) -> UICollectionViewDropProposal {

    guard let _ = getDestinationIndexPath(system: destinationIndexPath, session: session) else { return .init(operation: .forbidden) }
    return .init(operation: .move, intent: .insertAtDestinationIndexPath)
}
');

HTMLElements::text(
    'Обратите внимание: метод поможет только с дропом. Если используете `.insertAtDestinationIndexPath`, не получится переопределить, как будут расступаться ячейки.'
);

HTMLElements::titleSection(
    'Проблемы'
);

HTMLElements::text(
    'Большинство проблем связано с коллекцией, а именно с лейаутом. Из известных проблем - при попытке сбросить ячейку последней FlowLayout запросит несуществующие атрибуты ячейки. Когда ячейки расступаются, лейаут рисует ячейку внутри, а при дропе получается ячеек больше, чем моделей в Data Source. Это можно решить переопределением метода в `UICollectionViewFlowLayout`:'
);

HTMLElements::blockCode('
override func layoutAttributesForItem(at indexPath: IndexPath) -> UICollectionViewLayoutAttributes? {
   if let countItems = collectionView?.numberOfItems(inSection: indexPath.section) {
       if countItems == indexPath.row {
            // If ask layout cell which not isset,
            // shouldn\'t call super.
            return nil
       }
   }
   return super.layoutAttributesForItem(at: indexPath)
}
');

HTMLElements::text('
`.insertAtDestinationIndexPath` работает плохо, если тянуть ячейку из одной коллекции в другую. Приложение крашнется при драге за пределы первой секции, это связано с лейаутом. У таблиц проблем не ловил.
');

HTMLElements::text(
    'Мы закончили первую часть. Когда будет готова вторая, добавлю на неё ссылку. Если нужен ролик по теме или остались вопросы - пишите в комментариях к посту в ' . HTMLElements::embeddedTelegramPostLink("55", 'телеграм-канале') . '.'
);

HTMLElements::tutorialFooter($tutorial);