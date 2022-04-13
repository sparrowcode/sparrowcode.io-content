Access levels determine the availability of objects and methods. If an object is locked by an access level, it cannot be accessed by mistake, it simply will not be available. Of course, it is possible to ignore access levels, but this will reduce the security of the code. Encapsulated code shows which part of the code is an internal implementation. This is critical for teams where everyone is working on a part of the project.

In Swift, these keywords denote access levels:
- `public`
- `internal`
- `fileprivate`
- `private`
- `open`

Access levels can be assigned to properties, structures, classes, enumerations, and modules. Specify keywords before the declaration. Later in the text I will use the word "modules". A module can be an application, a library, or a target. 

## internal

The internal level is the default for properties and methods and provides access within the module. It is not necessary to explicitly specify `internal'.

These entries are equivalent:

```swift
var number = 3

internal var number = 3
``` 

Objects with `internal` cannot be accessed from another module:

![Objects of classes `A`, `B` and `C` can be created in a new source module file, but cannot be used in another module.](https://cdn.sparrowcode.io/tutorials/access-control/internal.png)

## public

It is usually used for frameworks. Modules have access to public objects of other modules.

>Beyond the source module `public` classes cannot be superclasses and their properties and methods cannot be overridden.

![Classes `A', `B` and `C` cannot be superclasses. Their objects can be created in a new file of the source and another module, but properties and methods cannot be overridden outside of the source.](https://cdn.sparrowcode.io/tutorials/access-control/public.png)

## open

Similar to `public` - allows access from other modules. Used only for classes, their properties and methods.

>In both the defining and importing module, `open` classes can be superclasses, and their properties and methods can be overridden by subclasses.

![Objects of classes `A`, `B` and `C` can be created either in a new source module file or in another module.](https://cdn.sparrowcode.io/tutorials/access-control/open.png)

## private

Limits access to properties and methods within structures, classes and enumerations. `private` is the strictest level, it hides auxiliary logic.

![`prop1` can be used in another source module file, and `private prop2` only in the class in which it was created.](https://cdn.sparrowcode.io/tutorials/access-control/private.png)

### For properties

`private` properties are read and written only in their structures and classes. 

Let's write a game where you have to give the right answer. Create a structure `Test` with a question and an answer. The answer will be compared to the user's answer.

```swift
struct Test {

    let question = "Capital of Peru?"
    let answer = "Lima"
}
```

Create an instance of `Test` with the name `test` and print the question:

```swift
let test = Test()
print(test.question) // The capital of Peru?
```

We know the question and we know how to look up the answer:

```swift
print(test.answer) // Lima
```

The player must not have access to the answer - let's specify the `private` level for the `answer` property.

```swift
struct Test {

    let question = "Capital of Peru?"
    private let answer = "Lima"
}
```

Print out the conclusion:

```swift
print(test.question) // The capital of Peru?
print(test.answer) // Error: "answer" is unavailable because of the "private" security level
```

We got an error: `answer` is unavailable because of the `private` access level. The behavior of `private` properties in classes is similar. Only members of the `Test` structure can read the `answer` property. Let's create a method `showAnswer` to display the answer on the screen:

```swift
struct Test {

    // ...

    func showAnswer() {
        print(answer)
    }
}
```

Checking:

```swift
test.showAnswer() // Lima
```

### For methods

When working with sensitive data, specify methods `private` to hide the implementation. Let's create variables `gamerAnswer` and `result` of type `String` with empty initial values. Make `result` as `private`:

```swift
struct Test {

    let question = "Capital of Peru?"
    private let answer = "Lima"
    var gamerAnswer = ""
    private var result = ""

    // ...
}
```

We will need two methods: 
- `compareAnswer()` - compares the player's answer to the correct answer, overwrites the value of the `result` property
- `getResult()` - displays the value of `result` on the screen

We will have access to `getResult()` outside the `Test` structure, but make `compareAnswer()` `private`.

```swift
struct Test {

    // ...
    
    private mutating func compareAnswer() {
        switch gamerAnswer {
        case "":
            result = "You did not answer the question".
        case answer:
            result = "The answer is correct!"
        default:
            result = "The answer is incorrect."
        }
    }
    
    mutating func getResult() {
        compareAnswer()
        print(result)
    }
}
```

Let's play!

```swift
var test = Test()
print(test.question) // "The capital of Peru?"
test.gamerAnswer = "Lima"
test.getResult() // "The answer is correct!"
```

## fileprivate

Similar to `private`. Only objects from the same file have access to objects at this level. The `fileprivate` comes in handy when we need additional objects or calculations within the same file.

![`prop1` can be used in another file of the source module, and `fileprivate prop2` only in the file in which it was created.](https://cdn.sparrowcode.io/tutorials/access-control/fileprivate.png)

### Difference from `private'

Create two files: `File1.swift` and `File2.swift`. In the first file the structures `Constants` and `PrinterConstants`:

```swift
struct Constants {

    static let decade = 10
    static let exp = 2.72
}

struct PrinterConstants {

    func printConstants() {
        print(Constants.decade)
        print(Constants.exp)
    }
}
```

In `File2.swift` the structure `PrinterConstantsFromOuterFile`:

```swift
struct PrinterConstantsFromOuterFile {

    func printConstants() {
        print(Constants.decade)
        print(Constants.exp)
    }
}
```

The `static` persistent structures of `Constants` have an `internal` level. This allows other structures from both files to refer to them. Let's specify `private` to the `Constant.exp` property.

```swift
struct Constants {

    // ...
    
    private static let exp = 2.72
}
```

Now the structures `PrinterConstants` and `PrinterConstantsFromOuterFile` cannot access the property `Constant.exp`. Replace `private` with `fileprivate`:

```swift
struct Constants {

    // ...
    
    fileprivate static let exp = 2.72
}
```

The `PrinterConstantsFromOuterFile` structure does not have access to the `Constatnts.exp` property, while `PrinterConstants` does. Let's fix the error. Delete the line `print(Constants.exp)` from the `PrinterConstantsFromOuterFile` structure.

```swift
struct PrinterConstantsFromOuterFile {

    func printConstants() {
        print(Constants.decade)
    }
}
```

## Computable properties

Computable properties use other properties to return a value. It is common to make such properties `private` and `public private` levels.

### Read-only

Only properties with `getter` are considered as `read-only` properties.

Create a structure `HappyMultiply`. Calculate the `multipliedHappyLevel` property based on the `private` property `happyLevel` to hide the calculations.

```swift
struct HappyMultiply {

    private var happyLevel: UInt
 
    var multipliedHappyLevel: UInt {
        get {
            return happyLevel != 0 ? happyLevel * 10 : 10
        }
    }
}
```

### Private Setter

The private `setter` is used to restrict access to a record outside the structure (class). To declare a private setter we use together keywords `private` and `set`. Create a structure `Vehicle`. Let's specify a private setter to the `numberOfWheels` property:

```swift
struct Vehicle {

    private(set) var numberOfWheels : UInt
}
```

### Public Private Setter

You can rewrite the `Vehicle` structure differently. 

```swift
struct Vehicle {

    public private(set) var numberOfWheels : UInt = 3
}

var kidBike = Vehicle()
print(kidBike.numberOfWheels) // 3
kidBike.numberOfWheels = 2 // Error: cannot assign to property: 'numberOfWheels' setter is inaccessible
```

The `Getter` has a `public` access level and the `setter` has a `private` access level.

## Modules and frameworks

We want to create a module `Tools` with writing accessories. Let's create an `internal` class `WritingTool` with properties `name`, `inscription` and method `write(word: String)`.

- `name` is a constant of type `String`, the name of the tool
- `inscription` - a variable of type `String` with an empty initial value, the inscription
- `write(word: String)` adds `word` to `inscription`

```swift
class WritingTool {

    let name: String
    var inscription = ""
    
    init(name: String) {
        self.name = name
    }
    
    func write(word: String) {
        inscription += word
    }
}
```

Within a module, anywhere in the project, we create a subclass based on it.

```swift
class Pencil: WritingTool {

    func clear() {
        inscription = ""
    }
}
```

You can create an instance of the `Pencil` class anywhere in the module.

```swift
let redPencil = Pencil(name: "red pencil")
redPencil.write(word: "writing by pencil")
print(redPencil.inscription) // "writing by pencil"
redPencil.clear()
print(redPencil.inscription) // ""
```

>The `WritingTool` and `Pencil` classes are only available inside our module because of the `internal` level. For our task `internal` is not suitable.

Let's change the class level of `Pencil` to `public`.

```swift
public class Pencil: WritingTool {}
```

We get an error: «Class cannot be declared public because its superclass is internal». 

>The level of a subclass must not be softer than the level of its superclass.

Let's change the level of the `WritingTool` class to `public`.

```swift
public class WritingTool {}
```

You can now import the module into other projects and use the `WritingTool` and `Pencil` classes.

```swift
import Tools

let redPencil = Pencil(name: "red pencil")
redPencil.write(word: "writing by pencil")
print(redPencil.inscription) // "writing by pencil"
redPencil.clear()
print(redPencil.inscription) // ""
```

In the new project, we want to create a class `Pen` that inherits from `WritingTool`.

>`public` does not allow the classes `WritingTool` and `Pencil` to be superclasses outside the `Tools` module. Another level is needed.

In the `Tools` module, change the level of the `WritingTool` class to `open`.

```swift
open class WritingTool {}
```

In the new project you can now create a class `Pen: WritingTool`.

```swift
import Tools

class Pen: WritingTool {

    var inkColor: CGColor = .black
    
    func changeInk(color: CGColor) {
        inkColor = color
    }
}
```

We left the class `Pencil` with the level `public`. It can be used in a new project, but it cannot be a superclass in it.

```swift
import Tools

class Pen: WritingTool {}

let greenPencil = Pencil(name: "green pencil")
let pen = Pen(name: "pen")
```

Properties and methods of class `WritingTool` (`open` level) can be overridden by classes `Pen` and `Pencil`. Properties and methods of class `Pencil` (`public` level) can be overridden only by its subclasses in module `Tools`.

## Tuples

The access level of a tuple is calculated based on the levels of its member types and gets the most stringent level of all its member types.

Consider an example:

```swift
struct A {
    
    let one = 1
    private let two = 2
    var toupleOneTwo: (Int, Int)
    
    init () {
        self.toupleOneTwo = (one, two)
    }
}

let a = A()
a.one // 1
a.toupleOneTwo // (.0 1, .1 2)
```

In structure `A` the property `one` has the level `internal` and the property `two` has the level `private`. The tuple `toupleOneTwo` is accessible from outside the structure `A`. For `toupleOneTwo` we specified the type `(Int, Int)`, and passed values of properties `one` and `two`, rather than trying to access the `private` property of `two` from outside. 

Let's move on to the definition of `Int':

```swift
@frozen public struct Int : FixedWidthInteger, SignedInteger { 

    // ... 
}
```

It follows from this definition that the tuple `toupleOneTwo` has a `public` level. Then it must be accessible outside the defining module. But the structure `A` itself, as well as its instance `a`, has the level `internal`, so it will not be accessible in another module, nor will the property `toupleOneTwo`.

Another example. Create two structures: `Letters` - `fileprivate`, `Numbers` - `private`.

```swift
fileprivate struct Letters {
	
    var userLetter: Character
}

private struct Numbers {
	
    var userNumber: UInt8
}
```

Now write an `internal` structure `Info` whose `userInfo` property is of type `(Letters, Numbers)`.

```swift
struct Info {
	
    var userInfo: (Letters, Numbers)
}
```

We get the error "property must be declared fileprivate because its type uses a private type". In this case, for the file in which we declared the `Letters` and `Numbers` structures, their `fileprivate` and `private` levels are equivalent - providing access only inside the file. Therefore, `userInfo` does not automatically get the `private` level, even though it is stricter than `fileprivate`. We can use either of these two levels for `userInfo`.

```swift
struct Info {
	
    fileprivate var userInfo: (Letters, Numbers)
}
```

You can now create an instance of the `Info` structure.

```swift
let info = Info(userInfo: (Letters(userLetter: "A"), Numbers(userNumber: 1)))
```

Change `fileprivate` to `private`.

```swift
struct Info {
	
    private var userInfo: (Letters, Numbers)
}
```

We get an error "'Info' initializer is inaccessible due to `private` protection level". We cannot create an instance of this structure because of the `private` level of the `userInfo` property. The types in the tuple allow us to make this property `private`, but we cannot use it.
