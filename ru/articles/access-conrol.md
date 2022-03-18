В этой статье рассмотрим уровни доступа и как они работают в практических примерах.

Уровни доступа определяют откуда видны свойства/методы. Когда методов много, вы можете случайно нарушить правила выбранной архитектуры. Если метод закрыт уровнем доступа, вы не вызовите его ошибочно - он будет не доступен.

В `Swift` эти ключевые слова обозначают уровни доступа:
- `public`
- `internal`
- `fileprivate`
- `private`
- `open`

Уровни доступа можно назначать свойствам, структурам, классам, перечислениям и модулям. Указывайте ключевые слова перед объявлением. 

Создадим переменную `name` типа `String` с уровнем доступа `public`:

```swift
public var name: String
```

>У функции уровень доступа должен быть мягче или такой же, как у её параметров.

Далее по тексту я буду использовать слово модули. Модулем может быть приложение, ваша библиотека, таргет. Рассмотрим уровни детально:

## `public`

Уровень `public` используют для фреймворков. Другие модули имеют доступ к публичным свойствам и методам.

>`public` классы не могут быть `суперклассами`, а их свойства и методы нельзя переопределять.

## `internal`

Внутренний уровень стоит по умолчанию для свойств и метдов. Он предоставляет доступ внутри модуля.

Запись ```var number = 3 ``` и ```internal var number = 3 ``` равнозначны. Явно указывать `internal` не требуется.

## `fileprivate`

`fileprivate` дает доступ только объектам в одном файле.

## `private`

`private` ограничивает доступ к свойствам и методам внутри структур, классов и перечислений. Является самым строгим уровнем.

## `open`

`open` похож на `public` - разрешает доступ из других модулей. Испоьзуется только для классов, их свойств и методов.

`open` классы наследуются в определяющем и импортирующем модуле. `open` свойства и методы класса переопределяются подклассами.

## Практика

Можно не использовать уровни доступа, но это снизит безопасность кода. Инкапсюлированный код показывает какая часть кода является внутренней реализацией. Для команд, где каждый работает над своей частью, это критично. Рассмотрим примеры использования уровней:

### `private` свойства в структурах и классах

`private` свойства читаются и записываются только в структурах и классах. Сделаем игру, где нужно дать правильный ответ.

Создадим структуру `Test` с вопросом и ответом. Ответ будем сравнивать с ответом пользователя.

```swift
struct Test {
    let question = "Столица Перу?"
    let answer = "Лима"
}
```

Создадим экземпляр `Test` с именем `test` и узнаем вопрос:

```swift
let test = Test()
print(test.question) // Столица Перу?
```

Мы знаем вопрос и знаем, как посмотреть ответ:

```swift
print(test.answer) // Лима
```

Игрок не должен иметь доступ к ответу. Укажем уровень `private` для свойства `answer`.

```swift
struct Test {
    let question = "Столица Перу?"
    private let answer = "Лима"
}
```

Посмотрим вывод:

```swift
print(test.question) // Столица Перу?
print(test.answer) // Ошибка: 'answer' is inaccessible due to 'private' protection level
```

Мы получили ошибку: `answer` недоступен из-за уровня доступа `private`. Поведение `private` свойств в классах аналогично. Прочесть свойство `answer` могут только члены структуры `Test`. Создадим метод `showAnswer` для вывода ответа на экран:

```swift
struct Test {

    func showAnswer() {
        print(answer)
    }
}
```

Теперь получим `answer` не напрямую:

``` swift
test.showAnswer() // Лима
```

### `private` методы в структарах и классах

Указывайте методам `private`, когда работаете с конфиденциальными данными. Это спрячет реализацию. Создадим переменные `gamerAnswer` и `result` с начальными значениями `""`. `result` сделаем `private`:

```swift
struct Test {
    let question = "Столица Перу?"
    private let answer = "Лима"
    var gamerAnswer = ""
    private var result = ""
}
```

Понадобятся два метода: 
- `compareAnswer()` - сравнивает ответ игрока с правильным ответом, перезаписывает значение свойства `result`
- `getResult()` - выводит значение `result` на экран

У нас будет доступ к `getResult()` снаружи структуры `Test`, а вот `compareAnswer()` сделаем `private`.

``` swift
struct Test {
        
    //...
    
    private mutating func compareAnswer() {
        switch gamerAnswer {
        case "":
            result = "Вы не ответили на вопрос."
        case answer:
            result = "Ответ верный!"
        default:
            result = "Ответ неверный."
        }
    }
    
    mutating func getResult() {
        compareAnswer()
        print(result)
    }
}
```

Играем!

```swift
var test = Test()
print(test.question) // Столица Перу?
test.gamerAnswer = "Лима"
test.getResult() // Ответ верный!
```

## Отличие `private` от `fileprivate`

Рассмотрим отличие `fileprivate` от `private`. Создадим два файла: `File1.swift` и `File2.swift`. `File1.swift` содержит структуры `Constants` и `PrinterConstants`:

```swift
struct Constants {

    static let decade = 10
    static let exp = 2.72
}

struct PrinterConstants {

    func printDecade() {
        print(Constants.decade)
        print(Constants.exp)
    }
}
```

`File2.swift` содержит структуру `PrinterConstantsFromOuterFile`:

```swift
struct PrinterConstantsFromOuterFile {

    func printConstants() {
        print(Constants.decade)
        print(Constants.exp)
    }
}
```

`static` постоянные структуры `Constants` имеют уровень `internal`. Это позволяет другим структурам из обоих файлов обращаться к ним.

Укажем `private` свойству `Constant.exp`.

```swift
struct Constants {
    
    //...
    
    private static let exp = 2.72
}
```

Теперь структуры `PrinterConstants` и `PrinterConstantsFromOuterFile` не могут обращаться к свойству `Constant.exp`.

Заменим `private` на `fileprivate`:

```swift
struct Constants {
        
    //...
    
    fileprivate static let exp = 2.72
}
```

Структура `PrinterConstantsFromOuterFile` не имеет доступ к свойству `Constatnts.exp`, а `PrinterConstants` - имеет.

Исправим ошибку. Удалим строку `print(Constants.exp)` из структуры `PrinterConstantsFromOuterFile`.

```swift
struct PrinterConstantsFromOuterFile {
    func printConstants() {
        print(Constants.decade)
    }
}
```


## Вычисляемые свойства

Вычисляемые свойства используют другие свойства для возврата значения.

### Read-only

Вычисляемым `read-only` свойством является вычисляемое свойство только с `геттером` (`getter`).

``` swift
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

Приватный `сеттер` используют для ограничения доступа к записи за пределами структуры (класса). Для объявления приватного `сеттера` используем совместно ключевые слова `private` и `(set)`. 

Создадим структуру `Vehicle`. Укажем свойству `numberOfWheels` типа `UInt` приватный `сеттер`:

``` swift
struct Vehicle {

    private(set) var numberOfWheels : UInt
}
```

### Public Private Setter

Можно переписать структуру `Vehicle` иначе. 

``` swift
struct Vehicle {
    public private(set) var numberOfWheels : UInt = 3
}

var kidBike = Vehicle()
print(kidBike.numberOfWheels) // 3
kidBike.numberOfWheels = 2 // Ошибка: cannot assign to property: 'numberOfWheels' setter is inaccessible
```

`геттер` имеет уровень доступа `public`, а `сеттер` - `private`.
