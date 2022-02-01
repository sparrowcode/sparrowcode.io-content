<?php

use App\HTMLElements;
use App\TutorialModel;
use App\ButtonModel;

/** @var TutorialModel $tutorial */

HTMLElements::tutorialHeader(
    $tutorial,
    [
        new ButtonModel(
            'developer.apple.com',
            'https://developer.apple.com/documentation/uikit/uisheetpresentationcontroller',
            true
        )
    ],
    [
        "https://cdn.ivanvorobei.by/websites/sparrowcode.io/uisheetpresentationcontroller/google-structured-data/article_4_3.jpg",
        "https://cdn.ivanvorobei.by/websites/sparrowcode.io/uisheetpresentationcontroller/google-structured-data/article_16_9.jpg",
        "https://cdn.ivanvorobei.by/websites/sparrowcode.io/uisheetpresentationcontroller/google-structured-data/article_1_1.jpg"
    ]
);

HTMLElements::text(
    "Попытки управлять высотой модальных контроллеров мучают разработчиков уже 4 года. " . HTMLElements::embeddedLink('Библиотеки получаются паршивыми', 'https://github.com/ivanvorobei/SPStorkController') . ": работают отвратительно или вообще не работают. За попытку обсудить эту тему на планёрке выкинули из окна ведущего инженера `UIKit`. К iOS 15 Тим Кук сжалился и открыл секретное знание."
);

HTMLElements::video(
    'UISheetPresentationController Preview',
    'https://cdn.ivanvorobei.by/websites/sparrowcode.io/uisheetpresentationcontroller/uisheetpresentationcontroller.mov',
    100
);

HTMLElements::text(
    "Выглядит круто, кейсов использования много. Чтобы показать дефолтный `sheet`-controller, используйте код:"
);

HTMLElements::blockCode('
let controller = UIViewController()
if let sheetController = controller.sheetPresentationController {
    sheetController.detents = [.medium(), .large()]
}
present(controller, animated: true)
');

HTMLElements::text(
    "Это модальный контроллер, которому добавили сложное поведение. Можно оборачивать в навигационный контроллер, добавлять заголовок и бар-кнопки. Оберните код с `sheetController` в `if #available(iOS 15.0, *) {}`, если проект поддерживает предыдущие версии iOS. "
);

HTMLElements::titleSection(
    'Detents (стопоры)'
);

HTMLElements::text(
    "Стопор - это высота, к которой стремится контроллер. Прямо как в пейджинге скролла или когда электрон не на своём энергетическом уровне."
);

HTMLElements::text("Доступно два стопора: `.medium()` с размером примерно на половину экрана и `.large()`, который повторяет большой модальный контроллер. Если оставить только `.medium()`-стопор, то контроллер откроется на половину экрана и подниматься выше не будет. Установить свою высоту нельзя.");

HTMLElements::titleSection(
    'Переключение между стопорами'
);

HTMLElements::text("Чтобы перейти из одного стопора в другой, используйте код:");

HTMLElements::blockCode("
sheetController.animateChanges {
    sheetController.selectedDetentIdentifier = .medium
}
");

HTMLElements::text("Можно вызывать без блока анимации.");

HTMLElements::titleSection(
    'Альбомная ориентация'
);

HTMLElements::text("По умолчанию `sheet`-контроллер в альбомной ориентации выглядит как обычный контроллер. Дело в том, что `.medium()` -стопор недоступен, а `.large()` - это и есть дефолтный режим модального контроллера. Но можно добавить отступы по краям.");

HTMLElements::blockCode("
sheetController.prefersEdgeAttachedInCompactHeight = true
");

HTMLElements::text("Вот как это выглядит:");

HTMLElements::image(
    "Landscape for UISheetPresentationController",
    "https://cdn.ivanvorobei.by/websites/sparrowcode.io/uisheetpresentationcontroller/landscape.jpg",
    100
);
HTMLElements::text("Чтобы контроллер учитывал prefered-размер, установите `.widthFollowsPreferredContentSizeWhenEdgeAttached` в `true`.");

HTMLElements::titleSection(
    'Индикатор'
);

HTMLElements::text("Чтобы добавить индикатор вверху контроллера, установите `.prefersGrabberVisible` в `true`. По умолчанию индикатор спрятан. Индикатор не влияет на safe area и layout margins, по крайней мере, на момент написания статьи.");

HTMLElements::image(
    "Grabber for UISheetPresentationController",
    "https://cdn.ivanvorobei.by/websites/sparrowcode.io/uisheetpresentationcontroller/prefers-grabber-visible.jpg",
    100
);

HTMLElements::titleSection(
    'Затемнение фона'
);

HTMLElements::text("Указываете самый большой стопор, который не нужно затемнять. Всё, что больше этого стопора, будет затемняться. Код:");

HTMLElements::blockCode("
sheetController.largestUndimmedDetentIdentifier = .medium
");

HTMLElements::text("Указано, что `.medium` затемняться не будет, а всё, что больше, будет. Можно убрать затемнение для самого большого стопора.");

HTMLElements::titleSection(
    'Corner Radius'
);

HTMLElements::text("Управляйте закруглением краёв у контроллера. Для этого установите `.preferredCornerRadius`. Обратите внимание, что закругление меняется не только у презентуемого контроллера, но и у родителя.");

HTMLElements::image(
    "Grabber for UISheetPresentationController",
    "https://cdn.ivanvorobei.by/websites/sparrowcode.io/uisheetpresentationcontroller/preferred-corner-radius.jpg",
    100
);

HTMLElements::text(
    "На скриншоте я установил corner-радиус в `22`. Радиус сохраняется для `.medium`-стопора. На этом всё. Напишите в " . HTMLElements::embeddedTelegramPostLink("71", 'коментариях к посту') . ", будете ли использовать в своих проектах sheet-контроллеры."
);

HTMLElements::tutorialFooter($tutorial);
