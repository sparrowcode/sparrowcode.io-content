Вы несете ответственность за код который интрегрируете в приложение, все данные которые вы сохраняете или собираете теперь нужно указывать в манифесте. Эти данные появятся на странице приложения, пользователи смогут отрыть их и посмотреть. 

Сторонние фреймворки тоже должны добавлять манифест, но ответственность в любом случае лежит на вас. На данный момент не у всех фрейморков заполнен манифест. А какие-то его вообще могут не иметь.

> Если во фреймворке есть манифест и он заполнен, то не нужно дублировать информацию в главный манифест. Все манифесты объединяются в один, когда собираем архив.


# Добавляем манифест

Манифест добовляется в проект. Нажимаем ⌘+N. В окне template опускаемся до раздела Resource и Выбираем App Privacy

![App Privacy](https://cdn.sparrowcode.io/tutorials/privacy-manifest/app-privacy.png)

Можно создать несколько манифестов для каждого таргета, указываем к какому таргету относится манифест. Он должен билдится вместе с таргетом.

![таргет](https://cdn.sparrowcode.io/tutorials/privacy-manifest/enable-target.png)

# Структура Манифеста

Манифест это plist файл с расширением .xcprivacy. Plist - обычный XML.

![Privacy Info](https://cdn.sparrowcode.io/tutorials/privacy-manifest/base-app-manifest.png)

Если вы не знаете сторонняя библиотека собирает данные или нет, а в проекте вы не можете трекать. Можно посмотреть что они указали в XML на гитхабе.

Манифест состоит из ключей. Одни отвечают за трекинг, другие за API которые вы используете. Сейчас разберем все по очереди:

## Если трекаете пользователя

`Privacy Nutrition Label Types` описывает какие данные собираем о пользователе, именно он показывается в поле App Privacy в App Store:

![Nutrition Label](https://cdn.sparrowcode.io/tutorials/privacy-manifest/nutrition-label-app-store.png)

В `Privacy Nutrition Label Types` входят:

1. **Collected Data Type** - здесь из списка выбираем категорию данных. В [документации](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_data_use_in_privacy_manifests#4250555) это поле **Data type**.

![Collected Data Type](https://cdn.sparrowcode.io/tutorials/privacy-manifest/collected-data-type.png)

2. **Linked to User** - если собираем данные связанные с личностью пользователя, ставим YES.

3. **Used for Tracking** - если ли данные из Nutrition Label используюся для отслеживания, ставим YES.

4. **Collection Purposes** - выбираем из списка ![причины](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_data_use_in_privacy_manifests#4250556), по которым собираем данные. Например аналитика, реклама, аутентификация.

![Collection Purposes](https://cdn.sparrowcode.io/tutorials/privacy-manifest/collection-purposes.png)

## Использование API

`Privacy Accessed API Types` важное поле, как раз по нему и прилетает письмо с ошибками от apple. В нем выбираем **тип АРІ**, которые по мнению Apple несут угрозу личным данным пользователя и указываем почему используем его:

![Privacy Accessed API Reasons](https://cdn.sparrowcode.io/tutorials/privacy-manifest/privacy-accessed-api-reasons.png)

## Если используете IDFA

В поле **Privacy Tracking Enabled** указываем YES.

В поле **Privacy Tracking Domains** указываем домены, которые участвуют в отслеживании IDFA. Если для Privacy Tracking Enabled установлено в YES, то нужно указать хотя бы один домен.

![Privacy Tracking Domains](https://cdn.sparrowcode.io/tutorials/privacy-manifest/tracking-enabled-tracking-domains.png)

Если вы не знаете какие домены отслеживают данные, можно воспользоваться профайлером:

![открывает profile](https://cdn.sparrowcode.io/tutorials/privacy-manifest/open-profile.png)

В открывшимся окне выбираем Network и жмем Choose

![profile network](https://cdn.sparrowcode.io/tutorials/privacy-manifest/profile-network.png)

В левом верхнем углу жмем кнопку **Start recording**. Выбираем вкладку **Points of Interest**, здесь показан список всех доменов. В примере обратите внимание на поле name в котором запись **Fault**, это значит что есть проблемы. В поле **Start Message** видно домен и указано что его не добавили в **Privacy Tracking Domains**

![Points of Interest](https://cdn.sparrowcode.io/tutorials/privacy-manifest/points-of-interest.png)

На случай если профайлер будет косячить в Points of Interest, домены можно посмотреть в сессиях, во вкладке вашего приложения. Но здесь не указано нужно или нет добавлять их в **Tracking Domains**. Тут можно получить хоть какую-то инфюрмации по доменам в приложении.

![app sessions](https://cdn.sparrowcode.io/tutorials/privacy-manifest/app-sessions.png)

Зная домены можно погуглит и выяснить сиспользуют они IDFA или нет.

# Если фрейворк не добавил манифест

Перед тем как добовлять библиотеку проверте у нее наличие манифеста. Его кладут в проект, смотрим файлы с расширением `.xcprivacy`. 

Обратите внимание на поле **Privacy Accessed API Types**, это проблемное место. Если поле пустое, а фрейворк собирает какие либо данные, от apple придет письмо с ошибками. Все что трекает фрейворк можно указать в своем манифесте.

# Ошибка в Манифесте библиотеки

В манифесте firebase crashlytics, профайлер находит использование домена **firebase-settings.crashlytics.com**. Но в своем манифесте они это не указали.

![Firebase манифест](https://cdn.sparrowcode.io/tutorials/privacy-manifest/firebase-manifest.png)

В такой ситуации добавляем домен в свой манифест, это перекроет проблемное поле в манифесте firebase. 

Не стоит надеяться на то, что в стороних фрейворках манифест будет правильно заполнен. Поэтому не забываем перепроверять за другими фрейворками.

# Посмотреть финальный манифест

Собираем архив:

![создаем архив](https://cdn.sparrowcode.io/tutorials/privacy-manifest/create-archive.png)

Правой кнопкой по архиву, выбераем Generate Privacy Report.

![Generate Privacy Report](https://cdn.sparrowcode.io/tutorials/privacy-manifest/generate-privacy-report.png)

В экспорте будет PDF. Все манифесты объединились:

![PDF отчет](https://cdn.sparrowcode.io/tutorials/privacy-manifest/pdf-report.png)

Все что с расширением `.app`, это ваш манифест. Все остальное сторонние фрейворки.



# Если вы ошиблись

>Когда вы выгружаете приложение, ошибка не придет. Чтобы пришла ошибка нужно обязательно отправить на ревью.

Если вы не понимаете как искать ошибки. Вам нужно посмотреть описание и найти ключ который начинается с `NS`, именно его и нужно будет добавить.

## NS ключи, описание на сайте apple

[File timestamp APIs](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278393): `NSPrivacyAccessedAPICategoryFileTimestamp`  даты создания файлов
[System boot time APIs](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278394): `NSPrivacyAccessedAPICategorySystemBootTime` информация о времени работы ОС
[Disk space APIs](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278397): `NSPrivacyAccessedAPICategoryDiskSpace` информация о доступном пространстве в хранилище устройства
[Active keyboard APIs](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278400): `NSPrivacyAccessedAPICategoryActiveKeyboards` доступ к списку активных клавиатур
[User defaults APIs](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278401): `NSPrivacyAccessedAPICategoryUserDefaults` хранение настроек и прочей информации