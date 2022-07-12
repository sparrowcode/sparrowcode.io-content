Расскажу как локализовать тексты, картинки, значения и даже пакеты. Что такое плюрализация и автогенерация. Полезные инструменты и тру-вей подход к локализации приложения.

![Пародийный постер к фильму «Перевозчик 3».](https://cdn.sparrowcode.io/tutorials/localisation/preview-ru.jpg)

## Структура

Что бы перевести текст нам понадобится `NSLocalizedString` - макрос, который возвращает локализованную строку и имеет 2 аргумента: ключ и комментарий. 

```swift
let localisedString = NSLocalizedString(
	"label text", // Уникальный ключ, по которому мы поймем какую строку локализуем
	comment: "Мало места, используем сокращения" // Комментарий для переводчика (можно оставить пустым)
)
```

Такой макрос попадёт в файл `Localizable.strings`, который автоматически создаст XCode после экспорта и импорта файлов локализации в формате "ключ" = "значение":

```swift
/* Мало места, используем сокращения */
"label text" = "Localised text";
```

Теперь при запросе ключа `label text` нам вернется локализованное значение "Localised text". Если использовать не локализованный ключ - он отобразится вместо текста.

### InfoPlist

`InfoPlist` - ресурс, содержащий ключ-пары для идентификации и конфигурации бандла. Их можно и нужно локализовать.

Например название приложения автоматически появится в `xcloc` файле после экспорта и его можно будет перевести. После импорта появится в файле `InfoPlist.strings`, который создаст XCode. 

Так же появятся ключи разрешений, если вы добавите их в приложение. Например можно перевести для чего вам нужен доступ к камере на разные языки.

### Передача параметров в локализационный ключ

В `NSLocalizedString` можно передавать параметры при помощи спецификатора формата `String`, например:

```swift
let parametrString = "Empty" // Текст, который хотим передать 

let localisedString = String.init(
    format: NSLocalizedString(
        "label text %@", // На месте %@ появится текст, который мы передадим ниже
        comment: ""
    ), parametrString // Указываем переменную, которую передаем
)
```

Теперь при выводе `localisedString` мы получим "label text Empty". При локализации можно переносить спецификатор и при запросе на его месте появится информация из переданной нами переменной.

**Можно передавать несколько параметров**

```swift
let parametrString = "Make Apple"
let secondParametrString = "great again"
let parametrInt = 941

let localisedString = String.init(
    format: NSLocalizedString(
        "label text %@ %@ %d",
        comment: ""
    ), parametrString, secondParametrString, parametrInt // Текст на месте спецификатора появится в том порядке, в каком вы его передадите
)
```

Если в локализационной строке встретится два одинаковых спецификатора XCode автоматически пронумерует их после экспорта. В `strings`-файле это будет выглядеть примерно так:

```swift
"label text %@ %@ %d" = "Lets %1$@ a true %2$@ at %3$d o’clock";
```

Теперь при выводе переменной `localisedString` мы получим следующий текст: «Lets Make Apple a true great again at 941 o'clock»

Именно для этого мы передаем переменные в порядке, в котором хотим видеть их в тексте. Например если сконфигурируем `localisedString` так: 

```swift
let parametrString = "Make Apple"
let secondParametrString = "great again"
let parametrInt = 941

let localisedString = String.init(
    format: NSLocalizedString(
        "label text %@ %@ %d",
        comment: ""
    ), secondParametrString, parametrString, parametrInt // Меняем parametrString и secondParametrString местами
)
```

При выводе получим: «Lets great again a true Make Apple at 941 o'clock»

**Есть разные спецификаторы**

- %@ - для значений String;
- %d - для значений Int;
- %f - для значений Float;
- %ld - для значений Long;

Познакомиться с остальными можно на сайте [Apple Developer](https://developer.apple.com/library/archive/documentation/Cocoa/Conceptual/Strings/Articles/formatSpecifiers.html).

## Export и import локализации

Переходим в Products и видим кнопки `Export` и `Import localizations...`.

![Расположение кнопок в верхнем баре.](https://cdn.sparrowcode.io/tutorials/localisation/export-menu.jpg)

`Export` позволяет вывести локализационные ключи для перевода.

![Содержимое `xcloc` каталога.](https://cdn.sparrowcode.io/tutorials/localisation/export-xcloc.jpg)

XCode создаст `Localization Catalog` (папку с расширением файла `xcloc`), содержащий локализуемые ресурсы для каждого языка и региона. Для того что бы перевести приложение на нужный язык достаточно его открыть.

![Встроенный в Xcode переводчик.](https://cdn.sparrowcode.io/tutorials/localisation/export-xcode-translator.jpg)

Это встроенный в XCode переводчик. На сайдбаре есть 2 файла - `InfoPlist` и `Localizable`, здесь они переводятся отдельно.

В первой колонке виден ключ, во второй мы заполняем перевод, а в третьей будет комментарий (если оставляли при конфигурации `NSLocalizedString`). Точно так же работает перевод `InfoPlist`. 

После того, как выполнили перевод - сохраняем файл и возвращаемся в проект. Снова переходим в Product, но уже выбираем `Import Localizations`. 

![Импортирование `xcloc` каталогов в проект.](https://cdn.sparrowcode.io/tutorials/localisation/export-import.jpg)

Здесь по-отдельности выбираем каждый каталог и загружаем в проект. Вуаля! В файле `Localizable.strings` нужного языка появятся все переведённые ключи:

```swift
/* No comment provided by engineer. */
"key a" = "Буква А";

/* No comment provided by engineer. */
"key b" = "Буква Б";

/* No comment provided by engineer. */
"key c" = "Буква С";

/* No comment provided by engineer. */
"key d" = "Буква Д";

/* No comment provided by engineer. */
"key e" = "Буква Е";
```

Перевод можно изменять прямо в файле, при следующем экспорте XCode считает это и изменения отобразятся в `xcloc`.

На этом этапе многие закроют ноутбук и откроют шампанское, но не стоит торопиться. Встроенный переводчик удобен если надо перевести небольшой объем текста, с задачами сложнее лучше справится [Poedit](https://poedit.net).

Возвращаемся на 2 минуты назад. Мы снова в папке с `xсloc` каталогами. Вместо того, что бы открыть его левой кнопкой мыши - нажимаем правую и переходим в содержимое пакета.

![Содержимое `xcloc` каталога.](https://cdn.sparrowcode.io/tutorials/localisation/export-xcloc-detail.jpg)

Глаза разбегаются, но не стоит паниковать - здесь нас интересует папка "Localized Contents". Внутри будет `xliff` файл, открываем его через `Poedit`.

![Интерфейс Poedit.](https://cdn.sparrowcode.io/tutorials/localisation/export-poedit.jpg)

Здесь есть все ключи списком. Выбираете нужный, внизу появляется исходный ключ и поле для ввода перевода. Если перевели приложение на основной английский язык - вместо ключей будет отображаться он. Справа есть варианты перевода, ключ и комментарий. С премиумом можно автоматически перевести все ключи с основного языка. Poedit подсветит ошибки в локализации. 

После перевода сохраняем файл и импортируем `xcloc` в проект.

## Автогенерация

Что бы добавить новый язык нужно перейти в настройки проекта -> Info.

![Добавление нового языка в настройках проекта.](https://cdn.sparrowcode.io/tutorials/localisation/autogeneration-new-language.jpg)

Здесь ищем секцию "Localizations" и плюс, через который добавляем столько языков, сколько нам нужно.

XCode автоматически сгенерирует `xсloc` файл для каждого языка при экспорте и `strings`-файлы при импорте. Есть одно НО - при смене ключа в переменной старый ключ останется в файле даже после экспорта, а не локализованный - при импорте.

Эти и другие ошибки появляются в результате автогенерации, из-за чего файлы с локализациями превращаются в кашу при создании большого проекта. По статистике при такой работе кресло среднестатистического разработчика полностью сгорает за 15 минут, но у нас есть выход - [BartyCrouch](https://github.com/Flinesoft/BartyCrouch).

Он автоматически ищет все локализации в проекте и икнрементально обновляет `strings`-файлы при появлении новых, удалении старых `NSLocalizedString` или `views` в `Storyboard` и `XIB`. Сортирует ключи по алфавиту, что бы избежать конфликтов слияния.

Выхода нет - добавляем в проект.

### BartyCrouch

- Открываем терминал и вводим команду для установки [Homebrew](https://brew.sh), через который установим BartyCrouch:
```swift
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```
- Следуем инструкциям по установке в терминале.
- Создаём файл конфигурации в папке проекта:
```swift
bartycrouch init
```

В папке появится скрытый файл `.bartycrouch.toml`. 

![Стандартный файл-конфигуратор `Bartycrouch`.](https://cdn.sparrowcode.io/tutorials/localisation/autogeneration-bartycrouch-file.jpg)

Это стандартная конфигурация, которая закрывает большинство проблем. Её можно настроить, давайте разберёмся. 

- Убираем задачу `[code]`, потому что её полностью заменяет `[transform]`. 
- Прописываем `paths` и `codePaths` для улучшения работы:

```swift
// Указывайте путь к файлам в вашем проекте, например:
paths = ["App/Localisations/"]
codePaths = ["App/Data/"]
```

В проекте есть другие опции.

Для задачи `interface`:

- `subpathsToIgnore = ["."]` - пути к файлам, которые будут игнорироваться при проверке.
- `defaultToBase = true` - добавляет значение от стандартного языка к новым, не локализованным ключам.
- `ignoreEmptyStrings = true` - не допускает создание `view` для пустых строк.
- `unstripped = true` - сохраняет пробелы в начале и конце `strings`-файлов.

Для задачи `normalize`:

- `separateWithEmptyLine = false` - создаёт пробелы между строками.
- `sourceLocale = "."` - переопределяет основной язык.
- `harmonizeWithSource = true` - синхронизирует ключи с остальными языками.
- `sortByKeys = true` - сортирует ключи по алфавиту.

Полный разбор опций есть [в документации](https://github.com/FlineDev/BartyCrouch#configuration).

Запускаем проверку `Bartycrouch` через команду:
```swift
bartycrouch update
```

Готово, мы сэкономили час работы и 2 таблетки успокоительного. `BartyCrouch` проверил все ключи, добавил их в `strings`-файлы и избавился от ненужных. 

Вы можете поменять задачи, которые вызываются через `update`, например: 

```swift
[update]
tasks = ["interfaces", "normalize"]
```

Теперь при вызове отработают только 2 задачи. Ещё есть `lint` - задача, которая делает поверхностную проверку. Вы тоже можете её настроить и вызвать.

Что бы не вызывать `Bartycrouch` вручную, в проект можно добавить скрипт, который сделает всё за вас:

```swift
if which bartycrouch > /dev/null; then
    bartycrouch update -x
    bartycrouch lint -x
else
    echo "warning: BartyCrouch not installed, download it from https://github.com/FlineDev/BartyCrouch"
fi
```

Переходим в таргет проекта -> `Build Phase`, нажимаем на плюсик и создаём новый скрипт:

![Добавление скрипта `Bartycrouch` в проект.](https://cdn.sparrowcode.io/tutorials/localisation/autogeneration-bartycrouch-script.jpg)

Теперь `Bartycrouch` будет делать проверку автоматически и напомнит, если его надо установить. Например, если открыли проект на другом компьютере.

## Плюрализация

Когда мы передаём количество в `NSLocalizedString` - стакливаемся с проблемой локализации множества имён существительных.

Например: 
- У Тима нет наушников;
- У Тима 1 наушник;
- У Тима 2 наушника;
- У Тима 7 наушников;

На помощь прийдёт `Stringsdict` с правилом Plural. Создаём функцию:

```swift
func headphonesCount(count: Int) -> String {
        let formatString: String = NSLocalizedString("headphones count", comment: "Don't localise, stringsdict") // Локализационный ключ, можно указать, что не требуется локализация
        let resultString: String = String.localizedStringWithFormat(formatString, count) // Передаем count
     return resultString // Возвращаем нужный текст
}
```

Создаём новый файл. В поиске пишем "strings" и выбираем `Stringsdict File`. Даём ему название `Localizable`, добавляем в проект.

![Добавление `Stringsdict` файла.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-new-stringsdict.jpg)

Переходим в файл, видим следующую структуру:

![Структура файла `Stringsdict`.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-stringsdict-empty.jpg)

- `Localised String Key` - локализационный ключ, который мы создали ранее (headphones count).
- `Localised Format Key` - параметр, значение которого войдёт в строку результата. В нашем случае только один (count).
- `NSStringFormatSpecTypeKey` - указывает единственный возможный тип перевода `NSStringPluralRuleType`, который значит то, что в переводе встречается множество имён существительных (его не трогаем).
- `NSStringFormatValueTypeKey` - строковый спецификатор формата числа (например `d` для целых чисел).
- `zero, one, two, few, many, other` - различные формы множественного числа для разных языков. Обязательным является `other` - он будет использован, если переданное число не удовлетворит ни одно из перечисленных условий. Остальные можно убрать, если они не требуются для локализуемого слова.

Заполняем файл:

![Заполненный ключ `headphones count`.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-string-headphones-prepare.jpg)

Видим, что `two, few, many` и `other` повторяются. Обязательно только последнее, поэтому остальные убираем.

![Отрефракторенный ключ `headphones count`.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-string-headphones-ready.jpg)

Файл заполнен, но при вызове функции `headphonesCount(count: 1)` мы получим ключ `headphones count`, вместо перевода, потому что XCode не локализует `.stringsdict` автоматически.

Переходим в инспектор -> кнопка `Localize...`

![Расположение кнопки `Localize...` в инспекторе.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-localize-button.jpg)

Затем выбираем языки, для которых нужно создать `.stringsdict` файлы - доступны все, что добавлены в проект.

![Выбор языков для перевода в инспекторе.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-localize-languages.jpg)

Локализовать `.stringsdict` можно как в новом созданной файле, так и через `xcloc` файл после экспорта. Пойдём первым путём.

Выбираем `Localizable (Russian)` в левом меню.

![`stringsdict`-файлы на сайдбаре.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-sidebar-languages.jpg)

Заполняем строки на русском, добавляем `few`, так как оно требуется для корректного перевода числа на этом языке.

![Локализованный ключ `headphones count`.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-string-headphones-translated.jpg)

Теперь при передаче в функцию `headphonesCount(count:)` чисел 0, 1, 2 и 7 получим:

**На русском языке**

- У Тима нет наушников;
- У Тима 1 наушник;
- У Тима 2 наушника;
- У Тима 7 наушников;

**На английском языке**

- Tim doesn't have headphones;
- Tim has 1 headphone;
- Tim has 2 headphones;
- Tim has 7 headphones;

Что бы локализовать другие слова достаточно создать ещё одну функцию и новое значение в `.stringsdict` файле, например считаем яблоки.

Создаём функцию с новым ключем.

```swift
func applesCount(count: Int) -> String {
    let formatString: String = NSLocalizedString("apples count", comment: "")
    let resultString: String = String.localizedStringWithFormat(formatString, count)
    return resultString
}
```

Переходим в `.stringsdict`, создаём новое значение `apples count`. Настраиваем как раньше. 

![Новый заполенный ключ `apples count`.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-string-apples-ready.jpg)

Что бы локализовать новое значение на другие языки - экспортируем локализацию и открываем нужный `xcloc`.

![Локализация `stringsdict`-файла в переводчике Xcode.](https://cdn.sparrowcode.io/tutorials/localisation/export-xcode-translator.jpg)

Переводим и импортируем в проект. Видим, что в `.stringsdict` файле русского языка осталось лишнее значение `many` - удаляем его и приводим остальные в порядок.

![Отрефракторенный ключ `apples count`.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-string-apples-translated.jpg)

Для проверки вызывааем `applesCount(count:)`, передаем числа 0, 1, 7, 131, 152 и получим:

**На русском языке**

- У Тима нет яблок;
- У Тима 1 яблоко;
- У Тима 7 яблок;
- У Тима 131 яблоко;
- У Тима 152 яблока;

**На английском языке**

- Tim doesn't have apples;
- Tim has 1 apple;
- Tim has 7 apples;
- Tim has 131 apples;
- Tim has 152 apples;

Таким образом можно создать и локализовать столько значений, сколько понадобится.

## Локализация пакетов

Создаём папку `Resources`, в ней должен быть файл `Texts` и папка языка, но который мы хотим локализовать пакет, например `en.lproj`. В неё помещаем файл `Localizable.strings`, делаем так для каждого языка, меняя название папки. Структура пакета должна выглядеть примерно так: 

![Структура локализуемого пакета.](https://cdn.sparrowcode.io/tutorials/localisation/package-configuration-structure.jpg)

В файле `Package` выставляем `defaultLocalization` - стандартный язык локализации, указываем нашу папку с файлами в `resources`.

![Структура файла локализуемого пакета.](https://cdn.sparrowcode.io/tutorials/localisation/package-configuration-file.jpg)

В файле `Texts` создаем `enum` и статические переменные, которые возвращают `NSLocalizedString` с `bundle: .module` в инициализаторе. 

```swift
enum Texts {
    
   static var first: String { NSLocalizedString("first key", bundle: .module, comment: "") }
   static var second: String { NSLocalizedString("second key", bundle: .module, comment: "") }
   static var third: String { NSLocalizedString("third key", bundle: .module, comment: "") }
    
}
```

Xcode не экспортирует и не импортирует локализационные ключи во встроенных в проект пакетах. Можно локализовать каждый ключ вручную, но мы воспользуемся костыльным вариантом. 

- Создаём пустой проект, дублируем файл `Texts` из пакета в него. 
- Через «замену» удаляем `bundle: .module` из `NSLocalizedString` по всему файлу. 
- Экспортируем и локализуем ключи.
- Импортируем обратно в проект.
- Копируем файл `Localizable` и вставляем в пакет вместо исходного.

```swift
/* No comment provided by engineer. */
"first key" = "First";

/* No comment provided by engineer. */
"second key" = "Second";

/* No comment provided by engineer. */
"third key" = "Third";
```

Пакет локализован. Проект можно сохранить для дальнейших локализаций, не забудьте добавить в него те языки, которые поддерживает пакет. 

## Локализация значений

### Идентификаторы языка

Во всех примерах будем использовать `(identifier:)` - функция, принимающая идентификатор языка, на который нужно локализовать значение. Полный список таких идентификаторов доступен [по ссылке](https://gist.github.com/jacobbubu/1836273).

Можно использовать `Locale.current.identifier` - вернется идентификатор в формате `"языкприложения_ЯЗЫКРЕГИОНА"`, например `"en_US"`.

Этот способ может сбоить, например если в приложении установлен английский язык, а регион на устройстве - Россия. При запросе получим `"en_RU"` - идентификатор, который не позволит правильно локализовать валюту. Вместо `"₽"` вернётся `"RUB"` и так далее. 

Что бы этого избежать рассмотрим два способа-костыля:

**Первый способ.**

Создаём `NSLocalizedString`

```swift
let langIdentifier = NSLocalizedString("language identifier", comment: "")
```

Локализуем и вручную проставляем идентификатор для каждого используемого языка.

```swift
// Английский `Localizable.strings` файл:
"language identifier" = "en_US";
```

```swift
// Русский `Localizable.strings` файл:
"language identifier" = "ru_RU";
```

**Второй способ.**

Создаём функцию, которая будет возвращать правильный идентификатор в зависимости от языка приложения.

```swift
func getLangIdentifier() -> String {
    let languageCode = Locale.current.languageCode
    switch languageCode {
    case "en":
        return "en_US"
    case "ru":
        return "ru_RU"
    case .none:
        return "en_US"
    case .some(_):
        return "en_US"
    }     
}
```

Создаём постоянную `langIdentifier`

```swift
let langIdentifier = getLangIdentifier()
```

**Использование**

Теперь при запросе `langIdentifier` (вне зависимости от способа, который использовали) получим идентификатор в правильном формате. 

### Валюты

Создаём и настраиваем объект класса `NumberFormatter`:

```swift
let currencyFormatter = NumberFormatter()
currencyFormatter.numberStyle = .currency
```

Локализуем с помощью `.locale`:

```swift
currencyFormatter.locale = Locale(identifier: langIdentifier) 
```

Выводим локализованное значение, например 3000:

```swift
print(currencyFormatter.string(from: 3000)!)
```

Получаем «`3 000,00 ₽`» в консоли.

### Даты

Получаем текущую дату:

```swift
let currentDate = Date() 
```

Создаём и настраиваем объект класса `DateFormatter`:

```swift
let dateFormatter = DateFormatter()
// Задаём стиль, например `.medium`
dateFormatter.dateStyle = DateFormatter.Style.medium
dateFormatter.timeStyle = DateFormatter.Style.medium 
```

Локализуем с помощью `.locale`:

```swift
dateFormatter.locale = Locale(identifier: langIdentifier) 
```

Выводим локализованную дату:

```swift
print(dateFormatter.string(from: currentDate))
```

Получаем «`24 апр. 2022 г., 02:05:34`» в консоли.

Вместо `currentDate` можно локализовать другую дату.

### Числа

Создаём и настраиваем объект класса `NumberFormatter`:

```swift
let numberFormatter = NumberFormatter()
formatter.numberStyle = .decimal
```

Локализуем с помощью `.locale`:

```swift
numberFormatter.locale = Locale(identifier: langIdentifier) 
```

Выводим локализованное число:

```swift
print(numberFormatter.locale.string(from: 123456))
```

Получаем «`123 456`» в консоли.

## Локализация изображений

Представим, что нам нужно показывать флаг страны, на язык которой локализовано приложение.

Переходим в `Assets` -> Добавляем стандартное изображение (оно появится, если для языка, который используется в приложении нет локализованного изображения). Для максимальной трушности выставляем `single scale`.

Переходим в инспектор -> кнопка `Localize...`

![Расположение кнопки `Localize...` в `Assets` каталоге Xcode.](https://cdn.sparrowcode.io/tutorials/localisation/image-prepare.jpg)

Выбираем языки, на которые хотим локализовать изображение (доступны все, добавленные в проект). Добавляем нужные изображения в появившихся полях.

![`Assets` после настройки под разные языки.](https://cdn.sparrowcode.io/tutorials/localisation/image-ready.jpg)

Проверяем как отображается изображение на разных языках.

![Превью локализованного изображения.](https://cdn.sparrowcode.io/tutorials/localisation/image-preview.jpg)

## Тру-вей в работе с локализациями

Можно бесконечно спорить на тему того как правильно рефракторить код. Спешу предложить свою структуру с оговоркой, что бы вы делали так, как вам удобно. 

### Распределение

**Отдельный файл для макросов**

Создаем файл и `enum` `Texts`. В нём создаём статические перемененные, которые вернут `NSLocalizedString`.

```swift
enum Texts {
    
    static var title: String { NSLocalizedString("controller title", comment: "") }
    static var subtitle: String { NSLocalizedString("controller subtitle", comment: "") }
    static var action_button: String { NSLocalizedString("controller action button", comment: "") }
    static var cancel_button: String { NSLocalizedString("controller cancel button", comment: "") }
    
}
```

Делаем это для того, что бы было удобно работать с ключами. В коде используем следующую запись:

```swift
titleLabel.text = Texts.title
```

**Сортировка Texts файла**

Если на этом моменте вы потянулись закрывать статью и ставить ей дизлайк - не торопитесь. Сейчас будет стук со дна - `enum Texts` можно сортировать. Например разделить ключи между контроллерами:

```swift
enum Texts {
    
    enum FirstController {
        
        static var title: String { NSLocalizedString("first controller title", comment: "") }
        static var subtitle: String { NSLocalizedString("first controller subtitle", comment: "") }
        static var action_button: String { NSLocalizedString("first controller action button", comment: "") }
        static var cancel_button: String { NSLocalizedString("first controller cancel button", comment: "") }
    }
    
    enum SecondController {
        
        static var title: String { NSLocalizedString("second controller title", comment: "") }
        static var subtitle: String { NSLocalizedString("second controller subtitle", comment: "") }
        static var action_button: String { NSLocalizedString("second controller action button", comment: "") }
        static var cancel_button: String { NSLocalizedString("second controller cancel button", comment: "") }
    }
}
```

Так можно разделить `Texts` на удобные блоки и использовать в проекте. Если переменных слишком много - можно создать несколько файлов и сделать их `extension Texts` для большего контроля.

**Функциональные слова**

Функциональные слова, такие как «ОК», «Отменить», «Удалить» и так далее, можно вынести в отдельный `enum Shared` и использовать по всему приложению, что бы не создавать одинаковых локализаций:

```swift
enum Shared {
        
    static var ok: String { NSLocalizedString("shared ok", comment: "") }
    static var cancel: String { NSLocalizedString("shared cancel", comment: "") }
    static var delete: String { NSLocalizedString("shared delete", comment: "") }    
}
```

`Shared` можно вынести в отдельный пакет, что бы использовать для разных модулей проекта и менять в одном месте для всех сразу. 

**Передача параметров в ключ**

Метод выноса макросов в `Texts` начинает нравиться на этапе передачи параметров в ключ. Можно оформить красиво:

```swift
static func fruitName(name: String) -> String {
    return String(format: NSLocalizedString("fruit name %@", comment: ""), name)
}
```

Вызываем в коде:

```swift
fruitNameLabel.text = Texts.fruitName(name: "Apple")
```

### Ключ

Создаём правильный ключ. `NSLocalizedString` принимает 2 параметра, которые в дальнейшем будут видны при локализации - ключ и комментарий. 

Можно создать не понятный ключ и подробно описать для чего он в комментарии, но лучше создать так, что бы было понятно без него. Например футер секции с фидбеком на экране настроек: 

```swift
NSLocalizedString("settings controller table feedback section footer", comment: "")
```

### Инструменты

Крупные проекты тяжело локализовать на разные языки и поддерживать `strings`-файлы в нормальном состоянии, поэтому рекомендую установить:

- [Poedit](https://poedit.net) - приложение для локализации `xcloc` файлов. Поддерживает автоматический перевод всех строк на другой язык, имеет удобный интерфейс.
- [BartyCrouch](https://github.com/FlineDev/BartyCrouch) - инструмент для рефракторинга локализаций. Удаляет неиспользуемые строки, сортирует по алфавиту, сообщает о других ошибках - можно настроить под свои нужны.

### Перевод

Обращаться к услугам переводчика или нет - снова выбор каждого. Я считаю, что это зависит от размера переводимого проекта.

Спешу поделиться своим списком наблюдений, которые могут помочь:

- Весь интерфейс должен быть динамическим. Заранее рассчитать ширину и высоту лейбла под текст не получится, потому что одни и те же слова занимают разное место на разных языках. Например «Как ты?» переводится с русского на французский как «Comment allez-vous?».
- На английском языке все действия, кнопки и прочие функциональные вещи - с большой буквы. Например кнопка «Add new» должна выглядеть как «Add New».
- Проверяйте арабскую локализацию. При её установке интерфейс автоматически переворачивается, но некоторые элементы могут начать вести себя не так, как планировалось. 
- Если пользуетесь автопереводом - заранее подготовьте язык, от которого он будет работать. Обычно это английский. 

Если знаете ещё - [дополните статью через PR](https://github.com/sparrowcode/sparrowcode.io-content/blob/main/ru/tutorials/localisation.md).