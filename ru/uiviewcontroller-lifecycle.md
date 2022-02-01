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
            'https://developer.apple.com/documentation/uikit/uiviewcontroller',
            true
        )
    ],
    [
        "https://cdn.ivanvorobei.by/websites/sparrowcode.io/uiviewcontroller-lifecycle/google-structured-data/article_1_1.jpg",
        "https://cdn.ivanvorobei.by/websites/sparrowcode.io/uiviewcontroller-lifecycle/google-structured-data/article_16_9.jpg",
        "https://cdn.ivanvorobei.by/websites/sparrowcode.io/uiviewcontroller-lifecycle/google-structured-data/article_4_3.jpg"
    ]
);

HTMLElements::text(
    "В этой статье рассмотрим жизненный цикл ViewController'a. Посмотрим когда вызываются методы и что можно делать внутри них. Так же рассмотрим частые ошибки."
);

HTMLElements::text(
    "Начнем с `UIView`. Он ведет себя предсказуемо, как только вызвали инициализатор - выделяется память. Теперь проперти имеют значения и объект можно использовать."
);

HTMLElements::text(
    "У контроллера есть вью. Но то, что контроллер создан, не означает что вью создана тоже. Система ждет повод создать её. Концепция жизненного цикла строится вокруг этой особенности. Просто держите в уме, что вью создается по необходимости."
);

HTMLElements::titleSection(
    "Инициализируем"
);

HTMLElements::text(
    "Рассмотрим базовый `UIViewController`, инициализаторов два:"
);

HTMLElements::blockCode("
override init(nibName nibNameOrNil: String?, bundle nibBundleOrNil: Bundle?) {
    super.init(nibName: nibNameOrNil, bundle: nibBundleOrNil)
}
    
required init?(coder: NSCoder) {
    super.init(coder: coder)
}
");

HTMLElements::text(
    "Ещё есть инициализатор без параметров `init()`, но это обертка над первым иницициализатором."
);

HTMLElements::text(
    "На этом этапе контроллер ведет себя как обычный класс: инициализирует проперти, отрабатывает тело инициализатора. Контроллер может быть долго в состоянии без загруженной вью, а может даже никогда не загрузить ее. Вью загрузится как только система или разработчик обратится к проперти `.view`."
);

HTMLElements::titleSection(
    'Загружаем'
);

HTMLElements::text(
    "Разработчик презентует контроллер. Для системы это повод загрузить вью - выделяется память. Мы можем следить за процессом и даже вмешаться. Глянем какие методы доступны:"
);

HTMLElements::blockCode("
override func loadView() {}
");

HTMLElements::text(
    "Метод `loadView()` вызывается системой. Его не нужно вызывать вручную, но можно переопределить, чтобы подменить корневую вью. Если нужно загрузить вью вручную (и вы знаете что делаете), то держите красную кнопку `loadViewIfNeeded()`."
);

HTMLElements::important("Вызывать `super.loadView()` не нужно.");

HTMLElements::text(
    "Второй метод легендарен, как Стив Джобс. Он вызывается когда вью закончила загрузку."
);

HTMLElements::blockCode("
override viewDidLoad() {
    super.viewDidLoad()
}
");

HTMLElements::text(
    "Разработчики не просто так делают настройку контроллера и вьюх в методе `viewDidLoad()`. До вызова этого метода корневая вью еще не существует, а после контроллер уже готов появиться на экране. `viewDidLoad()` - отличное место. Память под вью выделена, вью загружена и готова к настройке."
);

HTMLElements::text(
    "Вью нельзя настраивать в инициализаторе. При обращении к `.view` она загрузится, но контроллер появится на экране не сейчас (а может вообще не появится). Проект от такого не крашнется, но элементы интерфейса расходуют много памяти и она потратится раньше, чем нужно. Лучше делать это по необходимости."
);

HTMLElements::text(
    "Раньше я делал проперти-вьюхи контроллера просто создавая их:"
);

HTMLElements::blockCode("
class ViewController: UIViewController {
    
    var redView = UIView()
}
");

HTMLElements::text(
    "Проперти инициализируется вместе с контроллером, а значит память для вью выделится сразу. Чтобы отложить это до требования, нужно пометить проперти как `lazy`."
);

HTMLElements::text(
    'В методе `viewDidLoad()` размеры вьюхи неверные, привязываться к высоте и ширине нельзя. Делайте настройку, которая не зависят от размеров.'
);

HTMLElements::text(
    "Хочу остановиться на `viewDidUnload()`. Корневая вью может выгружаться из памяти, а это означает кое-что невероятное:"
);

HTMLElements::important("Метод `viewDidLoad()` может вызываться несколько раз.");

HTMLElements::text(
    "Например, если модальный контроллер закрыть, вью выгрузится из памяти, но объект контроллера еще будет жив. Если показать контроллер еще раз - вью снова загрузится. Если система выгрузила вью, значит был повод. Не нужно обращаться к корневой вью в этом методе - это вызовет ее загрузку. Аутлеты здесь активны, но уже не имеют смысла - их можно ресетить."
);

HTMLElements::text(
    "Не нужно срочно брать внеурочные и все выходные переделывать вашу VPN-ку. Ничего не сломается, `viewDidLoad()` редко вызывается несколько раз. Держите в уме, что нужно разнести настройку данных и вьюх в следующем проекте."
);

HTMLElements::titleSection("Показываем");

HTMLElements::text(
    "Появление контроллера начинается с метода `viewWillAppear`:"
);


HTMLElements::blockCode("
override func viewWillAppear(_ animated: Bool) {
    super.viewWillAppear(animated)
}
    
override func viewDidAppear(_ animated: Bool) {
    super.viewDidAppear(animated)
}
");

HTMLElements::text(
    "Оба метода в связке. Тут делать настройку не нужно, но можно спрятать/показать вьюхи или добавить несложное поведение. В методе `viewDidAppear()` начинайте сетевой запрос или крутите индикатор загрузки. Оба метода могут вызываться несколько раз."
);

HTMLElements::text(
    "Есть методы, которые сообщают что вью пропадает с экрана. Наглядная схема:"
);

HTMLElements::image(
    'ViewController LifeCycle',
    'https://cdn.ivanvorobei.by/websites/sparrowcode.io/uiviewcontroller-lifecycle/header.jpg',
    70
);

HTMLElements::text(
    "Обратите внимание на пару антагонистов `viewWillDisappear()` и `viewDidDisappear`. Они вызываются, когда вью удаляется из иерархии представлений. Если вы показываете другой контроллер поверх, то методы не вызываются."
);

HTMLElements::titleSection("Layout");

HTMLElements::text(
    'Методы лейаута, аналогично методам выше, подвязаны к жизненному циклу вьюхи. Доступно 3 метода:'
);

HTMLElements::blockCode("
override func viewWillLayoutSubviews() {
    super.viewWillLayoutSubviews()
}
    
override func viewDidLayoutSubviews() {
    super.viewDidLayoutSubviews()
}
");

HTMLElements::text(
    'Первый метод вызывается до `layoutSubviews()` корневой вью, второй - после. Во втором методе размеры корректные, а вью размещены правильно - можно подвязываться к размерам корневой вью.'
);

HTMLElements::text(
    'Есть отдельный метод про изменение размеров вью. Это не обязательно поворот устройства, хотя он тоже:'
);

HTMLElements::blockCode(" 
override func viewWillTransition(to size: CGSize, with coordinator: UIViewControllerTransitionCoordinator) {
    super.viewWillTransition(to: size, with: coordinator)
}
");

HTMLElements::text(
    'После будут вызваны методы `viewWillLayoutSubviews()` и `viewDidLayoutSubviews()`.'
);

HTMLElements::titleSection("Кончается память");

HTMLElements::text(
    'Вызывается, если память переполняется. Если вы не очистите объекты, из-за которых это происходит, iOS принудительно выключит приложение (для пользователя будет выглядеть как краш).'
);

HTMLElements::blockCode("
override func didReceiveMemoryWarning() {
    super.didReceiveMemoryWarning()
}
");

HTMLElements::text(
    'На этом всё. Жизненный цикл контроллера большая тема, я мог что-то упустить. Дайте мне знать если нашли что-то или есть хороший пример для статьи.'
);

HTMLElements::tutorialFooter($tutorial);
