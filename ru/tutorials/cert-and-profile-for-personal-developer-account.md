Если у вас индивидуальный аккаунт разработчика (на физ. лицо), то сторонний разработчик не сможет выгрузить билд. Для этого владельцу аккаунта нужно сделать сертификаты вручную.

> Может появиться идея передать логин-пароль, так делать небезопасно

Если у вас аккаунт компании (юр. лица), то сертификаты генерируются автоматически и делать ничего не нужно.

Статья написана по шагам, делать сверху-вниз:
- Сначала делаем подпись для сертификата
- Создадим сертификат
- Объединим этот сертификат с ключом
- Регистриурем приложение (если ещё не зарегистрировали)
- На основе сертификата сделаем профаил — именно он нужен, чтобы выгружать приложения

# Запрос сертификата

Сначала сделаем специальный запрос — это файл с расширением `.certSigningRequest`. Этот файл нужен, чтобы сделать сертификат.

Откроем *Keychain Access* и создадим файл `CertificateSigningRequest.certSigningRequest`:

![Запрос в центре сертификации](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/keychain-request.png)

Вводим почту и имя, выбираем *Saved to disk*. В следующем окне просто сохраните файл:

![Сохраняем подпись сертификата](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/keychain-sert-info.png?v=2)

У вас появится файл, он ещё пригодится:

![Готовый файл `.certSigningRequest`](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/keychain-sert-created.png?v=2)

# Делаем сертификат

Сертификат подтверждает что ваше приложение это именно оно. Расширение файла-сертификата `.cer`.

> Для каждого нового приложения инструкцию нужно повторить

Откройте свой *Developer Account*, вкладка сертификаты:

![Вкладка с сертификатами](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/main-sert.png)

Чтобы сделать новый сертификат, жмите плюс:

![Добавляем сертификат](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/add-sert.png)

Выбираем *Apple Distribution* и жмем *Continue*:

![Apple Distribution](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/new-sert.png)

На этой странице попросит файл-запрос на сертфиикат `.certSigningRequest`, который мы сделали выше. Выбирайте файл и идем дальше:

![Добавляем `.certSigningRequest`](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/select-new-sert.png)

Сертификат готов. Скачайте его, он ещё пригодится:

![Скачиваем сертификат](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/download-sert.png)

# Объединяем сертификат и ключ

Дальше нам нужен файл с расширением `.p12`. Он хранит связку сертификат + ключ.

Кликните два раза по файлу `distribution.cer`, он должен открыться в *Keychain Access*.

> Если ничего не происходит, просто найдите последний загруженный сертификат *Apple Distribution* по дате. Дата истечения будет через год

![Apple Distribution сертификат](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/distribution-sert.png)

Разверните выпадайку слева от сертификата и выделите сертификат и приватный ключ. Дальше жмем правую кнопку и выбираем `Export 2 items...`

![Экспорт сертификата с ключом](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/export-distribution-sert.png)

Сохраняем файл:

![Имя для сертификата](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/create-sert-p12.png)

Дальше оставьте поля пустыми и нажмите ok:

![Пароль для сертификата](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/sert-p12-non-pass.png)

Тут попросит пароль от вашего мака — вводите и нажмите *Always Allow*:

![Вводим пароль от вашего мака](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/sert-p12-system-pass.png)

Получим файл `Certificates.p12`:

![Сертификат .p12](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/save-sert-p12.png)

# Регистрируем приложение

> Если у вас уже есть приложение, этот шаг можно пропустить

`App ID` это уникальный идентификатор приложения. Он связывает приложения с сервисами Apple, такими как Push Notifications, iCloud, Game Center и др.

Идем снова в *Developer account*, выбираем *Identifiers* и жмем плюс:

![Вкладка Identifiers](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/identifiers.png)

Выбираем *App IDs*, далее *App*:

![App IDs и App](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/register-identifier-app-id.png)

Здесь в *Description* вводим название приложения. В *Bundle ID* указываем бандл приложения. `Explicit` - используется для подписи только одного приложения. `Wildcard` - используется для подписи нескольких приложений.

> Подробнее про Explicit и Wildcard, [здесь](https://developer.apple.com/library/archive/qa/qa1713/_index.html):

![Регистрация App ID](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/register-app-id.png)

Проверяем правильно ли все заполнили и жмем *Register*:

> Если получили ошибку проверьте поле Bundle ID, чаще всего проблема именно в нем.

![Регистрируем App ID](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/end-register-app-id.png)

На странице *Identifiers* появится идентификатор вашего приложения:

![Идентификатор приложения](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/identifiers-list.png)

# Provisioning Profile

`Provisioning Profile` связывает всё вместе — Apple Developer Account, App ID, сертификаты и зарегистрированные устройства. Это файл с расширением `.mobileprovision`.

Идем во вкладку *Profiles* жмем кнопку *Generate a profile* или плюс:

![Вкладка Profiles](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/profiles.png)

Выбираем *App Store Connect*:

![App Store Connect](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/new-profile.png)

В `App ID` выбираем нужный *Bundle ID* из списка:

![Выбираем App ID](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/generate-profile-app-id.png)

Выбираем недавно созданный сертификат (проверяй дату когда истекает):

![Добавляем сертификат](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/generate-profile-select-sert.png)

В поле Provisioning Profile Name введите имя приложения + *Distribution*. Жмем *Generate*:

![Название для Provisioning Profile](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/generate-profile-name.png)

Осталось скачать файл:

![Скачиваем Provisioning Profile](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/download-profile.png)

Получаем файл `Appname_Distribution.mobileprovision`:

![Provision Profile](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/created-profile.png)

# Передаем файлы разработчику

Передаем разработчику файл `.p12` и `Provision Profile`. Дальше разработчику нужно дважды щелкнуть на файл `.p12` или импортировать в *Keychain Access*:

![Импортируем `.p12`](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/add-p12.png)

Теперь разработчик идет в Xcode-проект. Нужно перейти в Project Settings и выбрать тарегт. На вкладке *Signing & Capabilities* отключаем `Automatically manage signing`, выбираем нужный Team ID и импортируем Provisioning Profile:

![Импортируем Provision Profile](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/add-profile-xcode.png)

Теперь разработчик сможет выгружать приложения на ваш индивидуальный аккаунт.
