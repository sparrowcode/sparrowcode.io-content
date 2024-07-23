Добавленный разработчик может разрабатывать, но не может просто так в вашем аккаунте выгружать приложения. 

> Если у вас аккаунт компании, то так делать не нужно. Все будет работать автоматически. Если у вас индивидуальный аккаунт и вы хотите добавить разработчика, нужно сделать сертификат вручную.

Как это будет выглядеть по шагам:
1. Создадим запрос на подписание
2. Подпишем сертификат.
3. Сгенерируем этот сертификат с подписью. 
4. Опциональный шаг, если у вас нет App ID приложения зарегистрируем его
5. Сделаем на основе сертификата профаил, он отвечает за то чтобы мы могли выгружать приложения

# Подготовка к подписи сертификата

Нам нужно создать запрос для подписи сертификата `CertificateSigningRequest`. Это файл с расширением `.certSigningRequest`. Он нужен для создания сертификатов, подписывания приложений и их публикации в App Store.

Чтобы вручную сгенерировать сертификат, нужно создать файл `CertificateSigningRequest` на вашем маке. Это делается с помощью приложения **Keychain Access**.

![Запрос сертификата в центре сертификации](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/keychain-request.png)

Вводим свою почту и имя, выбираем *Saved to disk* и жмем *Continue*. В следующем окне просто сохраняем файл.

![Сохранение сертификата](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/keychain-sert-info.png)

Получаем файл `CertificateSigningRequest.certSigningRequest`:

![Создание CertificateSigningRequest.certSigningRequest](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/keychain-sert-created.png)

# Создаем сертификат

Он подтверждает подлинность и целостность приложения. Расширение у него `distribution.cer`

Идем в свой **Developer account**, в сертификаты:

![Developer account Certificates](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/main-sert.png)

Чтобы добавить новый сертификат, жмем плюс:

![Добавляем сертификат](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/add-sert.png)

Выбираем *Apple Distribution* и жмем *Continue*:

![Apple Distribution](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/new-sert.png)

На странице создания нового сертификата в поле *Choose File*, вставляем ранее сгенерированный файл и жмем *Continue*:

![Добавляем CertificateSigningRequest](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/select-new-sert.png)

Сертификат создан, скачиваем его:

![Скачиваем сертификат](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/download-sert.png)

# Сохраняем сертификат с ключами

Файлы `Certificates.p12` используются для передачи и хранения сертификатов и связанных с ними закрытых ключей.

После двойного клика по файлу `distribution.cer`, он откроется в **Keychain Access**. Если этого не произошло, просто найдите последний загруженный сертификат *Apple Distribution* по дате. Дата истечения будет через год.

![Apple Distribution сертификат](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/distribution-sert.png)

Раскрываем сертификат и выделяем сертификат вместе с приватным ключом. Жмем правую кнопку и выбираем `Export 2 items...`

![Экспорт сертификата с ключом](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/export-distribution-sert.png)

Назвать файл можно как угодно, я сохраню как есть:

![Имя для сертификата](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/create-sert-p12.png)

Далее оставляем все поля пустыми и жмем *ok*:

![Пароль для сертификата](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/sert-p12-non-pass.png)

В связке ключей вводим пароль от своего мака и жмем *Always Allow*:

![Вводим пароль от вашего мака](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/sert-p12-system-pass.png)

Получим файл `Certificates.p12`:

![Сертификат .p12](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/save-sert-p12.png)

# Идентификатор для приложения

> Если у вас есть приложение, можно простить этот пункт.

`App ID` это уникальный идентификатор, используемый для регистрации и управления приложениями. `App ID` связывает приложения с различными сервисами Apple, такими как Push Notifications, iCloud, Game Center и другими.

Идем снова в **Developer account**, выбираем *Identifiers* и жмем плюс:

![Вкладка Identifiers](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/identifiers.png)

Выбираем *App IDs*, далее *App*:

![App IDs и App](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/register-identifier-app-id.png)

Здесь в *Description* вводим название приложения. В *Bundle ID* указываем бандл приложения. `Explicit` - используется для подписи только одного приложения. `Wildcard` - используется для подписи нескольких приложений.

> Подробнее про Explicit и Wildcard, [здесь](https://developer.apple.com/library/archive/qa/qa1713/_index.html):

![Регистрация App ID](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/register-app-id.png)

Если нужно Включите *Sign in with Apple*. Поставьте галочку, нажмите *Edit* и введите свой *Notification Endpoint*.

![Sign in with Apple](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/sign-in-with-apple.png)

Проверяем правильно ли все заполнили и жмем *Register*:

> Если получили ошибку проверьте поле Bundle ID, чаще всего проблема именно в нем.

![Регистрируем App ID](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/end-register-app-id.png)

После успешной регистрации, на странице *Identifiers* появится идентификатор вашего приложения:

![Идентификатор приложения](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/identifiers-list.png)

# Профиль для подписи приложений

`Provisioning Profile` связывает Apple Developer Account, App ID, сертификаты и зарегистрированные устройства. Это файл с расширением `.mobileprovision`.

После создания ID, идем в меню *Profiles* жмем кнопку *Generate a profile* или плюс:

![Вкладка Profiles](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/profiles.png)

Выбираем *App Store Connect*:

![App Store Connect](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/new-profile.png)

В `App ID` выбираем нужный *bundle id* из списка:

![Выбираем App ID](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/generate-profile-app-id.png)

Выбираем недавно созданный сертификат, смотрим на дату истечения:

![Добавляем сертификат](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/generate-profile-select-sert.png)

В поле `Provisioning Profile` *Name* вводим название приложения + **Distribution** и жмем *Generate*:

![Название для Provisioning Profile](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/generate-profile-name.png)

Осталось только скачать файл:

![Скачиваем Provisioning Profile](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/download-profile.png)

Получаем файл `Appname_Distribution.mobileprovision`:

![Provision Profile](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/created-profile.png)

# Передаем сертификат и профаил разработчику

Передаем разработчику файл `.p12` и `Provision Profile`. 
Далее нужно дважды щелкнуть на полученный файл `.p12` или использовать импорт в **Keychain Access**.

![Импортируем Certificates.p12](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/add-p12.png)

Чтобы добавить `Provision Profile` открываем Xcode с проектом. Переходим в Project Settings и выбираем target. На вкладке *Signing & Capabilities* отключаем **Automatically manage signing**, выбираем нужный `Team ID` и импортируем полученный `Provisioning Profile`.

![Импортируем Provision Profile](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/add-profile-xcode.png)
