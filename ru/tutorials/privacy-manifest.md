Если вы используете User Defaults или собираете данные о пользователе, то вам нужно заполнить манифест. Всё что вы укажите появиться на странице приложения.

> Авторы библиотеки тоже добавляют манифест. Но если они этого не сделали, то внутри проекта добавляет сам разработчик.

Если у библиотеки есть манифест, то не нужно дублировать в ваш манифест. Когда архивируете проект, все манифесты объединяются в один.

# Добавляем Манифест

Нажмите `⌘+N` и выберите `App Privacy`-файл.

![Создаем `App Privacy`-файл](https://cdn.sparrowcode.io/tutorials/privacy-manifest/app-privacy.png?v=2)

У каждого таргета свой манифест, поэтому внимательно ставьте чекмарк нужному таргету. Если манифест одинаковый для всех таргетов, то можно сразу указать несколько таргетов.

![Указываем таргет для манифеста](https://cdn.sparrowcode.io/tutorials/privacy-manifest/enable-target.png?v=2)

# Структура Манифеста

Манифест это plist-файл с расширением `.xcprivacy`.

![Пример заполненного Privacy Манифеста](https://cdn.sparrowcode.io/tutorials/privacy-manifest/base-app-manifest.png?v=2)

Манифест состоит из трех полей. Первое про трекинг — его заполняете когда собираете почту или имя. Второе отвечает за системные API, например, User Defaults. Третьер отвечает за `IDFA`. 

Разберем каждое поле подробнее.

## Трекинг пользователя

Поле `Privacy Nutrition Label Types` описывает какие данные собираем о пользователе. Все что укажите в манифесте, будет видно в поле App Privacy на странице приложения:

![Информация какие данные собираем на странице App Store](https://cdn.sparrowcode.io/tutorials/privacy-manifest/nutrition-label-app-store.png?v=2)

**Collected Data Type** — это тип данных, которые собираете о пользователе. Например, контакты или информация о платежах. Все типы на [официальном сайте](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_data_use_in_privacy_manifests#4250555), свои добавлять нельзя. В plist-файл добавлять строку из `Data type`.

![Типы данных про контакты для Манифеста](https://cdn.sparrowcode.io/tutorials/privacy-manifest/collected-data-type.png?v=2)

Для каждого типа данных создаете новый Item. Поля ниже нужно указывать для каждого типа данных:

**Linked to User** — если собираете данные, связанные с личностью пользователя, ставьте `YES`.

**Used for Tracking** — если ли данные используются для трекинга, ставим `YES`.

**Collection Purposes** — здесь указываем причины почему собираем данные. Например, аналитика, реклама или аутентификация. Выбирать из доступного [списка причин](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_data_use_in_privacy_manifests#4250556), свои указывать нельзя.

![Причины в Манифесте почему собираем данные](https://cdn.sparrowcode.io/tutorials/privacy-manifest/collection-purposes.png?v=2)

## Системное API

Для API отдельное поле `Privacy Accessed API Types`. Как раз по нему прилетает письмо с ошибками от Apple. В этом поле указываем какое API используем и почему.

![Тип API и причина его использования](https://cdn.sparrowcode.io/tutorials/privacy-manifest/privacy-accessed-api-reasons.png?v=2)

Это системные API, которые нужно указывать в манифесте:

[File Timestamp](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278393): Получаете время когда создан или изменен файл
[System Boot Time](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278394): Информация о запуске приложения и времени работы OS
[Disk Space](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278397): Доступное пространство в хранилище устройства
[Active Keyboard](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278400): Доступ к списку активных клавиатур
[User Defaults](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278401): Если используете User Defaults

Для каждого API по ссылке будет и список доступных причин. Свои причины указывать нельзя.

> Если подходит несколько причин, нужно указывать все

## IDFA

Если используете IDFA, добавьте поле **Privacy Tracking Enabled** и установите `YES`. Сразу добавляйте поле **Privacy Tracking Domains**, здесь нужно указать все домены, которые работают в IDFA.

![Поля для IDFA в Манифесте](https://cdn.sparrowcode.io/tutorials/privacy-manifest/tracking-enabled-tracking-domains.png?v=2)

> Если установили `Privacy Tracking Enabled`, то обязательно указать хотя бы один домен.

Чтобы получить какие домены используются для IDFA, откройте профайлер `Product` → `Profile`. Теперь в окне выберите Network:

![Окно профайлера](https://cdn.sparrowcode.io/tutorials/privacy-manifest/profile-network.png?v=2)

В левом верхнем углу жмем кнопку Start Recording. Выбираете вкладку **Points of Interest**, здесь будет список всех доменов. В колонке **Start Message** видно домен и указано что его не добавили в манифест.

![Как собрать домены IDFA](https://cdn.sparrowcode.io/tutorials/privacy-manifest/points-of-interest.png?v=2)

Профайл иногда сбоит, если в **Points of Interest** ничего не показывает или вообще пропадает, вот второй способ. Выбираете вкладку вашего приложения, а в сессиях видны все домены.

![Все домены в сессиях приложения](https://cdn.sparrowcode.io/tutorials/privacy-manifest/app-sessions.png?v=2)

Теперь придется проверить каждый или участвует он в IDFA. Сделать придется вам лично.

# Манифест в библиотеках

> Авторы библиотеки тоже добавляют манифест. Но если они этого не сделали, то внутри проекта добавляет сам разработчик

Если автор библиотеки не добавил манифест, то разработчик должен заполнить манифест сам.

Если в библиотеке есть манифест и он заполнен, то не нужно дублировать информацию в главный манифест. Все манифесты объединяются в один, когда собираем архив.

Если в манифесте есть ошибки, то разработчику придется самому дополнить манифест внутри проекта. Например, Firebase Сrashlytics использует домен **firebase-settings.crashlytics.com**. В своем манифесте они это не указали:

![Ошибка манифесте Firebase](https://cdn.sparrowcode.io/tutorials/privacy-manifest/firebase-manifest.png?v=2)

Мы это нашли с помощью [профайлера](https://sparrowcode.io/ru/tutorials/privacy-manifest#idfa). В такой ситуации добавляем домен в свой манифест, это перекроет проблемное поле в манифесте от Firebase. 

В манифестах библиотек допускают ошибки — обязательно перепроверяйте.

# Если ошибка в Манифесте

> Ошибки придут на почту, только когда отправите приложение на проверку. Если просто выгрузить проект, то ошибок не будет

На почту придут ошибки только про системное API:

![Письмо с ошибками в манифесте](https://cdn.sparrowcode.io/tutorials/privacy-manifest/privacy-manifest-email.png?v=2)

Чтобы быстро найти ключи, ввидите в поиске `NS`. Именно их не хватает в вашем Манифесте. Даже если вы не используете это API, его могут использовать библиотеки, которые вы добавили в проект.

Вот NS ключи, и ссылки на ключ и причину на сайте Apple:

- [NSPrivacyAccessedAPICategoryFileTimestamp](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278393)
- [NSPrivacyAccessedAPICategorySystemBootTime](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278394)
- [NSPrivacyAccessedAPICategoryDiskSpace](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278397)
- [NSPrivacyAccessedAPICategoryActiveKeyboards](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278400)
- [NSPrivacyAccessedAPICategoryUserDefaults](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278401)

# Финальный Манифест

Собираем архив Product -> Archive. Правой кнопкой по архиву, выбираем Generate Privacy Report.

![Экспорт финального манифеста](https://cdn.sparrowcode.io/tutorials/privacy-manifest/generate-privacy-report.png?v=2)

В экспорте PDF-файл. Все манифесты объединились в итоговый:

![PDF отчет со всеми манифестами](https://cdn.sparrowcode.io/tutorials/privacy-manifest/pdf-report.png?v=2)

Все поля что с расширением `.app`, это из вашего манифеста. Остальные поля это сторонние библиотеки в вашем проекте.