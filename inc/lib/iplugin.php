<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
/**
 * Class descrition of palugin for redButton
 * Class IPlugin
 */
abstract class IPlugin
  {
  /**
   *
   * Автор
   * Author
   * @var string
   */
  protected $m_author = "Dmitry Tarakanov";
  /**
   *
   * Описание
   * @var string
   */
  protected $m_description = "We are testing plugin system";
  /**
   *
   * Название плагина
   * @var string
   */
  protected $m_name = "Test plugin";
  /**
   *
   * Версия
   * @var string
   */
  protected $m_version = "1.00";

  /**
   * Получени имени автора
   *
   */
  public function GetAuthor()
    {
    return $this->m_author;
    }

  /**
   * Получени имени автора
   *
   */
  public function GetDescription()
    {
    return $this->m_description;
    }

  /**
   * Получение названия
   *
   */
  public function GetName()
    {
    return $this->m_name;
    }

  /**
   * Получение названия
   *
   */
  public function GetVersion()
    {
    return $this->m_version;
    }

  /**
   *
   * Выполение функции до начала работы с ключевиками
   * @param string $keyword
   * @param string $text
   * @param array $data
   */
  abstract public function OnBeginKeyword(&$keyword, &$text, &$data);

  /**
   *
   * Выполение функции до начала обработки макросов
   * @param string $keyword
   * @param string $text
   * @param array $data
   */
  abstract public function OnBeginMacros(&$keyword, &$text, &$data);

  /**
   *
   * Выполение функции после обработки всех макросов
   * @param string $keyword
   * @param string $text
   * @param array $data
   */
  abstract public function OnEndMacros(&$keyword, &$text, &$data);

  /**
   *
   * Выполение функции после обработки ключевика
   * @param string $keyword
   * @param string $text
   * @param array $data
   */
  abstract public function OnEndKeyword(&$keyword, &$text, &$data);

  /**
   *
   * Выполение функции получения имени страницы
   * @param string $keyword Кейворд
   * @param string $pageName Название урла будущей страницы
   * @param string $pageNum Номер страницы (кейворда)
   */
  public function OnGetPageName(&$keyword,&$pageName, $pageNum)
    {
    }

  /**
   *
   * Выполение функции сохранения имени страницы
   * @param string $keyword Кейворд
   * @param string $pageName Название урла будущей страницы
   */
  public function OnSavePage(&$keyword, &$pageName)
    {
    }

  /**
   *
   * Выполнение функции после того, как сгенерирован весь дорвей
   * @param string $localPath локальный путь, куда сохраняется дорвей
   * @param array $params все параметры, с которыми генерируется дорвей
   */
  public function OnEndGenerate($localPath, $params)
    {
    }

  /**
   *
   * Выполнение функции перед началом генерации дорвея
   * @param string $localPath локальный путь, куда сохраняется дорвей
   * @param array $params все параметры, с которыми генерируется дорвей
   */
  public function OnBeginGenerate($localPath, $params)
    {
    }
  /**
   * Выполнение функции перед встакой тегов форматирования в текст
   * @param array $textArray предложения с текстом
   */
  public function OnBeforTextAddTags(&$textArray)
    {
    }
  /**
   * Выполнение функции после того как текст отформатирован
   * @param string $text который будет вставляться
   */
  public function OnEndTextFormated(&$text)
    {
    }
  /**
   * Выполнение функции после того как текст отформатирован
   * @param string $keyword Кейворд
   * @param string $ulr Название урла будущей страницы
   */


    public function OnTranslitUrl(&$keyword,&$url){}

  }

?>