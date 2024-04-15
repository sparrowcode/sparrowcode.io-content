Все данные которые вы сохраняете или собираете теперь нужно указывать в манифесте. Эти данные появятся на странице приложения, пользователи смогут открыть их и посмотреть. Библиотеки тоже должны добавлять манифест. Вы несете ответственность за библиотеки которые добавляете.

> Если в библиотеке есть манифест и он заполнен, то не нужно дублировать информацию в главный манифест. Все манифесты объединяются в один, когда собираем архив.

# Добавляем Манифест

Нажимаем ⌘+N. В окне template опускаемся до раздела Resource и Выбираем App Privacy

![Создаем новый App Privacy файл](https://cdn.sparrowcode.io/tutorials/privacy-manifest/app-privacy.png?v=1)

Можно создать несколько манифестов для каждого таргета, указываем к какому таргету относится манифест.

![Указываем к какому таргету относится манифест](https://cdn.sparrowcode.io/tutorials/privacy-manifest/enable-target.png?v=1)

# Структура Манифеста

Манифест это plist файл с расширением .xcprivacy.

![Манифест приложения](https://cdn.sparrowcode.io/tutorials/privacy-manifest/base-app-manifest.png?v=1)

Манифест состоит из полей которые отвечают за `трекинг` - например собираете Email или Payment info, за `системные API` - например используете User Defaults или использование `IDFA`. Сейчас разберем все по очереди:

## Трекинг пользователя

Поле `Privacy Nutrition Label Types` описывает какие данные собираем о пользователе. Он виден в поле App Privacy на странице приложения:

![Иформация о собираемых данных в App Store](https://cdn.sparrowcode.io/tutorials/privacy-manifest/nutrition-label-app-store.png?v=1)

В это поле можем добавлять:

1. **Collected Data Type** - здесь из списка выбираем категорию данных. В [документации](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_data_use_in_privacy_manifests#4250555) это поле **Data type**.

![Документация по Collected Data Type](https://cdn.sparrowcode.io/tutorials/privacy-manifest/collected-data-type.png?v=1)

Для каждого отдельного Data Type создается новый item и все поля снизу будут указываться каждый раз.

2. **Linked to User** - если собираем данные связанные с личностью пользователя, ставим YES.

3. **Used for Tracking** - если ли данные из Nutrition Label используюся для отслеживания, ставим YES.

4. **Collection Purposes** - выбираем [причины](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_data_use_in_privacy_manifests#4250556) почему собираем данные. Например аналитика, реклама, аутентификация.

![Документация по Collection Purposes](https://cdn.sparrowcode.io/tutorials/privacy-manifest/collection-purposes.png?v=1)

## Системное API

`Privacy Accessed API Types` важное поле, как раз по нему и прилетает письмо с ошибками от apple. В нем выбираем **тип системного АРІ**, указываем почему используем его:

![Тип API и причина его использования](https://cdn.sparrowcode.io/tutorials/privacy-manifest/privacy-accessed-api-reasons.png?v=1)

[File timestamp](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278393): `NSPrivacyAccessedAPICategoryFileTimestamp`  даты создания файлов.

[System boot time](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278394): `NSPrivacyAccessedAPICategorySystemBootTime` информация о времени работы ОС.

[Disk space](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278397): `NSPrivacyAccessedAPICategoryDiskSpace` информация о доступном пространстве в хранилище устройства.

[Active keyboard](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278400): `NSPrivacyAccessedAPICategoryActiveKeyboards` доступ к списку активных клавиатур.

[User defaults](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278401): `NSPrivacyAccessedAPICategoryUserDefaults` хранение настроек и прочей информации.

## IDFA

Если отслеживаете IDFA, в **Privacy Tracking Enabled** указываем YES. В поле **Privacy Tracking Domains** указываем домены, они участвуют в отслеживании IDFA.

![Подтверждение импользования IDFA и домены](https://cdn.sparrowcode.io/tutorials/privacy-manifest/tracking-enabled-tracking-domains.png?v=1)

> Если поле Privacy Tracking Enabled установлено в YES, то нужно обязательно указать хотя бы один домен.

Если вы не знаете какие домены отслеживают данные, используйте профайлер:

![Открывает профайлер](https://cdn.sparrowcode.io/tutorials/privacy-manifest/open-profile.png?v=1)

В открывшимся окне выбираем Network и жмем Choose

![Окно профайлера](https://cdn.sparrowcode.io/tutorials/privacy-manifest/profile-network.png?v=1)

В левом верхнем углу жмем кнопку Start recording. Выбираем вкладку **Points of Interest**, здесь показан список всех доменов. В колонке **Start Message** видно домен и указано что его не добавили в **Privacy Tracking Domains**.

![Домены приложения в Points of Interest](https://cdn.sparrowcode.io/tutorials/privacy-manifest/points-of-interest.png?v=1)

Если в **Points of Interest** ничего не показывает или пропадает, есть еще один способ посмотреть домены. Выбираем вкладку вашего приложения, в сессиях виды все домены.

![Все домены в сессиях приложения](https://cdn.sparrowcode.io/tutorials/privacy-manifest/app-sessions.png?v=1)

Вы нашли домены. Теперь проверьте каждый из них, учавствует он отслеживание IDFA или нет. Сделать это придется вам самим.

# Манифест в библиотеках

Если вы не знаете библиотека собирает данные или нет, а в проекте вы не можете трекать. Можно посмотреть что они указали в XML на гитхабе.

## Не добавили Манифест

Смотрим в проекте файлы с расширением `.xcprivacy`. Если манифест пустой или его вообще нет, а библиотека собирает какие либо данные и вы их не указали, от apple придет письмо с ошибками. Все что трекает фрейворк можно указать в своем манифесте.

Если в библиотеке есть манифест и он заполнен, то не нужно дублировать информацию в главный манифест. Все манифесты объединяются в один, когда собираем архив.

## Манифест есть, но с ошибками

Firebase crashlytics использует домен **firebase-settings.crashlytics.com**. В своем манифесте они это не указали:

![Ошибка манифесте Firebase](https://cdn.sparrowcode.io/tutorials/privacy-manifest/firebase-manifest.png?v=1)

Мы это нашли с помощью [профайлера](https://beta.sparrowcode.io/ru/tutorials/privacy-manifest#idfa). В такой ситуации добавляем домен в свой манифест, это перекроет проблемное поле в манифесте firebase. 

Не стоит надеяться на то, что в стороних фрейворках манифест будет правильно заполнен. Поэтому не забываем перепроверять за другими фрейворками.

# Ошибки в Манифесте

>На почту придет список ошибок, только когда отправите на ревью. Если просто выгрузите - не придет.

Описание ошибок в письме не самое лучшее. Поэтому ввидите в поиске `NS` и вы найдете все NS ключи

![Письмо с ошибками в манифесте](https://cdn.sparrowcode.io/tutorials/privacy-manifest/nocorrect-manifest-letter.png)

NS ключи, описание на сайте apple:

1. [NSPrivacyAccessedAPICategoryFileTimestamp](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278393)

2. [NSPrivacyAccessedAPICategorySystemBootTime](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278394)

3. [NSPrivacyAccessedAPICategoryDiskSpace](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278397)

4. [NSPrivacyAccessedAPICategoryActiveKeyboards](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278400)

5. [NSPrivacyAccessedAPICategoryUserDefaults](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278401)

# Финальный Манифест

Собираем архив Product -> Archive.

Правой кнопкой по архиву, выбераем Generate Privacy Report.

![Создание архива](https://cdn.sparrowcode.io/tutorials/privacy-manifest/generate-privacy-report.png?v=1)

В экспорте будет PDF. Все манифесты объединились:

![PDF отчет со всеми манифестами](https://cdn.sparrowcode.io/tutorials/privacy-manifest/pdf-report.png?v=1)

Все что с расширением `.app`, это ваш манифест. Все остальное сторонние фрейворки.