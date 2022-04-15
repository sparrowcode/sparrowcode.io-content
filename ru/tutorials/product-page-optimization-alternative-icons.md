С помощью  [Product Page Optimization](https://developer.apple.com/app-store/product-page-optimization/) вы можете создавать варианты скриншотов, промо-текстов и иконок. Скриншоты и текст добавляются в App Store Connect, а вот иконки добавляет разработчик в Xcode-проект.

В документации написано: «Поместите иконки в Asset Catalog, отправьте бинарный файл в App Store Connect и используйте SDK». Правда, там не сказали, как закинуть иконки и что это за SDK. Давайте разбираться.

## Добавляем иконки в Assets

Альтернативную иконку делаем в нескольких разрешениях, как и основную. Я использую приложение [AppIconBuilder](https://apps.apple.com/app/id1294179975). Имя пакета иконок видно в App Store Connect.

![Добавляем иконки в Assets.](https://cdn.sparrowcode.io/tutorials/product-page-optimization-alternative-icons/adding-icons-to-assets.png)

## Настраиваем таргет

Нам понадобится Xcode 13 и выше. Выберите таргет приложения и перейдите на вкладку `Build Settings`. В поиск вставьте `App Icon` — увидите секцию `Asset Catalog Compiler`.

![Параметры в таргете проекта.](https://cdn.sparrowcode.io/tutorials/product-page-optimization-alternative-icons/adding-settings-to-target.png)

Нас интересуют 3 параметра:

`Alternate App Icons Sets` — перечисление названий иконок, которые добавили в каталог.

`Include All App Icon Assets` — установите в `true`, чтобы включить альтернативные иконки в сборку.

`Primary App Icon Set Name` — название иконки по умолчанию. Скорее всего, альтернативную иконку можно сделать основной. Не проверял.

## Выгружаем

Остаётся собрать приложение и отправить на проверку.

>Альтернативные иконки будут доступны после прохождения ревью.

Теперь можно собирать разные страницы приложения и создавать ссылки для A/B тестов.
