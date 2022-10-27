Live Activity объединяют пуш-уведомления в один интерактивный баннер. Представим приложение для вызова такси — какие там могут быть пуши? «Водитель едет», «Водитель уже рядом» и «Водитель ждёт». С новым инструментом разработчики смогут объединить пуши в Live Activity и обновлять её. 

> Live Activity доступны с iOS 14.1 и Xcode 14.1.

Live Activity не виджет — у неё нет таймлайнов и обновлений по времени. Основной способ обновления — как раз пуши. Способы обновления разберём в секции [Как обновить и завершить Live Activity](https://sparrowcode.io/ru/tutorials/live-activities).

![Compact и Expanded Live Activity.](https://cdn.sparrowcode.io/tutorials/live-activities/header.png)

Live Activity показываются на устройствах с Dynamic Island и без него. На заблокированном экране это будет похоже на обычное пуш-уведомление. А для устройств с Dynamic Island Live Activity показывается вокруг камер.

[Проект-пример на GitHub](https://github.com/sparrowcode/live-activity-example): Как добавить Live Activity, обновить и закрыть. UI для Live Activity.

## Добавляем Live Activity в проект

Live Activity используют фреймворк ActivityKit. Живут Live Activity в таргете виджета:

![Добавляем таргет WidgetKit в проект.](https://cdn.sparrowcode.io/tutorials/live-activities/add-widget-target.png)

Перейдите в таргет и оставьте код:

```swift
@main
struct LiveActivityWidget: Widget {

    let kind: String = "LiveActivityWidget"

    var body: some WidgetConfiguration {
        StaticConfiguration(kind: kind, provider: Provider()) { entry in
            widgetEntryView(entry: entry)
        }
        .configurationDisplayName("My Widget")
        .description("This is an example widget.")
    }
}
```

> Если у вас уже есть виджеты, используйте `WidgetBundle`, чтобы определить несколько `Widget`.

В `Info.plist` добавьте атрибут `Supports Live Activities`:

```
<key>NSSupportsLiveActivities</key>
<true/>
```

`StaticConfiguration` используется для виджетов и компликейшнов. Скоро мы заменим его на другой, но сначала определим модель данных. 

## Определяем модель данных

Live Activity создаётся в самом приложении, а модель будет использоваться и в приложении, и в виджете. Поэтому хорошо бы сделать один класс и пошарить его между таргетами. Создайте новый файл для модели. Для этого наследуемся от `ActivityAttributes`:

```swift
import ActivityKit

struct ActivityAttribute: ActivityAttributes {
    
    public struct ContentState: Codable, Hashable {
        
        // Динамические данные
        
        var dynamicStringValue: String
        var dynamicIntValue: Int
        var dynamicBoolValue: Bool
    }
    
    // Статические данные
    
    var staticStringValue: String
    var staticIntValue: Int
    var staticBoolValue: Bool
}
```

В структуре `ContentState` определяем динамические данные — они будут меняться и обновлять UI. За пределами `ContentState` работают статические данные, они доступны только при создании Live Activity.

Пошарьте файл между двумя таргетами, для этого в инспекторе справа выберите главный таргет приложения и таргет виджета:

![Файл будет доступен в главном и виджет-таргетах.](https://cdn.sparrowcode.io/tutorials/live-activities/shared-file-between-targets.png)

## Переходим к UI

В объекте `LiveActivityWidget` поменяйте конфигурацию на `ActivityConfiguration`:

```swift
struct LiveActivityWidget: Widget {
    
    let kind: String = "LiveActivityWidget"
    
    var body: some WidgetConfiguration {
        ActivityConfiguration(for: ActivityAttribute.self) { context in
            // Здесь UI для активити на заблокированном экране
        } dynamicIsland: { context in
            // Здесь UI для Dynamic Island
        }
    }
}
```

У нас есть два замыкания, первое — для UI на заблокированном экране, второе — для динамического острова. Обратите внимание, указываем класс атрибутов `ActivityAttribute.self` — это модель данных, которую определили выше.

> В Live Activity игнорируются модификаторы анимаций. 

### Работаем с Lock Screen

Эта View показывается на заблокированном экране. Все инструменты для виджетов доступны в Live Activity. Укажите проперти `context`, чтобы передать модель данных:

```swift
struct LockScreenLiveActivityView: View {

    let context: ActivityViewContext<ActivityAttribute>
    
    var body: some View {
        VStack {
            Text("Dyanmic String: \(context.state.dynamicStringValue))")
            Text("Static String: \(context.staticStringValue))")
        }
        .activitySystemActionForegroundColor(.indigo)
        .activityBackgroundTint(.cyan)
    }
}
```

> Максимальная высота Live Activity на Lock Screen — 160 точек.

В примере я распечатал и динамические, и статические проперти из `ActivityAttribute`. Давайте укажем вью в виджете:

```swift
struct LiveActivityWidget: Widget {
    
    let kind: String = "LiveActivityWidget"
    
    var body: some WidgetConfiguration {
        ActivityConfiguration(for: ActivityAttribute.self) { context in
            LockScreenLiveActivityView(context: context)
        } dynamicIsland: { context in
            // Здесь UI для Dynamic Island
        }
    }
}
```

### Обращаем внимание на Dynamic Island

У динамического острова есть 3 вида: компактный, минимальный и развёрнутый.

> Углы динамического острова закруглили в 44 точки. Это соответствует закруглению камеры TrueDepth.

#### Compact & Minimal
 
Если запущена одна активность, то контент можно разместить слева и справа от динамического острова.

![Compact Live Activity в Dynamic Island.](https://cdn.sparrowcode.io/tutorials/live-activities/live-activity-type-compact.png)

Если запущено несколько Live Activity, система выберет два из них. Одна будет показываться слева, она прикреплена к острову, а другая справа — отделённая от острова в кружке.

![Minimal Live Activity в Dynamic Island.](https://cdn.sparrowcode.io/tutorials/live-activities/live-activity-type-minimal.png)

Так выглядит код для каждого варианта отображения:

```swift
DynamicIsland {
    // Здесь будет код для развёрнутого вида.
    // Его разберем в след. пункте.
} compactLeading: {
    Text("Leading")
} compactTrailing: {
    Text("Trailing")
} minimal: {
    Text("Min")
}
```

#### Expanded

Развёрнутая Live Activity показывается, когда человек нажимает и удерживает компатный или минимальный вид. Когда Live Activity обновляется, развёрнутый вид появляется автоматически на пару секунд.

![Expanded Live Activity в Dynamic Island.](https://cdn.sparrowcode.io/tutorials/live-activities/live-activity-type-expanded.png)

А вот код для развёрнутого вида. Каждое замыкание определяет область на Live Activity.

```swift
DynamicIslandExpandedRegion(.center) {}
DynamicIslandExpandedRegion(.leading) {}
DynamicIslandExpandedRegion(.trailing) {}
DynamicIslandExpandedRegion(.bottom) {}
```

Разметка областей:

![Области Dynamic Island.](https://cdn.sparrowcode.io/tutorials/live-activities/live-activity-areas.png)

- **center** — контент под камерой.
- **leading** — пространство от левого угла до камеры. Если использовать вертикальный стек, будет доступно пространство ниже.
- **trailing** — аналогично `leading`, но для правого края.
- **bottom** — контент под всеми другими областями.

Если контент в левой и правой областях не помещается, можно объединить его с `Bottom`. Область будет адаптивная, на скриншоте сейчас максимальные размеры:

![Если не хватает места, обласи Dynamic Island можно объединить.](https://cdn.sparrowcode.io/tutorials/live-activities/live-activity-leading-expanded.png)

Чтобы разрешить области использовать пространство ниже, укажите `verticalPlacement`:

```swift
DynamicIslandExpandedRegion(.leading) {
    Text("Leading Text with merge region")
        .dynamicIsland(verticalPlacement: .belowIfTooWide)
}
```

> Максимальная высота Live Activity на Dynamic Island 160 точек.

## Как добавить новую Live Activity

Live Activity можно создать только внутри приложения. Обновить и закончить Live Activity можно и внутри приложения, и по пуш-уведомлению.

Сначала проверьте доступность Live Activity — пользователь мог запретить их. Вторая причина недоступности — в системе достигнут лимит. Чтобы проверить, используем код:

```swift
guard ActivityAuthorizationInfo().areActivitiesEnabled else {
    print("Activities are not enabled")
    return
}
```

Можно отслеживать статус: 

```swift
for await enabled in ActivityAuthorizationInfo().activityEnablementUpdates {
    // Здесь ваш код
}
```

Чтобы создать новую Live Activity, создайте атрибуты и после вызовите `request`:

```swift
let attributes = ActivityAttribute(...)
let contentState = ActivityAttribute.ContentState(...)
do {
    let activity = try Activity<ActivityAttribute>.request(
        attributes: attributes,
        contentState: contentState
    )
} catch {
    print("LiveActivityManager: Error in LiveActivityManager: \(error.localizedDescription)")
}
```

Обратите внимание: здесь разделились статические, а проперти обновляется на два объекта.

## Посмотреть список текущих Live Activity

Чтобы получить созданные Live Activity, укажите модель аттрибутов:

```swift
for actviity in Activity<ActivityAttribute>.activities {
    print("Activity details: \(actviity.contentState)")
}
```

## Обновить и завершить Live Activity

Обновлять и завершать Live Activity можно только с динамическими параметрами — Content State.

> Размер обновления Content State должен быть меньше 4KB.

### Внутри приложения

Как обновить Live Activity из приложения:

```swift
// Новые данные
let contentState = ActivityAttribute.ContentState(...)

Task {    
    await activity?.update(using: contentState)   
}
```

Чтобы завершить Live Activity, вызовите:

```swift
await activity?.end(dismissalPolicy: .immediate)
```

Live Activity закроется сразу. А вот как сделать, чтобы Live Activity осталась ещё некоторое время на экране:

```swift
await activity?.end(using: attributes, dismissalPolicy: .default)
```

Live Activity обновится финальными данными и будет на экране ещё некоторое время. Система закроет активность через 4 часа или когда убедится, что юзер увидел новые данные. Зависит от того, что наступит раньше.

У Live Activity нет таймлайна, как для виджетов. Для обновления или закрытия Live Activity — когда приложение в фоне — используйте [Background Tasks](https://developer.apple.com/documentation/backgroundtasks). 

> Background Tasks не гарантируют своевременного выполнения.

### Через пуш-уведомления

При создании Live Activity получаем `pushToken`. Он используется, чтобы обновлять Live Activity через пуш-уведомления. 

> Предварительно зарегистрируйте приложение для получения пушей.

Сформируем пуш для обновления Live Activity. Заголовки:

```
apns-topic: {Your App Bundle ID}.push-type.liveactivity
apns-push-type: liveactivity
authorization: bearer {Auth Token}
```

Тело:

```
"aps": {
    "timestamp": 1168364460,
    "event": "update", // or end
    "content-state": {
        "dynamicStringValue": "New String Value"
        "dynamicIntValue": 5
        "dynamicBoolValue": true
    },
    "alert": {
        "title": "Title of classic Push",
        "body": "Body or classic push",
    }
}
```

Словарь `content-state` должен совпадать с моделью атрибутов `ActivityAttribute.ContentState`. Мы можем обновлять только динамические проперти. Проперти не в Content State обновить не получится.

## Отследить нажатие

По нажатию на Live Activity хорошо открывать релеватный экран, для этого реализуйте Deep Link. Установите модификатор `widgetURL(_:)`. Можно задать разные ссылки для каждой области:

```swift
DynamicIslandExpandedRegion(.leading) {
    Text("Leading Text with merge region")
        .widgetURL(URL(string: "example://action"))
}
```

Развёрнутый вид Dynamic Island поддерживает [Link](https://developer.apple.com/documentation/SwiftUI/Link).
