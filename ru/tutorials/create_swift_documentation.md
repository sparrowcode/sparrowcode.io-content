Хорошая документация помогает понять как работает код. Какие функции он выполняет и как его использовать. Это важно для больших проектов и библиотек, которые могут использовать другие разработчики.

Для создания однострочной документации используется три косые черты. Для многострочной используем - /** ... */

Для описания используется синтаксис **Markdown**:

- Абзацы разделяются пустыми строками.

- Неупорядоченные списки отмечаются символами маркеров -, +, * или •

- В упорядоченных списках используются цифры, за которыми следует точка.

- Заголовкам обозначаются #

- Ссылки обозначаются `[text](https://developer.apple.com/)`

Первый абзац это всегда поле `summary`, краткое описание.

```swift
/// This is your User documentation.
struct User {
   let firstName: String
   let lastName: String
}

/**
    This is your User documentation.
    A very long one.
*/
struct Person {
   let firstName: String
   let lastName: String
}
```

![Summary документация](https://cdn.sparrowcode.io/tutorials/create_swift_documentation/summary.png)

Чтобы добавить раздел `overview`, добавляем еще один абзац. Второй абзац, будет относиться к разделу `overview`.

```swift
/**
    This is your User documentation (This is summary).

    A very long one (This will be shown in the discussion section).
*/
struct Person {
   let firstName: String
   let lastName: String
}
```

![Overview документация](https://cdn.sparrowcode.io/tutorials/create_swift_documentation/overview.png)

Пример простой документации. Первый абзац это `summary`. Второй абзац поподает в `overview`. Остальное сгруппированною в общий раздел. Обратите внимание на заголовки, списки и добавление ссылки.

```swift
/**
 This is your User documentation.
 A very long one.
 
 # Text
 It's very easy to make some words **bold** and other words *italic* with Markdown. You can even [link to Apple](https://developer.apple.com/)
 
 # Lists
 Sometimes you want numbered lists:

 1. One
 2. Two

 - Dashes work just as well
 - And if you have sub points, put two spaces before the dash or star:
   - Like this
 
 # Code
 ```swift
 if (isAwesome){
   return true
 }
*/
struct User {
   let firstName: String
   let lastName: String
}
```

![Пример документации](https://cdn.sparrowcode.io/tutorials/create_swift_documentation/example.png)

Для функции с параметрами добавляем раздел `параметров`. Есть два вида написания параметров. Раздел параметров и отдельные поля параметров.

```swift
/// - Parameter firstName: This is first name.
/// - Parameter lastName: This is last name.
struct User {
   let firstName: String
   let lastName: String
}


/// - Parameters:
///     - firstName: This is first name.
///     - lastName: This is last name.
struct User {
   let firstName: String
   let lastName: String
}
```

![Parameters документация](https://cdn.sparrowcode.io/tutorials/create_swift_documentation/parameters.png)

Для функции с возвращаемым значением добавляем раздел `Returns`, как с параметрами.

```swift
/// - Returns: A greeting of the current User.
func greeting(person: User) -> String {
   return "Hello \(person.firstName)"
}
```

![Returns документация](https://cdn.sparrowcode.io/tutorials/create_swift_documentation/returns.png)

В поле `Throws` указываем какие ошибки будут выброшена и в каких ситуациях.

```swift
/// - Throws: MyError.invalidPerson `if `person` is not known by the caller.
func greeting(person: User) throws -> String {
   return "Hello \(person.firstName)"
}
```

![Throws документация](https://cdn.sparrowcode.io/tutorials/create_swift_documentation/throws.png)

Так же можно ссылаться на другие сущности в проекте, используя двойные обратные кавычки

```swift
/// A greeting of the current ``User``
func greeting(person: User) String {
   return "Hello \(person.firstName)"
}
```

![Throws документация](https://cdn.sparrowcode.io/tutorials/create_swift_documentation/ref-entity.png)

Чтобы добавить изображение используем `![image](link)`

```swift
/**
   An example of using *images* to display a web image
 
   ![image](https://cdn.sparrowcode.io/authors/sparrowcode.jpg)
 */
```

![Добавление изображения](https://cdn.sparrowcode.io/tutorials/create_swift_documentation/image.png)

Есть еще много полей, которые можно добавить в документацию. Вот [список](https://developer.apple.com/library/archive/documentation/Xcode/Reference/xcode_markup_formatting_ref/Attention.html#//apple_ref/doc/uid/TP40016497-CH29-SW1):

![Дополнительные поля](https://cdn.sparrowcode.io/tutorials/create_swift_documentation/other-fields.png)

# Создание документации

DocC мощный инструмент для создания качественной документации из кода. Он позволяет структурировать информацию, добавлять примеры кода, изображения и диаграммы. Это упрощает понимание и использование проекта или фреймворка:

- **Автоматическая генерация документации:** DocC автоматически создает документацию на основе комментариев в коде и специальных аннотаций.

- **Поддержка разных типов контента:** Документация может включать текст, примеры кода, изображения и диаграммы.

- **Навигация по документации:** Документация имеет удобную структуру, включающую оглавление, навигационные ссылки и поисковую систему.

Нажмите **⌃** + **⇧** + **⌘** + **D** или **Editor** > **Structure** > **Add documentation**. Xcode сбилдит документацию.

![Генерация документации](https://cdn.sparrowcode.io/tutorials/create_swift_documentation/docc.png)

Когда добавляете что-то новое, нужно заново сбилдить документацию. После этого информация обновиться в браузере документации.