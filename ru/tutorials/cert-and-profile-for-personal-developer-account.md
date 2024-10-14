Вы хотите добавить разработчика в аккаунт, чтобы он мог выгружать приложения. Если у вас аккаунт компании (юр. лицо), то всё работает из коробки.

Но если у вас индивидуальный аккаунт (физ. лицо), то сторонний разработчик сможет выгружать приложения только со специальным профайлом.

> Передавать логин-пароль от вашего Apple ID небезопасно, не делайте так

Сертификаты можно сделать вручную или через API. В этой статье разберем ручной способ.

По шагам, что будем делать:
- Сначала запрос на подпись для сертификата
- Создадим сам сертификат
- Объединим этот сертификат с ключом
- Регистрируем приложение (возможно, оно у вас уже зарегано)
- Делаем профайл на основе сертификата — именно он нужен, чтобы выгружать приложения

# Запрос сертификата

Делаем специальный запрос на сертификат — это файл с расширением `.certSigningRequest`.

Открываем *Keychain Access* и создаём файл `CertificateSigningRequest.certSigningRequest`:

![Запрос в центре сертификации](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/keychain-request.png)

Вводим почту, имя и выбираем *Saved to disk*. В следующем окне просто сохраните файл:

![Сохраняем запрос на сертификат](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/keychain-sert-info.png?v=2)

У вас появится файл, он ещё пригодится:

![Готовый файл `.certSigningRequest`](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/keychain-sert-created.png?v=2)

> Если у владельца акаунта нет macOS, то запрос-файл делает разработчик и отправляет владельцу аккаунта

# Делаем сертификат

Сертификат подтверждает, что приложение именно ваше. Расширение у файла-сертификата — `.cer`.

Откройте в *Developer Account* вкладку сертификаты:

![Вкладка с сертификатами](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/main-sert.png)

Чтобы сделать новый сертификат, жмите плюс:

![Добавляем сертификат](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/add-sert.png)

Выбираем *Apple Distribution* и жмем *Continue*:

![Apple Distribution](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/new-sert.png)

На этой странице попросит файл-запрос на сертификат `.certSigningRequest`, который мы сделали выше. Выбирайте файл:

![Добавляем `.certSigningRequest`](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/select-new-sert.png)

Сертификат готов — скачайте его, он ещё пригодится:

![Скачиваем сертификат](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/download-sert.png)

# Объединяем сертификат и ключ

Дальше нужен файл с расширением `.p12`. Он хранит связку сертификат-ключ.

Кликните два раза по файлу `distribution.cer`, и он откроется *Keychain Access*.

> Если ничего не происходит, просто найдите последний загруженный сертификат *Apple Distribution* по дате. Дата истечения будет через год

![Apple Distribution сертификат](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/distribution-sert.png)

Разверните выпадайку (слева от сертификата), выделите сертификат и приватный ключ. Дальше нажмите правую кнопку и выберите `Export 2 items...`.

![Экспортируем сертификат с ключом](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/export-distribution-sert.png)

Сохраняем файл:

![Имя для сертификата](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/create-sert-p12.png)

Ставим пароль сертификату, можно оставить пустым:

![Пароль для сертификата](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/sert-p12-non-pass.png)

Тут попросит пароль от вашего мака — введите и нажмите *Always Allow*:

![Вводим пароль от вашего мака](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/sert-p12-system-pass.png)

Получим файл `Certificates.p12`:

![Сертификат `.p12`](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/save-sert-p12.png)

# Регистрируем приложение

> Если у вас уже есть приложение, этот шаг пропускаем

`App ID` это уникальный идентификатор приложения. Он связывает приложения с сервисами Apple, такими как Push Notifications, iCloud, Game Center и др.

Идем в *Developer Account* во вкладку *Identifiers* и жмем плюс:

![Вкладка Identifiers](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/identifiers.png)

Выбираем *App IDs*, далее *App*:

![App IDs и App](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/register-identifier-app-id.png)

Здесь в *Description* введите название приложения, а в *Bundle ID* бандл. `Explicit` — используется для подписи только одного приложения. `Wildcard` — используется для подписи нескольких приложений.

> Подробнее про Explicit и Wildcard [по ссылке](https://developer.apple.com/library/archive/qa/qa1713/_index.html):

![Регистрация App ID](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/register-app-id.png)

Когда заполнили поля, жмём *Register*:

> Если получили ошибку проверьте поле Bundle ID

![Регистрируем App ID](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/end-register-app-id.png)

На странице *Identifiers* появится идентификатор нового приложения:

![Идентификатор приложения](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/identifiers-list.png)

# Provisioning Profile

`Provisioning Profile` связывает всё вместе: Apple Developer Account, App ID, сертификаты и устройства.

Это файл с расширением `.mobileprovision`.

Идем во вкладку *Profiles*, жмем кнопку *Generate a profile*:

![Вкладка Profiles](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/profiles.png)

Выбираем *App Store Connect*:

![App Store Connect](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/new-profile.png)

В `App ID` выбираем нужный `Bundle ID` из списка:

![Выбираем App ID](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/generate-profile-app-id.png)

Выбираем недавно созданный сертификат (проверь дату, когда истекает):

![Добавляем сертификат](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/generate-profile-select-sert.png)

Заполните имя *Provisioning Profile Name* и нажмите *Generate*:

![Название для Provisioning Profile](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/generate-profile-name.png)

Осталось скачать файл:

![Скачиваем Provisioning Profile](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/download-profile.png)

Получаем файл с вашим именем и расширением `.mobileprovision`:

![Provision Profile](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/created-profile.png)

# Передаем файлы разработчику

Передаем разработчику файл `.p12` и `Provision Profile`. Дальше разработчику нужно дважды щелкнуть на файл `.p12` или импортировать в *Keychain Access*:

![Импортируем `.p12`](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/add-p12.png)

Теперь разработчик идет в Xcode-проект — Project Settings и выбирает таргет. На вкладке *Signing & Capabilities* отключаем `Automatically manage signing`, выбираем Team ID и импортируем Provisioning Profile:

![Импортируем Provision Profile](https://cdn.sparrowcode.io/tutorials/cert-and-profile-for-personal-developer-account/add-profile-xcode.png)

Готово! Разработчик сможет выгружать приложения на индивидуальный аккаунт.

> Инструкцию повторять только если меняется Profile. Для каждого приложения повторять не нужно