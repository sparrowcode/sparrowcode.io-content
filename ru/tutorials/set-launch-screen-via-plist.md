# Как удалить LaunchScreen.storyboard

По умолчанию `LaunchScreen.storyboard`-файл создается только для UIKit-проектов. Сначала удалите его:

![Как удалить `LaunchScreen.storyboard`.](https://cdn.sparrowcode.io/tutorials/set-launch-screen-via-plist/delete-launchscreen-storyboard-file.jpg)

Теперь выберите таргет приложения и перейдите на вкладку `Info`. Здесь нужно удалить ключ «Launch screen interface file base name» или `UILaunchStoryboardName`:

![Удалить ключ `UILaunchStoryboardName`.](https://cdn.sparrowcode.io/tutorials/set-launch-screen-via-plist/delete-launch-screen-interface-file-base-name-key.jpg)

Теперь здесь же добавить словарь `UILaunchScreen`:

![Добавить словарь `UILaunchScreen`.](https://cdn.sparrowcode.io/tutorials/set-launch-screen-via-plist/add-uilaunchscreen-key.jpg)

Словарь можно оставить пустым, тогда фон будет цвета `.systemBackground`.

# Настроить Launch Screen через `.plist`

Доступно для UIKit и SwiftUI начиная с iOS 14.

Можно добавить плейсхолдеры Tab/Nav/Tool-баров, чтобы переход между Launch Screen и стартовым контроллером был плавный. Ещё можно задать цвет фона и поставить картинку. Для всего этого указываем специальные ключи в plist-файле.

> Вы можете комбинировать ключи, например установить фон, картинку и Tab-бар вместе.

Разберем все 6 ключей:

## Background color

В Assets добавьте новый цвет, можно выбрать разные цвета для темной и светлой темы:

![Новый цвет в Assets.](https://cdn.sparrowcode.io/tutorials/set-launch-screen-via-plist/add-color-to-assets.jpg)

В словарь «Launch Screen» добавьте ключ `UIColorName` с именем цвета:

![Добавляем ключ `UIColorName`.](https://cdn.sparrowcode.io/tutorials/set-launch-screen-via-plist/add-background-color-launch-screen-key.jpg)

Теперь Launch Screen будет залит цветом:

![Результат с `UIColorName`.](https://cdn.sparrowcode.io/tutorials/set-launch-screen-via-plist/with-uicolorname-result.jpg)

## Image name

Можно установить картинку в центр Launch Screen. Добавляем картинку в Assets, а дальше добавьте ключ `UIImageName` и укажите имя картинки. Результат:

![Результат с `UIImageName`.](https://cdn.sparrowcode.io/tutorials/set-launch-screen-via-plist/with-uiimagename-result.jpg)

> Launch Screen кэшируется, поэтому если изменили картинку — симулятор нужно сбросить через `Device` → `Erase All Content and Settings...`.

## Image respects safe area insets

Ключ `UIImageRespectsSafeAreaInsets` должен влиять на размер картинки и вписывать ее в Safe Area. Я ставил разные картинки, но ключ ни на что не влияет. Проверял на iOS 17.2. Возможно это баг и его оправят в будущем.

## Show Tab Bar

Чтобы показать плейсхолдер Tab-бара, добавьте пустой словарь `UITabBar`:

![Добавить словарь `UITabBar`.](https://cdn.sparrowcode.io/tutorials/set-launch-screen-via-plist/add-uitabbar-key.jpg)

Снизу появится плейсхолдер Tab-бара:

![Результат c `UITabBar`.](https://cdn.sparrowcode.io/tutorials/set-launch-screen-via-plist/with-uitabbar-result.jpg)

> Высота Tab-бара на Launch Screen выше, чем должна быть. Это баг. Пока рекомендую использовать `Toolbar`, про него ниже.

## Show Toolbar

Аналогично можно показать плейсхолдер Tool-бара, для этого добавьте пустой словарь `UIToolbar`:

![Результат c `UIToolbar`.](https://cdn.sparrowcode.io/tutorials/set-launch-screen-via-plist/with-uitoolbar-result.jpg)

## Navigation bar

Чтобы добавить Navigation-бар, добавьте словарь `UINavigationBar`. По дефолту у Navigation-бара с большим заголовком фона нет, поэтому когда установите ключ - ничего не изменится.



