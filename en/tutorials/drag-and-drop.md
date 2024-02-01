Today we will learn how to change the order of cells, drag and drop cells in groups, move cells between collections and even between applications. Let's look at `UICollectionView` and `UITableView`.

Before diving into the code, let's understand how the life cycle of drag and drop is arranged.

![A still from the movie «Fast & Furious Presents: Hobbs & Shaw».](https://cdn.sparrowcode.io/tutorials/drag-and-drop/preview.jpg)

# Models

The drag is responsible for moving the object, and the drop is responsible for resetting the object and a new position. When a finger with a cell crawls across the screen, the delegate method is called. Very similar to `UIScrollViewDelegate` with `scrollViewDidScroll` method.

`UIDragSession` and `UIDropSession` are wrapper objects with information about finger position, objects for which actions were taken, custom context, etc. Provide a `UIDragItem` object before starting the drag. It should not be a cell class. Pass in an object that represents the data - for example a pizza model if you have a collection with pizzas.

```swift
let itemProvider = NSItemProvider.init(object: yourObject)
let dragItem = UIDragItem(itemProvider: itemProvider)
dragItem.localObject = action
return dragItem
```

To allow the provider to accept any object, implement the `NSItemProviderWriting` protocol:

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

We're ready.

# Drag

## One cell

Let's take a collection as an example. I advise you to use `UICollectionViewController`, it does more out of the box. But a simple collection view will do just as well.

Let's set up a drag-delegate:

```swift
class CollectionController: UICollectionViewController {
    
    func viewDidLoad() {
        super.viewDidLoad()
        collectionView.dragDelegate = self
    }
}
```

Let's implement the `UICollectionViewDragDelegate` protocol. The first will be the method `itemsForBeginning`:

```swift
func collectionView(_ collectionView: UICollectionView, itemsForBeginning session: UIDragSession, at indexPath: IndexPath) -> [UIDragItem] {
    let itemProvider = NSItemProvider.init(object: yourObject)
    let dragItem = UIDragItem(itemProvider: itemProvider)
    dragItem.localObject = action
    return dragItem
}
```

You have already seen this code above. It wraps our object in `UIDragItem`. The method is called when we suspect that the user wants to start a drag. 

> Do not use this method as the start of drag, because calling it only assumes that drag will start.

Let's add two more methods — `dragSessionWillBegin` and `dragSessionDidEnd`:

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

The first method is called when drag has started and the second method is called when drag is over. Before `dragSessionWillBegin` the `itemsForBeginning` method is called. But it is not certain that if `itemsForBeginning` is called, the `dragSessionWillBegin` method will be called. If you want to update the interface for the duration of the drag, for example to hide the delete buttons, `dragSessionWillBegin` is the right place. 

Let's see what we get at this stage.

[The beginning and end of the drag.](https://cdn.sparrowcode.io/tutorials/drag-and-drop/drag-delegate.mov)

The cell returns to its place because the drop is not yet ready, we implement it further.

## Multiple Cells

In the `UICollectionViewDragDelegate` protocol, we implemented the `itemsForBeginning` method, which returned a drag object. To add more objects to the current drag, implement the `itemsForAddingTo` method:

```swift
func collectionView(_ collectionView: UICollectionView, itemsForAddingTo session: UIDragSession, at indexPath: IndexPath, point: CGPoint) -> [UIDragItem] {
    // The code is similar. Create an `UIDragItem` based on our object:
    let itemProvider = NSItemProvider.init(object: yourObject)
    let dragItem = UIDragItem(itemProvider: itemProvider)
    dragItem.localObject = action
    return dragItem
}
```

The cells are now stacked. The stack can be reset as individual cells.

[Collecting cells in a stack during drag.](https://cdn.sparrowcode.io/tutorials/drag-and-drop/drag-stack.mov)

# Drop

## For `CollectionView`

Drag is half the battle. Now let's learn how to drop a cell. Let's implement the `UICollectionViewDropDelegate` protocol:

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

The first method requires the `UICollectionViewDropProposal` object to be returned. The method is responsible for previewing and updating the interface, telling the user what will happen if the drop is done now.

It is possible to return one of several statuses, let's analyze each one:

```swift
// The cell will return to its place, no visual indicators will appear. The action does not move other cells.
return .init(operation: .cancel)

// A gray crossed out icon will appear. This means that the operation is prohibited.
return .init(operation: .forbidden)

// A useful action will take place, no visual indicators will appear.
return .init(operation: .move)

// Cells are shifted for the proposed drop location, no visual indicators will appear.
return .init(operation: .move, intent: .insertAtDestinationIndexPath)

// A green plus sign appears - the copying indicator.
return .init(operation: .copy)
```

In our example, if there is a predictable `IndexPath`, we allow resetting. If not - we forbid it. It would be better to put cancellation, but it will be more clear.

```swift
func collectionView(_ collectionView: UICollectionView, dropSessionDidUpdate session: UIDropSession, withDestinationIndexPath destinationIndexPath: IndexPath?) -> UICollectionViewDropProposal {
        
    guard let _ = destinationIndexPath else { return .init(operation: .forbidden) }
    return .init(operation: .move, intent: .insertAtDestinationIndexPath)
}
```

`destinationIndexPath` — System calculation where a cell can be dropped. It is not binding to anything; moreover, we can drop it somewhere else. 

Now let's move on to the next method `performDropWith`. Here we do the most important things: change the data, rearrange the cells, and notify the system where the view was dropped so that the system draws the animation.

```swift
func collectionView(_ collectionView: UICollectionView, performDropWith coordinator: UICollectionViewDropCoordinator) {
        
    // If the system could not determine the IndexPath, then stop execution.
    // We will learn how to determine the index on our own, but we'll leave it that way for now.
    guard let destinationIndexPath = coordinator.destinationIndexPath else { return }
        
    for item in coordinator.items {
        // Gain access to our object, bring the type.
        guard let yourObject = item.dragItem.localObject as? YourClass else { continue }
        // We move the object from one place to another. I use a fake function, implying custom logic:
        move(object: yourObject, to: destinationIndexPath)
    }
        
    // Don't forget to update the collection.
    // If you use a classic data source, make changes in the `performBatchUpdates` block.
    // If you have a diffable data source, use snapshot updates.
    // The function is for example, there is no such function.
    collectionView.reloadAnimatable()
        
    // Notify where the element is dumped to.
    // Implement the `getIndexPath` function yourself.
    for item in coordinator.items {
        guard let yourObject = item.dragItem.localObject as? YourClass else { continue }
        if let indexPath = getIndexPath(for: yourObject) {
            coordinator.drop(item.dragItem, toItemAt: indexPath)
        }
    }
}
```

Now the collection and data source are updated when you move it, and the cell is dropped at the new index. Let's see what happened:

[Moving and dropping a cell into the collection.](https://cdn.sparrowcode.io/tutorials/drag-and-drop/drop-delegate.mov)

To make the cells split to drop another cell, use Drop Proposal with `.insertAtDestinationIndexPath`. Any other intent won't do this. Sometimes bugs with collection, be careful.

When you try to drop a cell last `FlowLayout` will ask for nonexistent cell attributes. When cells are collapsed, the layout draws a cell inside, and the dropout results in more cells than the models in the Data Source. This is solved by overriding the method in `UICollectionViewFlowLayout`:

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

`.insertAtDestinationIndexPath` works poorly when pulling a cell from one collection to another. The application crashes when dragging outside the first section, this is related to the layout. Tables have no problem.

## For `TableView`

For a table, there are similar protocols `UITableViewDragDelegate` and `UITableViewDropDelegate`. The methods are repeated with a disclaimer on the table:

```swift
public protocol UITableViewDragDelegate: NSObjectProtocol {
    
    optional func tableView(_ tableView: UITableView, itemsForAddingTo session: UIDragSession, at indexPath: IndexPath, point: CGPoint) -> [UIDragItem]
    
    optional func tableView(_ tableView: UITableView, dragSessionWillBegin session: UIDragSession)
    
    optional func tableView(_ tableView: UITableView, dragSessionDidEnd session: UIDragSession)
}
```

Drop works without crutches in the table, I suspect this is due to the lack of leyout. Editing the table has no effect on drop method calls:

```swift
tableView.isEditing = true
```

That is, you can have a system cell reorder and drop in cells.

[Moving and dropping a cell from a collection to a table.](https://cdn.sparrowcode.io/tutorials/drag-and-drop/table-drop.mov)

# `DestinationIndexPath`

The system parameter `DestinationIndexPath` does not always determine the position perfectly. For example, if you go beyond the edge of the collection content, the system will not offer to reset the cell as the last one.

Let's write a function that can offer its index if the system sentence is `nil`.

```swift
// We use the system index and the drop session as input parameters.
// If the system index is `nil`, then we will have a second index prediction system.

private func getDestinationIndexPath(system passedIndexPath: IndexPath?, session: UIDropSession) -> IndexPath? {
        
    // Here we will try to get the index by drop location.
    // Most often the result will match the system one, but when there is no system one, it may return a good value.
    let systemByLocationIndexPath = collectionView.indexPathForItem(at: session.location(in: collectionView))
        
    // Here is the hardcore. We take the location and look for the closest cell within a radius of 100 points.
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
        
    // Let's return the value in order of priority.
    return passedIndexPath ?? systemByLocationIndexPath ?? customByLocationIndexPath
}
```

Improve the code to update the interface:

```swift
func collectionView(_ collectionView: UICollectionView, dropSessionDidUpdate session: UIDropSession, withDestinationIndexPath destinationIndexPath: IndexPath?) -> UICollectionViewDropProposal {
        
    guard let _ = getDestinationIndexPath(system: destinationIndexPath, session: session) else { return .init(operation: .forbidden) }
    return .init(operation: .move, intent: .insertAtDestinationIndexPath)
}
```

Note: the method will only help with drop. If you use `.insertAtDestinationIndexPath`, you cannot override how cells will be indented.
