We'll learn how to reorder cells, drag and drop multiple cells, move cells between collections, and even between applications.

In this part, we'll cover dragging and dropping for collections and tables. In the next part, we'll see how to drag any views anywhere and handle resetting them. Before we dive, let's break down how the drag and drop lifecycle is designed.

![preview](https://cdn.ivanvorobei.by/websites/sparrowcode.io/drag-and-drop-part-1/preview.jpg)

## Models

Drag is responsible for moving the object, while the drop is responsible for dropping the object and a new position. There is no service responsible for starting a drag. When a finger with a cell crawls across the screen, the delegate method is called. Very similar to `UIScrollViewDelegate` with the `scrollViewDidScroll` method.

The `UIDragSession` and `UIDropSession` become available when the delegate methods are called. These are wrappers with information about finger position, objects for which actions were taken, custom context, and others. Before starting the action, provide the `UIDragItem` object. `UIDragItem` is a wrapper over the data. Literally, that's what we want to drag.

```swift
let itemProvider = NSItemProvider.init(object: yourObject)
let dragItem = UIDragItem(itemProvider: itemProvider)
dragItem.localObject = action
return dragItem
```

Implement the `NSItemProviderWriting` protocol so that the provider can «eat» any object:

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

## Drag

We'll use a collection. It's better to use `UICollectionViewController`, it can do more from the box. A simple view will also do.

Set up a drag delegate:

```swift
class CollectionController: UICollectionViewController {
    
    func viewDidLoad() {
        super.viewDidLoad()
        collectionView.dragDelegate = self
    }
}
```

Let's implement the `UICollectionViewDragDelegate` protocol. The first method will be `itemsForBeginning`:

```swift
func collectionView(_ collectionView: UICollectionView, itemsForBeginning session: UIDragSession, at indexPath: IndexPath) -> [UIDragItem] {
        let itemProvider = NSItemProvider.init(object: yourObject)
        let dragItem = UIDragItem(itemProvider: itemProvider)
        dragItem.localObject = action
        return dragItem
    }
```

You have already seen this code above. It wraps our item in `UIDragItem`. The method is called when we suspect that the user wants to start a drag. Do not use this method as the initial drag, since calling it only assumes that the drag is just about to start.

Let's add two methods — `dragSessionWillBegin` and `dragSessionDidEnd`:

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

The first method is called when drag has been started. The second method is called when drag is over. Before `dragSessionWillBegin` the `itemsForBeginning` method is called. But it is not certain that if `itemsForBeginning` is called, the `dragSessionWillBegin` method will be also called.

If you need to update the interface for the dragging time (hide the buttons), this is the right place. Now, let's see what we get at this point.

[Drag Preview](https://cdn.ivanvorobei.by/websites/sparrowcode.io/drag-and-drop-part-1/drag-delegate.mov)

The cell returns to its original position. We'll take care of the implementation of the drop below.

## Drop

Drag is half the story. Now we're going to learn how to drop a cell to the proper position. Implement the `UICollectionViewDropDelegate` protocol:

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

The first method requires the `UICollectionViewDropProposal` object to be returned. This method is responsible for reviewing and updating the interface, it tells the user what will happen if the drop is done now.

You can return one of several statuses, so let's analyze each one.

```swift
// The cell will return to default, without any visual indicators. The action doesn't displace other cells.
return .init(operation: .cancel)
// A gray icon will appear. This means that the operation is forbidden.
return .init(operation: .forbidden)
// A useful action will occur, the visual indicators will not appear.
return .init(operation: .move)
// Cells are moved for the proposed drop location, no visual indicators will appear.
return .init(operation: .move, intent: .insertAtDestinationIndexPath)
// A green plus appears that looks like a copy indicator.
return .init(operation: .copy)
```

In our example, if there is a predicted IndexPath, we allow the reset. If not, we deny it. It's better to put a cancel, but it will be more clear.

```swift
func collectionView(_ collectionView: UICollectionView, dropSessionDidUpdate session: UIDropSession, withDestinationIndexPath destinationIndexPath: IndexPath?) -> UICollectionViewDropProposal {

    guard let _ = destinationIndexPath else { return .init(operation: .forbidden) }
    return .init(operation: .move, intent: .insertAtDestinationIndexPath)
}
```

The `destinationIndexPath` is a system calculation where a cell can be dropped. It doesn't require anything, and you can drop it somewhere else. Moving on to the next `performDropWith` method.

Now we move on to the important step. We change the data, rearrange the cells, and notify the system where the view was dropped so that the system draws the animation.

```swift
func collectionView(_ collectionView: UICollectionView, performDropWith coordinator: UICollectionViewDropCoordinator) {
    
    // Stop execution if the system could not determine IndexPath.
    // Later we will learn how to determine the index, but now we will leave it that way.
    guard let destinationIndexPath = coordinator.destinationIndexPath else { return }
    
    for item in coordinator.items {
        // Get access to our object and cast a type.
        guard let yourObject = item.dragItem.localObject as? YourClass else { continue }
        // We move the object from one place to another. I use a pseudo function with custom logic:
        move(object: yourObject, to: destinationIndexPath)
    }
    
    // Don't forget to update collection.
    // If you are using a classic data source, make the changes in the `performBatchUpdates` block.
    // If you have a diffable data source, use the snapshot update.
    // The method below doesn't exist.
    collectionView.reloadAnimatable()
    
    // Notify where the element is dumped.
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

[Drag Preview](https://cdn.ivanvorobei.by/websites/sparrowcode.io/drag-and-drop-part-1/drop-delegate.mov)

To make the cells split to drop another cell, use Drop Proposal with `.insertAtDestinationIndexPath`. Any other intent won't do this. Be careful, because sometimes bugs happen with the collection

## Drag multiple cells

In the `UICollectionViewDragDelegate` protocol, we implemented the `itemsForBeginning` method. It returned a drag object. To add more objects to the current drag, implement the `itemsForAddingTo` method:

```swift
func collectionView(_ collectionView: UICollectionView, itemsForAddingTo session: UIDragSession, at indexPath: IndexPath, point: CGPoint) -> [UIDragItem] {
    // Same code.
    // Create an `UIDragItem` based on object.
    let itemProvider = NSItemProvider.init(object: yourObject)
    let dragItem = UIDragItem(itemProvider: itemProvider)
    dragItem.localObject = action
    return dragItem
}
```

Now the cells will be collected in a stack and the group can be moved.

[Drag Stack](https://cdn.ivanvorobei.by/websites/sparrowcode.io/drag-and-drop-part-1/drag-stack.mov)

## Table View

There are similar protocols for table `UITableViewDragDelegate` and `UITableViewDropDelegate`. The methods are repeated with a reference to the table.

```swift
public protocol UITableViewDragDelegate: NSObjectProtocol {

    optional func tableView(_ tableView: UITableView, itemsForAddingTo session: UIDragSession, at indexPath: IndexPath, point: CGPoint) -> [UIDragItem]

    optional func tableView(_ tableView: UITableView, dragSessionWillBegin session: UIDragSession)

    optional func tableView(_ tableView: UITableView, dragSessionDidEnd session: UIDragSession)
}
```

Drop works the same way. Note that drop is more stable in the table, because of missing layouts.

The editing table has no effect on drop method calls.

```swift
tableView.isEditing = true
```

You can have a system cell reorder and drop, for example, inside cells.

[Table Drop](https://cdn.ivanvorobei.by/websites/sparrowcode.io/drag-and-drop-part-1/table-drop.mov)

## DestinationIndexPath

The system parameter `DestinationIndexPath` does not always determine the position perfectly. For example, if you go beyond the edge of the collection content, the system will not suggest resetting the cell as last.

Let's write a function that can suggest its index if the system suggestion equals `nil`.

```swift
// We use the system index and the drop session as input parameters.
// If the system index equals `nil`, then we will have two calculation systems.
private func getDestinationIndexPath(system passedIndexPath: IndexPath?, session: UIDropSession) -> IndexPath? {

            // Try to get an index of the drop location.
            // Most often the result will be the same as the system result, but when the system result is not present, it may return a good value.
            let systemByLocationIndexPath = collectionView.indexPathForItem(at: session.location(in: collectionView))
            
            // The code below is difficult to understand.
            // We take the location and look for the nearest cell within a radius of 100 points.
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
            
            // Return the value in order of priority.
            return passedIndexPath ?? systemByLocationIndexPath ?? customByLocationIndexPath
}
```

We can also improve the code to update the interface:

```swift
func collectionView(_ collectionView: UICollectionView, dropSessionDidUpdate session: UIDropSession, withDestinationIndexPath destinationIndexPath: IndexPath?) -> UICollectionViewDropProposal {

    guard let _ = getDestinationIndexPath(system: destinationIndexPath, session: session) else { return .init(operation: .forbidden) }
    return .init(operation: .move, intent: .insertAtDestinationIndexPath)
}
```

Note: the method will only help with the drop. If you use `.insertAtDestinationIndexPath`, you can't override how cells are indented.

## Issues

Most of the problems are related to the collection, specifically to the layout. Of the known problems, when you try to drop a cell last FlowLayout will ask for nonexistent cell attributes. When cells are expanded, the layout draws a cell inside, and dropping it will result in more cells than the models in the Data Source. This can be solved by overriding the method in `UICollectionViewFlowLayout`:

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

`.insertAtDestinationIndexPath' works poorly when pulling a cell from one collection to another. The application crashes when dragging outside of the first section, this is related to the layout. I haven't found any problems with the tables.

We finished the first part. When the second is ready, I will add a link to it. If you need a video or still have questions write comments to the post in the [telegram](https://t.me/sparrowcode/55).
