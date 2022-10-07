Переводим приложение за 15 минут. Тексты, изображения, значения и SPM пакеты. Удобные инструменты и лайфхаки по работе с локализацией. 

![Пародийный постер к фильму «Перевозчик 3».](https://cdn.sparrowcode.io/tutorials/localisation/preview-ru.jpg)

## Основы

Начнем с простого - добавим язык и переведём ключи. Это закроет 80% будущих задач.

### Добавить язык

Что бы добавить новый язык перейдите в Настройки проекта -> `Info`. Найдите секцию `Localizations`. Нажмите на кнопку `+` и выберите новый язык.

![Добавление нового языка в настройках проекта.](https://cdn.sparrowcode.io/tutorials/localisation/add-new-language.jpg)

### Локализация строки

Что бы перевести строку, используем макрос `NSLocalizedString`. Он принимает 2 параметра - ключ и комментарий, а возвращает локализованную строку.

```swift
let localisedString = NSLocalizedString(
    "label text", // Уникальный ключ, связан со строкой
    comment: "Пример комментария для ключа" // Комментарий для переводчика. Можно оставить пустым
)
```

> Если строка не локализована - вернётся имя ключа.

Теперь переведем ключи. Создайте файл `Localizable.strings`. Файл можно перевести на языки, которые поддерживает проект. Необязательно переводить на все языки. В инспекторе справа можно увидеть какие языки поддерживает файл. Чтобы перевести строки на новый язык, поставьте рядом с ним галочку.

![Здесь выбираем языки для локализации файла.](https://cdn.sparrowcode.io/tutorials/localisation/string-localisation-inspector.jpg)

Локализация заполняется в формате `"ключ" = "значение"`. Перейдите в файл и добавьте строки:

```txt
/* Пример комментария для ключа */
"label text" = "Localised Text";
```

Строка локализована. Заполните по аналогии другие языки, теперь по ключу `label text` вернется локализованное значение `Localised Text`.

### Передача параметра в строку

Пригодится, если хотите поприветствовать пользователя, например `Привет, Имя!` или отобразить время `Осталось X минут`. В `NSLocalizedString` можно передавать параметры - строки или числа. Для этого нужны спецификаторы - Xcode заменит их на значения:

- %@ - для значений String;
- %d - для значений Int;
- %f - для значений Float;
- %ld - для значений Long;

Весь список спецификаторов на сайте [Apple Developer](https://developer.apple.com/library/archive/documentation/Cocoa/Conceptual/Strings/Articles/formatSpecifiers.html).

Перейдем к примеру. Создаём объект `String` с инициализатором `format`:

```swift
let parametrString = "Parametr Example" // Параметр, который будем передать 

let localisedString = String(
    format: NSLocalizedString(
        "label text", // ключ локализации 
        comment: "" // комменатрий
    ), parametrString // переменная
)
```

Теперь локализуем ключ с параметром. Перейдем в `Localizable.strings` и добавим:

```txt
"label text" = "Localised Text with %@";
```

Теперь при выводе ключа `label text` получим `label text with Parametr Example`. Спецификатор заменился значением.

### Порядок параметров

Если в строке два спецификатора одинакового типа - значения отобразятся в том порядке, в котором мы их передадим. Например, создадим переменную `localisedString`, принимающую 3 параметра:

```swift
let parametrString = "Make Apple"
let secondParametrString = "great again"
let parametrInt = 941

let localisedString = String(
    format: NSLocalizedString("label text", comment: ""), 
    parametrString, secondParametrString, parametrInt
)
```

Локализуем ключ в `strings`-файле:

```txt
"label text" = "Lets %1$@ a true %2$@ at %3$d o’clock";
// %1$@ - для первого текстового значения и так далее. 
// %3$d - для первого числового значения.
```

Нумерацию параметров можно игнорировать, тогда строка будет такая:

```txt
"label text" = "Lets %@ a true %@ at %d o’clock";
```

Теперь при выводе переменной `localisedString` мы получим текст: `Lets Make Apple a true great again at 941 o'clock`. Если изменить порядок элементов, то изменится их порядок при выводе. Например, если создадим `localisedString` так: 

```swift
let parametrString = "Make Apple"
let secondParametrString = "great again"
let parametrInt = 941

let localisedString = String(
    format: NSLocalizedString("label text", comment: ""), 
    secondParametrString, parametrString, parametrInt // Меняем parametrString и secondParametrString местами
)
```

При выводе получим `Lets great again a true Make Apple at 941 o'clock`

## Локализация `InfoPlist`

`Info.plist` - системный файл проекта, содержит информацию о бандле, имени приложения, ключах разрешений и т.д. Мы можем локализовать имя приложения и ключи разрешений. Создаем файл `InfoPlist.strings` и в инспекторе выбираем поддерживаемые языки.

> Имя файла обязательно должно быть `InfoPlist.strings`, иначе локализация не подтянется

Что бы локализовать название приложения, доабвим в файл `CFBundleName` в формате `"ключ" = "значение"`:

```txt
"CFBundleName" = "App name";
```

Когда добавляете в `Info.plist` разрешения, например для использования камеры - нужно объяснить для чего оно нужно приложению. Локализуем это сообщение. 

// TODO: ПРИМЕР РАЗРЕШЕНИЯ ФОТО
// TODO: Вставить ссылку

Список всех ключей можно глянуть [здесь](https://github.com/sparrowcode/PermissionsKit#permissions). Вставляем ключ и локализуем:

```text
/* Privacy - Camera Usage Description */
"NSCameraUsageDescription" = "We use the camera to take pictures.";
```

## Экспорт и Импорт локализации

Экспорт и импорт локализации автоматизирует действия, добавляет ключи. Экспорт помогает передать файлы переводчику, не передавая весь проект целиком. Переводчик видит имя ключа и комментарии к нему.

Перейдем в Products и видим кнопки `Export Localizations...` и `Import Localizations...`.

![Расположение кнопок экспорта и импорта локализации.](https://cdn.sparrowcode.io/tutorials/localisation/export-menu.jpg)

После экспорта создаются файлы `xcloc`. Они содержат необходимую информацию для переводчика:

![Сгенерированные `xcloc` каталога.](https://cdn.sparrowcode.io/tutorials/localisation/export-xcloc.jpg)

> `xcloc` расшифровывается как Xcode Localization Catalog
 
Внутри `xcloc` находится 3 папки и файл:
- Папка `Localized Contents` содержит локализуемые ресурсы, включая файл `XLIFF`. Он содержит локализуемые строки.
- Папка `Notes` содержит дополнительную информацию для переводчиков: скриншоты, видео или текстовые файлы.
- Папка `Source Contents` содержит исходные `strings`-файлы и контекст для переводчиков: файлы интерфейса и другие ресурсы.
- Файл `contents.json` хранит метаданные о каталоге: регион разработки, язык, номер версии Xcode, а также номер версии каталога.

Переведем приложение через экспортированный файл. Откройте `xcloc`-каталог. Xcode имеет встроенную IDE для редактирования файла.

![Встроенная в Xcode IDE для редактирования `xcloc`.](https://cdn.sparrowcode.io/tutorials/localisation/export-xcode-translator.jpg)

На сайдбаре увидите 2 файла - `InfoPlist` и `Localizable`. В первой колонке ключ, во второй переводим его, а в третьей находится комментарий. После перевода - сохраните файл. Чтобы мпортировать локализацию, перейдите в Product -> `Import Localizations`. 

![Импортирование `xcloc` каталога в проект.](https://cdn.sparrowcode.io/tutorials/localisation/export-import.jpg)

Здесь выбираем каталог и загружаем в проект. В файле `Localizable.strings` импортированного языка появятся переведённые ключи:

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

Встроенный переводчик удобно использовать, если переводить небольшие файлы. Дальше мы рассмотрим другие способы.

### Poedit

Это альтернативная IDE для редактирования `xсloc`-каталогов. Она покажет ошибки в переводе, отсутствующие строки и может автоматически перевести ключи на другой язык. 

Poedit умеет читать только xliff-файлы, поэтому открываем `xcloc`-каталог правой кнопкой и переходим в содержимое пакета.

![Содержимое `xcloc` каталога.](https://cdn.sparrowcode.io/tutorials/localisation/export-xcloc-detail.jpg)

Нас интересует папка `Localized Contents`. Внутри будет `xliff` файл, его открываем через `Poedit`.

![Интерфейс Poedit.](https://cdn.sparrowcode.io/tutorials/localisation/export-poedit.jpg)

Здесь все ключи списком. Выбираете нужный, внизу появляется исходный ключ и поле для ввода перевода. Справа есть варианты перевода, ключ и комментарий. После перевода сохраняем файл и импортируем `xcloc` в проект.

> Можно импортировать только `xliff`-файл.

### BartyCrouch

Это консольный инструмент и встраиваемый плагин. Он автоматизирует локализацию и генерацию ключей, обновляет `strings`-файлы, удаляет неиспользуемые ключи и сортирует ключи по алфавиту.

Чтобы установить `BartyCrouch`:
- Откройте терминал и установите [Homebrew](https://brew.sh):
```
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```
- В терминал вводим `brew install bartycrouch`
- Создаем дефолтный конфиг, для этого в терминал вставляем:
```
bartycrouch init
```

В папке появится скрытый файл `.bartycrouch.toml`. 

![Стандартный файл-конфигуратор `Bartycrouch`.](https://cdn.sparrowcode.io/tutorials/localisation/autogeneration-bartycrouch-file.jpg)

Это стандартная конфигурация. Прописываем `paths` и `codePaths` для быстрого поиска файлов:

// TODO что за файлы здесь указывать
```swift
// Указывайте путь к файлам в вашем проекте, например:
paths = ["App/Localisations/"]
codePaths = ["App/Data/"]
```

Для задачи `interfaces`:
- `subpathsToIgnore = ["."]` - пути к файлам, которые нужно игнорировать.
- `defaultToBase = true` - добавляет значение от стандартного языка к новым не локализованным ключам.
- `ignoreEmptyStrings = true` - не допускает создание `view` для пустых строк.
- `unstripped = true` - сохраняет пробелы в начале и конце `strings`-файлов.

Для задачи `normalize`:
- `separateWithEmptyLine = false` - создаёт пробелы между строками.
- `sourceLocale = "."` - переопределяет основной язык.
- `harmonizeWithSource = true` - синхронизирует ключи с остальными языками.
- `sortByKeys = true` - сортирует ключи по алфавиту.

Опций больше, весь список [в документации](https://github.com/FlineDev/BartyCrouch#configuration).

После того, как настроили конфиг, можно запустить проверку:
```swift
bartycrouch update
```

`BartyCrouch` проверит ключи, добавит их `strings`-файлы и избавится от ненужных. Команды, которые вызываются через `update` меняются, например: 

```swift
[update]
tasks = ["interfaces", "normalize", "code"]
```

Теперь при вызове отработают только 3 задачи. Ещё есть `lint` - задача, которая по умолчанию делает поверхностную проверку - ищет повторяющиеся ключи и пустые строки.

Что бы не вызывать `Bartycrouch` вручную, можно встроить его в Xcode - проверка будет запускаться при каждом билде. Переходим в таргет проекта -> `Build Phase`, нажимаем на плюсик и создаём новый скрипт:

![Добавление скрипта `Bartycrouch` в проект.](https://cdn.sparrowcode.io/tutorials/localisation/autogeneration-bartycrouch-script.jpg)

Вставляем код:

```swift
if which bartycrouch > /dev/null; then
    bartycrouch update -x
    bartycrouch lint -x
else
    echo "warning: BartyCrouch not installed, download it from https://github.com/FlineDev/BartyCrouch"
fi
```

Теперь `Bartycrouch` делает проверку автоматически.

## Плюрализация

Поддержка разного количества и падежей в локализации, например:

- У Тима нет наушников;
- У Тима 1 наушник;
- У Тима 2 наушника;
- У Тима 7 наушников;

Создаём функцию:

```swift
func headphonesCount(count: Int) -> String {
    let formatString: String = NSLocalizedString("headphones count", comment: "Don't localise, stringsdict") // Локализационный ключ, можно указать, что не требуется локализация
    let resultString: String = String.localizedStringWithFormat(formatString, count) // Передаем count
    return resultString // Возвращаем нужный текст
}
```

Создаём новый файл. В поиске пишем `strings` и выбираем `Stringsdict File`. Называем `Localizable` и добавляем в проект.

![Добавление `Stringsdict` файла.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-new-stringsdict.jpg)

Переходим в файл и видим структуру:

![Структура файла `Stringsdict`.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-stringsdict-empty.jpg)

// TODO ПЕРЕФРАЗИРОВАТЬ НОРМАЛЬНО
- `Localised String Key` - ключ локализации: headphones count.
- `Localised Format Key` - параметр, значение которого войдёт в строку результата. В нашем случае только один: count.
- `NSStringFormatSpecTypeKey` - указывает единственный возможный тип перевода `NSStringPluralRuleType`, который значит то, что в переводе встречается множество имён существительных (то, что мы хотим сделать) - его не трогаем.
- `NSStringFormatValueTypeKey` - строковый спецификатор формата числа (например `d` для целых чисел).
- `zero, one, two, few, many, other` - различные формы множественного числа для языков. Обязательное `other` - оно будет использовано, если переданное число не удовлетворит ни одно из перечисленных условий. Остальные можно убрать, если они не требуются для локализуемого слова.

Заполняем файл:

![Заполненный ключ `headphones count`.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-string-headphones-prepare.jpg)

Видим, что `two, few, many` и `other` повторяются. Обязательно только последнее, поэтому остальные убираем.

![Отрефракторенный ключ `headphones count`.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-string-headphones-ready.jpg)

Файл заполнен, но при вызове функции `headphonesCount(count: 1)` мы получим ключ `headphones count`, вместо перевода.

> Xcode не локализует `stringsdict` автоматически.

Для того что бы локализовать `stringsdict`, перейдем в инспектор -> кнопка `Localize`

![Расположение кнопки `Localize` в инспекторе.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-localize-button.jpg)

Затем выбираем языки, для которых нужно создать `stringsdict`-файлы.

![Выбор языков для перевода в инспекторе.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-localize-languages.jpg)

Локализовать `.stringsdict` можно прямо в созданном файле. Выбираем `Localizable (Russian)` в левом меню.

![`stringsdict`-файлы на сайдбаре.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-sidebar-languages.jpg)

Заполняем строки на русском, добавляем `few` для корректного перевода числа на этом языке.

![Локализованный ключ `headphones count`.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-string-headphones-translated.jpg)

Получаем:

```txt
headphonesCount(count: 0) // У Тима нет наушников
headphonesCount(count: 1) // У Тима 1 наушник
headphonesCount(count: 2) // У Тима 2 наушника
headphonesCount(count: 7) // У Тима 7 наушников
```

Если нужно локализовать другое слово - создайте новое значение в `stringsdict`-файле. Например, посчитаем яблоки. Создаём функцию с новым ключом:

```swift
func applesCount(count: Int) -> String {
    let formatString: String = NSLocalizedString("apples count", comment: "")
    let resultString: String = String.localizedStringWithFormat(formatString, count)
    return resultString
}
```

Переходим в `stringsdict`, создаём новое значение `apples count`. Настраиваем как в прошлих шагах. 

![Новый заполненный ключ `apples count`.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-string-apples-ready.jpg)

Новое значение все ещё можно локализовать прямо в файле, но в этот раз для перевода используем другой способ и экспортируем локализацию через `Product` -> `Export Localizations...`. Открываем нужный `xcloc`-каталог:

![Локализация `stringsdict`-файла в переводчике Xcode.](https://cdn.sparrowcode.io/tutorials/localisation/export-xcode-translator.jpg)

Переводим и импортируем в проект через `Product` -> `Import Localizations...`. В `stringsdict`-файле русского языка осталось лишнее значение `many` - удаляем его.

![Отрефракторенный ключ `apples count`.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-string-apples-translated.jpg)

Проверяем:

```swift
applesCount(count: 0) // У Тима нет яблок
applesCount(count: 1) // У Тима 1 яблоко
applesCount(count: 7) // У Тима 7 яблок
applesCount(count: 131) // У Тима 131 яблоко
applesCount(count: 152) // У Тима 152 яблока
```

## Локализация SPM-пакетов

Чтобы локализовать SPM-пакет, создадим папку внутри пакета с идентификатором языка. Например, `en.lproj`. У каждого языка есть свой идентификатор, весь список можно глянуть [по ссылке](https://gist.github.com/jacobbubu/1836273). В папке создаём файл `Localizable.strings`. 

Повторяем процедуру для каждого нужного языка.

![Структура локализуемого пакета.](https://cdn.sparrowcode.io/tutorials/localisation/package-configuration-structure.jpg)

В файле `Package` выставляем `defaultLocalization` - стандартный язык локализации. Указываем папку с файлами локализации в `resources`.

![Структура файла локализуемого пакета.](https://cdn.sparrowcode.io/tutorials/localisation/package-configuration-file.jpg)

В файле `Localizable.strings` каждого языка должны храниться ключи и значения `NSLocalizedString`, которые мы используем в пакете. Например:

```swift
NSLocalizedString("first key", bundle: .module, comment: "")
```

А в `Localizable.strings`:

```txt
/* No comment provided by engineer. */
"first key" = "First key";
```

Указываем `bundle: .module` в инициализаторе `NSLocalizedString`, что бы указать что строку нужно искать в пакете.

### Экспорт и Импорт

![Экспорт локализации пакета.](https://cdn.sparrowcode.io/tutorials/localisation/package-export.jpg)

Чтобы экспортировать пакет, перейдите в `Products -> Export Localisations` и выберите пакет. Способы локализации экспортированных файлов рассмотрели выше.  

> При экспорте основного таргета, экспортируются и локальные SPM-пакеты.

> Xcode ниже 14 версии не экспортирует и не импортирует ключи во встроенных SPM-пакетах.

## Локализация специальных данных

Понадобится, если захотите локализовать валюту в правильном формате. Например, сумму `3 000,00 ₽`, дату `24 апр. 2022г.` или число `123456`.

### Идентификаторы языка

В примерах будем использовать `Locale.current.identifier` - параметр, которая вернет идентификатор в формате `языкприложения_ЯЗЫКРЕГИОНА`, например `en_US`. Полный список таких идентификаторов доступен [по ссылке](https://gist.github.com/jacobbubu/1836273)

> Apple используют ISO стандартизацию, поэтому при получении разных языка и региона вернуться разные значения. Например, для `en_RU` - вместо `₽` вернётся `RUB`.

### Валюта

Создадим объект `NumberFormatter`:

```swift
let currencyFormatter = NumberFormatter()
currencyFormatter.numberStyle = .currency
```

Укажем локаль:

```swift
currencyFormatter.locale = Locale.current
```

Получим локализованное значение для 3000:

```swift
print(currencyFormatter.string(from: 3000)!)
```

В консоли будет `3 000,00 ₽`.

### Дата

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
dateFormatter.locale = Locale.current
```

Выводим локализованную дату:

```swift
print(dateFormatter.string(from: currentDate))
```

В консоли будет `24 апр. 2022 г., 02:05:34`.

### Числа

Создаём и настраиваем объект класса `NumberFormatter`:

```swift
let numberFormatter = NumberFormatter()
formatter.numberStyle = .decimal
```

Локализуем с помощью `.locale`:

```swift
numberFormatter.locale = Locale.current
```

Выводим локализованное число:

```swift
print(numberFormatter.locale.string(from: 123456))
```

Получаем `123 456` в консоли.

## Локализация изображений

Представим, что нам нужно показывать флаг страны по локализации приложения. Переходим в `Assets` -> Добавляем стандартное изображение. Переходим в инспектор -> `Localize...`

![Расположение кнопки `Localize...` в `Assets` каталоге Xcode.](https://cdn.sparrowcode.io/tutorials/localisation/image-prepare.jpg)

Выбираем языки, на которые хотим локализовать изображение. Добавляем нужные изображения в появившихся полях.

![`Assets` после настройки.](https://cdn.sparrowcode.io/tutorials/localisation/image-ready.jpg)

Проверяем как отображается изображение на разных языках.

![Превью локализованного изображения.](https://cdn.sparrowcode.io/tutorials/localisation/image-preview.jpg)

## Рекомендации

Делюсь советами по работе с локализацией, что бы сэкономить время, избежать переиспользования кода.

### Разделение на файлы

#### Отдельный файл для ключей

Создаем файл, внутри делаем `enum Texts`. В нём создаём статические перемененные, которые вернут `NSLocalizedString`. Его можно структурировать, создавая дочерние `enum` внутри других `enum`:

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

В проекте получение строки будет выглядеть так:

```swift
titleLabel.text = Texts.FirstController.title
```

Если переменных много - можно создать несколько файлов и разбить их на файлы.

#### Часто-используемые слова

Функциональные слова, такие как `ОК`, `Отменить`, `Удалить` можно вынести в отдельный `enum Shared` и использовать по всему приложению, что бы не дублировать локализации:

```swift
enum Shared {
        
    static var ok: String { NSLocalizedString("shared ok", comment: "") }
    static var cancel: String { NSLocalizedString("shared cancel", comment: "") }
    static var delete: String { NSLocalizedString("shared delete", comment: "") }    
}
```

`Shared` можно вынести в отдельный пакет, что бы использовать для разных таргетов проекта.

#### Передача параметров в ключ

Можно красиво передать параметры в `NSLocalizedString`, создадим функцию в `Texts`:

```swift
static func fruitName(name: String) -> String {
    return String(format: NSLocalizedString("fruit name %@", comment: ""), name)
}
```

Вызываем в коде:

```swift
fruitNameLabel.text = Texts.fruitName(name: "Apple")
```

### Как называть ключи

`NSLocalizedString` принимает 2 параметра, которые будут видны при локализации - ключ и комментарий. Можно создать непонятный ключ и подробно описать для чего он в комментарии. Но лучше делать понятные имена. Например, футер секции с фидбеком на экране настроек: 

```swift
NSLocalizedString("settings controller table feedback section footer", comment: "")
```

### Полезные инструменты

[Poedit](https://poedit.net): Приложение для локализации `xcloc` файлов. Поддерживает автоматический перевод всех строк на другой язык, имеет удобный интерфейс.
[BartyCrouch](https://github.com/FlineDev/BartyCrouch): Автоматизация локализаций. Удаляет неиспользуемые строки, сортирует по алфавиту - можно настроить.

### Особенности

- Интерфейс должен быть динамическим. Заранее рассчитать ширину и высоту лейбла под текст не получится, потому что одни и те же слова занимают разное место в зависимости от языка. Например «Как ты?» переводится с русского на французский как «Comment allez-vous?».
- На английском языке действия, кнопки и функциональные слова - с большой буквы. Например кнопка «Add new» должна выглядеть как «Add New».