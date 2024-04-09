Вы несете ответственность за код который интрегрируете в приложение, все данные которые вы сохраняете или собираете теперь нужно указывать в манифесте. Эти данные появятся на странице приложения, пользователи смогут отрыть их и посмотреть. 

Сторонние фреймворки тоже должны добавлять манифест, но ответственность в любом случае лежит на вас. На данный момент не у всех фрейморков заполнен манифест. А какие-то его вообще могут не иметь.

> Если во фреймворке есть манифест и он заполнен, то не нужно дублировать информацию в главный манифест. Все манифесты объединяются в один, когда собираем архив.


# Добавляем манифест

Манифест добовляется в проект. Нажимаем ⌘+N. В окне template опускаемся до раздела Resource и Выбираем App Privacy

![App Privacy](https://cdn.sparrowcode.io/tutorials/privacy-manifest/app-privacy.png)

Можно создать несколько манифестов, при создании указываем к какому таргету относится манифест. Обратите внимание, он должен билдится вместе с таргетом.

![таргет](https://cdn.sparrowcode.io/tutorials/privacy-manifest/enable-target.png)

# Его структура

Манифест это plist файл с расширением .xcprivacy. Plist - обычный XML.

![PrivacyInfo](https://cdn.sparrowcode.io/tutorials/privacy-manifest/base-app-manifest.png)

XML - для более глубокого понимания. Например хотим затащить в проект какую-то спорную или не особо популярную либу, но у нас в проекте есть ограничения на сбор каких то данных. Можно быстро глянуть XML на gitHub  и не тащить ее в проект чтобы читать манифест.

Здесь XML пустого манифеста, что бы познакомиться с общей структурой:

```xml
<plist version="1.0">
<dict>
	<key>NSPrivacyCollectedDataTypes</key> // App Privacy Configuration
	<array>  // Privacy Nutrition Label Types
		<dict>
			<key>NSPrivacyCollectedDataType</key>  // Collected Data Type
			<string></string>
			<key>NSPrivacyCollectedDataTypeLinked</key>  // Linked to User
			<false/>
			<key>NSPrivacyCollectedDataTypeTracking</key>   // Used for Tracking
			<false/>
			<key>NSPrivacyCollectedDataTypePurposes</key>   // Collection Purposes
			<array>
				<string></string>
			</array>
		</dict>
	</array>
	<key>NSPrivacyAccessedAPITypes</key>   // Privacy Accessed API Types
	<array>
		<dict>
			<key>NSPrivacyAccessedAPIType</key> // Privacy Accessed API Type
			<string></string>
			<key>NSPrivacyAccessedAPITypeReasons</key>   // Privacy Accessed API Reasons
			<array>
				<string></string>
			</array>
		</dict>
	</array>
	<key>NSPrivacyTracking</key>  // Privacy Tracking Enabled
	<false/>
	<key>NSPrivacyTrackingDomains</key> // Privacy Tracking Domains
	<array/>
</dict>
</plist>
```

Манифест состоит из:

## Privacy Nutrition Label Types

Это массив словарей, он описывает какие данные вы собираете о пользователе, именно он показывается в поле App Privacy в App Store:

![Nutrition Label](https://cdn.sparrowcode.io/tutorials/privacy-manifest/nutrition-label-app-store.png)

### Collected Data Type

Описывает категории данных, например email, device id или аудио. Подробно и понятно о каждом пункте в [документации](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_data_use_in_privacy_manifests#4250555).

![Collected Data Type](https://cdn.sparrowcode.io/tutorials/privacy-manifest/collected-data-type.png)

### Linked to User

Если собираем данные свзязанные с личностью пользователя, ставим YES.

![Linked to User](https://cdn.sparrowcode.io/tutorials/privacy-manifest/linked-to-user.png)

### Used for Tracking

Если ли данные из Nutrition Label используюся для отслеживания, ставим YES.

![Used for Tracking](https://cdn.sparrowcode.io/tutorials/privacy-manifest/used-for-tracking.png)

### Collection Purposes

Массив, в котором нужно выбрать причины, по которым собираются данные, например аналитика, реклама, аутентификация.

![Collection Purposes](https://cdn.sparrowcode.io/tutorials/privacy-manifest/collection-purposes.png)

## Privacy Accessed API Types

Важное поле, как раз по нему и прилетает письмо с ошибками от apple. Это массив словарей, в котором нужно выбрать АРІ, которые по мнению Apple несут угрозу личным данным пользователя и указать что именно вы используете.

### Privacy Accessed API Type

Здесь указываем API.

![Privacy Accessed API Type](https://cdn.sparrowcode.io/tutorials/privacy-manifest/privacy-accessed-api-type.png)

### Privacy Accessed API Reasons

указываем что именно мы используем в этом API. Естественно указанные значения должны быть связанными с Privacy Accessed API Type.

![Privacy Accessed API Reasons](https://cdn.sparrowcode.io/tutorials/privacy-manifest/privacy-accessed-api-reasons.png)

## Privacy Tracking Enabled

Если используем IDFA, указываем YES.

![Privacy Tracking Enabled](https://cdn.sparrowcode.io/tutorials/privacy-manifest/privacy-tracking-enabled.png)

## Privacy Tracking Domains

Это массив строк, в нем нужно указывать домены, которые участвуют в отслеживании IDFA. Если для Privacy Tracking Enabled установлено в YES, то нужно указать хотя бы один домен. Можно проверить в профайлере какие домены отслеживают данные.

![Privacy Tracking Domains](https://cdn.sparrowcode.io/tutorials/privacy-manifest/privacy-tracking-domains.png)

# Проверяем Домены

Воспользуемся профайлером чтобы это узнать.

![открывает profile](https://cdn.sparrowcode.io/tutorials/privacy-manifest/open-profile.png)

В открывшимся окне выбираем Network и жмем Choose

![profile network](https://cdn.sparrowcode.io/tutorials/privacy-manifest/profile-network.png)

В левом верхнем углу жмем кнопку **Start recording**. Выбираем вкладку **Points of Interest**, здесь показан список всех доменов. В примере обратите внимание на поле name в котором запись **Fault**, это значит что есть проблемы. В поле **Start Message** видно домен и указано что его не добавили в **Privacy Tracking Domains**

![Points of Interest](https://cdn.sparrowcode.io/tutorials/privacy-manifest/points-of-interest.png)

На случай если профайлер будет косячить в Points of Interest, домены можно посмотреть в сессиях, во вкладке вашего приложения. Но здесь не указано нужно или нет добавлять их в **Tracking Domains**. Тут можно получить хоть какую-то инфюрмации по доменам в приложении.

![app sessions](https://cdn.sparrowcode.io/tutorials/privacy-manifest/app-sessions.png)

Зная домены можно погуглит и выяснить сиспользуют они IDFA или нет.

# Если фрейворк не добавил манифест

Проверяйте наличие манифеста и его содержание. Его кладут в проект, просто посмотрите файлы с расширением `.xcprivacy`. Обращатите внимание на поле **Privacy Accessed API Types**, это проблемное место. Как раз по нему и приходит письмо с ошибками от apple.

# Пример ошибки в стороннем Манифесте

Здесь посмотрим реальную проблему с доменом firebase crashlytics. Обратите внимание на домен **firebase-settings.crashlytics.com** в главном манифесте.

![заполненый манифест](https://cdn.sparrowcode.io/tutorials/privacy-manifest/full-manifest.png)

Хороший пример того, как профайлер указал что домен firebase crashlytics нужно добать в **Privacy Tracking Domains**. Google почему-то решил не добавлять его в свой манифест. 

![Points of Interest](https://cdn.sparrowcode.io/tutorials/privacy-manifest/full-manifest-points-domens.png)

Манифест Firebase crashlytics, как видим поле с доменом пустое:

![Firebase манифест](https://cdn.sparrowcode.io/tutorials/privacy-manifest/firebase-manifest.png)

Не стоит надеяться на то, что в стороних фрейворках манифест будет правильно заполнен. Вся ответственность на вас, поэтому не забываем проверять все сами.

# Как посмотреть финальный манифест

Чтобы увидеть все собираемые данные нами и сторонними фрейворками в приложении,  получим подробный отчёт. Для этого нужно собрать архив.

![создаем архив](https://cdn.sparrowcode.io/tutorials/privacy-manifest/create-archive.png)

Правой кнопкой по архиву, выбераем Generate Privacy Report.

![Generate Privacy Report](https://cdn.sparrowcode.io/tutorials/privacy-manifest/generate-privacy-report.png)

Сгенерируется PDF. Как говорилось выше, все манифесты объединились:

![PDF отчет](https://cdn.sparrowcode.io/tutorials/privacy-manifest/pdf-report.png)

Все что с расширением `.app`, относится к главному манифесту приложения. Этот манифест как раз в главе “Пример ошибки в стороннем Манифесте”. Все остальное сторонние фрейворки.



# Если вы ошиблись

Сразу после отправки на проверку придет письмо с указанием проблем. В тексте ошибки обратите внимание на **API categories** и ключ который начинается с **NS**. Потому что ITMS-91053: Missing API declaration - в доке не описывается, в отличии от ключей. Ниже краткое описание ключей и ссылки на документацию по ним.

Missing API declaration относится к полю **Privacy Accessed API Types**, нужно указать что именно используется в приложении.

![Некорректный манифест](https://cdn.sparrowcode.io/tutorials/privacy-manifest/nocorrect-manifest.png)

## NS ключи и ссылки на документацию

[File timestamp APIs](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278393): `NSPrivacyAccessedAPICategoryFileTimestamp`  даты создания файлов
[System boot time APIs](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278394): `NSPrivacyAccessedAPICategorySystemBootTime` информация о времени работы ОС
[Disk space APIs](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278397): `NSPrivacyAccessedAPICategoryDiskSpace` информация о доступном пространстве в хранилище устройства
[Active keyboard APIs](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278400): `NSPrivacyAccessedAPICategoryActiveKeyboards` доступ к списку активных клавиатур
[User defaults APIs](https://developer.apple.com/documentation/bundleresources/privacy_manifest_files/describing_use_of_required_reason_api#4278401): `NSPrivacyAccessedAPICategoryUserDefaults` хранение настроек и прочей информации