# Redbutton3

RedButton 3 Website Doorways Generator

Top secret development of Russian scientists is now available to you! The new website generator RedButton great for creating a network of websites.

It is a convenient, fast and functional website generator in which we have invested all his 8 years experience in creating scripts for SEO.
Opportunities of the doorway are constantly evolving, adapting to changes in search engine algorithms.

**Now the generation of websites just got easier!**

In our case, the macros — a special code in the generation process doorway pages will be replaced by the corresponding value. Macros are used in a pattern, file with the text to insert "Random lines" and in some fields of the admin redbutton doorway page generator.

For examples of the macro, we will use the keywords from the topics of "windows" and "buy viagra".
Macros for the Keyword

[KEYWORD] — current keyword is

    Inserts the current keyword for the page
    Example:
    На нашем сайте можно заказать [KEYWORD].
    Results:
    На нашем сайте можно заказать пластиковые окна
[UC-KEYWORD] — current keyword is with a capital

    Inserts the current word, written with a capital letter.
    Example:
    [UC-KEYWORD]: описание и характеристики
    result:
    Пластиковые окна: описание и характеристики
[UCW-KEYWORD] — current keyword, all the words that begin with a capital letter

    Recommended for doorways in English, it is accepted to write the main title so that all the words are capitalized.
    Example:
    [UCW-KEYWORD]
    Result:
    Buy Viagra
[RAND-KEYWORD] — random keyword is

    Inserts random keyword from the list, choose to create doorway.
    Example:
    Также у нас есть [RAND-KEYWORD]
    Result:
    Также у нас есть пластиковые окна Salamander
[RAND-UC-KEYWORD] — keyword is casual with a capital

    Inserts random keyword with capital from the list, choose to create a doorway.
    Example:
    Похожие товары:
    [RAND-UC-KEYWORD]
    [RAND-UC-KEYWORD]
    Похожие товары:
    [RAND-UC-KEYWORD]
    [RAND-UC-KEYWORD]
    Похожие товары:
    [RAND-UC-KEYWORD]
    [RAND-UC-KEYWORD]
    Result:
    Похожие товары:
    Пластиковые окна Salamander
    Пластиковые окна Proplex
    Похожие товары:
    Пластиковые окна Salamander
    Пластиковые окна Proplex

The structure of the doorways

You can create a two-tier doorways. They are much more similar to this web site, rather than doorways with a linear structure.

It turns out that doorway consists of several sections. The section titles and a list of pages for each section, you can specify a file with keywords.

Below is an example of a regular file with the key words, where are the section names (they are marked by square brackets). All the key words that come after the title of the section, are of this section. So until then, until a different title.
...
пластиковые окна
[услуги]
ремонт пластиковых окон
регулировка пластиковых окон

[комплектующие]
ручки для пластиковых окон
замки для пластиковых окон
жалюзи для пластиковых окон
москитные сетки
...

[MENU-CATEGORY] — creates a menu of the section titles Doorway

    Inserts HTML-list partitioning doorway as hyperlinks. Keywords in hyperlinks automatically will begin with a capital letter. With CSS menu can be both vertical and horizontal.
    Example:
    Разделы сайта: [MENU-CATEGORY]
    Result:
    Разделы сайта:

        Services
        Components 

Links and hyperlinks

[GO-URL] — link to go to an external resource

    In dorgenov may provide a link to an external resource, such as TDS or feed from affiliate programs. If the link you want to pass the current word, you can do this using a macro [KEYWORD].
    Suppose we have indicated in the "External Links" following link:
    http://www.example.com?feed.php?partner=123&keyword=[KEYWORD]
    Example:
    Подробнее про <a href="[GO-URL]">[KEYWORD]</a>
    Result:
    Подробнее про пластиковые окна
[GO-LINK-HTML] — HTML-code with a hyperlink to go to an external resource

    This macro is in addition to the macro [GO-URL] and allows you to make a simple link from a full HTML-block with a hyperlink. In the «HTML-link to an external resource" you can choose the appearance of the hyperlink — it could be the image or hyperlink to the text.
    Suppose we have indicated in the "External Links" following link:
    http://www.example.com?feed.php?partner=123&keyword=[KEYWORD]
    and in the "Link Text" indicated the following:
    Узнайте больше про [KEYWORD]
    Example:
    [GO-LINK-HTML]
    Result:
    Узнайте больше про пластиковые окна
[RAND-LINK] — a link to a random page

    A hyperlink to the keyword on a random page doorway. Keyword in the hyperlink will be just for the page on which to make the transition.
    Example:
    Похожие статьи: [RAND-LINK]
    Result:
    Похожие статьи: ремонт пластиковых окон
[RAND-UC-LINK] — a link to a random page, beginning with a capital letter

    A hyperlink to the keyword on a random page doorway. The first character in the keyword converted to upper case.
    Example:
    Похожие статьи:
    [RAND-UC-LINK]
    [RAND-UC-LINK]
    Похожие статьи:
    [RAND-UC-LINK]
    [RAND-UC-LINK]
    Похожие статьи:
    [RAND-UC-LINK]
    [RAND-UC-LINK]
    Result:
    Похожие статьи:
    Ремонт пластиковых окон
    Установка пластиковых окон
    Похожие статьи:
    Ремонт пластиковых окон
    Установка пластиковых окон
[RAND-UC-LINK-5-10] — a list of hyperlinks to random pages beginning with a capital letter

    HTML-list with links to the keyword on a random page doorway. The first character in the keyword each hyperlink uppercase.

    The number of hyperlinks in the list is selected at random from the specified in the macro range. For example, if you specify a range of 5-10, then the list can be from 5 to 10 hyperlinks.
    Example:
    Похожие статьи: [RAND-UC-LINK-2-4]
    Result:
    Похожие статьи:

        Repair of plastic windows
        Installation of plastic windows
        Adjustment of plastic windows 

[UC-LINK-5] — a hyperlink to the specified page

    A hyperlink to the keyword on the specified page doorway. The first character in the keyword converted to upper case.

    The number in the macro corresponds to the number of key words (and the page number) doorway.

    This macro should be applied if you want all pages doorway had any permanent hyperlink. For example, to create a pseudo-menu.
    Example:
    Дополнительные услуги:
    [UC-LINK-1]
    [UC-LINK-5]
    [UC-LINK-8]
    Дополнительные услуги:
    [UC-LINK-1]
    [UC-LINK-5]
    [UC-LINK-8]
    Дополнительные услуги:
    [UC-LINK-1]
    [UC-LINK-5]
    [UC-LINK-8]
    Result:
    Дополнительные услуги:
    Монтаж пластиковых окон
    Регулировка пластиковых окон
    Ремонт пластиковых окон
    Дополнительные услуги:
    Монтаж пластиковых окон
    Регулировка пластиковых окон
    Ремонт пластиковых окон
[TAGS] — inserts a tag cloud

    It is ready for a tag cloud consisting of hyperlinks that lead to other pages doorway. Font size in each link is exposed randomly.
    Example:
    Популярные тэги:
    [TAGS]
    Популярные тэги:
    [TAGS]
    Популярные тэги:
    [TAGS]
    Result:
    Популярные тэги:
    Монтаж пластиковых окон Пластиковые окна Регулировка пластиковых окон Москитные сетки
    Ремонт пластиковых окон
    Популярные тэги:
    Монтаж пластиковых окон Пластиковые окна Регулировка пластиковых окон Москитные сетки
    Ремонт пластиковых окон

Text

[TEXT-5-10] — the text from 5 to 10 offers

    This macro inserts the text that has been selected in the admin doorways.

    Before insertion, the text is treated in all the ways that have been selected in the admin doorways — adding to text keywords, rewrite the text, synonymy, etc.

    The number of sentences in the text is randomly selected from the range specified in the macro. For example, if you specify a range of 3-5, then every time the text is inserted, from 3 to 5 sentences.

    In addition, the text is automatically divided into a random number of paragraphs (<p> </ p>).
    Example:
    <h1>[UC-KEYWORD]</h1>
    [TEXT-3-7]
    <h1>[UC-KEYWORD]</h1>
    [TEXT-3-7]
    <h1>[UC-KEYWORD]</h1>
    [TEXT-3-7]
    Result:
    Installation of plastic windows

    As we mentioned, installation of plastic windows is strictly vertical and horizontal, with a tolerance of no more than 3 mm in the entire length of the window. After installation of the plastic window, paste special vapor barrier tape around the perimeter of the window. This tape will ensure continued installation and protect against mold and condensation inside the cavity of the joint.

    When finished, the installer fills the joint between the frame and the wall of the special foam. 
[RANDLINE] — random line from a file with strings (macro temporarily not available)

    This macro inserts a random line from a file, which is specified in the admin doorways as "Random lines from a file."

    Each line of the file, you can use different macros.

    Version of the file with the lines:

    Ordered [KEYWORD], all did great!

    On the advice of a friend took <b> [KEYWORD] </ b>. A quality I have not seen. Very pleased!

    Long sought <b> [KEYWORD] </ b>, found here. All placed very professional.

    Hooray! Yesterday put [KEYWORD]! I have long been waiting for!
    Example:

    Customer Reviews:

    [RANDLINE]

    [RANDLINE]
    The result:

    Customer Reviews:

    Hooray! Yesterday put new windows Salamander! I have long been waiting for!

    Been looking for new windows Salamander, found here. All placed very professional.

    Ordered windows Salamander, did everything perfectly! 
[RANDLINE-ANYWORD] — random line from a file with strings, which are divided into separate categories

    In a macro specifies the name of the category, which is present in the file with strings. In the same file, is specified in the category: [PRIMER:], with a mandatory colon before the closing bracket. And if we want to select a random string from this category, the macro would look like this: [RANDLINE-PRIMER].

    Each line of the file, you can use different macros.

    Version of the file with the lines:
    [CATS:]
    бобтейл
    британский короткошерстный
    перс
    полуперс
    ...
    [DOGS:]
    такса
    лабрадор
    сенбернар
    Example:

    I have a dog [RANDLINE-DOGS]

    And I [RANDLINE-CATS], it is a cat.
    The result:

    I have a labrador dog.

    And I polupers this cat. 

Image

To help manage the pictures we offer to sort them by category and upload the folder data / images.

In your data / images you can create separate folders for each category of images, such as: data / images / music.

And then in the process of generation, doorways will copy itself from there he needed pictures and save them in a folder with a ready doorways.

[RAND-IMG] — the path to a randomly selected image

    This macro displays the path to a randomly selected images copied from the folder data / images in a folder with the doorways.
    Example:

    Photo of the day:
    <img [RAND-IMG] alt="[KEYWORD]"> Result:

    Photo of the day:
    <img src="/i/window123.jpg" width="300" height="200" alt="plastikovye okna"> 
[RAND-IMG-200-250] — path to a randomly selected image, reduced in width

    This macro displays the path to a randomly selected images copied from the folder data / images in a folder with the doorways.

    But in contrast to the macro [RAND-IMG], there is still a decrease image size. In a macro specifies the desired width of the range of the image (in pixels). When generating a random number will be chosen from this range, which will be to reduce the width of the image. Height of the image will also be proportionally reduced, depending on the chosen width.
    Example:

    Our windows are:
    <img [RAND-IMG-400-500] alt="[KEYWORD]"> Result:

    Our windows are:
    <img src="/i/windows254.jpg" width="425" height="200" alt="plastikovye okna"> 
[RAND-IMG-doctor] — path to a random image from the folder

    This macro displays the path to a randomly selected images copied from the folder data/images/doctor in a folder with the doorways.

    In a macro, you can specify any name for the folder that you want to pre-create the folder data/images. This is very convenient, because can be pre-split images into separate categories for creating doorways pack.
    Example:

    Our windows are:
    <img [RAND-IMG-windows] alt="[KEYWORD]"> Result:

    Our windows are:
    <img src="/i/window12.jpg" width="300" height="200" alt="new windows"> 
[RAND-IMG-doctor-200-250] — path to the random images, reduced in width to the range (in pixels)
[GEN-IMG] — path to the image that is generated based on images from the public folder data / images
[GEN-IMG-200-250] — the path to the generated image, reduced in width to the range (in pixels)
[GEN-IMG-doctor] — path to the image that is generated based on images from the public folder data / images / doctor
[GEN-IMG-doctor-200-250] — path to the image generated based on images from the folder data / images.doctor and reduced in width to the range (in pixels)

Miscellanea

    [HOME-URL] — the path to the root of the doorway, like: http://www.example.com
    [NICK] — random nick-name
    [RAND-5-25] — a random number between 5 and 25
    [N] — serial number of the current keyword 


Генератор дорвеев RedButton 3

Секретная разработка ученых теперь доступна и вам! Новый генератор дорвеев Redbutton отлично подходит для создания современных дорвеев и сайтов-сателлитов.

Это удобный, быстрый и функциональный дорген, в который мы вложили весь свой 8-ми летний опыт создания скриптов для SEO.
Возможности генератора дорвеев постоянно совершенствуются, подстраиваясь под изменения в алгоритмах поисковых систем.

**Теперь генерация дорвеев стала еще проще**

Мы упростили работу с генератором дорвеев. Вам не нужно иметь большие знания в области HTML и JavaScript. Дорген не будет требовать, чтобы вы заполняли массу сложных и непонятных форм. Все сделано для вашего удобства.
Посмотрите [демо-версию](http://getredbutton.com/demo) генератора дорвеев.



Начало работы с генератором дорвеев RedButton
Основные настройки
Кейворды (ключевые слова)

Файл с кейвордами должен быть в формате "TXT" и состоять из слов или фраз, разбитых на отдельные строки. Рекомендуемая кодировка — UTF-8.

Наиболее часто используемые файлы можно положить в папку "data/keywords", в этом случае файл с кейвордами можно будет выбрать из выпадающего списка.

Для каждого кейворда создается одна страница дорвея. Соответственно, сколько кейвордов — столько и страниц будет в дорвее.
Выбрать случайные кейворды
Используйте эту опцию, чтобы указать минимальное и максимальное кол-во кейвордов, которое будет выбираться случайно при генерации каждого дорвея. При этом, кейворды всегда будут браться с разных строчек.

Эту опцию стоит использовать, если вы часто делаете дорвеи по одним и тем же кейвордам. Соберите все кейворды в один файл, чтобы они использовались при генерации каждого дорвея.
Текст

Файл с текстом должен иметь формат "TXT" и состоять из предложений. Рекомендуемая кодировка — UTF-8.

Наиболее часто используемые файлы можно положить в папку "data/texts", в этом случае файл с текстом можно будет выбрать из выпадающего списка.
Адрес будущего дорвея

Укажите в этом поле URL главной страницы для создаваемого вами дорвея. Например — www.example.com/mydoorway или mydoorway.example.com. Это необходимо, чтобы сохранить ссылки на все страницы дорвея для их перелинковки с новыми дорвеями.
Путь к папке с дорвеем

Если вы создаете дорвей с последующей автоматической закачкой на FTP-сервер, то укажите путь от той папки, в которой вы находитесь при входе на FTP-сервер. Например, при входе на FTP вы оказываетесь в корневой папке "domains", а дорвей нужно сохранить в папке "domains/example.com/www/mydoorway". В этом случае, укажите путь "example.com/www/mydoorway".

В случае, если при входе вы оказываетесь в папке с доменом, например domains/example.com, а вам нужно сохранить дорвей в папке с другим доменом, используйте ".." для перехода в вышестоящую папку, например — "../newexample.com".

Если вы создаете дорвей на том же сервере, где установлен дорген, то просто укажите путь относительно папки с доргеном. Например, если дорген находится в domains/example.com/www/redbutton, а вам нужно создать дорвей в domains/newexample.com/www, то укажите такой путь — ../../newexample.com/www.

Здесь также используется ".." для перехода в вышестоящую папку.
Настройки текста
Способ генерации текста

RedButton генерирует уникальный текст, используя выбранный вами файл с текстом. Вставка сгенерированного текста осуществляется через макрос [TEXT-min-max], где min — минимальное кол-во предложений, а max — максимальное. Количество предложений для сгенерированного текста каждый раз выбирается случайным образом из указанного диапазона. Например, [TEXT-5-10].

Алгоритмы генерации текста:

    Марков — алгоритм Маркова на основе триграмм. Выдает достаточно читабельный текст, но чтобы он был уникальный, нужно использовать текстовый файл размером от 1 Мб. Этот алгоритм достаточно сложный, что может сказаться на времени генерации.
    Карл Маркс — шуточный алгоритм, который я придумал в 2007-м году. Текст получается довольно бредовый, но уникальный. Работает очень быстро.

Количество кейвордов в тексте

Чтобы сгенерированный текст стал более ревалентным, в него аккуратно подмешивается текущий кейворд. Количество подмешиваний выбирается случайным образом из указанного диапазона.


Макросы для генератора дорвеев red.Button

В нашем случае, макрос — это специальный код, который в процессе генерации дорвея будет заменен на соответствующее ему значение. Макросы используются в шаблонах, файлах с текстом для вставки «Случайных строчек» и в некоторых полях админки генератора дорвеев.

Для примеров работы макросов, мы будем использовать ключевые слова из тематик "пластиковые окна" и "buy viagra".
Макросы для работы с ключевыми словами

[KEYWORD] — текущий кейворд

    Вставляет текущее ключевое слово для страницы
    Пример:
    На нашем сайте можно заказать [KEYWORD].
    Результат:
    На нашем сайте можно заказать пластиковые окна
[UC-KEYWORD] — текущий кейворд с большой буквы

    Вставляет текущее ключевое слово, написанное с большой буквы.
    Пример:
    [UC-KEYWORD]: описание и характеристики
    Результат:
    Пластиковые окна: описание и характеристики
[UCW-KEYWORD] — текущий кейворд, все слова которого начинаются с большой буквы

    Рекомендуется использовать для дорвеев на английском языке, где принято писать самые главные заголовки так, чтобы все слова начинались с большой буквы.
    Пример:
    [UCW-KEYWORD]
    Результат:
    Buy Viagra
[RAND-KEYWORD] — случайный кейворд

    Вставляет случайное ключевое слово из списка, выбранного для создания дорвея.
    Пример:
    Также у нас есть [RAND-KEYWORD]
    Результат:
    Также у нас есть пластиковые окна Salamander
[RAND-UC-KEYWORD] — случайный кейворд с большой буквы

    Вставляет случайное ключевое слово с большой буквы из списка, выбранного для создания дорвея.
    Пример:
    Похожие товары:
    [RAND-UC-KEYWORD]
    [RAND-UC-KEYWORD]
    Результат:
    Похожие товары:
    Пластиковые окна Salamander
    Пластиковые окна Proplex

Структура дорвеев

Вы можете создавать дорвеи с двухуровневой структурой. Они гораздо больше похожи на настоящие веб-сайты, нежели чем дорвеи с линейной структурой.

Получается, что дорвей состоит из нескольких разделов. Названия разделов и перечень страниц для каждого раздела можно указать в файле с ключевыми словами.

Ниже представлен пример обычного файла с ключевыми словами, где указаны названия разделов (они выделены квадратными скобками). Все ключевые слова, которые идут после названия раздела, относятся именно к этому разделу. И так до тех пор, пока не встретится другое название раздела.
...
пластиковые окна
[услуги]
ремонт пластиковых окон
регулировка пластиковых окон

[комплектующие]
ручки для пластиковых окон
замки для пластиковых окон
жалюзи для пластиковых окон
москитные сетки
...

[MENU-CATEGORY] — создает меню из названий разделов дорвея

    Вставляет HTML-список с разделами дорвея в виде гиперссылок. Ключевые слова в гиперссылках автоматически будут начинаться с большой буквы. С помощью CSS меню можно сделать как вертикальным, так и горизонтальным.
    Пример:
    Разделы сайта: [MENU-CATEGORY]
    Результат:
    Разделы сайта:

        Услуги
        Комплектующие

Ссылки и гиперссылки

[GO-URL] — ссылка для перехода на внешний ресурс

    В доргене можно указать ссылку на внешний ресурс, например на TDS или фид от партнерской программы. Если в ссылке требуется передать текущее ключевое слово, то это можно сделать при помощи макроса [KEYWORD].
    Допустим, мы указали в поле "Ссылка на внешний ресурс" такую ссылку:
    http://www.example.com?feed.php?partner=123&keyword=[KEYWORD]
    Пример:
    Подробнее про <a href="[GO-URL]">[KEYWORD]</a>
    Результат:
    Подробнее про пластиковые окна
[GO-LINK-HTML] — HTML-код с гиперссылкой для перехода на внешний ресурс

    Этот макрос является дополнением к макросу [GO-URL] и позволяет сделать из простой ссылки полноценный HTML-блок с гиперссылкой. В поле «HTML-ссылка на внешний ресурс» можно выбрать тип оформления гиперссылки — это может быть гиперссылка с изображением или с текстом.
    Допустим, мы указали в поле "Ссылка на внешний ресурс" такую ссылку:
    http://www.example.com?feed.php?partner=123&keyword=[KEYWORD]
    А в поле "Ссылка с текстом" указали следующий текст:
    Узнайте больше про [KEYWORD]
    Пример:
    [GO-LINK-HTML]
    Результат:
    Узнайте больше про пластиковые окна
[RAND-LINK] — гиперссылка на случайную страницу

    Гиперссылка с ключевым словом на случайную страницу дорвея. Ключевое слово в гиперссылке будет именно для той страницы, на которую будет осуществляться переход.
    Пример:
    Похожие статьи: [RAND-LINK]
    Результат:
    Похожие статьи: ремонт пластиковых окон
[RAND-UC-LINK] — гиперссылка на случайную страницу, начинающаяся с большой буквы

    Гиперссылка с ключевым словом на случайную страницу дорвея. Первый символ в ключевом слове переводится в верхний регистр.
    Пример:
    Похожие статьи:
    [RAND-UC-LINK]
    [RAND-UC-LINK]
    Результат:
    Похожие статьи:
    Ремонт пластиковых окон
    Установка пластиковых окон
[RAND-UC-LINK-5-10] — список гиперссылок на случайные страницы, начинающиеся с большой буквы

    HTML-список с гиперссылками с ключевым словом на случайную страницу дорвея. Первый символ в ключевом слове каждой гиперссылки переводится в верхний регистр.

    Количество гиперссылок в списке выбирается случайным образом из указанного в макросе диапазона. Например, если указан диапазон 5-10, то в списке может быть от 5 до 10 гиперссылок.
    Пример:
    Похожие статьи: [RAND-UC-LINK-2-4]
    Результат:
    Похожие статьи:

        Ремонт пластиковых окон
        Установка пластиковых окон
        Регулировка пластиковых окон

[UC-LINK-5] — гиперссылка на указанную страницу

    Гиперссылка с ключевым словом на указанную страницу дорвея. Первый символ в ключевом слове переводится в верхний регистр.

    Число, указанное в макросе, соответствует порядковому номеру ключевого слова (и соответственно номеру страницы) дорвея.

    Этот макрос стоит применить, если вы хотите, чтобы на всех страницах дорвея были какие-либо постоянные гиперссылки. Например, для создания псевдо-меню.
    Пример:
    Дополнительные услуги:
    [UC-LINK-1]
    [UC-LINK-5]
    [UC-LINK-8]
    Результат:
    Дополнительные услуги:
    Монтаж пластиковых окон
    Регулировка пластиковых окон
    Ремонт пластиковых окон
[TAGS] — вставляет облако тэгов

    Полностью готовое облако тэгов, состоящее из гиперссылок, ведущих на другие страницы дорвея. Размер шрифта у каждой гиперссылки выставляется случайным образом.
    Пример:
    Популярные тэги:
    [TAGS]
    Результат:
    Популярные тэги:
    Монтаж пластиковых окон Пластиковые окна Регулировка пластиковых окон Москитные сетки
    Ремонт пластиковых окон

Текст

[TEXT-5-10] — текст от 5 до 10 предложений

    Этот макрос вставляет часть текста, который был выбран в админке доргена.

    Перед вставкой, текст обрабатывается всеми способами, которые были выбраны в админке доргена — добавление в текст ключевых слов, рерайт текста, синонимизация и т.д.

    Количество предложений в тексте выбирается случайным образом из указанного в макросе диапазона. Например, если указан диапазон 3-5, то каждый раз будет вставляться текст, объемом от 3 до 5 предложений.

    Кроме того, текст автоматически разбивается на случайное количество параграфов (<p></p>).
    Пример:
    <h1>[UC-KEYWORD]</h1>
    [TEXT-3-7]
    Результат:
    Монтаж пластиковых окон

    Как мы уже писали, монтаж пластиковых окон производится строго по вертикали и горизонтали, с допустимым отклонением не более 3 мм на всю длину окна. После установки пластикового окна нужно приклеить специальную пароизоляционную ленту по периметру окна. Эта лента сможет обеспечить длительный срок службы монтажа и защитит от появления плесени и конденсата внутри полости шва.

    Когда все закончено, монтажник заполняет шов между рамой и стеной специальной монтажной пеной.
[RANDLINE] — случайная строка из файла со строками (макрос временно не доступен)

    Этот макрос вставляет случайную строку из файла, который указан в админке доргена как «Случайные строчки из файла».

    В каждой строчке этого файла можно использовать различные макросы.

    Вариант файла со строчками:

    Заказал [KEYWORD], все сделали отлично!

    По совету друга взял <b>[KEYWORD]</b>. Такого качества я еще не видел. Очень доволен!

    Долго искала <b>[KEYWORD]</b>, нашла здесь. Все поставили очень профессионально.

    Ура! Вчера поставили [KEYWORD]! Я так давно этого ждала!
    Пример:

    Отзывы клиентов:

    [RANDLINE]

    [RANDLINE]
    Результат:

    Отзывы клиентов:

    Ура! Вчера поставили пластиковые окна Salamander! Я так давно этого ждала!

    Долго искала пластиковые окна Salamander, нашла здесь. Все поставили очень профессионально.

    Заказал пластиковые окна Salamander, все сделали отлично!
[RANDLINE-ANYWORD] — случайная строка из файла со строками, который раздел на отдельные категории

    В макросе указывается название категории, которая присутствует в файле со строками. В самом же файле, категория указывается так: [PRIMER:], с обязательным двоеточием перед закрывающейся квадратной скобкой. И если мы хотим выбрать случайную строку из этой категории, то макрос будет выглядеть так: [RANDLINE-PRIMER].

    В каждой строчке этого файла можно использовать различные макросы.

    Вариант файла со строчками:
    [CATS:]
    бобтейл
    британский короткошерстный
    перс
    полуперс
    ...
    [DOGS:]
    такса
    лабрадор
    сенбернар
    Пример:

    У меня есть собака [RANDLINE-DOGS]

    А у меня [RANDLINE-CATS], это кошка.
    Результат:

    У меня есть собака лабрадор.

    А у меня полуперс, это кошка.

Изображения

Для удобной работы с картинками мы предлагаем отсортировать их по категориям и закачать в папку data/images.

В папке data/images можно создать отдельные папки для каждой категории картинок, например: data/images/music.

И тогда в процессе генерации, дорген сам будет копировать оттуда нужные ему картинки и сохранять их в папку с готовым дорвеем.

[RAND-IMG] — путь и размеры случайно выбранного изображения

    Этот макрос выводит путь и размеры случайно выбранного изображения, скопированного из папки data/images в папку с дорвеем.
    Пример:

    Фото дня:
    <img [RAND-IMG] alt="[KEYWORD]">
    Результат:

    Фото дня:
    <img src="/i/window123.jpg" width="300" height="200" alt="пластиковые окна">
[RAND-IMG-200-250] — путь и размеры случайно выбранного изображения, уменьшенного по ширине

    Этот макрос выводит путь и размеры случайно выбранного изображения, скопированного из папки data/images в папку с дорвеем.

    Но в отличии от макроса [RAND-IMG], здесь еще происходит уменьшение размеров изображения. В макросе указывается диапазон желаемой ширины изображения (в пикселях). При генерации будет выбрано случайное число из этого диапазона, до которого и будет уменьшена ширина изображения. Высота изображения также будет пропорциональна уменьшена, в зависимости от выбранной ширины.
    Пример:

    Наши окна:
    <img [RAND-IMG-400-500] alt="[KEYWORD]">
    Результат:

    Наши окна:
    <img src="/i/windows254.jpg" width="224" height="150" alt="пластиковые окна">
[RAND-IMG-doctor] — путь к случайно выбранному изображению из указанной папки

    Этот макрос выводит путь и размеры случайно выбранного изображения, скопированного из папки data/images/doctor в папку с дорвеем.

    В макросе можно указать любое название папки, которую нужно предварительно создать в папке data/images. Это очень удобно, т.к. можно заранее разбить изображения на отдельные категории для создания дорвеев разных тематик.
    Пример:

    Наши окна:
    <img [RAND-IMG-OKNA] alt="[KEYWORD]">
    Результат:

    Наши окна:
    <img src="/i/okno12.jpg" width="500" height="300" alt="пластиковые окна">
[RAND-IMG-doctor-200-250] — путь к случайной картинки, уменьшенной по ширине до указанного диапазона (в пикселях)
[GEN-IMG] — путь к картинке, сгенерированной на основе картинки из общей папки data/images
[GEN-IMG-200-250] — путь к сгенерированной картинке, уменьшенной по ширине до указанного диапазона (в пикселях)
[GEN-IMG-doctor] — путь к картинке, сгенерированной на основе картинки из общей папки data/images/doctor
[GEN-IMG-doctor-200-250] — путь к картинке, сгенерированной на основе картинки из папку data/images.doctor и уменьшенной по ширине до указанного диапазона (в пикселях)

Разное

    [HOME-URL] — путь к корню дорвея, типа: http://www.example.com
    [NICK] — случайный ник-нэйм
    [RAND-5-25] — случайное число от 5 до 25
    [N] — порядковый номер текущего ключевого слова



