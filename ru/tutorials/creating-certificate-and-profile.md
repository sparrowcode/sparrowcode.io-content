Если у вас индивидуальный аккаунт и вы хотите добавить разработчика, нужно сделать сертификат вручную.
Добавленный разработчик может разрабатывать, но не может просто так в вашем аккаунте выгружать приложения. 

> Если у вас аккаунт компании, то так делать не нужно. Все будет работать автоматически.

Смотрите нам нужен сертификат. 
Для этого нужно создать запрос на подписание, сделаем это в первом шаге.
Сертификат нам нужно подписать, это мы будем делать во втором шаге. 
Во третьем шаге сгенерируем этот сертификат с подписью. 
Четвертый шаг опциональный, если у вас нет App ID приложения зарегистрируем его. 
В пятом шаге делаем на основе сертификата профаил, он отвечает за то чтобы мы могли выгружать приложения.

# Запрос на подписание сертификата

`CertificateSigningRequest`, далее CSR используется для запроса цифрового сертификата. CSR нужен для создания сертификатов разработчика, для подписывания приложений и их публикации в App Store.

Чтобы вручную сгенерировать сертификат, нужно создать файл CSR на вашем маке. Это делается с помощью приложения **Keychain Access**.

**Keychain Access** > **Certificate Assistant** > **Request a Certificate From a Certificate Authority...**

![Запрос сертификата в центре сертификации](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/keychain-request.png)

Вводим свою почту и имя, выбираем Saved to disk и жмем Continue. В следующем окне просто сохраняем фаил.

![Сохранение сертификата](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/keychain-sert-info.png)

Получаем файл CertificateSigningRequest.certSigningRequest:

![Создание CertificateSigningRequest.certSigningRequest](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/keychain-sert-created.png)

# Сертификат для подписи приложений

`distribution.cer` — это цифровой сертификат, который выдается разработчику и используется для подписывания приложений перед их публикацией в App Store или для распространения через другие официальные каналы. Сертификат подтверждает подлинность и целостность приложения.

Идем в свой **Developer account**, в сертификаты:

![Developer account Certificates](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/main-sert.png)

Чтобы добавить новый сертификат, жмем плюс:

![Добавляем сертификат](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/add-sert.png)

Выбираем **Apple Distribution** и жмем Continue:

![Apple Distribution](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/new-sert.png)

На странице создания нового сертифика в поле **Choose File**, вставляем ранее сгенерированный файл и жмем Continue:

![Добавляем CertificateSigningRequest](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/select-new-sert.png)

Сертификат создан, скачиваем его:

![Скачиваем сертификат](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/download-sert.png)

# Сертификат с ключами

Файлы `Certificates.p12` используются для передачи и хранения сертификатов разработчика и связанных с ними закрытых ключей.

Скачанный сертификат файл из предыдущей главы это `distribution.cer`.

После двойного клика по файлу, он откроется в **Keychain Access**. Если этого не произошло, просто найдите последний загруженный сертификат **Apple Distribution** по дате. Дата истечения будет через год.

![Apple Distribution сертификат](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/distribution-sert.png)

Раскрываем сертификат и выделяем сертификат вместе с приватным ключем. Жмем правую кнопку и выбираем `Export 2 items...`

![Экспорт сертификата с ключем](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/export-distribution-sert.png)

Назвать файл можно как угодно, я сохраню как есть:

![Имя для сертификата](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/create-sert-p12.png)

Далее оставляем все поля пустыми и жмем ok:

![Пароль для сертификата](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/sert-p12-non-pass.png)

В связке ключей вводим пароль от своего мака и жмем **Always Allow**:

![Вводим пароль от вашего мака](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/sert-p12-system-pass.png)

Получим файл `Certificates.p12`:

![Сертификат .p12](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/save-sert-p12.png)

# App ID приложения

Если у вас есть приложение, можно простить этот пункт.

`App ID` это уникальный идентификатор, используемый для регистрации и управления приложениями в экосистеме Apple. `App ID` связывает приложения с различными сервисами Apple, такими как Push Notifications, iCloud, Game Center и другими.

Идем снова в **Developer account**, выбираем **Identifiers** и жмем плюс:

![Вкладка Identifiers](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/identifiers.png)

Выбираем **App IDs**, далее **App**:

![App IDs и App](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/register-identifier-app-id.png)

Здесь в Description вводим название приложения. В Bundle ID указываем бандл приложения. 
Explicit - используется для подписи только одного приложения.
Wildcard - используется для подписи нескольких приложений.

Подробнее про Explicit и Wildcard, [здесь](https://developer.apple.com/library/archive/qa/qa1713/_index.html):

![Регистрация App ID](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/register-app-id.png)

Если нужно Включите **Sign in with Apple**. Поставьте галочку, нажмите Edit и введите свой Notification Endpoint.

![Sign in with Apple](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/sign-in-with-apple.png)

Проверяем правильно ли все заполнили и жмем Register:

> Если получили ошибку проверьте поле Bundle ID, чаще всего проблема именно в нем.

![Регистрируем App ID](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/end-register-app-id.png)

После успешной регистрации, на странице **Identifiers** появится идентификатор вашего приложения:

![Идентификатор приложения](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/identifiers-list.png)

# Profile для выгрузки приложений

`Provisioning Profile` позволяет запускать и тестировать приложения на реальных устройствах Apple и загружать их в App Store. Он связывает ваш Apple Developer Account, App ID, сертификаты и зарегистрированные устройства.

После создания ID, идем в меню **Profiles** жмем кнопку Generate a profile или плюс:

![Вкладка Profiles](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/profiles.png)

Выбираем App Store Connect:

![App Store Connect](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/new-profile.png)

В `App ID` выбираем нужный bundle id из списка:

![Выбираем App ID](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/generate-profile-app-id.png)

Выбираем недавно созданный сертификат, смотрим на дату истечения:

![Добавляем сертификат](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/generate-profile-select-sert.png)

В поле `Provisioning Profile` Name вводим название приложения + **Distribution** и жмем Generate:

![Название для Provisioning Profile](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/generate-profile-name.png)

Осталось только скачать файл:

![Скачиваем Provisioning Profile](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/download-profile.png)

Получаем файл Appname_Distribution.mobileprovision:

![Provision Profile](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/created-profile.png)

# Передаем сертификат и профаил другому разработчику

Передаем разработчику файл `.p12` и `Provision Profile`. 
Далее нужно дважды щелкнуть на полученный файл `.p12` или использовать импорт в **Keychain Access**.

![Импортируем Certificates.p12](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/add-p12.png)

Чтобы добавить `Provision Profile` открываем Xcode с проектом. Переходим в Project Settings и выбираем target. На вкладке Signing & Capabilities отключаем **Automatically manage signing**, выбираем нужный `Team ID` и импортируем полученный `Provisioning Profile`.

![Импортируем Provision Profile](https://cdn.sparrowcode.io/tutorials/creating-certificate-and-profile/add-profile-xcode.png)
