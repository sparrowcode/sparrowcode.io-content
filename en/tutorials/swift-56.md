## Existential any

We often write code like this:

```swift
protocol Vehicle {
    func travel(to destination: String)
}

struct Car: Vehicle {
    func travel(to destination: String) {
        print("I'm driving to \(destination)")
    }
}

let vehicle = Car()
vehicle.travel(to: "London")
```

It’s also possible to use protocols as generic type constraints in functions, meaning that we write code that can work with any kind of data that conforms to a particular protocol. This will work with any kind of type that conforms to Vehicle:

```swift
func travel<T: Vehicle>(to destinations: [String], using vehicle: T) {
    for destination in destinations {
        vehicle.travel(to: destination)
    }
}

travel(to: ["London", "Amarillo"], using: vehicle)
```

When that code compiles, Swift can see we’re calling `travel` with a `Car` instance and so it is able to create optimized code to call the `travel` function directly – a process known as static dispatch.

```swift
let vehicle2: Vehicle = Car()
vehicle2.travel(to: "Glasgow")
```

Here we are still creating a `Car` struct, but we’re storing it as a `Vehicle`.  `Vehicle` type is a whole other thing called an existential type: a new data type that is able to hold any value of any type that conforms to the `Vehicle` protocol.

Existential types are different from `opaque` types that use the `some` keyword, e.g. `some View`.

We can use existential types with functions too, like this::

```swift
func travel2(to destinations: [String], using vehicle: Vehicle) {
    for destination in destinations {
        vehicle.travel(to: destination)
    }
}
```

That might look similar to the other `travel` function, but as this one accepts any kind of `Vehicle` object Swift can no longer perform the same set of optimizations – it has to use a process called dynamic dispatch, which is less efficient than the static dispatch available in the generic equivalent.

Swift 5.6 introduces a new `any` keyword for use with existential types, so that we’re explicitly acknowledging the impact of existentials in our code:

```swift
let vehicle3: any Vehicle = Car()
vehicle3.travel(to: "Glasgow")

func travel3(to destinations: [String], using vehicle: any Vehicle) {
    for destination in destinations {
        vehicle.travel(to: destination)
    }
}
```

## Type placeholders `_`

Here's an example:

```swift
let num: Int = 5 // num: Int = 5
let num: _ = 5 // num: Int = 5

let dict: [Int: _] = [0: 10, 1: 20, 2: 30] // dict: [Int: Int]
let dict: [_: String] = [0: "zero", 1: "one", 2: "two"] // dict: [Int: String]


Array<_> // array with placeholder element type
[Int: _] // dictionary with placeholder value type
(_) -> Int // function type accepting a single type placeholder argument and returning 'Int'
(_, Double) // tuple type of placeholder and 'Double'
_? // optional wrapping a type placeholder
```

Type placeholder cannot be applied to the return type:

```swift
struct Player<T: Numeric> {
    var name: String
    var score: T
}

func createPlayer() -> _ {
    Player(name: "Anonymous", score: 0)
}

// error: type placeholder may not appear in function return type.
// note: replace the placeholder with the inferred type 'Player<Int>'.
```

Think of type placeholders as a way of simplifying long type annotations.

## `CodingKeyRepresentable` protocol

Look at the code:

```swift
import Foundation

enum OldSettings: String, Codable {
    case name
    case twitter
}

let oldDict: [OldSettings: String] = [.name: "Paul", .twitter: "@twostraws"]
let oldData = try JSONEncoder().encode(oldDict)
print(String(decoding: oldData, as: UTF8.self))

/*
oldDict: [OldSettings : String] = 2 key/value pairs {
  [0] = {
    key = name
    value = "Paul"
  }
  [1] = {
    key = twitter
    value = "@twostraws"
  }
}
*/

// Print: ["name","Paul","twitter","@twostraws"]
```

Although the enum has a `String` raw value, because the `oldDict` keys aren’t String or Int the resulting string will be `["twitter","@twostraws","name","Paul"]` – four separate string values, rather than something that is obviously key/value pairs.

The new `CodingKeyRepresentable` resolves this, allowing the new dictionary keys to be written correctly:

```swift
enum NewSettings: String, Codable, CodingKeyRepresentable {
    case name
    case twitter
}

let newDict: [NewSettings: String] = [.name: "Paul", .twitter: "@twostraws"]
let newData = try! JSONEncoder().encode(newDict)
print(String(decoding: newData, as: UTF8.self))

// Print: {"twitter":"@twostraws","name":"Paul”}
```

## Unavailability condition

introduces an inverted form of `#available` called `#unavailable`:

```swift
if #unavailable(iOS 15) {
    // Code to make iOS 14 and earlier work correctly
}
```

Apart from their flipped behavior, one key difference between `#available` and `#unavailable` is the platform wildcard `*`. The platform wildcard is not allowed with `#unavailable`: only platforms you specifically list are considered for the test. The code below won't compile:

```swift
if #unavailable(iOS 15, *) {
    // error: platform wildcard '*' is always implicit in #unavailable
}
```

## Concurrency changes

Swift 5.6 introduced all-new ways to prevent data races, including the introduction of the Sendable protocol. Sendable is a way to mark values that can be used across different actors and prevent data from colliding:

```swift
class MyCounter {
  var value = 0
}

func f() -> MyCounter {
  let counter = MyCounter()
  Task {
    counter.value += 1  // warning: capture of non-Sendable type 'MyCounter'
  }
  return counter
}
```
