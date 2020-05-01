<?php
class CApi extends IPage
  {
  private $m_current_setting = 'default';
  private $m_settings;
  //--- переводы
  protected static $m_translate = array('ru' => array('main_title'                             => 'API',
                                                      'b_text'                                 => 'Текст',
                                                      'b_text_parsing_url'                     => 'Парсим текст с URL',
                                                      'b_text_generate_key'                    => 'Генерация текста из файла, с настройками (аналог макроса [TEXT-5-10]). Значение keyword можно вставлять несколько ключевых слов разделенных "|", в ответ получится несколько текстов с ключевыми словами. Тексты будут разделены "\r\n.\r\n"',
                                                      'b_text_generate_n_keys'                 => 'Генерация текста с 3 ключами. Вместо 3 подставляйте любое число',
                                                      'b_text_generate_n_n_keys'               => 'Генерация текста с 3 до 5 ключами, количество выбирается случайным образом. Вместо 3 и 5 подставляйте любые числа',
                                                      'b_text_generate_n_keys_file'            => 'Генерация текста с 3 до 5 ключами взятые из указанного файла, количество выбирается случайным образом. Вместо 3 и 5 подставляйте любые числа',
                                                      'b_text_generate_n_keys_from_file'       => 'Генерация текста с 3 до 5 ключами взятые из указанного файла, количество выбирается случайным образом. Вместо 3 и 5 подставляйте любые числа. В ответ получится несколько текстов с ключевыми словами. Тексты будут разделены "\r\n.\r\n"',
                                                      'b_text_generate_links'                  => 'Вставка в текст случайных ссылок из указанного файла. Файл должен быть закачен в папку data/links',
                                                      'b_text_generate_links_params'           => '<b>links</b> = количество ссылок от 3 до 7<br>
<b>urls</b> = список файлов со списком урлов<br>
<b>anchors</b> = список файлов с анкорами<br>
<b>noanchor</b> = от 30% до 50 % ссылок без анкора.<br>',
                                                      'b_text_generate_links_params_nokeyword' => '<b>links</b> = количество ссылок от 3 до 7<br>
<b>urls</b> = список файлов со списком урлов<br>
<b>anchors</b> = список файлов с анкорами<br>
<b>noanchor</b> = от 30% до 50 % ссылок без анкора<br>
<b>nokeyword</b> = 1 не вставлять ключевики в текст.<br>',
                                                      'b_text_generate_randline'               => 'Получение случайной строки из указанного файла, без разбивки на параграфы<br>
  <b>filename</b> = имя файла откуда брать случайные строки. Файл должен находится в папке data/randlines<br>',
                                                      'b_text_generate_randline_paragraph'     => 'Получение случайной строки из указанного файла с разбивкой на параграфы<br>
  <b>filename</b> = имя файла откуда брать случайные строки. Файл должен находится в папке data/randlines<br>
  <b>paragraph</b> = количество предложений в параграфе<br>',
                                                      'b_settings_name'                        => 'Выбрать настройку',
                                                      'b_action_name'                          => 'Что делать',
                                                      'b_action_textgenerator'                 => 'Генерация текста',
                                                      'b_action_randline'                      => 'Случайная строка',
                                                      'b_action_parsing'                       => 'Парсинг текста с урла',
                                                      'b_pharagraph'                           => 'Параграфы',
                                                      'b_from'                                 => 'от',
                                                      'b_to'                                   => 'до',
                                                      'b_filetext'                             => 'Файл с текстом',
                                                      'b_filekeyword'                          => 'Файл с ключами',
                                                      'b_or_keyword'                           => ' или просто ключ',
                                                      'b_keywords_count'                       => 'Сколько ключей вставлять в текст',
                                                      'b_file_links'                           => 'Файл с ссылками',
                                                      'b_links_count'                          => 'Сколько ссылок',
                                                      'b_add_links'                            => 'Добавить ссылки',
                                                      'b_add_anchors'                          => 'Отдельно анкоры и урлы',
                                                      'b_noachors_count'                       => 'Сколько без анкорных ссылки',
                                                      'b_file_anchors'                         => 'Файл с анкорами',
                                                      'b_file_anchors_title'                   => 'Файл с анкорами, каждый анкор с новой строчки',
                                                      'b_file_urls'                            => 'Файл с урлами',
                                                      'b_file_urls_title'                      => 'Файл с урлами, каждый урл с новой строчки',
                                                      'b_get_url'                              => 'Получить урл',
                                                      'b_url'                                  => 'Url сайта для парсинга',
                                                      'b_result_url'                           => 'Рабочий урл API:',
                                                      'b_add_images'                           => 'Добавление картинок в текст',
                                                      'b_file_images'                          => 'Файл с урлами на картинки',
                                                      'b_file_images_links'                    => 'Нужны ли картинки в виде ссылок, выбрать файл со ссылками',
                                                      'b_images_count'                         => 'Сколько картинок в тексте',
                                                      'b_images_where'                         => 'Куда вставлять картинки',
                                                      'b_where_up'                             => 'Перед основным текстом',
                                                      'b_where_center'                         => 'Где-то в центре текста',
                                                      'b_where_down'                           => 'Внизу текста',
                                                      'b_where_random'                         => 'Случайным образом',
                                                      'b_images_position'                      => 'Позиция картинки или обтекание, align',
                                                      'b_position_left'                        => 'Left',
                                                      'b_position_center'                      => 'Center',
                                                      'b_position_right'                       => 'Right',
                                                      'b_position_random'                      => 'Случайным образом',
                                                      'b_image_in_text'                        => 'Картинку вставлять в текст, иначе отдельным тегом &lt;p&gt;',
                                                      'b_add_randline'                         => 'Добавлять случайные строки',
                                                      'b_file_randlines'                       => 'Файл со случайными строчками',
                                                      'b_file_randlines_title'                 => 'Файл со случайными строчками, которые будут вставлять в текст',
                                                      'b_randlines_count'                      => 'Количество случайных строк в тексте',
                                                      'b_add_noparagraph'                      => 'Не вставлять тег &lt;p&gt;, как разделить параграфов',
                                                      'b_action_randkeywords'                  => 'Случайные ключи',
                                                      'b_count_keywords'                       => 'Количество ключевых слов',
                                                      'b_delimiter_keywords'                   => 'Разделитель ключевых слов, можно несколько через |',
                                                      'b_first_alpha_big'                      => 'Первое слово с заглавной буквы и в конце точка',
                                                      'b_first_alpha_big_title'                => 'Первое слово с заглавной буквы и в конце точка',
                                                      'b_senteces_count'                       => 'Количество необходимых предложений',
                                                      'b_paragraph_count'                      => 'Количество необходимых параграфов',
                                                      'b_paragraph_tag'                        => 'Параграфы в тег &lt;p&gt;',
                                                      'b_next_links'                           => 'ссылки по порядку'),
    //---
                                        'en' => array('main_title'                             => 'API',
                                                      'b_text'                                 => 'Text',
                                                      'b_text_parsing_url'                     => 'Text parsing from URL',
                                                      'b_text_generate_key'                    => 'Text generating from file with current settings (like macros [TEXT-5-10])',
                                                      'b_text_generate_n_keys'                 => 'Generation of text with 3 keys. Instead any number',
                                                      'b_text_generate_n_n_keys'               => 'Generating text with from 3 to 5 keys, the number is chosen randomly. Instead of 3 and 5 substitute any number',
                                                      'b_text_generate_n_keys_file'            => 'Generating text from 3 to 5 keys taken from the chosen file, number of keys is selected randomly. Instead of 3 and 5 substitute any number',
                                                      'b_text_generate_n_keys_from_file'       => 'Generating text from 3 to 5 keys taken from the chosen file, number of keys is selected randomly. Instead of 3 and 5 substitute any number. In response get several texts with keywords. Texts will be divided "\r\n.\r\n"',
                                                      'b_text_generate_links'                  => 'Insert to text of random links from the specified file. The file must be uploaded into the path data/links',
                                                      'b_text_generate_links_params'           => '<b>links</b> = count of links from 3 to 7<br>
<b>urls</b> = list of files with urls<br>
<b>anchors</b> = list of files with text of urls (anchor)<br>
<b>noanchor</b> = links without anchor from 30% to 50%.<br>',
                                                      'b_text_generate_links_params_nokeyword' => '<b>links</b> = count of links from 3 to 7<br>
<b>urls</b> = list of files with urls<br>
<b>anchors</b> = list of files with text of urls (anchor)<br>
<b>noanchor</b> = links without anchor from 30% to 50%<br>
<b>nokeyword</b> = 1 do not insert keyword in text.<br>',
                                                      'b_text_generate_randline'               => 'Getting a random string from the selected file, no breakdown into <p><br>
  <b>filename</b> = file name where to get random strings. File must be to path data/randlines<br>',
                                                      'b_text_generate_randline_paragraph'     => 'Getting a random string from the selected file broken into paragraphs<br>
  <b>filename</b> = file name where to get random strings. File must be to path data/randlines<br>
  <b>paragraph</b> = the number of sentences in the paragraph, [3-7]<br>',
                                                      'b_settings_name'                        => 'Select settings',
                                                      'b_action_name'                          => 'What action',
                                                      'b_action_textgenerator'                 => 'Text generate',
                                                      'b_action_randline'                      => 'Random line',
                                                      'b_action_parsing'                       => 'Parser text from url',
                                                      'b_pharagraph'                           => 'Pharagraphs',
                                                      'b_from'                                 => 'from',
                                                      'b_to'                                   => 'to',
                                                      'b_filetext'                             => 'File with text',
                                                      'b_filekeyword'                          => 'File with keywords',
                                                      'b_or_keyword'                           => ' or only on keyword',
                                                      'b_keywords_count'                       => 'How many keywords insert in text',
                                                      'b_file_links'                           => 'File with links',
                                                      'b_links_count'                          => 'How many links',
                                                      'b_add_links'                            => 'Add links',
                                                      'b_add_anchors'                          => 'Separately, anchors and URLs',
                                                      'b_noachors_count'                       => 'How many links without anchors',
                                                      'b_file_anchors'                         => 'File with anchors',
                                                      'b_file_anchors_title'                   => 'File with anchors, each anchor in new line',
                                                      'b_file_urls'                            => 'File with urls',
                                                      'b_file_urls_title'                      => 'File with urls, each url in new line',
                                                      'b_get_url'                              => 'Get url',
                                                      'b_url'                                  => 'Url for parsing',
                                                      'b_result_url'                           => 'Result url API:',
                                                      'b_add_images'                           => 'Adding images to text',
                                                      'b_file_images'                          => 'File with the urls to the images',
                                                      'b_file_images_links'                    => 'Do you need the image in the form of links, select the file with links',
                                                      'b_images_count'                         => 'How many images in the text',
                                                      'b_images_where'                         => 'Where to insert images',
                                                      'b_where_up'                             => 'Before the main text',
                                                      'b_where_center'                         => 'Somewhere in the middle of text',
                                                      'b_where_down'                           => 'After the main text',
                                                      'b_where_random'                         => 'Random',
                                                      'b_images_position'                      => 'Position pictures: value of align',
                                                      'b_position_left'                        => 'Left',
                                                      'b_position_center'                      => 'Center',
                                                      'b_position_right'                       => 'Right',
                                                      'b_position_random'                      => 'Random',
                                                      'b_image_in_text'                        => 'КартинкуImage inserted into the text otherwise separate tag &lt;p&gt;',
                                                      'b_add_randline'                         => 'Add rand lines',
                                                      'b_file_randlines'                       => 'File rand lines',
                                                      'b_file_randlines_title'                 => 'File rand lines',
                                                      'b_randlines_count'                      => 'How much rand line add to text',
                                                      'b_add_noparagraph'                      => 'Do not insert &lt;p&gt;',
                                                      'b_action_randkeywords'                  => 'Random keywords',
                                                      'b_count_keywords'                       => 'Count of keywords',
                                                      'b_delimiter_keywords'                   => 'Delimiter of keywords, list with delimiter "|"',
                                                      'b_first_alpha_big'                      => 'The first word with a capital letter and end point',
                                                      'b_first_alpha_big_title'                => 'The first word with a capital letter and end point',
                                                      'b_senteces_count'                       => 'The number of necessary sentences',
                                                      'b_paragraph_count'                      => 'The number of necessary paragraphs',
                                                      'b_paragraph_tag'                        => 'Paragraphs inside tag &lt;p&gt;',
                                                      'b_next_links'                           => 'links in order'));
  /**
   * Токен
   * @var string
   */
  private $m_token;
  /**
   * управление API
   * @var CModel_api
   */
  private $m_model;

  /**
   * Конструктор
   */
  public function __construct()
    {
    $this->SetTitle(CAPI::GetTranslate('main_title'));
    $this->m_model  = new CModel_api();
    $settings_array = $this->m_model->LoadCurrentSettings();
    if(!empty($settings_array)) $this->m_model->SetSettingsArray($settings_array);
    //---
    $this->m_settings        = new CModel_settings();
    $this->m_current_setting = $this->m_settings->LoadCurrentSettings();
    if(!empty($this->m_current_setting))
      {
      $this->m_settings->SetSettingsArray($this->m_settings->Load($this->m_current_setting));
      }
    }

  /**
   * Отображение доверя
   * @see IPage::Show()
   */
  public function Show($path = null)
    {
    global $LNG;
    $this->m_token = $this->m_model->GetToken();
    //$this->m_list_news = $this->m_model->GetNews();
    //---
    include("./inc/pages/api/index.phtml");
    }

  /**
   * Обработка запросов
   * @see IPage::Action()
   */
  public function Action($url, $action)
    {
    $method_name = 'on' . $action;
    //---
    if(method_exists($this, $method_name)) $this->$method_name($url);
    }

  /**
   * Получение перевода
   *
   * @param string $name
   */
  public static function GetTranslate($name)
    {
    global $LNG;
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
   * Сохранение настроек
   * @param array $url
   */
  private function OnChangeToken($url)
    {
    $this->m_model->ChangeToken();
    //---
    CLogger::write(CLoggerType::DEBUG, "token was changed");
    //---
    header("location: ./?module=api");
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
    header("location: ./?module=api");
    exit;
    }

  /**
   * ПОлучение настроек
   */
  private function GetSettings()
    {
    return $this->m_model;
    }

  private function OnSaveSettings()
    {
    $protocol = "http://";
    if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on') $protocol = "https://";
    $url     = $protocol . $_SERVER["HTTP_HOST"];
    $sub_url = '';
    if(isset($_SERVER["REQUEST_URI"]))
      {
      $sub_url = $_SERVER["REQUEST_URI"];
      $pos     = strpos($sub_url, '?');
      if($pos !== false)
        {
        $sub_url = substr($sub_url, 0, $pos);
        }
      }
    $_POST['action'] = $_POST['actions'];
    $url .= $sub_url . 'api.php?token=' . $this->m_model->GetToken() . '&action=';
    $this->m_model->SaveCurrent($_POST);
    $data = $_POST;
    //---
    switch($data['action'])
    {
      case 'randline':
        $this->GetRandlineUrl($data, $url);
        break;
      case 'textparser':
        $this->GetTextparserUrl($data, $url);
        break;
      //---
      case 'randkeywords':
        $this->GetRandkeywords($data, $url);
        break;
      default:
        $this->GetTextgenerateUrl($data, $url);
    }
    //var_dump($url);
    echo '<a href="' . $url . '" target="_blank">' . $url . '</a>';
    }

  /**
   * Урл для получения случайной строки из файла
   * @param $data
   * @param $url
   */
  private function GetRandlineUrl($data, &$url)
    {
    $url .= 'randline';
    if(!empty($data['textsFile'])) $url .= '&filename=' . urlencode($data['textsFile']);
    //---
    if(!empty($data['fromPharagraph'])) $url .= '&amp;paragraph=[' . (int)$data['fromPharagraph'] . '-' . (int)$data['toPharagraph'] . ']';
    }

  /**
   * Урл для получения текста с сайта
   * @param $data
   * @param $url
   */
  private function GetTextparserUrl($data, &$url)
    {
    $url .= 'textparser';
    if(!empty($data['urlParser'])) $url .= '&amp;url=' . urlencode($data['urlParser']);
    }

  /**
   * Урл для генерации ключей
   * @param $data
   * @param $url
   */
  private function GetRandkeywords($data, &$url)
    {
    $url .= 'randkeywords';
    //--- имя файла
    if($data['fromRandKeys'] > 0 || $data['toRandKeys'] > 0)
      {
      $url .= '&keyword=[' . $data['fromRandKeys'] . '-' . $data['toRandKeys'] . '-' . $data['keywordsFile'] . ']';
      }
    //--- разделитель
    $url .= '&delimiter=' . urlencode(!empty($data['delimiterKeys']) ? $data['delimiterKeys'] : ',');
    //--- нужно ли первая буква с большой буквы
    $needSentences = (!empty($data['needFirstBigAlpha']) && $data['needFirstBigAlpha'] == 'on');
    $url .= '&aplhabig=' . ($needSentences ? '1' : '0');
    if($needSentences)
      {
      $url .= '&sentences=[' . (empty($data['fromSentencesCount']) ? 2 : (int)$data['fromSentencesCount']) . '-' . (empty($data['toSentencesCount']) ? 3 : (int)$data['toSentencesCount']) . ']';
      $url .= '&amp;paragraphs=[' . (empty($data['fromParagraphCount']) ? 2 : (int)$data['fromParagraphCount']) . '-' . (empty($data['toParagraphCount']) ? 3 : (int)$data['toParagraphCount']) . ']';
      }
    //--- урлы для вставки
    if(!empty($data['needLinksKey']) && $data['needLinksKey'] == 'on')
      {
      $url .= '&links=[' . $data['fromLinksKey'] . '-' . $data['toLinksKey'] . '-' . $data['linksFileKey'] . ']';
      }
    if(!empty($data['paragraphTag']) && $data['paragraphTag'] == 'on')
      {
      $url .= '&amp;paragraphtag=1';
      }
    if(!empty($data['next_links']) && $data['next_links'] == 'on')
      {
      $url .= '&amp;next_links=1';
      }
    //--- анкоры урлы
    if(!empty($data['needAnchorsRand']) && $data['needAnchorsRand'] == 'on')
      {
      $url .= '&linksurls=[' . $data['fromLinksAncRand'] . '-' . $data['toLinksAncRand'] . ']';
      //--- урлы
      $url .= '&urls=[';
      $url .= $data['urlsFileRand1'];
      if(!empty($data['urlsFileRand2'])) $url .= '|' . $data['urlsFileRand2'];
      if(!empty($data['urlsFileRand3'])) $url .= '|' . $data['urlsFileRand3'];
      $url .= ']';
      //--- анкоры
      $url .= '&anchors=[';
      $url .= $data['anchorsFileRand1'];
      if(!empty($data['anchorsFileRand2'])) $url .= '|' . $data['anchorsFileRand2'];
      if(!empty($data['anchorsFileRand3'])) $url .= '|' . $data['anchorsFileRand3'];
      $url .= ']';
      //--- без анкорные
      if($data['fromNoAchorsRand'] > 0 || $data['toNoAchorsRand'] > 0)
        {
        $url .= '&noanchor=[' . $data['fromNoAchorsRand'] . '-' . $data['toNoAchorsRand'] . ']';
        }
      }
    $url .= '&settings=' . ($data['settings']);
    }

  /**
   * Урл для генерации текста
   * @param $data
   * @param $url
   */
  private function GetTextgenerateUrl($data, &$url)
    {
    $url .= 'textgenerate';
    //--- имя файла
    $url .= '&filename=' . ($data['textsFile']);
    //--- ключ
    if(!empty($data['keyword']))
      {
      $url .= '&keyword=' . ($data['keyword']);
      }
    else
      {
      if($data['fromKeywords'] > 0 || $data['toKeywords'] > 0)
        {
        $url .= '&keyword=[' . $data['fromKeywords'] . '-' . $data['toKeywords'] . '-' . $data['keysFile'] . ']';
        }
      }
    //--- анкоры урлы
    if(!empty($data['needAnchors']) && $data['needAnchors'] == 'on')
      {
      $url .= '&links=[' . $data['fromLinksAnc'] . '-' . $data['toLinksAnc'] . ']';
      //--- урлы
      $url .= '&urls=[';
      $url .= $data['urlsFile1'];
      if(!empty($data['urlsFile2'])) $url .= '|' . $data['urlsFile2'];
      if(!empty($data['urlsFile3'])) $url .= '|' . $data['urlsFile3'];
      $url .= ']';
      //--- анкоры
      $url .= '&anchors=[';
      $url .= $data['anchorsFile1'];
      if(!empty($data['anchorsFile2'])) $url .= '|' . $data['anchorsFile2'];
      if(!empty($data['anchorsFile3'])) $url .= '|' . $data['anchorsFile3'];
      $url .= ']';
      //--- без анкорные
      if($data['fromNoAchors'] > 0 || $data['toNoAchors'] > 0)
        {
        $url .= '&noanchor=[' . $data['fromNoAchors'] . '-' . $data['toNoAchors'] . ']';
        }
      }
    //--- ссылки, если не нужны анкоры
    elseif(!empty($data['needLinks']) && $data['needLinks'] == 'on')
      {
      $url .= '&links=[' . $data['fromLinks'] . '-' . $data['toLinks'] . '-' . $data['linksFile'] . ']';
      }
    //--- настройки картинок
    if(!empty($data['needImages']) && $data['needImages'] == 'on')
      {
      $url .= '&images=[' . $data['fromImages'] . '-' . $data['toImages'] . '-' . $data['imagesFile1'] . ']';
      //--- ссылки на картинки
      if(!empty($data['imagesUrl1']))
        {
        $url .= '&images_urls=[' . $data['imagesUrl1'] . ']';
        }
      //--- настроки где будет появляться картинка
      $image_where = '';
      //---
      if(!empty($data['whereUp']) && $data['whereUp'] == 'on') $image_where .= (!empty($image_where) ? '|' : '') . 'up';
      if(!empty($data['whereCenter']) && $data['whereCenter'] == 'on') $image_where .= (!empty($image_where) ? '|' : '') . 'center';
      if(!empty($data['whereDown']) && $data['whereDown'] == 'on') $image_where .= (!empty($image_where) ? '|' : '') . 'down';
      if(!empty($data['whereRandom']) && $data['whereRandom'] == 'on') $image_where .= (!empty($image_where) ? '|' : '') . 'rand';
      //---
      if(empty($image_where)) $image_where = 'rand';
      //---
      $url .= '&images_where=[' . $image_where . ']';
      //--- позиция картинки
      $image_position = '';
      //---
      if(!empty($data['positionLeft']) && $data['positionLeft'] == 'on') $image_position .= (!empty($image_position) ? '|' : '') . 'left';
      if(!empty($data['positionCenter']) && $data['positionCenter'] == 'on') $image_position .= (!empty($image_position) ? '|' : '') . 'center';
      if(!empty($data['positionRight']) && $data['positionRight'] == 'on') $image_position .= (!empty($image_position) ? '|' : '') . 'right';
      if(!empty($data['positionRandom']) && $data['positionRandom'] == 'on') $image_position .= (!empty($image_position) ? '|' : '') . 'rand';
      //---
      if(empty($image_position)) $image_position = 'rand';
      //---
      $url .= '&images_pos=[' . $image_position . ']';
      //---  Картинку вставлять в текст, иначе отдельным тегом
      if(!empty($data['imageInText']) && $data['imageInText'] == 'on') $url .= '&images_intext=1';
      }
    //--- случаных строк
    if(!empty($data['needRandLine']) && $data['needRandLine'] == 'on')
      {
      $url .= '&randlines=[' . $data['fromRandLines'] . '-' . $data['toRandLines'] . '-' . $data['randlinesFile1'] . ']';
      }
    //--- параграфы
    $url .= '&from=' . ($data['fromPharagraph']);
    $url .= '&to=' . ($data['toPharagraph']);
    //--- надо ли вставлять разделить в параграфах тег <p>
    if(!empty($data['noparagraph']) && $data['noparagraph'] == 'on') $url .= '&noparagraph=1';
    //--- настройки
    $url .= '&settings=' . ($data['settings']);
    }
  }

?>
