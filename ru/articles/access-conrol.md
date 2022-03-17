В этой статье мы рассмотрим уровни доступа, выделим основные случаи их применения.

## Безопасность кода

Безопасность кода очень важна. Для написания безопасного кода необходимо  определить, какие его части могут иметь доступ к свойствам и методам, считывать и записывать в них значения, а также выполнять эти методы. Такой подход позволяет защитить данные от изменений, чтения и некорректного использования. Это повышает надёжность кода, даёт возможность вручную управляем областями видимости.

В `Swift` существуют ключевые слова, обозначающие `уровни доступа`:
- `public`;
- `internal`;
- `fileprivate`;
- `private`;
- `open`.

Уровни доступа можно назначать свойствам, структурам, классам, перечислениям и модулям.

Для обозначения уровня доступа необходимо перед объявлением указать соответсвующее ключевое слово. Создадим переменную `name` типа `String` с уровнем доступа `public`:

```swift
public var name: String
```

>**Примечание.** Функция должна обладать тем же уровнем доступа, что и её параметры, или менее строгим. 

Рассмотрим уровни.

## Public

Уровень `public` удобен при создании фреймворков или библиотек. Сторонние модули получают доступ к свойствам и методам этих фреймворков. Он предоставляет доступ изнутри и снаружи модуля.

>**Примечание.** `public` классы не могут быть `суперклассами`, а их свойства и методы не могут быть переопределены.

## Internal

`internal` - внутренний уровень. Все свойства и методы имеют этот уровень по умолчанию, если явно не указан другой. Он предоставляет доступ внутри модуля.

Запись ```var number = 3 ``` и ```internal var number = 3 ``` равнозначны. Явно указывать `internal` не требуется.

## Fileprivate

`fileprivate` предоставляет доступ к свойствам и методам только объектам в исходном в файле.

## Private

`private` ограничивает доступ к свойствам и методам внутри структур, классов и перечислений. Является самым строгим уровнем.

## Open

`open` схож с `public`. Он разрешает доступ из других модулей. Отличие в том, что это относится исключительно к классам, их свойствам и методам.

`open` классы могут наследоваться в определяющем и импортирующем модуле. `open` свойства и методы класса переопределяются подклассами также.

## Применение

Без контроля доступа обойтись можно, но это снизит безопасность и надёжность кода. Безопасный код легче понимать. Он важен в командной разработке, помогает легче ориентироваться в чужих и собственных проектах.

Рассмотрим основные случаи использования уровней. Наибольшее внимание уделим `private`.

## Private свойства в структурах и классах

`private` свойства читаются и записываются только в их структурах и классах.


Мы решили создать игру, цель которой - дать правильный ответ. 

Создадим структуру `Test` с одним вопросом и ответом на него. Ответ нужен для сравнения с ответом пользователя, так игра определяет верный ли он.

```swift
struct Test {
    let question = "Столица Перу?"
    let answer = "Лима"
}
```

Создадим экземпляр `Test` с именем `test` и посмотрим вопрос.

```swift
let test = Test()
print(test.question) // Столица Перу?
```

Мы знаем вопрос и знаем, как посмотреть ответ.

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

Посмотрим изменения.

```swift
print(test.question) // Столица Перу?
print(test.answer) // Ошибка: 'answer' is inaccessible due to 'private' protection level
```

Мы получили ошибку: `answer` недоступен из-за уровня доступа `private`.

Поведение `private` свойств в классах аналогично.

Прочесть свойство `answer` могут только члены структуры `Test`. Создадим метод `showAnswer` для вывода ответ на экран.

```swift
struct Test {
    ...

    func showAnswer() {
        print(answer)
    }
}
```

Теперь можно получить `answer` не напрямую.

``` swift
test.showAnswer() // Лима
```

## Private методы в структарах и классах

Когда работаете с конфиденциальными данными, укажите методам `private` - это скроет вычисления.

Видоизменим структуру `Test`. Создадим переменные `gamerAnswer`  и `result` с начальными значениями `""`. `result` сделаем `private`.

```swift
struct Test {
    let question = "Столица Перу?"
    private let answer = "Лима"
    var gamerAnswer = ""
    private var result = ""
}
```

Нам понадобятся два метода: 
- `compareAnswer()` - сравнивает ответ игрока с правильным ответом, перезаписывает значение свойства `result`;
- `getResult()` - выводит значение `result` на экран.

У нас будет доступ к `getResult()` снаружи структуры `Test`, а вот `compareAnswer()` сделаем `private`.

``` swift
struct Test {
    ...
    
    private mutating func compareAnswer() {
        switch gamerAnswer {
        case "":
            result = "Вы не ответили на вопрос."
            break
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

## Вычисляемые свойства

Вычисляемые свойства хранят значение не напрямую. Они используют другие свойства и постоянные для вычисления и возврата значения.

### Read-only (только для чтения)

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

Приватный `сеттер` используют для ограничения доступа к записи свойства за пределами структуры (класса).

Для объявления приватного `сеттера` используем совместно ключевые слова `private` и `(set)`. 

Создадим структуру `Vehicle`. Укажем свойству `numberOfWheels` типа `UInt` приватный `сеттер`.

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

## Private в MVVM

`private` играет важную роль в шаблоне проектирования `MVVM` (Model-View-ViewModel). Данные, модель данных и представление разделяются так, чтобы представление не имело доступа к данным напрямую. `private` позволяет сделать это правильно, надёжно и безопасно.

## Подробнее о fileprivate

Рассмотрим отличие `fileprivate` от `private`. Создадим два файла: `File1.swift` и `File2.swift`.

`File1.swift` содержит структуры `Constants` и `PrinterConstants`:

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
    ...
    private static let exp = 2.72
}
```

Теперь структуры `PrinterConstants` и `PrinterConstantsFromOuterFile` не могут обращаться к свойству `Constant.exp`.

Заменим `private` на `fileprivate`:

```swift
struct Constants {
    ...
    fileprivate static let exp = 2.72
}
```

Структура `PrinterConstantsFromOuterFile` по-прежнему не имеет доступ к свойству `Constatnts.exp`, а `PrinterConstants` - имеет.

Исправим ошибку. Удалим строку `print(Constants.exp)` из структуры `PrinterConstantsFromOuterFile`.

```swift
struct PrinterConstantsFromOuterFile {
    func printConstants() {
        print(Constants.decade)
    }
}
```