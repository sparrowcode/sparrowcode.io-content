Переводим приложение за 15 минут. Тексты, изображения, значения и SPM пакеты. Удобные инструменты и лайфхаки для работы с локализацией. 

![Пародийный постер к фильму «Перевозчик 3».](https://cdn.sparrowcode.io/tutorials/localisation/preview-ru.jpg)

## Основы

Добавим язык и переведём ключи. Это закроет 80% задач локализации.

### Добавить язык

Что бы добавить новый язык нужно перейти в настройки проекта -> Info.

![Добавление нового языка в настройках проекта.](https://cdn.sparrowcode.io/tutorials/localisation/add-new-language.jpg)

Найдите секцию `Localizations`. Нажмите на кнопку `+` и выберите нужные языки. После добавления приложение можно будет на них перевести: создать файлы локализации вручную или автоматически.

### Локализация строки

Что бы перевести строку понадобится `NSLocalizedString`-класс. Он принимает 2 параметра - ключ и комментарий. Возвращает локализованную строку.

> Если строка не локализована - вернётся имя ключа.

```swift
let localisedString = NSLocalizedString(
	"label text", // Уникальный ключ, связан со строкой
	comment: "Мало места, используем сокращения" // Комментарий для переводчика. Можно оставить пустым
)
```

Создайте файл `Localizable.strings`. Если языки автоматически не выбрались, поставьте галочки справа в инспекторе. В инспекторе будут все языки, которые поддерживает проект. 

![Инспектор с невыбранным языком.](https://cdn.sparrowcode.io/tutorials/localisation/string-localisation-inspector.jpg)

Перейдите в файл и добавьте локализацию в формате `"ключ" = "значение"`:

```swift
/* Мало места, используем сокращения */
"label text" = "Localised text";
```

Теперь при запросе ключа `label text` вернется локализованное значение `Localised text`.

### Передача параметра в строку

Пригодится, если хотите поприветствовать пользователя при входе в приложение `Привет, Имя!`, отобразить время `Осталось X минут` или передать другое значение. 

В `NSLocalizedString` можно передавать параметры - строки или числа. Для этого нужны спецификаторы:

- %@ - для значений String;
- %d - для значений Int;
- %f - для значений Float;
- %ld - для значений Long;

Есть еще спецификаторы, полный список на сайте [Apple Developer](https://developer.apple.com/library/archive/documentation/Cocoa/Conceptual/Strings/Articles/formatSpecifiers.html).

Создаём объект `String` с инициализатором `format`:

```swift
let parametrString = "Empty" // Параметр, который будем передать 

let localisedString = String.init(
    format: NSLocalizedString(
        "label text %@", // На месте %@ появится переданный ниже текст
        comment: ""
    ), parametrString // Указываем переменную, которую передаем
)
```

Теперь при выводе `localisedString` получим `label text Empty`. Переданное значение будет отображаться на месте спецификатора.

### Порядок параметров

Если в локализационной строке встретится два спецификатора одинакового типа - значения отобразятся в том порядке, в котором мы их передадим. 

Например, создаём переменную `localisedString`, принимающую 3 параметра:

```swift
let parametrString = "Make Apple"
let secondParametrString = "great again"
let parametrInt = 941

let localisedString = String.init(
    format: NSLocalizedString(
        "label text %@ %@ %d",
        comment: ""
    ), parametrString, secondParametrString, parametrInt
)
```

В `strings`-файле это должно выглядеть так:

```swift
"label text %@ %@ %d" = "Lets %1$@ a true %2$@ at %3$d o’clock";
// `%1$@` - для первого текстового значения и так далее. `%3$d` - для первого числового значения.
```

Теперь при выводе переменной `localisedString` мы получим текст: `Lets Make Apple a true great again at 941 o'clock`

Если поменяем порядок элементов, то изменится их порядок отображения при выводе. Например, если создадим `localisedString` так: 

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

При выводе получим: `Lets great again a true Make Apple at 941 o'clock`

## Локализация `InfoPlist`

`info.plist` - системный файл, который автоматически появляется в проекте после создания и используется для его сборки и запуска. Содержит в себе информацию о бандле, имени приложения, ключах разрешений и так далее. Последние 2 можно и нужно локализовать.

Для каждого языка создаем файл `InfoPlist.strings`, в инспекторе справа ставим галочки около всех добавленных в проект языков.

Что бы локализовать название приложения добавляем в файл `CFBundleName` в формате `"ключ" = "значение"`:

```swift
// На русском:

/* Bundle name */
"CFBundleName" = "Название приложения";

// На английском:

/* Bundle name */
"CFBundleName" = "App name";
```

Когда добавляете в `info.plist` разрешения, например на использование камеры - нужно объяснить для чего он вам нужен и перевести на другие языки. 

![Список разрешений с сайта «iosdev.recipes».](https://cdn.sparrowcode.io/tutorials/localisation/infoplist-permissions-ru.jpg)

Копируем название ключа, который используем в приложении. Вставляем в файл и локализуем, например:

```swift
// На русском:

/* Privacy - Camera Usage Description */
"NSCameraUsageDescription" = "Мы используем камеру, что бы делать фото.";

// На английском:

/* Privacy - Camera Usage Description */
"NSCameraUsageDescription" = "We use the camera to take pictures.";
```

## `Export` и `Import` локализации

Переходим в Products и видим кнопки `Export Localizations...` и `Import Localizations...`.

![Расположение кнопок в верхнем баре.](https://cdn.sparrowcode.io/tutorials/localisation/export-menu.jpg)

`Export` позволяет получить каталоги каждого языка с созданными нами ключами `Localizable.strings` и `InfoPlist.strings` для дальнейшего перевода.

![Сгенерированные `xcloc` каталога.](https://cdn.sparrowcode.io/tutorials/localisation/export-xcloc.jpg)

Xcode создаст `Localization Catalog` (папку с расширением файла `xcloc`), содержащий локализуемые ресурсы для каждого языка и региона. В каталоге находится 4 файла:
- Файл `contents.json` - содержит метаданные о каталоге, такие как регион разработки, язык, номер версии Xcode, а также номер версии каталога.
- Папка `Localized Contents` - содержит локализуемые ресурсы, включая файл `XLIFF`, содержащий локализуемые строки.
- Папка `Notes` - содержит дополнительную информацию для переводчиков, например, скриншоты, фильмы или текстовые файлы.
- Папка `Source Contents` - содержит исходные `strings`-файлы, а так же дополнительный контекст для переводчиков (файлы интерфейса и другие ресурсы).

Открываем его левой кнопкой мыши, что бы перевести приложение на нужный язык.

![Встроенный в Xcode переводчик.](https://cdn.sparrowcode.io/tutorials/localisation/export-xcode-translator.jpg)

Это переводчик, встроенный в Xcode. На сайдбаре есть 2 файла - `InfoPlist` и `Localizable`, здесь они переводятся отдельно.

В первой колонке виден ключ, во второй заполняем перевод, а в третьей будет комментарий (если оставляли при создании `NSLocalizedString`). `InfoPlist` переводится идентично. 

После перевода - сохраняем файл и возвращаемся в проект. Снова переходим в Product, но уже выбираем `Import Localizations`. 

![Импортирование `xcloc` каталогов в проект.](https://cdn.sparrowcode.io/tutorials/localisation/export-import.jpg)

Здесь по-отдельности выбираем каждый каталог и загружаем в проект. Вуаля! В файле `Localizable.strings` нужного языка появятся переведённые ключи:

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

На этом этапе многие закроют ноутбук и откроют шампанское, но не стоит торопиться. Встроенный переводчик удобен если надо перевести небольшой объем текста, для других задач подойдет [Poedit](https://poedit.net).

### Poedit

Покажет ошибки в переводе, отсутствующие строки, может автоматически перевести ключи на другой язык.

Возвращаемся на 2 минуты назад. Мы снова в папке с `xсloc` каталогами. Вместо того, что бы открыть его левой кнопкой мыши - нажимаем правую и переходим в содержимое пакета.

![Содержимое `xcloc` каталога.](https://cdn.sparrowcode.io/tutorials/localisation/export-xcloc-detail.jpg)

Глаза разбегаются, но не стоит паниковать - здесь нас интересует папка `Localized Contents`. Внутри будет `xliff` файл, открываем через `Poedit`.

![Интерфейс Poedit.](https://cdn.sparrowcode.io/tutorials/localisation/export-poedit.jpg)

Здесь все ключи списком. Выбираете нужный, внизу появляется исходный ключ и поле для ввода перевода. Если перевели приложение на основной язык, например английский - вместо ключей будет отображаться он. Справа есть варианты перевода, ключ и комментарий.

После перевода сохраняем файл и импортируем `xcloc` в проект.

### BartyCrouch

Автоматически ищет все локализации в проекте, обновляет `strings`-файлы при появлении новых, удалении старых `NSLocalizedString` или `views` в `Storyboard` и `XIB`. Сортирует ключи по алфавиту, что бы избежать конфликтов слияния.

**Устанавливаем:**

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

Это стандартная конфигурация, её можно настроить. 

Прописываем `paths` и `codePaths` для улучшения работы:

```swift
// Указывайте путь к файлам в вашем проекте, например:
paths = ["App/Localisations/"]
codePaths = ["App/Data/"]
```

Другие опции:

Для задачи `interfaces`:

- `subpathsToIgnore = ["."]` - пути к файлам, которые будут игнорироваться при проверке.
- `defaultToBase = true` - добавляет значение от стандартного языка к новым, не локализованным ключам.
- `ignoreEmptyStrings = true` - не допускает создание `view` для пустых строк.
- `unstripped = true` - сохраняет пробелы в начале и конце `strings`-файлов.

Для задачи `normalize`:

- `separateWithEmptyLine = false` - создаёт пробелы между строками.
- `sourceLocale = "."` - переопределяет основной язык.
- `harmonizeWithSource = true` - синхронизирует ключи с остальными языками.
- `sortByKeys = true` - сортирует ключи по алфавиту.

Опций больше, полный список есть [в документации](https://github.com/FlineDev/BartyCrouch#configuration).

Запускаем проверку `Bartycrouch` в терминале. Все команды вызовутся автоматически:
```swift
bartycrouch update
```

Готово, мы сэкономили час работы и 2 таблетки успокоительного. `BartyCrouch` проверил ключи, добавил в `strings`-файлы и избавился от ненужных. 

Команды, которые вызываются через `update` меняются, например: 

```swift
[update]
tasks = ["interfaces", "normalize", "code"]
```

Теперь при вызове отработают только 3 задачи. Ещё есть `lint` - задача, которая по умолчанию делает поверхностную проверку (ищет повторяющиеся ключи и пустые строки). Она тоже настраивается.

Что бы не вызывать `Bartycrouch` вручную, в проект можно добавить скрипт, который сделает всё за вас:

Переходим в таргет проекта -> `Build Phase`, нажимаем на плюсик и создаём новый скрипт:

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

Теперь `Bartycrouch` будет делать проверку автоматически и напомнит, если его надо установить. Например, если открыли проект на другом компьютере.

## Плюрализация

Нужна для правильной локализации при передаче количества, например:

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

Создаём новый файл. В поиске пишем `strings` и выбираем `Stringsdict File`. Называем `Localizable`, добавляем в проект.

![Добавление `Stringsdict` файла.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-new-stringsdict.jpg)

Переходим в файл, видим следующую структуру:

![Структура файла `Stringsdict`.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-stringsdict-empty.jpg)

- `Localised String Key` - локализационный ключ, созданный ранее (headphones count).
- `Localised Format Key` - параметр, значение которого войдёт в строку результата. В нашем случае только один (count).
- `NSStringFormatSpecTypeKey` - указывает единственный возможный тип перевода `NSStringPluralRuleType`, который значит то, что в переводе встречается множество имён существительных (то, что мы хотим сделать) - его не трогаем.
- `NSStringFormatValueTypeKey` - строковый спецификатор формата числа (например `d` для целых чисел).
- `zero, one, two, few, many, other` - различные формы множественного числа для языков. Обязательное `other` - оно будет использовано, если переданное число не удовлетворит ни одно из перечисленных условий. Остальные можно убрать, если они не требуются для локализуемого слова.

Заполняем файл:

![Заполненный ключ `headphones count`.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-string-headphones-prepare.jpg)

Видим, что `two, few, many` и `other` повторяются. Обязательно только последнее, поэтому остальные убираем.

![Отрефракторенный ключ `headphones count`.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-string-headphones-ready.jpg)

Файл заполнен, но при вызове функции `headphonesCount(count: 1)` мы получим ключ `headphones count`, вместо перевода, потому что Xcode не локализует `.stringsdict` автоматически.

Переходим в инспектор -> кнопка `Localize...`

![Расположение кнопки `Localize...` в инспекторе.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-localize-button.jpg)

Затем выбираем языки, для которых нужно создать `.stringsdict` файлы - доступны добавленные в проект.

![Выбор языков для перевода в инспекторе.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-localize-languages.jpg)

Локализовать `.stringsdict` можно как в новом созданной файле, так и через `xcloc` файл после экспорта. Пойдём первым путём.

Выбираем `Localizable (Russian)` в левом меню.

![`stringsdict`-файлы на сайдбаре.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-sidebar-languages.jpg)

Заполняем строки на русском, добавляем `few` для корректного перевода числа на этом языке.

![Локализованный ключ `headphones count`.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-string-headphones-translated.jpg)

Получаем:

```swift
// На русском языке

headphonesCount(count: 0)
// У Тима нет наушников
headphonesCount(count: 1)
// У Тима 1 наушник
headphonesCount(count: 2)
// У Тима 2 наушника
headphonesCount(count: 7)
// У Тима 7 наушников

// На английском языке

headphonesCount(count: 0)
// Tim doesn't have headphones
headphonesCount(count: 1)
// Tim has 1 headphone
headphonesCount(count: 2)
// Tim has 2 headphones
headphonesCount(count: 7)
// Tim has 7 headphones
```

Если нужно локализовать другое слово - создайте новое значение в `.stringsdict` файле, например считаем яблоки.

Создаём функцию с новым ключем.

```swift
func applesCount(count: Int) -> String {
    let formatString: String = NSLocalizedString("apples count", comment: "")
    let resultString: String = String.localizedStringWithFormat(formatString, count)
    return resultString
}
```

Переходим в `.stringsdict`, создаём новое значение `apples count`. Настраиваем как раньше. 

![Новый заполненный ключ `apples count`.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-string-apples-ready.jpg)

Что бы локализовать новое значение на другие языки - экспортируем локализацию и открываем нужный `xcloc`.

![Локализация `stringsdict`-файла в переводчике Xcode.](https://cdn.sparrowcode.io/tutorials/localisation/export-xcode-translator.jpg)

Переводим и импортируем в проект. Видим, что в `.stringsdict` файле русского языка осталось лишнее значение `many` - удаляем его и приводим остальные в порядок.

![Отрефракторенный ключ `apples count`.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-string-apples-translated.jpg)

Проверяем:

```swift
// На русском языке

applesCount(count: 0)
// У Тима нет яблок
applesCount(count: 1)
// У Тима 1 яблоко
applesCount(count: 7)
// У Тима 7 яблок
applesCount(count: 131)
// У Тима 131 яблоко
applesCount(count: 152)
// У Тима 152 яблока

// На английском языке

applesCount(count: 0)
// Tim doesn't have apples
applesCount(count: 1)
// Tim has 1 apple
applesCount(count: 7)
// Tim has 7 apples
applesCount(count: 131)
// Tim has 131 apples
applesCount(count: 152)
// Tim has 152 apples
```

## Локализация SPM-пакетов

Создаём папку, в названии пишем идентификатор языка, на который хотим перевести пакет, например `en.lproj`. У каждого языка есть свой идентификатор, полный список можно посмотреть [по ссылке](https://gist.github.com/jacobbubu/1836273). В папке создаём файл `Localizable.strings`. 

Повторяем процедуру для каждого языка, который хотим добавить, меняя название папки.

![Структура локализуемого пакета.](https://cdn.sparrowcode.io/tutorials/localisation/package-configuration-structure.jpg)

В файле `Package` выставляем `defaultLocalization` - стандартный язык локализации, указываем нашу папку с файлами локализации в `resources`.

![Структура файла локализуемого пакета.](https://cdn.sparrowcode.io/tutorials/localisation/package-configuration-file.jpg)

В файле `Localizable.strings` каждого языка должны храниться ключи и значения `NSLocalizedString`, которые мы используем в пакете. Например:

```swift
// Swift File

NSLocalizedString("first key", bundle: .module, comment: "")

// Localizable.strings

/* No comment provided by engineer. */
"first key" = "First key";
```

Указываем `bundle: .module` в инициализаторе `NSLocalizedString`, что бы указать отношение к пакету.

![Экспорт локализации пакета.](https://cdn.sparrowcode.io/tutorials/localisation/package-export.jpg)

Экспортируем локализацию, выбираем пакет. Переводим и импортируем обратно в проект. Готово - пакет локализован. 

> Xcode ниже 14 версии не экспортирует и не импортирует локализационные ключи во встроенных в проект пакетах. 

Можно прописывать каждый ключ вручную или воспользоваться нашим вариантом: 

- Создаём пустой проект. 
- Добавляем в него языки и ключи, которые используем в пакете. 
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

Пакет локализован. Сохраните проект для дальнейших локализаций.

## Локализация значений

### Идентификаторы языка

В примерах будем использовать `Locale.current.identifier` - функцию, которая вернет идентификатор в формате `языкприложения_ЯЗЫКРЕГИОНА`, например `en_US`. Полный список таких идентификаторов доступен [по ссылке](https://gist.github.com/jacobbubu/1836273)

> Apple используют ISO стандартизацию, поэтому при получении идентификатора из отличающегося языка региона и приложения, например `en_RU` - вместо `₽` вернётся `RUB` и так далее.

### Валюта

Создаём и настраиваем объект класса `NumberFormatter`:

```swift
let currencyFormatter = NumberFormatter()
currencyFormatter.numberStyle = .currency
```

Локализуем с помощью `.locale`:

```swift
currencyFormatter.locale = Locale(identifier: Locale.current.identifier) 
```

Выводим локализованное значение, например 3000:

```swift
print(currencyFormatter.string(from: 3000)!)
```

Получаем `3 000,00 ₽` в консоли.

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
dateFormatter.locale = Locale(identifier: Locale.current.identifier) 
```

Выводим локализованную дату:

```swift
print(dateFormatter.string(from: currentDate))
```

Получаем `24 апр. 2022 г., 02:05:34` в консоли.

Вместо `currentDate` можно локализовать другую дату.

### Числа

Создаём и настраиваем объект класса `NumberFormatter`:

```swift
let numberFormatter = NumberFormatter()
formatter.numberStyle = .decimal
```

Локализуем с помощью `.locale`:

```swift
numberFormatter.locale = Locale(identifier: Locale.current.identifier) 
```

Выводим локализованное число:

```swift
print(numberFormatter.locale.string(from: 123456))
```

Получаем `123 456` в консоли.

## Локализация изображений

Представим, что нам нужно показывать флаг страны, на язык которой локализовано приложение.

Переходим в `Assets` -> Добавляем стандартное изображение (появится, если для языка, который используется в приложении нет локализованного изображения). Для максимальной трушности выставляем `single scale`.

Переходим в инспектор -> кнопка `Localize...`

![Расположение кнопки `Localize...` в `Assets` каталоге Xcode.](https://cdn.sparrowcode.io/tutorials/localisation/image-prepare.jpg)

Выбираем языки, на которые хотим локализовать изображение (доступны все, добавленные в проект). Добавляем нужные изображения в появившихся полях.

![`Assets` после настройки.](https://cdn.sparrowcode.io/tutorials/localisation/image-ready.jpg)

Проверяем как отображается изображение на разных языках.

![Превью локализованного изображения.](https://cdn.sparrowcode.io/tutorials/localisation/image-preview.jpg)

## Рекомендации

Делюсь советами по работе с локализацией, что бы сэкономить время, избежать переиспользования кода и других трудностей.

### Разделение на файлы

#### Отдельный файл для ключей

Создаем файл и `enum` `Texts`. В нём создаём статические перемененные, которые вернут `NSLocalizedString`. Его можно сортировать, создавая `enum` внутри:

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

Делаем это для того, что бы было удобно работать с ключами. В коде используем следующую запись:

```swift
titleLabel.text = Texts.FirstController.title
```

Если переменных слишком много - можно создать несколько файлов и сделать их `extension Texts` для большего контроля.

#### Часто-используемые слова

Функциональные слова, такие как `ОК`, `Отменить`, `Удалить` и так далее, можно вынести в отдельный `enum Shared` и использовать по всему приложению, что бы не создавать одинаковых локализаций:

```swift
enum Shared {
        
    static var ok: String { NSLocalizedString("shared ok", comment: "") }
    static var cancel: String { NSLocalizedString("shared cancel", comment: "") }
    static var delete: String { NSLocalizedString("shared delete", comment: "") }    
}
```

`Shared` можно вынести в отдельный пакет, что бы использовать для разных модулей проекта и менять в одном месте для всех сразу. 

#### Передача параметров в ключ

Можно красиво оформить передачу параметров в `NSLocalizedString`, создав такую функцию в `Texts`:

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

Создаём правильный ключ. `NSLocalizedString` принимает 2 параметра, которые в дальнейшем будут видны при локализации - ключ и комментарий. 

Можно создать не понятный ключ и подробно описать для чего он в комментарии, но лучше создать так, что бы было понятно без него. Например, футер секции с фидбеком на экране настроек: 

```swift
NSLocalizedString("settings controller table feedback section footer", comment: "")
```

### Полезные инструменты

[Poedit](https://poedit.net): Приложение для локализации `xcloc` файлов. Поддерживает автоматический перевод всех строк на другой язык, имеет удобный интерфейс.
[BartyCrouch](https://github.com/FlineDev/BartyCrouch): Инструмент для рефракторинга локализаций. Удаляет неиспользуемые строки, сортирует по алфавиту, сообщает о других ошибках - можно настроить под свои задачи.

### Особенности локализации

Если проект большой - обращайтесь к переводчикам. Если переводите проект вручную - посмотрите лайфхаки:

- Интерфейс должен быть динамическим. Заранее рассчитать ширину и высоту лейбла под текст не получится, потому что одни и те же слова занимают разное место в зависимости от языка. Например «Как ты?» переводится с русского на французский как «Comment allez-vous?».
- На английском языке действия, кнопки и функциональные слова - с большой буквы. Например кнопка «Add new» должна выглядеть как «Add New».

Если знаете ещё - [дополните статью через PR](https://github.com/sparrowcode/sparrowcode.io-content/blob/main/ru/tutorials/localisation.md).