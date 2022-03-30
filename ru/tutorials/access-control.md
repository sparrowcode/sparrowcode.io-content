Уровни доступа определяют доступность объектов и методов. Если объект закрыт уровнем доступа, то по ошибке обратиться к нему не получится, он просто не будет доступен. Конечно, можно игнорировать уровни доступа, но это снизит безопасность кода. Инкапсюлированный код показывает, какая часть кода является внутренней реализацией. Это критично для команд, где каждый работает над частью проекта.

В Swift эти ключевые слова обозначают уровни доступа:
- `public`
- `internal`
- `fileprivate`
- `private`
- `open`

Уровни доступа можно назначать свойствам, структурам, классам, перечислениям и модулям. Указывайте ключевые слова перед объявлением. Далее по тексту я буду использовать слово «модули». Модулем может быть приложение, библиотека или таргет. 

## internal

Внутренний уровень стоит по умолчанию для свойств и методов и предоставляет доступ внутри модуля. Явно указывать `internal` не требуется.

Эти записи равнозначны:

```swift
var number = 3 
``` 

```swift
internal var number = 3
```

`internal` объектам не нужны дополнительные разрешения и ограничения.

## public

Обычно его используют для фреймворков. Модули имеют доступ к публичным объектам других модулей.

>За пределами исходного модуля `public`-классы не могут быть суперклассами, а их свойства и методы нельзя переопределять.

## open

Похож на `public` - разрешает доступ из других модулей. Используется только для классов, их свойств и методов.

>`open`-классы наследуются в определяющем и импортирующем модуле, свойства и методы класса переопределяются также подклассами.

## private

Ограничивает доступ к свойствам и методам внутри структур, классов и перечислений. `private` — самый строгий уровень, он скрывает вспомогательную логику.

### Для свойств

`private`-свойства читаются и записываются только в их структурах и классах. 

Давайте напишем игру, где нужно дать правильный ответ. Создадим структуру `Test` с вопросом и ответом. Ответ будем сравнивать с ответом пользователя.

```swift
struct Test {

    let question = "Столица Перу?"
    let answer = "Лима"
}
```

Создадим экземпляр `Test` с именем `test` и распечатаем вопрос:

```swift
let test = Test()
print(test.question) // Столица Перу?
```

Мы знаем вопрос и знаем, как посмотреть ответ:

```swift
print(test.answer) // Лима
```

У игрока не должно быть доступа к ответу — укажем уровень `private` для свойства `answer`.

```swift
struct Test {

    let question = "Столица Перу?"
    private let answer = "Лима"
}
```

Распечатаем вывод:

```swift
print(test.question) // Столица Перу?
print(test.answer) // Ошибка: 'answer' is inaccessible due to 'private' protection level
```

Мы получили ошибку: `answer` недоступен из-за уровня доступа `private`. Поведение `private`-свойств в классах аналогично. Прочесть свойство `answer` могут только члены структуры `Test`. Создадим метод `showAnswer` для вывода ответа на экран:

```swift
struct Test {

    // ...

    func showAnswer() {
        print(answer)
    }
}
```

Теперь получим `answer` не напрямую:

```swift
test.showAnswer() // Лима
```

### Для методов

Когда работаете с конфиденциальными данными, указывайте методам `private`, чтобы спрятать реализацию. Создадим переменные `gamerAnswer` и `result` типа `String` с пустыми начальными значениями. `result` сделаем `private`:

```swift
struct Test {

    let question = "Столица Перу?"
    private let answer = "Лима"
    var gamerAnswer = ""
    private var result = ""

    // ...
}
```

Понадобятся два метода: 
- `compareAnswer()` - сравнивает ответ игрока с правильным ответом, перезаписывает значение свойства `result`
- `getResult()` - выводит значение `result` на экран

У нас будет доступ к `getResult()` снаружи структуры `Test`, а вот `compareAnswer()` сделаем `private`.

```swift
struct Test {

    // ...
    
    private mutating func compareAnswer() {
        switch gamerAnswer {
        case "":
            result = "Вы не ответили на вопрос".
        case answer:
            result = "Ответ верный!"
        default:
            result = "Ответ неверный".
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
print(test.question) // "Столица Перу?"
test.gamerAnswer = "Лима"
test.getResult() // "Ответ верный!"
```

## fileprivate

Похож на `private`. Доступ к объектам этого уровня есть только у объектов из того же файла. `fileprivate` пригодится, когда нам нужны дополнительные объекты или вычисления в рамках одного файла.

### Отличие от `private`

Создадим два файла: `File1.swift` и `File2.swift`. В первом файле структуры `Constants` и `PrinterConstants`:

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

В `File2.swift` структура `PrinterConstantsFromOuterFile`:

```swift
struct PrinterConstantsFromOuterFile {

    func printConstants() {
        print(Constants.decade)
        print(Constants.exp)
    }
}
```

`static` постоянные структуры `Constants` имеют уровень `internal`. Это позволяет другим структурам из обоих файлов обращаться к ним. Укажем `private` свойству `Constant.exp`.

```swift
struct Constants {

    // ...
    
    private static let exp = 2.72
}
```

Теперь структуры `PrinterConstants` и `PrinterConstantsFromOuterFile` не могут обращаться к свойству `Constant.exp`. Заменим `private` на `fileprivate`:

```swift
struct Constants {

    // ...
    
    fileprivate static let exp = 2.72
}
```

У структуры `PrinterConstantsFromOuterFile` нет доступа к свойству `Constatnts.exp`, а у `PrinterConstants` есть. Исправим ошибку. Удалим строку `print(Constants.exp)` из структуры `PrinterConstantsFromOuterFile`.

```swift
struct PrinterConstantsFromOuterFile {

    func printConstants() {
        print(Constants.decade)
    }
}
```

## Вычисляемые свойства

Вычисляемые свойства используют другие свойства для возврата значения. Такие свойства принято делать `private`- и `public private`-уровней в ряде случаев.

### Read-only

Вычисляемым `read-only`-свойством считается только свойство с `getter`.

Создадим структуру `HappyMultiply`. Свойство `multipliedHappyLevel` рассчитаем на основе `private` свойства `happyLevel`, чтобы скрыть вычисления.

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

Приватный `setter` используют для ограничения доступа к записи за пределами структуры (класса). Для объявления приватного сеттера используем совместно ключевые слова `private` и `set`. Создадим структуру `Vehicle`. Укажем свойству `numberOfWheels` приватный сеттер:

```swift
struct Vehicle {

    private(set) var numberOfWheels : UInt
}
```

### Public Private Setter

Можно переписать структуру `Vehicle` иначе. 

```swift
struct Vehicle {

    public private(set) var numberOfWheels : UInt = 3
}

var kidBike = Vehicle()
print(kidBike.numberOfWheels) // 3
kidBike.numberOfWheels = 2 // Ошибка: cannot assign to property: 'numberOfWheels' setter is inaccessible
```

`Getter` имеет уровень доступа `public`, а `setter` - `private`.

## Модули и фреймворки

Мы хотим создать модуль `Tools` с письменными принадлежностями. Создадим `internal` класс `WritingTool` со свойствами `name`, `inscription` и методом `write(word: String)`.

- `name` - постоянная типа `String`, название инструмента
- `inscription` - переменная типа `String` с пустым начальным значением, надпись
- `write(word: String)` добавляет `word` к `inscription`

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

В рамках модуля в любом месте проекта мы создаём подкласс на его основе.

```swift
class Pencil: WritingTool {

    func clear() {
        inscription = ""
    }
}
```

Создать экземпляр класса `Pencil` можно в любом месте модуля.

```swift
let redPencil = Pencil(name: "red pencil")
redPencil.write(word: "writing by pencil")
print(redPencil.inscription) // "writing by pencil"
redPencil.clear()
print(redPencil.inscription) // ""
```

>Классы `WritingTool` и `Pencil` доступны только внутри нашего модуля из-за `internal`-уровня. Для нашей задачи `internal` не подходит.

Изменим уровень класса `Pencil` на `public`.

```swift
public class Pencil: WritingTool { }
```

Получаем ошибку: «Сlass cannot be declared public because its superclass is internal». 

>Уровень подкласса не должен быть мягче уровня его суперкласса.

Изменим уровень класса `WritingTool` на `public`.

```swift
public class WritingTool { }
```

Теперь можно импортировать модуль в другие проекты и использовать классы `WritingTool` и `Pencil`.

```swift
import Tools

let redPencil = Pencil(name: "red pencil")
redPencil.write(word: "writing by pencil")
print(redPencil.inscription) // "writing by pencil"
redPencil.clear()
print(redPencil.inscription) // ""
```

В новом проекте мы хотим создать класс `Pen`, наследующийся от `WritingTool`.

>`public` не позволяет классам `WritingTool` и `Pencil` быть суперклассами за пределами модуля `Tools`. Нужен другой уровень.

В модуле `Tools` изменим уровень класса `WritingTool` на `open`.

```swift
open class WritingTool { }
```

В новом проекте теперь можно создать класс `Pen: WritingTool`.

```swift
import Tools

class Pen: WritingTool {

    var inkColor: CGColor = .black
    
    func changeInk(color: CGColor) {
        inkColor = color
    }
}
```

Класс `Pencil` мы оставили с уровнем `public`. Он может использоваться в новом проекте, но не может быть в нём суперклассом.

```swift
import Tools

class Pen: WritingTool { }

let greenPencil = Pencil(name: "green pencil")
let pen = Pen(name: "pen")
```

Свойства и методы класса `WritingTool` (`open` уровень) могут быть переопределены классами `Pen` и `Pencil`. Свойства и методы класса `Pencil` (`public` уровень) могут быть переопределены только его подклассами в модуле `Tools`.
