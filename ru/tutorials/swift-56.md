# Что нового в Swift 5.6

## Ключевое слово `any` для экзистенциальных (existential) типов.

Обычно мы реализуем протокол так:

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

Возможно использовать протоколы в качестве обобщений (Generic).
Код ниже будет работать с любым типом, соответствующим протоколу `Vehicle`:

```swift
func travel<T: Vehicle>(to destinations: [String], using vehicle: T) {
    for destination in destinations {
        vehicle.travel(to: destination)
    }
}

travel(to: ["London", "Amarillo"], using: vehicle)
```

Компилятор видит, что мы вызываем функцию `travel` с экземпляром `Car`, поэтому компилятор может создать оптимизированный код для прямого вызова `travel` — процесс известный как статическая диспетчеризация.

Это важно для понимания, потому что существует другой способ использования протоколов:

```swift
let vehicle2: Vehicle = Car()
vehicle2.travel(to: "Glasgow")
```

Создаем структуру `Car`, но храним ее в `Vehicle`. Теперь тип `Vehicle` — экзистенциальный (existential) тип.
Это новый тип данных, который может хранить любое значение любого типи, соответствующее протоколу `Vehicle`.

**Обратите внимание**: Экзистенциальный тип различается от `opaque` типа, который использует ключевое слово `some`, например: `some View`.

Попробуем использовать новый тип с функциями:

```swift
func travel2(to destinations: [String], using vehicle: Vehicle) {
    for destination in destinations {
        vehicle.travel(to: destination)
    }
}
```

Функция `travel2` схожа с функций `travel`, но поскольку эта функция принимает любой объект `Vehicle`, то компилятор не может делать набор оптимизаций.

В Swift 5.6 ввели новое ключевое слово `any` для использования с экзистенциальными типами:

```swift
let vehicle3: any Vehicle = Car()
vehicle3.travel(to: "Glasgow")

func travel3(to destinations: [String], using vehicle: any Vehicle) {
    for destination in destinations {
        vehicle.travel(to: destination)
    }
}
```


## Аннотация неявного типа с помощью `_`.

Рассмотрим пример ниже:

```swift
let num: Int = 5 // num: Int = 5
let num: _ = 5 // num: Int = 5

let dict: [Int: _] = [0: 10, 1: 20, 2: 30] // dict: [Int: Int]
let dict: [_: String] = [0: "zero", 1: "one", 2: "two"] // dict: [Int: String]


Array<_> // массив с неявным типом
[Int: _] // словарь
(_) -> Int // функция принимающая неявный тип и возвращающая 'Int'
(_, Double) // кортеж неявного типа и 'Double'
_? // опциональный неявный тип
```

Неявный тип нельзя применять к возвращаемому типу функций:

```swift
struct Player<T: Numeric> {
    var name: String
    var score: T
}

func createPlayer() -> _ {
    Player(name: "Anonymous", score: 0)
}

// ошибка: возвращаемый тип функции не может быть неявным.
// примечание: замените тип `_` на ожидаемый `Player<Int>`.
```

Неявный тип — способ упростить аннотацию длинных типов с помощью нижнего подчеркивания, чтобы сделать код более читаемым.

## Протокол `CodingKeyRepresentable`.

Рассмотрим на примере:

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

// Выведет: ["name","Paul","twitter","@twostraws"]
```

Перечисление имеет тип String в качестве сырого (raw) значения, но ключи словаря `oldDict` не являются типом String или Int. В результате получаем 4 отдельных значения, а не key/value (ключ/значение).

Новый протокол `CodingKeyRepresentable` решает эту проблему:

```swift
enum NewSettings: String, Codable, CodingKeyRepresentable {
    case name
    case twitter
}

let newDict: [NewSettings: String] = [.name: "Paul", .twitter: "@twostraws"]
let newData = try! JSONEncoder().encode(newDict)
print(String(decoding: newData, as: UTF8.self))

// Выведет: {"twitter":"@twostraws","name":"Paul”}
```

## Атрибут недоступности.

Появилась противоположная форма `#available` — `#unavailable`:

```swift
if #unavailable(iOS 15) {
    // Работающий код для iOS 14 и ниже.
}
```

Ключевое различие между `#available` и `#unavailable` в звездочке.
Нет необходимости писать `if #unavailable(iOS 15, *)`, потому что `unavailable` уже подразумевает знак платформы, поэтому код ниже не скомпилируется:

```swift
if #unavailable(iOS 15, *) {
    // error: platform wildcard '*' is always implicit in #unavailable
}
```

## Изменения в параллелизме

Компилятор будет уведомлять о возможной гонки данных (data race) когда не-Sendable тип передается в actor или task:

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
