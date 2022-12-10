# Локализация специальных данных

Она пригодится, если захотите локализовать данные в правильном формате в зависимости от выбранного языка. Например, сумму `3 000,00 ₽`, дату `24 апр. 2022 г.` или процент `54 %`.

![Пример локализации процента на разные языки с помощью форматтера.](https://cdn.sparrowcode.io/tutorials/localisation/formatters-preview.jpg)

## Идентификаторы языка

Чтобы получить идентификатор локали, вызовите `Locale.current.identifier`. Вернётся значение `языкприложения_ЯЗЫКРЕГИОНА`, например, `en_US`. Полный список таких идентификаторов найдёте [по ссылке](https://gist.github.com/jacobbubu/1836273)

> Apple используют ISO стандартизацию, поэтому если на устройстве язык, который не соответствует региону, вернутся разные значения. Например, для `en_RU` вместо `₽` вернётся `RUB`.

## Дата

Получаем текущую дату:

```swift
let currentDate = Date() 
```

Создаём и настраиваем объект `DateFormatter`:

```swift
let dateFormatter = DateFormatter()
// Задаём стиль, например `.medium`
dateFormatter.dateStyle = DateFormatter.Style.medium
dateFormatter.timeStyle = DateFormatter.Style.medium 

// Указываем локаль
dateFormatter.locale = Locale.current
```

Выводим локализованную дату:

```swift
print(dateFormatter.string(from: currentDate))
```

В консоли будет `24 апр. 2022 г., 02:05:34`.

Так же можно создать свой формат даты, вместо стиля:

```swift
dateFormatter.setLocalizedDateFormatFromTemplate("MMddyyyy") // Так же доступны часы `HH` и минуты `mm` 
```

В консоли будет `24/04/2022`. 

## Время

### Продолжительность

Создаем объект `DateComponentsFormatter`:

```swift
let dateComponentsFormatter = DateComponentsFormatter()
```

Выбираем стиль и единицы времени для отображения:

```swift
dateComponentsFormatter.unitsStyle = .abbreviated // Стиль
dateComponentsFormatter.allowedUnits = [.month, .day, .hour, .minute] // Единицы, при выводе используются нужные. Можно убрать лишние
```

Доступны разные стили:

- `.abbreviated` - 2 ч 32 мин
- `.full` - 2 часа 32 минуты
- `.spellOut` - два часа тридцать две минуты
- `.positional` - 2:32 (надо убрать лишние `allowedUnits`)
- `.short` - сокращение (для некоторых языков)
- `.brief` - короче, чем `short`

Получаем интервал, который будем локализовать:

```swift
let interval = Date.current.timeIntervalSince(Date.current.addingTimeInterval(-9132))
let formattedInterval = dateComponentsFormatter.string(from: interval)
```

Выводим результат:

```swift
print(formattedInterval)
```

Получаем `2 ч 32 мин` в консоли.

### Отсчет

Создаем объект `RelativeDateTimeFormatter`:

```swift
let relativeDateTimeFormatter = RelativeDateTimeFormatter()
```

Выбираем стиль: 

```swift
relativeDateTimeFormatter.unitsStyle = .full
```

Доступны разные стили:
- `.full` - полное отображение «2 месяца назад»
- `.short` - сокращение «2 мес. назад»
- `.abbreviated` - аббревиатура «-2 м»
- `.spellOut` - разговорное «два месяца назад»

```swift
let start = Date.current.addingTimeInterval(-15) // время, от которого считаем сколько прошло
let finish = Date() // время, к которому считаем сколько прошло

let interval = relativeDateTimeFormatter.localizedString(for: start, relativeTo: finish)
```

Выводим результат:

```swift
print(interval)
```

Получаем `15 секунд назад` в консоли. Если поменяем `start` на `Date.current.addingTimeInterval(15)` (будущее время), получим `через 15 секунд` в консоли.

## Валюта

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

## Дробное число

Создаём и настраиваем объект `NumberFormatter`:

```swift
let numberFormatter = NumberFormatter()
numberFormatter.numberStyle = .decimal

// Указываем локаль
numberFormatter.locale = Locale.current
```

Выводим локализованное число:

```swift
print(numberFormatter.string(from: 123456))
```

Получаем `123,456` в консоли.

## Процент

Создаем число, из которого хотим сделать процент:

```swift
let number = 54

// Получаем число с процентом, используя форматтер:
let percent = number.formatted(.percent)
```

Выводим процент:

```swift
print(percent)
```

Получаем `54 %` в консоли.

## Расстояние

Создаем объект `Measurement`:

```swift
let measurement = Measurement(
    value: 43.23, // Расстояние
    unit: UnitLength.kilometers // Единица измерения
)
``` 

В `UnitLength` доступно 22 единицы измерения.

Создаем объект `MeasurementFormatter`:

```swift
let measurementFormatter = MeasurementFormatter()

// Выбираем стиль, доступы полный `.long` и сокращенные `.short`, `.medium`
measurementFormatter.unitStyle = .long
```

Выводим расстояние:

```swift
print(measurementFormatter.string(from: measurement))
```

Получаем `43,23 километра` в консоли.

## Размер

Создаем объект `LengthFormatter`

```swift
let lengthFormatter = LengthFormatter()

// Выбираем стиль, доступы полный `.long` и сокращенные `.short`, `.medium`
lengthFormatter.unitStyle = .long
```

Получаем значение:

```swift
let value = lengthFormatter.string(fromValue: 14.5, unit: .millimeter)
```

Доступны разные `unit`:
- `millimeter` - милиметр
- `centimeter` - сантиметр
- `meter` - метр
- `kilometer` - километр
- `inch` - дюйм
- `foot` - фут
- `yard` - ярд
- `mile` - миля

Выводим размер:

```swift
print(value)
```

Получаем `14,5 миллиметра` в консоли.


## Энергия

Создаем объект `EnergyFormatter`:

```swift
let energyFormatter = EnergyFormatter()

// Выбираем стиль, доступы полный `.long` и сокращенные `.short`, `.medium`
energyFormatter.unitStyle = .long
```

Получаем значение:

```swift
let value = energyFormatter.string(fromValue: 69.5, unit: .calorie)
```

Доступны разные `unit`:
- `.calorie` -  калории
- `.joule` - джоули
- `.kilocalorie` - килокалории
- `.kilojoule` - килоджоули

Выводим значение:

```swift
print(value)
```

Получаем `69,5 калории` в консоли.

## Вес 

Создаем объект `MassFormatter`

```swift
let massFormatter = MassFormatter()

// Выбираем стиль, доступы полный `.long` и сокращенные `.short`, `.medium`
massFormatter.unitStyle = .long
```

Получаем значение:

```swift
let value = massFormatter.string(fromValue: 75.2, unit: .kilogram)
```

Доступны разные `unit`:
- `.kilogram` -  килограмм
- `.gram` - грамм
- `.pound` - фунт
- `.ounce` - унция
- `.stone` - стоун

Выводим вес:

```swift
print(value)
```

Получаем `75,2 килограмма` в консоли.

## Объем файла

Создаем проперти с объемом файла в байтах:

```swift
let number = 54347323

// Получаем локализованный объем файла, используя форматтер:
let byteCount = number.formatted(.byteCount(style: .file))
```

Выводим объем:

```swift
print(byteCount)
```

Получаем `54.3 МБ` в консоли.

## Список

Создаём массив, из которого будем делать список:

```swift
let list = ["Swift", "Java", "Python"]
```

Создаём и настраиваем объект `ListFormatter`:

```swift
let listFormatter = ListFormatter()

// Указываем локаль
listFormatter.locale = Locale.current
```

Выводим локализованный список:

```swift
print(listFormatter.string(from: list))
```

Получаем `Swift, Java и Python` в консоли. Работает с любым количеством элементов. 

## Имена

Создаём и настраиваем объект класса `PersonNameComponents`:

```swift
var nameComponents = PersonNameComponents()
nameComponents.familyName = "Петров"
nameComponents.givenName = "Александр"
nameComponents.nameSuffix = "Младший"
nameComponents.nickname = "Саня"
```

Доступны разные компоненты, например:
- `namePrefix` - часть имени, до основного
- `givenName` - основное имя
- `nameSuffix` - часть имени, после основного
- `middleName` - второе имя
- `familyName` - фамилия
- `nickname` - псевдоним

Создаём объект класса `PersonNameComponentsFormatter`, с помощью которого будем форматировать имя:

```swift
let nameFormatter = PersonNameComponentsFormatter()
```

Задаем стиль, доступны: 
- `.default` и `.medium` - имя, фамилия
- `.short` - псевдоним
- `.abbreviated` - инициалы имени, фамилии
- `.long` - все компоненты, кроме псевдонима

Выводим результат:

```swift
formatter.style = .default // совпадает с `.medium` или отсутствием стиля
print(nameFormatter.string(from: nameComponents))
// В консоли `Александр Петров`

formatter.style = .short
print(nameFormatter.string(from: nameComponents))
// В консоли `Саня`

formatter.style = .abbreviated
print(nameFormatter.string(from: nameComponents))
// В консоли `АП`

formatter.style = .long
print(nameFormatter.string(from: nameComponents))
// В консоли `Александр Младший Петров`
```
