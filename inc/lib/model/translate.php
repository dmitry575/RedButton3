<?php
class CModel_translate
  {
  const TRANSLATE_YANDEX = 1;
  const TRANSLATE_GOOGLE = 2;

  /**
   * Перевод текста
   * @param string $text             текст который нужно перевести
   * @param string $lang_from        перевести с языка
   * @param string $lang_to          перевести на язык
   * @param int    $translate_system с помощью какого сайта
   */
  public function Translate($text, $lang_from, $lang_to, $translate_system)
    {
    //--- с помощью чего будем переводить
    $translator = null;
    if(($translate_system & self::TRANSLATE_YANDEX) > 0) $translator = new CModel_TranslateYandex();
    else                                                 $translator = new Cmodel_TranslateGoogle();
    //---
    $textArray = CModel_TranslateBigText::toBigPieces($text);
    //---
    $numberOfTextItems = count($textArray);
    CLogger::write(CLoggerType::DEBUG, "translate: text size: " . strlen($text) . ', count pieces: ' . $numberOfTextItems);
    //---
    foreach($textArray as $key => $textItem)
      {
      //--- отправим запрос
      $translatedItem = $translator->Translate($lang_from, $lang_to, $textItem);
      //---
      $translatedArray[$key] = $translatedItem;
      CLogger::write(CLoggerType::DEBUG, "translate: key: " . $key . ', ' . strlen($textItem) . ' bytes to ' . strlen($translatedItem) . ' bytes');
      //---
      }
    //--- соберем кусочки переводов
    $translatedBigText = CModel_TranslateBigText::fromBigPieces($translatedArray);
    //---
    return $translatedBigText;
    }
  }