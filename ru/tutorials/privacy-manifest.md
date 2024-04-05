# О Privacy Manifest

Используются для полной прозрачности обработки собранных данных, в приложениях и сторонних фрейворков. Пользователи получают больше контроля над тем, как и когда их данные используются.

# Добавляем манифест

Манифест добовляется в корень проекта

![Корень проекта](https://cdn.sparrowcode.io/tutorials/privacy-manifest/root-proj.png)

Нажимаем ⌘+N. В окне template опускаемся до раздела Resource и Выбираем App Privacy

![App Privacy](https://cdn.sparrowcode.io/tutorials/privacy-manifest/app-privacy.png)

Включаем таргет

![таргет](https://cdn.sparrowcode.io/tutorials/privacy-manifest/enable-target.png)

Видим наш манифест - PrivacyInfo.xcprivacy

![Манифест в корне проекта](https://cdn.sparrowcode.io/tutorials/privacy-manifest/app-manifest.png)

# Что внутри

Манифест это plist файл с расширением .xcprivacy. Plist - обычный XML.

![PrivacyInfo](https://cdn.sparrowcode.io/tutorials/privacy-manifest/base-app-manifest.png)

Перед тем как установить сторонний фрейворк можно посмотреть его манифест на GitHub.
так выглядит манифест из примера выше:

![XML](https://cdn.sparrowcode.io/tutorials/privacy-manifest/base-app-manifest-xml.png)

Все ниже описанное относится к личным приложениям и сторонним фрейворкам:

**App Privacy Configuration**:

**Privacy Nutrition Label Types** - массив словарей, описывающий собираемые типы данных. Показывается в поле App Privacy в App Store:

![Nutrition Label](https://cdn.sparrowcode.io/tutorials/privacy-manifest/nutrition-label-app-store.png)

- **Collected Data Type** - тип собираемых данных, например email, id или контакты. Подробно и понятно о каждом пункте в [документации](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_data_use_in_privacy_manifests#4250555).
- **Linked to User** -  собранные данные связаные с личностью пользователя. Данные из приложения, часто связаны с личностью пользователя.
- **Used for Tracking** - используются ли эти данные для отслеживания
- **Collection Purposes** - массив в котором перечислены причины, по которым собираются данные:
- 
- Analytics - любая аналитика
- App functionality - функциональность приложения, например аутентификация, безопасность, производительность и т. д.
- Developer’s advertising or marketing - показ своей рекламы в приложении, отправка рекламных сообщений
- Other purposes - другие причины, не указанные в списке
- Product personalization - настройка того, что видит пользователь, например список рекомендуемых продуктов, публикаций или предложений.
- Third-party advertising - показ сторонней рекламы

**Privacy Accessed API Types** - массив словарей, описывающий типы API, для доступа к которым требуются определенные основания. Apple сформировала список «потенциально опасных» АРІ для пользователя, для которых нужно указывать причины использования:

1. **Privacy Accessed API Type** - тип причины, определяет категорию API.

2. **Privacy Accessed API Reasons** - сама причина, по которой используется API. Указанные значения должны быть связанными с Privacy Accessed API Type.

**Privacy Tracking Enabled** - используются ли данные для отслеживания IDFA, фреймворк [App Tracking Transparency](https://developer.apple.com/documentation/apptrackingtransparency).

**Privacy Tracking Domains** - массив строк, в нем перечисляются интернет-домены, которые участвуют в отслеживании IDFA. Если для Privacy Tracking Enabled установлено значение YES, то необходимо указать хотя бы один домен.

# Подробнее о Tracking Domains

В **Privacy Tracking Domains** указываются домены которые ослеживают пользователя.

С iOS 14.5 мы дожны запрашивать [разрешение на отслеживание данных](https://support.apple.com/en-us/102420) пользователя. Для этого используется фреймворк **App Tracking Transparency**. Он позволяет получить доступ к **IDFA** - идентификатор устройства для рекламодателей.

Запрос на отслеживание выглядит так:

![Запрос на отслеживание](https://cdn.sparrowcode.io/tutorials/privacy-manifest/tracking-domains.png)

Пользователь может отказаться выбрав **Ask App Not to Track**, запрос к домену не выполнится и получим ошибку.

> Работает на данный момент не стабильно

## Проверка сторонних доменов

Воспользуемся профайлером чтобы это узнать, есть ли еще домены в приложении, которые собирают данные. Но имейте ввиду работает этот способ совсем не стабильно и в такие моменты кресло под вами будет подгорать.

![открывает profile](https://cdn.sparrowcode.io/tutorials/privacy-manifest/open-profile.png)

В открывшимся окне выбираем Network и жмем Choose

![profile network](https://cdn.sparrowcode.io/tutorials/privacy-manifest/profile-network.png)

В левом верхнем углу жмем кнопку **Start recording**. Выбираем вкладку **Points of Interest**, здесь показан список всех доменов. В примере обратите внимание на поле name в котором запись **Fault**, это значит что есть проблемы. В поле **Start Message** видно домен и указано что его не добавили в **Privacy Tracking Domains**

![Points of Interest](https://cdn.sparrowcode.io/tutorials/privacy-manifest/points-of-interest.png)

Еще домены можно посмотреть в сессиях, во вкладке вашего приложения. Но здесь не указано нужно или нет добавлять их в **Tracking Domains**. Зная домен можно попробовать выяснить это самостоятельно.

![app sessions](https://cdn.sparrowcode.io/tutorials/privacy-manifest/app-sessions.png)

# Сторонние Фрейворки

На данный момент не у всех фрейморков заполнен манифест, он есть но пустой. А какие-то его вообще могут не иметь, поэтому обязательно проверяйте наличие манифеста и его содержание. Особенно поле **Privacy Accessed API Types**.

Распрастранненые пути манифеста: 

- framework/Sources/PrivacyInfo.xcprivacy
- framework/Source/PrivacyInfo.xcprivacy
- framework/Sources/Resources/PrivacyInfo.xcprivacy
- framework/Sources/Library/Resources/PrivacyInfo.xcprivacy

Если во фрейме есть манифест и он заполнен, то не нужно дублировать информацию в главный манифест. Все манифесты объединяются в один при публикации.

# Пример заполненого Манифеста

Вариант того как может выглялеть главный манифест, запомните домен:

![заполненый манифест](https://cdn.sparrowcode.io/tutorials/privacy-manifest/full-manifest.png)

он же в XML:

![заполненый манифест XML](https://cdn.sparrowcode.io/tutorials/privacy-manifest/full-manifest-xml.png)

Здесь хороший пример того, как профайлер указал что домен firebase crashlytics нужно добать в **Privacy Tracking Domains**. Google почему-то решил не добавлять его в свой манифест. 

![Points of Interest](https://cdn.sparrowcode.io/tutorials/privacy-manifest/full-manifest-points-domens.png)

Манифест Firebase crashlytics:

![Firebase манифест](https://cdn.sparrowcode.io/tutorials/privacy-manifest/firebase-manifest.png)

# Генерируем отчет по Манифесту

Чтобы проверить манифест, получим подробный отчёт. Для этого нужно собрать архив.

![создаем архив](https://cdn.sparrowcode.io/tutorials/privacy-manifest/create-archive.png)

Правой кнопкой по архиву, выбераем Generate Privacy Report.

![Generate Privacy Report](https://cdn.sparrowcode.io/tutorials/privacy-manifest/generate-privacy-report.png)

Сгенерируется PDF. Как говарилось выше, все манифесты объединились:

![PDF отчет](https://cdn.sparrowcode.io/tutorials/privacy-manifest/pdf-report.png)

# Если манифест не заполнен

Если манифест не правильно или не полностью заполнен. Сразу после отправки на проверку придет
письмо с указанием проблем. В тексте ошибки обратите внимание на **API categories** и ключ который начинается с **NS**. В массиве **Privacy Accessed API Types** манифеста, нужно указать что именно используется в приложении.

![Некорректный манифест](https://cdn.sparrowcode.io/tutorials/privacy-manifest/nocorrect-manifest.png)

## NS ключи и ссылки на документацию по ним

[File timestamp APIs](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278393): `NSPrivacyAccessedAPICategoryFileTimestamp`  даты создания файлов
[System boot time APIs](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278394): `NSPrivacyAccessedAPICategorySystemBootTime` информация о времени работы ОС
[Disk space APIs](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278397): `NSPrivacyAccessedAPICategoryDiskSpace` информация о доступном пространстве в хранилище устройства
[Active keyboard APIs](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278400): `NSPrivacyAccessedAPICategoryActiveKeyboards` доступ к списку активных клавиатур
[User defaults APIs](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278401): `NSPrivacyAccessedAPICategoryUserDefaults` хранение настроек и прочей информации