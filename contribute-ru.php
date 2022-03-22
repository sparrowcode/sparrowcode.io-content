<?php

use App\HTMLElements;
use App\PageModel;
use App\Constants;

$page = new PageModel(
    'home',
    'Код Воробья',
    'Стать Автором',
    '',
    [],
    '20.03.2022',
    '20.03.2022'
);

HTMLElements::header($page, []);

HTMLElements::titleSection('Выбрать тему');

HTMLElements::text('Свободный выбор темы про iOS/macOS/watchOS разработку. Если не знаете про что писать, гляньте на список: `Date` и временные зоны, `FileManager`, Cell Registration + SideBar, работа с потоками.');

HTMLElements::titleSection('План');

HTMLElements::text('План статьи это секции и подсекции. ' . HTMLElements::embeddedLink('Отправьте его', Constants::$telegram_my) . ' для утверждения.');

HTMLElements::titleSection('Форматирование, медиа-файлы');

HTMLElements::text('Используем Markdown. В описании ' . HTMLElements::embeddedLink('репозитория', 'https://github.com/sparrowcode/Articles') . ' есть готовые статьи, гляньте форматирование в них. Заголовок писать не нужно, он указывается в meta-файле.');

HTMLElements::text('Рекомендации чтобы сделать текст чистым и упругим:');
HTMLElements::text("- Если без слова/предложения смысл не меняется - удаляем. Читателю будет легче фокусироваться.");
HTMLElements::text("- Не давать оценок за читателя. Опишите преимущества и недостатки через примеры.");
HTMLElements::text("- Стремится к конструкции подлежащее+сказуемое. Слова `измененный`, `перенесенный` - убивают энергию глагола. Переформулируйте.");

HTMLElements::text('Картинки, видео и файлы отправляйте мне - я загружу их на хостинг и дам ссылки.');

HTMLElements::titleSection('Публикация');

HTMLElements::important('Чтобы поддержать авторов, мы платим 40$ за туториал.');

HTMLElements::text('Когда закончили статью, сделайте Pull Request. Не забудьте добавить meta-файл с указанием заголовка, автора, ключевых слов и т.д. Проверка займет 1-2 дня.');

HTMLElements::line();

HTMLElements::footer();