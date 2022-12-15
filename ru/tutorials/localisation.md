Это большой ультимативный гайд по локализации. Если вы только начинаете изучить локализацию - рекомендуем читать по порядку. Все инструменты в статье редакция выстрадала опытом и временем.

![Пародийный постер к фильму `Перевозчик 3`.](https://cdn.sparrowcode.io/tutorials/localisation/preview-ru.jpg)

# Основы

Начнём с основ — добавим язык и переведём слова.

## Добавление языка

Чтобы добавить новый язык, перейдите в `Настройки проекта` -> `Info`. Потом найдите секцию `Localizations`, нажмите на кнопку `+` и выберите новый язык.

![Добавление нового языка в настройках проекта.](https://cdn.sparrowcode.io/tutorials/localisation/add-new-language.jpg)

## Локализация строки

Чтобы перевести строку, используем макрос `NSLocalizedString`. Он принимает 2 параметра — ключ и комментарий, а возвращает уже локализованную строку.

```swift
let localisedString = NSLocalizedString(
    "label text", // уникальный ключ, связан со строкой
    comment: "Пример комментария для ключа" // комментарий для переводчика, можно оставить пустым
)
```

> Если строка не локализована, вернётся имя ключа.

Теперь добавим перевод. Создайте файл `Localizable.strings`. Файл можно перевести на языки, которые поддерживает проект. Необязательно переводить на все языки. В инспекторе справа вы увидите, какие языки поддерживает файл. Чтобы перевести строки на новый язык, поставьте рядом с ним галочку.

![Здесь выбираем языки для локализации файла.](https://cdn.sparrowcode.io/tutorials/localisation/string-localisation-inspector.jpg)

Локализация заполняется в формате `"ключ" = "значение";`. Перейдите в файл и добавьте строки:

```txt
/* Пример комментария для ключа */
"label text" = "Localised Text";
```

Ключ локализован - теперь по ключу `label text` вернётся локализованное значение `Localised Text`. Заполните по аналогии другие языки.

Структура папок на диске будет отличаться. Xcode создаст папку с идентификатором локали `en.lproj` и файлы локализации будет помещать в неё. Так в папке может быть несколько файлов одной локализации.

![Как выглядят файлы локализации в папке.](https://cdn.sparrowcode.io/tutorials/localisation/locale-folder-files.jpg)

## Передача параметра в строку

Возможность пригодится, если хотите поприветствовать пользователя. Например, написать `Привет, Имя!` или отобразить время `Осталось X минут`. В `NSLocalizedString` можно передавать параметры — строки или числа. Для этого нужны спецификаторы - Xcode заменит их на значения:

- %@ - для значений String;
- %d - для значений Int;
- %f - для значений Float;
- %ld - для значений Long;

Весь список спецификаторов находится на сайте [Apple Developer](https://developer.apple.com/library/archive/documentation/Cocoa/Conceptual/Strings/Articles/formatSpecifiers.html).

Перейдём к примеру. Давайте создадим объект `String` с инициализатором `format`:

```swift
let parametrString = "Parametr Example" // параметр, который будем передавать 

let localisedString = String(
    format: NSLocalizedString(
        "label text", // ключ локализации 
        comment: "" // комментарий
    ), parametrString // переменная
)
```

Теперь локализуем ключ с параметром. Перейдём в `Localizable.strings` и добавим:

```txt
"label text" = "Localised Text with %@";
```

Мы использовали спецификатор для параметров с типом `String` - `%@`, он заменится значением.  Теперь при выводе ключа `label text` получим `label text with Parametr Example`.

## Порядок параметров

Если в строке находятся два спецификатора одинакового типа, то значения отобразятся в том порядке, в котором мы их передадим. Давайте создадим переменную `localisedString`, принимающую 3 параметра:

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

Нумерацию параметров можно не указывать, тогда строка будет такая:

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
    secondParametrString, parametrString, parametrInt // меняем parametrString и secondParametrString местами
)
```

При выводе получим `Lets great again a true Make Apple at 941 o'clock`

# Локализация `Info.plist`

`Info.plist` — системный файл проекта, который содержит информацию о бандле, имени приложения, ключах разрешений и т. д. Мы можем локализовать имя приложения и ключи разрешений. Создаём файл `InfoPlist.strings` и в инспекторе выбираем поддерживаемые языки.

> Файл должен называться `InfoPlist.strings`, иначе локализация не заработает.

Чтобы локализовать название приложения, добавим в файл `CFBundleName` в формате `"ключ" = "значение";`:

```txt
"CFBundleName" = "App name";
```

Когда добавляете в `Info.plist` разрешения, например, для использования камеры, объясните, зачем оно нужно приложению. Локализуем это сообщение. 

![Пример текста в запросе разрешения.](https://cdn.sparrowcode.io/tutorials/localisation/infoplist-permission-example.jpg)

Список ключей можно глянуть [здесь](https://github.com/sparrowcode/PermissionsKit#permissions). Вставляем ключ и локализуем:

```txt
/* Privacy - Camera Usage Description */
"NSCameraUsageDescription" = "We use the camera to take pictures.";
```

# Экспорт и импорт локализации

Экспорт и импорт локализации автоматизирует действия, добавляет ключи. Экспорт помогает передать файлы переводчику, не передавая весь проект целиком. Переводчик видит имя ключа и комментарии к нему.

Перейдём в `Product`. Тут мы видим кнопки `Export Localizations...` и `Import Localizations...`.

![Расположение кнопок экспорта и импорта локализации.](https://cdn.sparrowcode.io/tutorials/localisation/export-menu.jpg)

После экспорта создаются файлы `xcloc` — в них находится информация для переводчика:

![Сгенерированные `xcloc` каталога.](https://cdn.sparrowcode.io/tutorials/localisation/export-xcloc.jpg)

> `xcloc` расшифровывается как Xcode Localization Catalog
 
Внутри `xcloc` находятся 3 папки и файл:

- Папка `Localized Contents` содержит локализуемые ресурсы, включая файл `xliff`. В нём находятся локализуемые строки.
- Папка `Notes` содержит дополнительную информацию для переводчиков: скриншоты, видео или текстовые файлы.
- Папка `Source Contents` хранит исходные `strings`-файлы и контекст для переводчиков: файлы интерфейса и другие ресурсы.
- Файл `contents.json` хранит метаданные о каталоге: регион разработки, язык, номер версии Xcode, а также номер версии каталога.

> В Xcode 12 экспортировался только `xliff`. Теперь `xliff` только часть каталога `xloc`.

Переведём приложение через экспортированный файл. У Xcode есть встроенная IDE для редактирования файла. Откройте `xcloc`-каталог.

![Встроенная в Xcode IDE для редактирования `xcloc`.](https://cdn.sparrowcode.io/tutorials/localisation/export-xcode-translator.jpg)

На сайдбаре увидите 2 файла — `InfoPlist` и `Localizable`. В первой колонке ключ, во второй переводим его, а в третьей находится комментарий. Сохраните файл после перевода. Чтобы импортировать локализацию, перейдите в `Product` -> `Import Localizations`. 

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

Встроенный переводчик удобно использовать для быстрой правки локализации.

## Poedit

Это альтернативная IDE для редактирования `xсloc`-каталогов. Она покажет ошибки в переводе, отсутствующие строки и автоматически переведёт ключи на другой язык. 

Poedit умеет читать только `xliff`-файлы, поэтому открываем `xcloc`-каталог правой кнопкой и переходим в содержимое пакета.

![Содержимое `xcloc` каталога.](https://cdn.sparrowcode.io/tutorials/localisation/export-xcloc-detail.jpg)

Итак, нас интересует папка `Localized Contents`. Внутри будет `xliff` файл, откройте его через `Poedit`.

![Интерфейс Poedit.](https://cdn.sparrowcode.io/tutorials/localisation/export-poedit.jpg)

Здесь содержатся все ключи списком. Выберите нужный. После этого внизу появится исходный ключ и поле для ввода перевода. Справа есть варианты перевода, ключ и комментарий. После перевода сохраните файл и импортируйте `xсloc` в проект.

> Можно импортировать не только `xсloc` целиком, но и отдельно `xliff`-файлы.

## BartyCrouch

Это консольный инструмент и встраиваемый плагин. Он автоматизирует локализацию и генерацию ключей, обновляет `strings`-файлы, удаляет неиспользуемые ключи и сортирует ключи по алфавиту.

### Установка

Как установить `BartyCrouch`:
- Откройте терминал и установите [Homebrew](https://brew.sh):
```
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```
- Введите в терминал `brew install bartycrouch`
- Создайте дефолтный конфиг, для этого вставьте в терминал:
```
bartycrouch init
```

В папке появится скрытый файл `.bartycrouch.toml`. 

![Стандартный файл-конфигуратор `Bartycrouch`.](https://cdn.sparrowcode.io/tutorials/localisation/autogeneration-bartycrouch-file.jpg)

Это стандартная конфигурация. 

### Настройка

Прописываем `paths` и `codePaths` чтобы файлы локализации нашлись быстрее:

```swift
// Указывайте путь к файлам в вашем проекте, например:
paths = ["App/Localisations/"] // `strings`-файл
codePaths = ["App/Data/"] // Файл с `enum`, если используете `supportedLanguageEnumPaths`
```

Что пригодится для задачи `interfaces`:
- `subpathsToIgnore = ["."]` — пути к файлам, которые нужно игнорировать.
- `defaultToBase = true` — добавляет значение от стандартного языка к новым не локализованным ключам.
- `ignoreEmptyStrings = true` — запрещает создание `view` для пустых строк.
- `unstripped = true` — сохраняет пробелы в начале и конце `strings`-файлов.

Что пригодится для задачи `normalize`:
- `separateWithEmptyLine = false` — создаёт пробелы между строками.
- `sourceLocale = "."` — переопределяет основной язык.
- `harmonizeWithSource = true` — синхронизирует ключи с остальными языками.
- `sortByKeys = true` — сортирует ключи по алфавиту.

Опций больше, весь список смотрите [в документации](https://github.com/FlineDev/BartyCrouch#configuration).

После настройки конфига запустите проверку:
```
bartycrouch update
```

`BartyCrouch` проверит ключи, добавит их в `strings`-файлы и избавится от ненужных строк. Команды, которые будут выполнятся через `update`, можно настроить. Например: 

```json
[update]
tasks = ["interfaces", "normalize", "code"]
```

Теперь при вызове отработают только 3 задачи. Ещё есть `lint`-задача, которая по умолчанию делает поверхностную проверку. Она ищет повторяющиеся ключи и пустые строки.

### Встроить в Xcode

Чтобы не вызывать `Bartycrouch` вручную, можно встроить его в Xcode — проверка будет запускаться при каждом билде. Переходим в таргет проекта -> `Build Phase`, нажимаем на плюсик и создаём новый скрипт:

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

# Плюрализация

Пригодится, если захотим локализовать количество, например:

- У Тима нет наушников;
- У Тима 1 наушник;
- У Тима 2 наушника;
- У Тима 7 наушников;

Создаём функцию:

```swift
func headphonesCount(count: Int) -> String {
    let formatString: String = NSLocalizedString("headphones count", comment: "Don't localise, will localise in stringsdict") 
    let resultString: String = String.localizedStringWithFormat(formatString, count) // передаем count
    return resultString // возвращаем нужный текст
}
```

Создаём новый файл. В поиске пишем `strings` и выбираем `Stringsdict File`. Называем `Localizable` и добавляем в проект.

![Добавление `Stringsdict` файла.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-new-stringsdict.jpg)

Переходим в файл и видим структуру:

![Структура файла `Stringsdict`.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-stringsdict-empty.jpg)

- `Localised String Key` — ключ локализации - `headphones count`.
- `Localised Format Key` — параметр, значение которого войдёт в строку результата. В нашем случае только один: `count`.
- `NSStringFormatSpecTypeKey` — указывает единственный возможный тип перевода `NSStringPluralRuleType`. Он значит то, что в переводе встречается множество имён существительных (то, что мы хотим локализовать). Его не трогаем.
- `NSStringFormatValueTypeKey` — строковый спецификатор формата числа. Например, `d` для целых чисел. Полный список [тут](https://developer.apple.com/library/archive/documentation/Cocoa/Conceptual/Strings/Articles/formatSpecifiers.html).
- `zero, one, two, few, many, other` — различные формы множественного числа для языков. Обязательное `other` — оно будет использовано, если переданное число не удовлетворит ни одному из перечисленных условий. Остальные можно убрать, если они не используются.

Заполняем файл:

![Заполненный ключ `headphones count`.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-string-headphones-prepare.jpg)

Видим, что `two, few, many` и `other` повторяются. Обязательно только `other`, поэтому остальные убираем.

![Отрефракторенный ключ `headphones count`.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-string-headphones-ready.jpg)

Файл заполнен, но при вызове функции `headphonesCount(count: 1)` вместо перевода мы получим ключ `headphones count`.

> Xcode не локализует `stringsdict` автоматически.

Чтобы локализовать `stringsdict`, перейдём в инспектор -> кнопка `Localize`

![Расположение кнопки `Localize` в инспекторе.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-localize-button.jpg)

Затем выбираем языки, для которых нужно создать `stringsdict`-файлы.

![Выбор языков для перевода в инспекторе.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-localize-languages.jpg)

Локализовать `.stringsdict` можно прямо в созданном файле. Выбираем `Localizable (Russian)` в левом меню.

![`stringsdict`-файлы на сайдбаре.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-sidebar-languages.jpg)

Заполняем строки, добавляем `few` для корректного перевода числа на русском.

![Локализованный ключ `headphones count`.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-string-headphones-translated.jpg)

Что получим:

```txt
headphonesCount(count: 0) // У Тима нет наушников
headphonesCount(count: 1) // У Тима 1 наушник
headphonesCount(count: 2) // У Тима 2 наушника
headphonesCount(count: 7) // У Тима 7 наушников
```

Если нужно локализовать другое слово, создайте новое значение в `stringsdict`-файле. Например, посчитаем яблоки. Для этого создаём функцию с новым ключом:

```swift
func applesCount(count: Int) -> String {
    let formatString: String = NSLocalizedString("apples count", comment: "")
    let resultString: String = String.localizedStringWithFormat(formatString, count)
    return resultString
}
```

Переходим в `stringsdict`, создаём новое значение `apples count`. Настраиваем так же, как в прошлых шагах. 

![Новый заполненный ключ `apples count`.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-string-apples-ready.jpg)

Новое значение всё ещё можно локализовать прямо в файле, но в этот раз для перевода используем другой способ и экспортируем локализацию через `Product` -> `Export Localizations...`. Открываем нужный `xcloc`-каталог:

![Локализация `stringsdict`-файла в переводчике Xcode.](https://cdn.sparrowcode.io/tutorials/localisation/export-xcode-translator.jpg)

Переводим и импортируем в проект через `Product` -> `Import Localizations...`. В `stringsdict`-файле русского языка осталось лишнее значение `many`. Удаляем его.

![Отрефракторенный ключ `apples count`.](https://cdn.sparrowcode.io/tutorials/localisation/pluralisation-string-apples-translated.jpg)

Проверяем:

```swift
applesCount(count: 0) // У Тима нет яблок
applesCount(count: 1) // У Тима 1 яблоко
applesCount(count: 7) // У Тима 7 яблок
applesCount(count: 131) // У Тима 131 яблоко
applesCount(count: 152) // У Тима 152 яблока
```

# Локализация SPM-пакетов

Чтобы локализовать SPM-пакет, создадим папку внутри пакета с идентификатором языка. Например, `en.lproj`. У каждого языка есть свой идентификатор, весь список можно глянуть [по ссылке](https://gist.github.com/jacobbubu/1836273). В папке создаём файл `Localizable.strings`. 

Повторяем процедуру для каждого нужного языка.

![Структура локализуемого пакета.](https://cdn.sparrowcode.io/tutorials/localisation/package-configuration-structure.jpg)

В файле `Package` выставляем `defaultLocalization` — этот язык будет использоваться по умолчанию. Указываем папку с файлами локализации в `resources`.

![Структура файла локализуемого пакета.](https://cdn.sparrowcode.io/tutorials/localisation/package-configuration-file.jpg)

В файле `Localizable.strings` каждого языка должны храниться пары ключ-значение `NSLocalizedString`, которые мы используем в пакете. Например:

```swift
NSLocalizedString("first key", bundle: .module, comment: "")
```

Указываем `bundle: .module` в инициализаторе `NSLocalizedString`. Так мы указываем, что строку нужно искать в пакете. А в `Localizable.strings` локализуем как обычно:

```txt
/* No comment provided by engineer. */
"first key" = "First key";
```

## Экспорт и импорт

![Экспорт локализации пакета.](https://cdn.sparrowcode.io/tutorials/localisation/package-export.jpg)

Чтобы экспортировать пакет, перейдите в `Product` -> `Export Localisations` и выберите пакет. Выше мы как раз рассмотрели способы локализации экспортированных файлов.  

> При экспорте главного таргета экспортируются и локальные SPM-пакеты.

> Xcode ниже 14 версии не экспортирует и не импортирует ключи в локальных SPM-пакетах.

# Локализация специальных данных

Она пригодится, если захотите локализовать данные в правильном формате в зависимости от выбранного языка. Например, сумму `3 000,00 ₽`, дату `24 апр. 2022 г.` или процент `54 %`.

![Пример локализации процента на разные языки с помощью форматтера.](https://cdn.sparrowcode.io/tutorials/localisation/formatters-preview.jpg)

В английском языке процент пишется слитно с числом `61%`, а в немецком - раздельно `61 %`. Что бы значения автоматически изменялись правильно - существуют форматтеры, подробнее о них в нашей [статье по форматтерам](https://sparrowcode.io/ru/tutorials/formatters).

# Идентификаторы языка

Чтобы получить идентификатор локали, вызовите `Locale.current.identifier`. Вернётся значение `языкприложения_ЯЗЫКРЕГИОНА`, например, `en_US`. Полный список таких идентификаторов найдёте [по ссылке](https://gist.github.com/jacobbubu/1836273)

> Apple используют ISO стандартизацию, поэтому если на устройстве язык, который не соответствует региону, вернутся разные значения. Например, для `en_RU` вместо `₽` вернётся `RUB`.

# Локализация изображений

Представим, что нам нужно показывать флаг страны по локализации приложения. Переходим в `Assets` -> Добавляем стандартное изображение. Потом переходим в инспектор -> `Localize...`

![Расположение кнопки `Localize...` в `Assets` каталоге Xcode.](https://cdn.sparrowcode.io/tutorials/localisation/image-prepare.jpg)

Выбираем языки, на которые хотим локализовать изображение. Добавляем нужные изображения в появившихся полях.

![`Assets` после настройки.](https://cdn.sparrowcode.io/tutorials/localisation/image-ready.jpg)

Проверяем, как отображается изображение на разных языках.

![Превью локализованного изображения.](https://cdn.sparrowcode.io/tutorials/localisation/image-preview.jpg)

# Языки справа-налево RTL

В английском, русском, немецком и других языках текст пишется слева-направо. Но есть языки с направлением справа-налево, например иврит, персидский и арабский.

> Направление текста слева-направо называется Left-to-Right, сокр. LTR. Направление справа-налево называется RTL.

![Пример LTR и RTL интерфейса.](https://cdn.sparrowcode.io/tutorials/localisation/ltr-rtl-preview.jpg)

Направление текста, а соответственно и интерфейса, можно определить разными способами. Глобальное направление приложения определяется в объекте `UIApplication`:

```swift
if UIApplication.shared.userInterfaceLayoutDirection == .leftToRight {
    // Код для LTR направления
} else {
    // Код для RTL направления
}
```

Иногда направление конкретной вью не соответствует направлению приложения. Чтобы получить направление для конкретной вью, используйте `effectiveUserInterfaceLayoutDirection`:

```swift
if view.effectiveUserInterfaceLayoutDirection == .leftToRight {
    // Код для LTR направления
} else {
    // Код для RTL направления
}
```

## Картинки

В RTL отзеркаливаются иконки, SFSymbols умеют это из коробки.

![Направление SFSymbols в LTR и RTL.](https://cdn.sparrowcode.io/tutorials/localisation/rtl-ltr-sfsymbols-preview.jpg)

Собственные иконки прийдется отзеркалить вручную с помощью метода `imageFlippedForRightToLeftLayoutDirection()`: 

```swift
let image = UIImage.init(named: "icon") // Оригинальная иконка
let flippedImage = image?.imageFlippedForRightToLeftLayoutDirection() // Отзеркаленная
```

> Иконки, которые повторяют реальные объекты, логотипы, фотографии и иллюстрации не надо отзеркаливать.

![Изображения, которые не нужно отзеркаливать.](https://cdn.sparrowcode.io/tutorials/localisation/rtl-flipping-preview.jpg)

## Текст

Для лейблов выставляем `textAlignment = .natural`, оно вернет правильное выравнивание текста для используемого в приложении языка. В LTR это `.left`, а в RTL - `.right`.

Если в приложении есть текст больше трех строк, направление которого отличается от основного языка - выставляйте ему своё выравнивание. Например, в арабском приложении текст пишется справа-налево, а абзац на английском - слева-направо:

![Выравнивание текста на разных языках.](https://cdn.sparrowcode.io/tutorials/localisation/rtl-paragraph-alignment.jpg)

> Если у вас есть список на разных языках, выравнивание выставляется по направлению интерфейса приложения. 

![Выравнивание текста на разных языках в списке.](https://cdn.sparrowcode.io/tutorials/localisation/rtl-list-alignment.jpg)

## Фреймы

Создаем квадрат размером 100 на 100. Задаем `frame` с точкой `x`, которая будем менять свое положение в зависимости от направления лейаута:

```swift
let x: CGFloat = 30
let size: CGFloat = 100
        
squareView.frame = .init(
    x: squareView.effectiveUserInterfaceLayoutDirection == .leftToRight ? x : view.frame.width - x - size,
    y: 200,
    width: size,
    height: size
)
```

Теперь, если лейаут LTR - квадрат будет стоять в 30 пикселях от левого края. Если RTL - в 30 пикселях от правого:

![Лейаут квадрата на фреймах в LTR и RTL](https://cdn.sparrowcode.io/tutorials/localisation/ltr-rtl-layout-preview.jpg)

## Auto Layout

Если использовать `leftAnchor` и `rightAnchor` в RTL - все вью останутся у своих краев, поэтому для лейаута к левому и правому краю нам нужны `leadingAnchor` и `trailingAnchor`. Они автоматически зеркалятся при изменении направления интерфейса.

Создаем квадрат размером 100 на 100. Указываем `leadingAnchor` и  констрейнты:

```swift
squareView.topAnchor.constraint(equalTo: view.topAnchor, constant: 200),
squareView.leadingAnchor.constraint(equalTo: view.leadingAnchor, constant: 30),
squareView.widthAnchor.constraint(equalToConstant: 100),
squareView.heightAnchor.constraint(equalToConstant: 100)
```

Теперь в LTR направлении квадрат будет стоять в 30 пикселях от левого края, а в RTL - от правого. `trailingAnchor` работает так же, только для правого края.

![Авто лейаут квадрата в `LTR` и `RTL`](https://cdn.sparrowcode.io/tutorials/localisation/ltr-rtl-layout-preview.jpg)

# Рекомендации

Теперь поделюсь советами по работе с локализацией, чтобы вы могли сэкономить время и избежать переиспользования кода.

## Отдельный файл для ключей

Создаём файл, внутри делаем `enum Texts`. В нём создаём статические переменные, которые вернут `NSLocalizedString`. Его можно структурировать, создавая дочерние `enum` внутри других `enum`:

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

Если переменных много, можно создать несколько файлов и разгруппировать ключи.

## Часто используемые слова

Функциональные слова, такие как `ОК`, `Отменить`, `Удалить` можно вынести в отдельный `enum Shared` и использовать по всему приложению, чтобы не дублировать локализации:

```swift
enum Shared {
        
    static var ok: String { NSLocalizedString("shared ok", comment: "") }
    static var cancel: String { NSLocalizedString("shared cancel", comment: "") }
    static var delete: String { NSLocalizedString("shared delete", comment: "") }    
}
```

`Shared` можно вынести в отдельный пакет, чтобы использовать для разных таргетов проекта.

## Передача параметров в ключ

Чтобы красиво передать параметры в `NSLocalizedString`, создайте функцию:

```swift
static func fruitName(name: String) -> String {
    return String(format: NSLocalizedString("fruit name %@", comment: ""), name)
}
```

Вызываем в коде:

```swift
fruitNameLabel.text = Texts.fruitName(name: "Apple")
```

## Как называть ключи

`NSLocalizedString` принимает 2 параметра, которые будут видны при локализации — ключ и комментарий. Можно создать непонятный ключ и подробно описать в комментарии, зачем он нужен. Но лучше делать понятные имена. Например, секции в футере с фидбеком на экране настроек: 

```swift
NSLocalizedString("settings controller table feedback section footer", comment: "")
```

> Рекомендуем не заполнять пустые пространства нижним подчеркиванием `_`. Даже в маленьких проектах ключи становятся большими - Xcode криво переносит длинные строки. Сохраняйте пробелы.

## Полезные инструменты

[Poedit](https://poedit.net): Приложение для локализации `xcloc`-файлов. Поддерживает автоматический перевод всех строк на другой язык, обладает удобным интерфейсом.
[BartyCrouch](https://github.com/FlineDev/BartyCrouch): Автоматизация локализаций. Удаляет неиспользуемые строки, сортирует по алфавиту — это настраивается.

## Особенности

- Интерфейс должен быть динамическим. Заранее рассчитать ширину и высоту лейбла под текст не получится, потому что одни и те же слова занимают разное место в зависимости от языка. Обычное «Как ты?» переводится на французский как «Comment allez-vous?».
- В английском языке функциональные слова пишутся с большой буквы. Так, кнопка «Add new» пишется с двух заглавных «Add New». В русском языке такое встречается, но не часто.
