<?php
class CHome extends IPage
  {
  private $m_settings;
  private $m_news;
  private $m_current_setting = 'default';
  /**
   * Доступные плагины
   * @var array
   */
  private $m_plugins_access = array();
  //--- переводы
  protected static $m_translate = array('ru' => array('main'                              => 'Главная',
                                                      'create_doorway'                    => 'Создать дорвей',
                                                      'on_ftp_server'                     => 'на FTP-сервере',
                                                      'on_this_server'                    => 'на этом сервере',
                                                      'b_keywords'                        => 'Кейворды',
                                                      'from_file'                         => 'из файла',
                                                      'from_list'                         => 'из списка',
                                                      'b_text'                            => 'Текст',
                                                      'b_url_doorway'                     => 'Адрес будущего дорвея:',
                                                      'b_path_to_doorway'                 => 'Путь к папке с дорвеем:',
                                                      'b_ftp_server'                      => 'FTP-сервер:',
                                                      'b_ftp_login'                       => 'Логин на FTP:',
                                                      'b_password'                        => 'Пароль:',
                                                      'b_create'                          => 'Создать',
                                                      'add_task'                          => 'добавить в пакетную генерацию',
                                                      'b_main'                            => 'Главная',
                                                      'b_tasks'                           => 'Пакетная генерация',
                                                      'b_translate'                       => 'Перевод',
                                                      'b_settings_save_success'           => 'Настройки сохранены успешно',
                                                      'b_settings_save_error'             => 'Настройки не сохранены',
                                                      'b_method_generate_text'            => 'Способ генерации текста:',
                                                      'b_algoritm_generate_text'          => 'Алгоритм генерации текста',
                                                      'b_markov'                          => 'Марков',
                                                      'b_karlmarks'                       => 'Карл Маркс',
                                                      'b_simple'                          => 'Текст не меняется',
                                                      'b_density_keywords_text'           => 'Количество кейвордов в тексте:',
                                                      'from'                              => 'от',
                                                      'to'                                => 'до',
                                                      'b_count_empty_text_keywords_title' => 'Количество \'белых\' страниц, где в текст не подмешиваются кейворды',
                                                      'b_count_empty_text_keywords'       => 'Cтраниц с текстом без кейвордов:',
                                                      'b_synonimizer_ru'                  => 'Синонимайзер русских слов',
                                                      'b_synonimizer_en'                  => 'Синонимайзер английских слов',
                                                      'random_lines'                      => 'Случайные строчки',
                                                      'density_selection'                 => 'Плотность выделения',
                                                      'keywords'                          => 'кейвордов',
                                                      'phrases'                           => 'фраз',
                                                      'keywords_phrases'                  => 'фраз и кейвордов',
                                                      'tags'                              => 'тегами',
                                                      'italic'                            => 'курсив',
                                                      'bold'                              => 'жирный',
                                                      'b_page_settings'                   => 'Настройки страниц',
                                                      'b_type_doorways'                   => 'Тип дорвея:',
                                                      'static'                            => 'статический',
                                                      'dynamic'                           => 'динамический',
                                                      'another'                           => 'другое',
                                                      'b_template'                        => 'Шаблон:',
                                                      'b_page_name'                       => 'Название страниц:',
                                                      'b_format_title_page'               => 'Заголовок страницы:',
                                                      'b_settings_action'                 => 'Настройки действий',
                                                      'b_use_redirect'                    => 'Использовать редирект',
                                                      'b_create_sitemap'                  => 'Создавать SiteMap (sitemap.xml)',
                                                      'b_create_rss'                      => 'Создавать RSS (rss.xml)',
                                                      'b_upload_image'                    => 'Закачивать картинки',
                                                      'b_settings'                        => 'Настройки:',
                                                      'b_settings_standart'               => 'Стандартные',
                                                      'b_save'                            => 'Сохранить',
                                                      'b_setting_text'                    => 'Настройки текста',
                                                      'b_pictures'                        => 'Картинки',
                                                      'b_pictures_path_settings'          => 'Путь к картинкам, [TEMPLATE] - картинки берутся из папки шаблона (/i, /images, /image), или вы можете указать путь откуда брать картинки',
                                                      'b_image_crop'                      => 'Обрезание картинки со всех сторон на несколько px',
                                                      'b_image_invert'                    => 'Инверсия картинки',
                                                      'b_image_resize_random'             => 'Изменение размера картинки случайным образом',
                                                      'b_image_resize_propotion'          => 'Изменение картинки пропорционально, работает только если отметить "Изменение размера картинки случайным образом"',
                                                      'b_image_watermark'                 => 'Водяной знак на картинках:',
                                                      'b_image_mirror'                    => 'Зеркалирование',
                                                      'b_image_negatif'                   => 'Негатив картинки',
                                                      'b_image_emboss'                    => 'Рельфная картинка',
                                                      'b_image_gray'                      => 'Черно-белое',
                                                      'b_image_filename_keyword'          => 'Имя файла картинки "[KEYWORD].jpg"',
                                                      'b_synonimizer_settings'            => 'Настройки синонимайзера',
                                                      'b_synonimizer_percent'             => 'Синонимизировать текст',
                                                      'b_rewrite_text'                    => 'Рерайт (перемешка предложений, перестановка частей предложений)',
                                                      'b_save_link'                       => 'Сохранять ссылки на созданные страницы в папку',
                                                      'b_rewrite_shake'                   => 'Перемешивать предложения',
                                                      'b_rewrite_changestruct'            => 'Изменять структуру предложения',
                                                      'b_sentense_paragraph'              => 'Количество предложений в параграфе',
                                                      'b_links'                           => 'Ссылки',
                                                      'b_links_go'                        => 'Ссылка на внешний ресурс',
                                                      'b_rewrite_adj'                     => 'Добавлять прилагательные',
                                                      'b_links_go_html'                   => 'HTML-ссылка на внешний ресурс',
                                                      'b_links_go_html_picture'           => 'Ссылка с картинкой из',
                                                      'b_links_go_html_text'              => 'Ссылка с текстом',
                                                      'b_help'                            => 'Помощь',
                                                      'b_templates'                       => 'Шаблоны',
                                                      'b_plugins'                         => 'Плагины',
                                                      'b_news'                            => 'Новости',
                                                      'b_xrumer'                          => 'Хрумер',
                                                      'b_select_rand_keywords'            => 'Выбрать случайные кейворды',
                                                      'b_xrumer_project'                  => 'Создать проект для Хрумера',
                                                      'b_page_categories'                 => 'Категории',
                                                      'b_categories'                      => 'Категории',
                                                      'b_categories_info'                 => 'Формат <br>Название категории|URL |Количество страниц',
                                                      'b_randlist_description'            => 'Вставка случайно выбранной строки из файла выполняется через макрос [RANDLINE], который нужно прописать в шаблоне.',
                                                      'b_tags_count'                      => 'ссылок в облаке, для макроса [TAGS]',
                                                      'b_module_image'                    => 'Изображения',
                                                      'b_module_texts'                    => 'Тексты',
                                                      'b_synonimizer_download'            => '(словарь не найден, пожалуйста, <a href="http://support.getredbutton.com/ru/download/dictionaries">скачайте его</a>)',
                                                      'b_redirect'                        => 'Редирект',
                                                      'b_redirect_description'            => 'Различными способами будет показываться страница указанная в поле "URL для редиректа". Фактического редиректа пользователя происходить не будет, но сам дорвей пользовтель не увидет',
                                                      'b_redirect_link'                   => 'URL для редиректа и клоакинга. Можно использовать несколько урлов, каждый с новой строчки. Для клоакинга будет использоваться только первый урл',
                                                      'b_redirect_type_none'              => 'Не использовать редирект',
                                                      'b_redirect_type_div'               => 'Загрузка сайта в DIV',
                                                      'b_redirect_type_iframe'            => 'Загрузка сайта в IFRAME',
                                                      'b_redirect_type_iframe_many'       => 'Загрузка сайта в IFRAME (более сложный javascript)',
                                                      'b_go_link_html_description'        => 'Вставка HTML-кода с гиперссылкой  выполняется через макрос [GO-LINK-HTML], который нужно прописать в шаблоне.',
                                                      'b_archive_zip'                     => 'Запаковать в ZIP-архив <div class="info">Архив с дорвеем закачивается на удаленный сервер гораздо быстрее.</div>',
                                                      'b_un_archive_zip'                  => 'Распаковать после закачки <div class="info">На хостинге, где будет распаковываться дорвея, должно работать PHP.</div>',
                                                      'b_auto_categories'                 => 'Автоматическое создание категорий',
                                                      'b_template_auto_categories'        => 'Шаблон названия категории в URL:',
                                                      'b_auto_categories_info'            => 'Введите шаблон для URL, поддерживаются макрос: [N]',
                                                      'b_count_auto_categories'           => 'Количество слов в категории:',
                                                      'b_min_categories_info'             => 'Минимальное количество слов в категории',
                                                      'b_max_categories_info'             => 'Максимальное количество слов в категории',
                                                      'b_delayed_publication'             => 'Отложенная публикация',
                                                      'b_cloaking'                        => 'Клоакинг',
                                                      'b_cloaking_description'            => 'Различный виды клоакинга, для поисковых ботов будет показана страница сайта, а для пользователей произойдет редирект. Вид редиректа и страницу для редиректа нужно указывать в разделе Редирект (чуть выше)<br>',
                                                      'b_cloaking_type_none'              => 'Не использовать клоакинг',
                                                      'b_cloaking_type_htaccess'          => 'Клоакинг через файл .htaccess',
                                                      'b_cloaking_type_js'                => 'Проверка поисковика с помощью javascript',
                                                      'b_cloaking_type_php'               => 'Проверка поисковика с помощью PHP, будет работать только для динамических сайтов',
                                                      'b_save_link_one_file'              => 'Сохранять все ссылки в один файл',
                                                      'b_links_go_desc'                   => 'Вставка ссылки  выполняется через макрос [GO-URL], который нужно прописать в шаблоне.',
                                                      'b_urls_language'                   => 'Урлы сайта на языке кейворда. Не используется транслит',
                                                      'b_create_sitemap_html'             => 'Создавать SiteMap (sitemap.html)',
                                                      'b_onepage_create'                  => 'Одностраничный дорвей <div class="info">Каждый кейворд будет поддоменом главного: [KEYWORD].main-door.ru</div>',
                                                      'b_image_default_path'              => 'Папка откуда брать картинки для RAND-IMG, GEN-IMAGE data/images/',
                                                      'b_image_path_root'                 => 'Из корня папки',
                                                      'b_delete_settings'                 => 'Удалить настройку',
                                                      'b_sure_delete_settings'            => 'Точно хотите удалить текущую настройку',
                                                      'b_settings_from_file'              => 'настройки берутся из текущей формы, и изменения в текущих настройках не будет влиять на пакетную генерацию',
                                                      'b_modul_settings'                  => 'Настройки',
                                                      'b_pinger'                          => 'Pinger',
                                                      'b_pinger_task'                     => 'Добавить задачу в пингатор',
                                                      'b_onepage_oneword'                 => 'Поддомен в одно слово',
                                                      'b_onepage_dir_len'                 => 'Максимальная длина поддомена',
                                                      'b_redirect_type_urls'              => 'Типы редиректа, если введено несколько ссылок',
                                                      'b_redirect_type_random'            => 'На любой странице всегда будет разный редирект',
                                                      'b_redirect_type_page'              => 'На каждой страницу будет свой редирект, который будет постоянным',
                                                      'b_redirect_type_site'              => 'На весь дорвей будет один редирект, выбранный рандомно при генерации',
                                                      'b_random_template'                 => 'Случайный',
                                                      'b_links_old_dor'                   => 'Ссылки на старые дорвеи',
                                                      'b_links_this_dor'                  => 'Ссылки на страницы текущего дорвея',
                                                      'b_ftp_proxy'                       => 'Использовать прокси для закачки по ftp',
                                                      'b_ftp_delayed'                     => 'Отложенная загрузка по ftp',
                                                      'b_modul_upload'                    => 'FTP',
                                                      'b_module_video'                    => 'Видео',
                                                      'b_urls_no_lower'                   => 'Не преобразовывать урлы к нижнему регистру',
                                                      'b_keys_shaken'                     => 'Перемешивание слов в ключевике',
                                                      'b_is_commpress_title'              => 'Сжимаем HTML, sitemap.xml и другие возможные файлы',
                                                      'b_is_commpress'                    => 'Сжатие HTML',
                                                      'b_is_js_obfuscator'                => 'Обфускация js файлов',
                                                      'b_js_obfuscator_primer1'           => 'Метод 1',
                                                      'b_js_obfuscator_primer2'           => 'Метод 2',
                                                      'b_ftp_delete_always'               => 'Удалять файлы, если не удалось закачать по фтп',
                                                      'b_keywords_color_text'             => 'Выделение ключей в тексте цветом',
                                                      'b_need_keyord_color'               => 'Выделять ключи цветом',
                                                      'b_keyord_color_rand'               => 'Выделение ключа случайным цветом',
                                                      'b_keyword_color_set'               => 'Выделять только из выбранных цветов,<br> указать через запятую',
                                                      'b_keyword_color_back_rand'         => 'Выделять фон под ключевиками случайно',
                                                      'b_keyword_back_color_set'          => 'Выделять фон только из выбранными <br>цветами, указать через запятую',
                                                      'b_density_keywords_color_text'     => 'Количество выделяемых ключей',
                                                      'b_keywords_size_text'              => 'Изменять шрифт у ключей',
                                                      'b_keyword_size_number'             => 'Количество изменяемых ключей',
                                                      'b_need_keyord_size'                => 'Изменять шрифт у ключей',
                                                      'b_keyword_font_set'                => 'Изменять шрифт, название через запятую',
                                                      'b_keyword_size_change'             => 'Изменять размер шрифта',
                                                      'b_keyword_color_set_title'         => 'Формат: #fff,#fa0000,#dadada',
                                                      'b_need_sentence'                   => 'Выделение предложений',
                                                      'b_keywords_sentence_text'          => 'Выделять предложения',
                                                      'b_sentence_color_rand'             => 'Выделение предложения случайным <br>цветом',
                                                      'b_sentence_color_set'              => 'Выделять только из выбранных цветов,<br>указать через запятую',
                                                      'b_sentence_color_back_rand'        => 'Выделение фона предложения <br>случайным цветом',
                                                      'b_sentence_back_color_set'         => 'Выделять фон предложения из выбранных <br>цветов, указать через запятую',
                                                      'b_sentence_strong'                 => 'Выделять предложение <strong>тегом strong</strong>',
                                                      'b_sentence_how_select'             => 'Сколько предложений выделять',
                                                      'b_sentence_color_set_title'        => 'Формат: #fff,#fa0000,#dadada',
                                                      'b_path_onepage'                    => 'Путь к папкам одностраничника'),
//---
                                        'en' => array('main'                              => 'Main',
                                                      'create_doorway'                    => 'Create a doorway',
                                                      'on_ftp_server'                     => 'on the FTP-server',
                                                      'on_this_server'                    => 'on this server',
                                                      'b_keywords'                        => 'Keywords',
                                                      'from_file'                         => 'from the file',
                                                      'from_list'                         => 'from the list',
                                                      'b_text'                            => 'Text',
                                                      'b_url_doorway'                     => 'Doorway URL:',
                                                      'b_path_to_doorway'                 => 'Doorway directory path:',
                                                      'b_ftp_server'                      => 'FTP-server:',
                                                      'b_ftp_login'                       => 'User name:',
                                                      'b_password'                        => 'Password:',
                                                      'b_create'                          => 'Create',
                                                      'add_task'                          => 'add to Tasks',
                                                      'b_main'                            => 'Main',
                                                      'b_tasks'                           => 'Tasks',
                                                      'b_translate'                       => 'Translate',
                                                      'b_settings_save_success'           => 'Settings was successfully saved',
                                                      'b_settings_save_error'             => 'Settings did not save',
                                                      'b_method_generate_text'            => 'Method of text generation:',
                                                      'b_algoritm_generate_text'          => 'Algorithm of text generation',
                                                      'b_markov'                          => 'Markov',
                                                      'b_karlmarks'                       => 'Karl Marks',
                                                      'b_simple'                          => 'Text not change',
                                                      'b_density_keywords_text'           => 'Number of keywords in the text:',
                                                      'from'                              => 'from',
                                                      'to'                                => 'to',
                                                      'b_count_empty_text_keywords_title' => 'Number of pages where did not have keywords',
                                                      'b_count_empty_text_keywords'       => 'Number of pages without keywords:',
                                                      'b_synonimizer_ru'                  => 'Synonimizer russian words',
                                                      'b_synonimizer_en'                  => 'Synonimizer english words',
                                                      'random_lines'                      => 'Random lines',
                                                      'density_selection'                 => 'Density of selection of the',
                                                      'keywords'                          => 'keywords',
                                                      'phrases'                           => 'phrases',
                                                      'keywords_phrases'                  => 'phrases and keywords',
                                                      'tags'                              => 'tags',
                                                      'italic'                            => 'italic',
                                                      'bold'                              => 'bold',
                                                      'b_page_settings'                   => 'Pages',
                                                      'b_type_doorways'                   => 'Type of doorway:',
                                                      'static'                            => 'static',
                                                      'dynamic'                           => 'dynamic',
                                                      'another'                           => 'another',
                                                      'b_template'                        => 'Template:',
                                                      'b_page_name'                       => 'Page name:',
                                                      'b_format_title_page'               => 'Page Title:',
                                                      'b_settings_action'                 => 'Actions',
                                                      'b_use_redirect'                    => 'Redirect use',
                                                      'b_create_sitemap'                  => 'Create SiteMap (sitemap.xml)',
                                                      'b_create_rss'                      => 'Create RSS (rss.xml)',
                                                      'b_upload_image'                    => 'Images upload',
                                                      'b_settings'                        => 'Settings:',
                                                      'b_settings_standart'               => 'Standart',
                                                      'b_save'                            => 'Save',
                                                      'b_setting_text'                    => 'Text Settings',
                                                      'b_pictures'                        => 'Pictures',
                                                      'b_pictures_path_settings'          => 'Images path, [TEMPLATE] - get images from template (/i, /images, /image), or you can write full path to images',
                                                      'b_image_crop'                      => 'Crop a few pixels from all sides',
                                                      'b_image_invert'                    => 'Invert image',
                                                      'b_image_resize_random'             => 'Image resize to random width and height',
                                                      'b_image_resize_propotion'          => 'Image resize to proportional by width, if you check "Image resize random"',
                                                      'b_image_watermark'                 => 'Add watermark:',
                                                      'b_image_mirror'                    => 'Mirror',
                                                      'b_image_negatif'                   => 'Negative',
                                                      'b_image_emboss'                    => 'Emboss',
                                                      'b_image_gray'                      => 'Shades of gray',
                                                      'b_image_filename_keyword'          => 'Image file name "[KEYWORD].jpg"',
                                                      'b_synonimizer_settings'            => 'Settings synonimizer',
                                                      'b_synonimizer_percent'             => 'synonimize text',
                                                      'b_rewrite_text'                    => 'Rewrite (shake sentence, processing commas)',
                                                      'b_save_link'                       => 'Save links to pages in the directory',
                                                      'b_rewrite_shake'                   => 'Shake sentences',
                                                      'b_rewrite_changestruct'            => 'Change structure sentences',
                                                      'b_sentense_paragraph'              => 'Count of sentenses in paragraph',
                                                      'b_links'                           => 'Links',
                                                      'b_links_go'                        => 'External link',
                                                      'b_rewrite_adj'                     => 'Add adjective',
                                                      'b_links_go_html'                   => 'External HTML-link',
                                                      'b_links_go_html_picture'           => 'Link with a picture from',
                                                      'b_links_go_html_text'              => 'Link with a text',
                                                      'b_help'                            => 'Help',
                                                      'b_templates'                       => 'Templates',
                                                      'b_plugins'                         => 'Plugins',
                                                      'b_news'                            => 'News',
                                                      'b_xrumer'                          => 'Xrumer',
                                                      'b_select_rand_keywords'            => 'Select random keywords',
                                                      'b_xrumer_project'                  => 'Create Xrumer project',
                                                      'b_randlist_description'            => 'Input random line from file, use this input by macros [RANDLINE].',
                                                      'b_tags_count'                      => 'Count links in TAGS',
                                                      'b_module_image'                    => 'Images',
                                                      'b_module_texts'                    => 'Texts',
                                                      'b_synonimizer_download'            => '(dictionary not found, please, <a href="http://support.getredbutton.com/en/download/dictionaries">download</a>)',
                                                      'b_redirect'                        => 'Redirect',
                                                      'b_redirect_description'            => 'Различными способами будет показываться страница указанная в поле "URL для редиректа". Фактического редиректа пользователя происходить не будет, но сам дорвей пользовтель не увидет',
                                                      'b_redirect_type_none'              => 'Redirect not use',
                                                      'b_redirect_type_div'               => 'Load website in DIV',
                                                      'b_redirect_type_iframe'            => 'Load website in IFRAME',
                                                      'b_redirect_type_iframe_many'       => 'Load website in IFRAME (but the javascript to big)',
                                                      'b_go_link_html_description'        => 'Insert HTML code in macros [GO-LINK-HTML]. Use it in template',
                                                      'b_archive_zip'                     => 'Create ZIP-archive',
                                                      'b_un_archive_zip'                  => 'Unzip website on server <div class="info">For web-servers with PHP support.</div>',
                                                      'b_auto_categories'                 => 'Auto-create categories',
                                                      'b_template_auto_categories'        => 'Template name of category in URL:',
                                                      'b_auto_categories_info'            => 'Enter the template for, use macros: [N]',
                                                      'b_count_auto_categories'           => 'Words in category:',
                                                      'b_max_categories_info'             => 'Max num of words in category',
                                                      'b_min_categories_info'             => 'Min num of words in category',
                                                      'b_delayed_publication'             => 'Delayed publication',
                                                      'b_cloaking'                        => 'Cloaking',
                                                      'b_cloaking_description'            => 'The different types of cloacking',
                                                      'b_cloaking_type_none'              => 'Do not use cloacking',
                                                      'b_cloaking_type_htaccess'          => 'Check search bot by .htaccess',
                                                      'b_cloaking_type_js'                => 'Check search bot by javascript',
                                                      'b_cloaking_type_php'               => 'Check search bot by PHP, work ony for dynamic sites',
                                                      'b_save_link_one_file'              => 'All links save to file',
                                                      'b_links_go_desc'                   => 'Insert links with macros [GO-URL]. You need write macros in template of web site',
                                                      'b_urls_language'                   => 'Write ulr on language of keywords, not use convert to latin',
                                                      'b_create_sitemap_html'             => 'Createg SiteMap (sitemap.html)',
                                                      'b_redirect_link'                   => 'URL for redirect and cloaking. You can write many urls. For cloacking use only first',
                                                      'b_categories'                      => 'Categories',
                                                      'b_page_categories'                 => 'Categories',
                                                      'b_onepage_create'                  => 'Create one page websites <div class="info">Each keyword will be subdomain of main domain: [KEYWORD].main-site.com</div>',
                                                      'b_image_default_path'              => 'Path for images of tags RAND-IMG, GEN-IMAGE data/images/',
                                                      'b_image_path_root'                 => 'root',
                                                      'b_delete_settings'                 => 'Setting delete',
                                                      'b_sure_delete_settings'            => 'Are you sure you want to delete current setting',
                                                      'b_settings_from_file'              => 'settings get from current form, if you will change settings it not be use in tasks',
                                                      'b_modul_settings'                  => 'Settings',
                                                      'b_pinger'                          => 'Pinger',
                                                      'b_pinger_task'                     => 'Add task to pinger',
                                                      'b_onepage_oneword'                 => 'Subdomain in one word',
                                                      'b_onepage_dir_len'                 => 'Max length subdomain',
                                                      'b_redirect_type_urls'              => 'Types of redirect, if you enter a few links',
                                                      'b_redirect_type_random'            => 'On any page will always redirect to a different sites',
                                                      'b_redirect_type_page'              => 'On each page is a redirect, which will be a permanent',
                                                      'b_redirect_type_site'              => 'On the whole website is a redirect, selected randomly to generate',
                                                      'b_random_template'                 => 'Random',
                                                      'b_links_old_dor'                   => 'Links on old sites',
                                                      'b_links_this_dor'                  => 'Links on pages current site',
                                                      'b_ftp_proxy'                       => 'Use proxies for upload file on ftp',
                                                      'b_ftp_delayed'                     => 'Delayed upload files on ftp',
                                                      'b_modul_upload'                    => 'FTP',
                                                      'b_module_video'                    => 'Video',
                                                      'b_urls_no_lower'                   => 'Not convert urls to lower case',
                                                      'b_keys_shaken'                     => 'Words mix in keys',
                                                      'b_is_commpress_title'              => 'Compress HTML, sitemap.xml and other files',
                                                      'b_is_commpress'                    => 'Compress HTML',
                                                      'b_is_js_obfuscator'                => 'Obfucation js files',
                                                      'b_js_obfuscator_primer1'           => 'Method 1',
                                                      'b_js_obfuscator_primer2'           => 'Method 2',
                                                      'b_ftp_delete_always'               => 'Delete doorway if not upload on ftp',
                                                      'b_keywords_color_text'             => 'Mark keyword another color',
                                                      'b_need_keyord_color'               => 'Select keywords colors',
                                                      'b_keyord_color_rand'               => 'Allocation key random color',
                                                      'b_keyword_color_set'               => 'Select only from the follow colors, <br> specify a comma',
                                                      'b_keyword_color_back_rand'         => 'Allocate background for keywords random',
                                                      'b_keyword_back_color_set'          => 'Allocate background only selected <br> color specified separated by commas',
                                                      'b_density_keywords_color_text'     => 'Number of allocated keys',
                                                      'b_keywords_size_text'              => 'Change fonts keywords',
                                                      'b_keyword_size_number'             => 'How much keywords change font',
                                                      'b_need_keyord_size'                => 'Change fonts keywords',
                                                      'b_keyword_font_set'                => 'Change font, name separated by commas',
                                                      'b_keyword_size_change'             => 'Change font size',
                                                      'b_keyword_color_set_title'         => 'Format: #fff,#fa0000,#dadada',
                                                      'b_need_sentence'                   => 'Select sentences',
                                                      'b_keywords_sentence_text'          => 'Select sentences',
                                                      'b_sentence_color_rand'             => 'Select sentence random color',
                                                      'b_sentence_color_set'              => 'Get color for select sentence from list',
                                                      'b_sentence_color_back_rand'        => 'Select background of sentence <br>random color',
                                                      'b_sentence_back_color_set'         => 'Get color for select of background of sentence <br>from list',
                                                      'b_sentence_strong'                 => 'Select senrnce by <strong>tag strong</strong>',
                                                      'b_sentence_how_select'             => 'How much sentence select',
                                                      'b_sentence_color_set_title'        => 'Format: #fff,#fa0000,#dadada',
                                                      'b_path_onepage'                    => 'Path to one page sites'));

  /**
   * Конструктор

   */
  public function __construct()
    {
    $this->m_settings = new CModel_settings();
    $this->m_news     = new CModel_news();
    //--- текущая настройка
    $this->m_current_setting = $this->m_settings->LoadCurrentSettings();
    if(!empty($this->m_current_setting))
      {
      $this->m_settings->SetSettingsArray($this->m_settings->Load($this->m_current_setting));
      }
    $this->SetTitle(CHome::GetTranslate('main'));
    //---
    }

  /**
   * Отображение админки доргена
   * @see IPage::Show()
   */
  public function Show($path = null)
    {
    global $IS_CRYPT, $LAST_BUILD, $BUILD;
    //--- может началась генерация?
    if(isset($_SESSION['home_startgenerating']) && $_SESSION['home_startgenerating'] == 1)
      {
      //--- произошел старт, отобразим пользователю это
      include("./inc/pages/home/start.phtml");
      return;
      }
    //---
    $this->m_plugins_access['onepage'] = true;
    $this->m_plugins_access['pinger'] = true;
    //---
    include("./inc/pages/home/index.phtml");
    //--- зачистка сессий
    if(isset($_SESSION['home_savesettings'])) unset($_SESSION['home_savesettings']);
    if(isset($_SESSION['home_startgenerating'])) unset($_SESSION['home_startgenerating']);
    //--- наличие ошибок при логине
    if(isset($_SESSION['need_check'])) unset($_SESSION['need_check']);
    if(isset($_SESSION['check_servers'])) unset($_SESSION['check_servers']);
    }

  /**
   * @param array $url
   * @param string $action
   */
  public function Action($url, $action)
    {
    $method_name = 'on' . $action;
    //---
    if(method_exists($this, $method_name)) $this->$method_name($url);
    }

  /**
   * Сохранение настроек
   * @param array $url
   */
  private function OnSaveSettings($url)
    {
    $settings = new CModel_settings();
    $name     = $settings->Save($_POST, $_POST['settings'], $_POST['settings_name']);
    if(!empty($name)) $_SESSION['current_settings'] = $name;
    //---
    CLogger::write(CLoggerType::DEBUG, "settings saved: " . $name);
    //---
    $_SESSION['home_savesettings'] = 1;
    header("location: ./");
    //---
    $settings->SaveCurrentSettings($name);
    //---
    exit;
    }

  /**
   * Изменить настройки
   * @param array $url
   */
  private function OnChangeSettings($url)
    {
    if(!empty($_REQUEST['n'])) $_SESSION['current_settings'] = $_REQUEST['n'];
    //---
    $this->m_settings->SaveCurrentSettings($_REQUEST['n']);
    //---
    header("location: ./");
    exit;
    }

  /**
   * Запуск генерации дорвея
   */
  private function OnStart($url)
    {
    global $LNG;
    //--- настройки
    set_time_limit(0);
    ini_set('max_execution_time', 0);
    ini_set('set_time_limit', 0);
    ini_set('implicit_flush', 1);
    ini_set('output_buffering', 0);
    ini_set('gd.jpeg_ignore_warning', 1);
    //--- размер памяти
    $memory_limit = (int)ini_get("memory_limit");
    if($memory_limit < 256)
      {
      $res = ini_set('memory_limit', '256M');
      CLogger::write(CLoggerType::DEBUG, "set memory limit " . ini_get("memory_limit") . ' old value: ' . $res);
      }
    else
      {
      CLogger::write(CLoggerType::DEBUG, "memory limit " . $memory_limit);
      }
    //---
    CLogger::write(CLoggerType::DEBUG, "starting generate...");
    //---
    $generator = new CModel_generator($_POST, $_FILES);
    $generator->Start();
    //---
    if(rand(1, 2) > 0)
      {
      $model_cleaner = new CModel_cleaner();
      //--- зачистка старых папок в tmp
      //--- удаление папок от фтп
      $model_cleaner->Clean();
      }
    //---
    echo '</body></html>';
    }

  /**
   * ПОлучение настроек
   */
  private function GetSettings()
    {
    return $this->m_settings;
    }

  /**
   * Получение перевода
   * @param string $name
   *
   * @return string
   */
  public static function GetTranslate($name)
    {
    global $LNG;
    //---
    if(isset(self::$m_translate[$LNG]) && isset(self::$m_translate[$LNG][$name]))
      {
      return self::$m_translate[$LNG][$name];
      }
    //--- если языка нет, то может английский подойдет?
    if($LNG != 'en' && isset(self::$m_translate['en']) && isset(self::$m_translate[$LNG][$name]))
      {
      return self::$m_translate[$LNG][$name];
      }
    //----
    return '[' . $name . ']';
    }

  /**
   * ПОлучение последних новостей и билда
   * @param array $url
   */
  public function OnGetLastNews($url)
    {

    }

  /**
   * Удаление настроек
   * @param $url
   */
  public function OnDeleteSettings($url)
    {
    $name = $_REQUEST['name'];
    if($name != 'default') $this->m_settings->DeleteSettings($name);
    //---
    header("location: ./");
    exit;
    }

  /**
   * Скачивание зип архива
   * @param $url
   */
  public function OnDownload($url)
    {
    if(empty($_REQUEST['f']))
      {
      CLogger::write(CLoggerType::ERROR, "download zip empty file");
      return;
      }
    //---
    $filename = $_REQUEST['f'];
    //---
    $path = pathinfo($filename);
    //---
    $ext = $path['extension'];
    if($ext != 'zip')
      {
      CLogger::write(CLoggerType::ERROR, "download zip but it not zip: " . $filename);
      return;
      }
    //---
    $mime = CModel_tools::GetMime($ext);
    //---
    CLogger::write(CLoggerType::ERROR, "download zip file: " . $filename);
    //---
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . $path['basename'] . '"');
    header('Content-Size: ' . (int)filesize($filename));
    header('Cache-Control: public');
    //---
    readfile($filename);
    //---
    exit;
    }
  }

?>
