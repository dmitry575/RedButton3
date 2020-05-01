<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+
/**
 * Генерация проектов для хрумера
 *
 */
class CModel_xrumer
{
    /**
     * Путь к данных для проекта хруммера
     *
     */
    const PATH = "data/xrumer/";
    const PATH_URL = "urls/";
    const PATH_PROJECTS = "data/xrumer/projects/";
    const PATH_EMAILS = "emails/";
    /**
     * Список стран и городов
     *
     */
    const FILE_CITIES = 'cities.txt';
    /**
     * Список стран и городов
     *
     */
    const FILE_INTEREST = 'interest.txt';
    /**
     * Список что делаем
     *
     */
    const FILE_OCCUPATION = 'occupation.txt';
    /**
     * файл с настройками
     *
     */
    const FILE_SETTINGS = 'settings.txt';
    /**
     * Префикс для логов
     *
     */
    const LOG_PREFIX = 'xrumer:';
    /**
     * Длина ника
     */
    const NICK_LEN = 9;
    /**
     * Настройки для работы с проектами хрумера
     * @var array
     */
    private $m_set_xrumer;
    /**
     * Общие параметры для всего дорвея
     * @var array
     */
    private $m_params;
    /**
     * страны и города
     * @var array
     */
    private $m_countries = null;
    /**
     * разделы
     * @var array
     */
    private $m_occupations = null;
    /**
     * интересы
     * @var array
     */
    private $m_interests = null;
    /**
     * Модуль для управления текстами
     * @var CModel_text
     */
    private $m_model_text = null;

    /**
     * Конструктор
     *
     */
    public function __construct()
    {
    }

    /**
     * Сохранение настроек
     *
     */
    public function SaveSettings($data)
    {
        if (!is_array($data)) {
            CLogger::write(CLoggerType::ERROR, self::LOG_PREFIX . ' data for settings invalid ' . var_export($data, true));
            return false;
        }
        //---
        $old = $this->ReadSettings();
        //---
        foreach ($data as $key => $value) {
            $old[$key] = $value;
        }
        //--- данные сохраним в файл
        return $this->SaveToFile($old);
    }

    /**
     * Сохраним настройки в файл
     *
     * @param array $data
     * @return bool
     */
    private function SaveToFile($data)
    {
        //--- имя файла с настройками
        $filename = self::PATH . self::FILE_SETTINGS;
        //--- открываем файл
        $fp = fopen($filename, 'w');
        if (!$fp) {
            CLogger::write(CLoggerType::ERROR, self::LOG_PREFIX . ' can not open file settings: ' . $filename);
            return false;
        }
        $params = XrumerSettings::ListParams();
        //--- запись данных  в файл
        foreach ($data as $key => $value) {
            //--- проверим нужно ли сохранять параметр
            if (in_array($key, $params)) fwrite($fp, $key . '=' . $value . "\r\n");
        }
        fclose($fp);
        return true;
    }

    /**
     * Считываем настройки
     *
     */
    public function ReadSettings()
    {
        $filename = self::PATH . self::FILE_SETTINGS;
        if (!file_exists($filename)) return array();
        //--- открываем файл
        $fp = fopen($filename, 'r');
        if (!$fp) {
            CLogger::write(CLoggerType::ERROR, self::LOG_PREFIX . ' can not open file settings: ' . $filename);
            return array();
        }
        //---
        $result = array();
        //--- читаем построчно и сохраняем данные
        while (!feof($fp)) {
            $line = fgets($fp);
            if (empty($line) || $line[0] == ';') continue;
            //---
            $v = explode('=', $line, 2);
            //--- проверка
            if (count($v) <= 1) continue;
            //---
            $result[trim($v[0])] = trim($v[1]);
        }
        fclose($fp);
        //--- все настройки в одном файле
        return $result;
    }

    /**
     * Инцилизация для работы с данными
     */
    public function Init(&$params, &$model_text)
    {
        $this->m_params = $params;
        $this->m_model_text = $model_text;
        //--- загрузка параметров
        $this->m_set_xrumer = $this->ReadSettings();
    }

    /**
     * Создание проекта для хрумера
     */
    public function CreateProject(&$urls)
    {
        CLogger::write(CLoggerType::DEBUG, self::LOG_PREFIX . ' begin create xrumer project');
        //--- загрузка шаблона
        $project_name = $this->GetProjectName();
        $name = $this->GetNicks();
        //--- загрузка шаблона
        $template = $this->LoadTemplate();
        if (empty($template)) {
            CLogger::write(CLoggerType::ERROR, self::LOG_PREFIX . ' template is empty');
            return;
        }
        //--- страна и город
        $country = '';
        $city = '';
        //---
        $this->GetRandCountry($country, $city);
        //---
        $email = '';
        $email_password = '';
        //---
        $this->GetEmailPassword($email, $email_password);
        //---
        $xml = str_replace('[PROJECT]', $project_name, $template);
        $xml = str_replace('[NICK]', $name, $xml);
        $xml = str_replace('[REALNAME]', $name, $xml);
        $xml = str_replace('[PASS]', CModel_tools::GetRandomHex(self::NICK_LEN), $xml);
        $xml = str_replace('[EMAIL]', $email, $xml);
        $xml = str_replace('[EMAIL_LOGIN]', $email, $xml);
        $xml = str_replace('[EMAIL_PASS]', $email_password, $xml);
        $xml = str_replace('[EMAIL_POP]', !empty($this->m_set_xrumer[XrumerSettings::EMAIL_POP]) ? $this->m_set_xrumer[XrumerSettings::EMAIL_POP] : "", $xml);
        $xml = str_replace('[URL]', $this->m_params['nextUrl'], $xml);
        $xml = str_replace('[CITY]', $city, $xml);
        $xml = str_replace('[COUNTRY]', $country, $xml);
        $xml = str_replace('[OCCUP]', $this->GetRandOccupation(), $xml);
        $xml = str_replace('[INTERES]', $this->GetRandInteres(), $xml);
        $xml = str_replace('[RAND_TEXT]', '#file_links[' . self::PATH_URL . $project_name . '_text.txt,1,N]', $xml);
        $xml = str_replace('[RAND_URL]', '#file_links[' . self::PATH_URL . $project_name . '_links.txt,1,N]', $xml);
        //--- создаем файл c проектом
        $this->CreateFileProject($project_name, $xml);
        //--- создаем файл ссылками
        $this->CreateFileLinks($project_name, $urls);
        //--- создаем файл со случайными предложениями
        $this->CreateFileText($project_name);
        //--- добавляем задание в шедуллер
        $this->AddSchedule($project_name);
        CLogger::write(CLoggerType::DEBUG, self::LOG_PREFIX . ' xrumer project created');
    }

    /**
     * Добавление проекта к шедуллеру
     * @param string $project_name
     */
    private function AddSchedule($project_name)
    {
        $num = 0;
        $filename = self::PATH_PROJECTS . 'schedule.xml';
        if (!file_exists($filename)) {
            $text = '<?xml version="1.0" encoding="UTF-8"?>
<body>';
        } else {
            $text = file_get_contents($filename);
            //--- нужно отрезать </body>
            $pos = strpos($text, '</body>');
            if ($pos > 0) $text = substr($text, 0, $pos);
            //--- узнаем последний номер задачи
            $pos = strrpos($text, '');
            if ($pos > 0) {
                $num = (int)substr($text, $pos, 7);
            }
        }
        //---
        $num++;
        $text .= "<Schedule" . $num . ">
	<PerformedTime></PerformedTime>
	<EventNum>6</EventNum>
	<EventParameter></EventParameter>
	<JobNum>1</JobNum>
	<JobParameter></JobParameter>
</Schedule" . $num . ">";
        $num++;
        $text .= "<Schedule" . $num . ">
	<PerformedTime></PerformedTime>
	<EventNum>0</EventNum>
	<EventParameter></EventParameter>
	<JobNum>4</JobNum>
	<JobParameter>" . $project_name . "</JobParameter>
</Schedule" . $num . ">";
        //---
        file_put_contents($filename, $text);
    }

    /**
     * Получаем емайл и пароль к емайлу из файла.
     * Из файла удаляем строку и добавляем к файлу used
     * @param string $email
     * @param string $email_password
     */
    private function GetEmailPassword(&$email, &$email_password)
    {
        if (empty($this->m_set_xrumer[XrumerSettings::EMAILS_FILE])) {
            CLogger::write(CLoggerType::ERROR, self::LOG_PREFIX . ' params not exists ' . XrumerSettings::EMAILS_FILE);
            return;
        }
        //---
        $filename = self::PATH . self::PATH_EMAILS . $this->m_set_xrumer[XrumerSettings::EMAILS_FILE];
        //---
        if (!file_exists($filename)) {
            CLogger::write(CLoggerType::ERROR, self::LOG_PREFIX . ' file email not exists ' . $filename);
            return;
        }
        $text = file_get_contents($filename);
        $pos = strpos($text, "\n");
        if ($pos === FALSE) {
            $line = $text;
            file_put_contents($filename, '');
        } else {
            $line = trim(substr($text, 0, $pos + 1));
            file_put_contents($filename, substr($text, $pos + 1));
        }
        //---
        $l = explode('|', $line);
        $email = $l[0];
        if (isset($l[1])) $email_password = $l[1];
        //---
        unset($text);
    }

    /**
     * Создание файла со ссылками на дорвей
     * @param string $project_name
     */
    private function CreateFileText($project_name)
    {
        $path = self::PATH_PROJECTS . self::PATH_URL;
        //--- проверка пути
        if (!file_exists($path)) { //--- создаем рекурсивно
            if (!mkdir($path, 0777, true)) {
                CLogger::write(CLoggerType::ERROR, self::LOG_PREFIX . ' can not create path ' . $path);
                return;
            }
            CLogger::write(CLoggerType::DEBUG, self::LOG_PREFIX . ' created path ' . $path);
        }
        //---
        $filename = $path . $project_name . '_text.txt';
        //---
        $texts = $this->m_model_text->GetTextArraySententes(array(1 => 20, 2 => 100));
        //--- сохраняем текст
        file_put_contents($filename, join("\r\n", $texts));
        chmod($filename, 0777);
        //---
        CLogger::write(CLoggerType::DEBUG, self::LOG_PREFIX . ' create links file ' . $filename);
    }

    /**
     * Создание файла со ссылками на дорвей
     * @param string $project_name
     * @param array $urls
     */
    private function CreateFileLinks($project_name, &$urls)
    {
        if (empty($urls)) return;
        //---
        $path = self::PATH_PROJECTS . self::PATH_URL;
        //--- проверка пути
        if (!file_exists($path)) { //--- создаем рекурсивно
            if (!mkdir($path, 0777, true)) {
                CLogger::write(CLoggerType::ERROR, self::LOG_PREFIX . ' can not create path ' . $path);
                return;
            }
            CLogger::write(CLoggerType::DEBUG, self::LOG_PREFIX . ' created path ' . $path);
        }
        //---
        $filename = $path . '/' . $project_name . '_links.txt';
        $handel_urls = fopen($filename, 'w+');
        //---
        if (!$handel_urls) {
            CLogger::write(CLoggerType::ERROR, self::LOG_PREFIX . ' can not create file ' . $filename);
            return;
        }
        //---
        reset($urls);
        $urls_text = '';
        $i = 0;
        foreach ($urls as $url => $key) {
            $urls_text .= "[url=" . $url . "]" . $key . "[/url]\r\n";
            if (($i % 100) == 0) {
                fwrite($handel_urls, $urls_text);
                $urls_text = '';
            }
            $i++;
        }
        //--- остаток запишем
        if (!empty($urls_text)) {
            fwrite($handel_urls, $urls_text);
        }
        //---
        fclose($handel_urls);
        chmod($filename, 0777);
        //---
        CLogger::write(CLoggerType::DEBUG, self::LOG_PREFIX . ' create links file ' . $filename . ', links ' . $i);
    }

    /**
     * Создаем файл с проектом
     * @param string $project_name
     * @param string $xml
     */
    private function CreateFileProject($project_name, $xml)
    {
        $path = self::PATH_PROJECTS;
        //--- проверка пути
        if (!file_exists($path)) { //--- создаем рекурсивно
            if (!mkdir($path, 0777, true)) {
                CLogger::write(CLoggerType::ERROR, self::LOG_PREFIX . ' can not create path ' . $path);
                return;
            }
            CLogger::write(CLoggerType::DEBUG, self::LOG_PREFIX . ' created path ' . $path);
        }
        //--- создаем файл
        $filename = $path = self::PATH_PROJECTS . $project_name . '.xml';
        file_put_contents($filename, $xml);
        chmod($filename, 0777);
    }

    /**
     * Загрузка шаблона
     */
    private function LoadTemplate()
    {
        $filename = self::PATH . 'templates/default.xml';
        if (!file_exists($filename)) {
            CLogger::write(CLoggerType::ERROR, self::LOG_PREFIX . ' template file not exists: ' . $filename);
            return '';
        }
        //---
        return file_get_contents($filename);
    }

    /**
     * Получение страны и города
     * @param $country
     * @param $city
     * @return bool
     */
    private function GetRandCountry(&$country, &$city)
    {
        if (empty($this->m_countries)) $this->LoadCitiesCountries();
        //--- проверка
        if (!empty($this->m_countries)) {
            //--- случайный город из списка
            $country = array_rand($this->m_countries);
            $city = $this->m_countries[$country][rand(0, count($this->m_countries[$country]) - 1)];
            return true;
        }
        return false;
    }

    /**
     * Загр
     * @return array
     */
    private function LoadCitiesCountries()
    {
        //---
        $filename = self::PATH . 'cities.txt';
        if (!file_exists($filename)) {
            CLogger::write(CLoggerType::ERROR, self::LOG_PREFIX . ' cities file not exists: ' . $filename);
            return false;
        }
        //---
        $i = 0;
        $this->m_countries = array();
        //--- города и страны храняться в формате страна|город1, город2
        $cities = explode("\n", file_get_contents($filename));
        foreach ($cities as $city) {
            $c = explode("|", $city);
            if (count($c) < 2) continue;
            //---
            $many_city = explode(",", $c[1]);
            array_walk($many_city, 'CModel_tools::Trim');
            $this->m_countries[trim($c[0])] = $many_city;
            $i++;
        }
        //---
        CLogger::write(CLoggerType::ERROR, self::LOG_PREFIX . ' loaded ' . $i . ' contries');
        return true;
    }

    /**
     * Случайная категория
     * @return string
     */
    private function GetRandOccupation()
    {
        //--- проверка
        if (empty($this->m_occupations)) {
            //---
            $filename = self::PATH . 'occupation.txt';
            if (!file_exists($filename)) {
                CLogger::write(CLoggerType::ERROR, self::LOG_PREFIX . ' occupation.txt file not exists: ' . $filename);
                return null;
            }
            //---
            $this->m_occupations = explode("\n", file_get_contents($filename));
            array_walk($this->m_occupations, 'CModel_tools::Trim');
        }
        //--- случайный город из списка
        return $this->m_occupations[rand(0, count($this->m_occupations) - 1)];
    }

    /**
     * Случайный интерес
     * @return string
     */
    private function GetRandInteres()
    {
        //--- проверка
        if (empty($this->m_occupations)) {
            //---
            $filename = self::PATH . 'interest.txt';
            if (!file_exists($filename)) {
                CLogger::write(CLoggerType::ERROR, self::LOG_PREFIX . ' interest.txt file not exists: ' . $filename);
                return null;
            }
            //---
            $this->m_interests = explode("\n", file_get_contents($filename));
            array_walk($this->m_interests, 'CModel_tools::Trim');
        }
        //--- случайный город из списка
        return $this->m_interests[rand(0, count($this->m_interests) - 1)];
    }

    /**
     * Формирование ников
     * @return string
     */
    private function GetNicks()
    {
        return '#gennick[' . CModel_tools::GetRandomHex(self::NICK_LEN) . ']';
    }

    /**
     * Получим название проекта для хрумера
     * @return string
     */
    private function GetProjectName()
    {
        //---
        if (!CModel_helper::IsExistHttp($this->m_params['nextUrl'])) {
            $n = $this->m_params['nextUrl'];
        } else {
            //--- уберем в начале http://
            $n = CModel_helper::DeleteHttp($this->m_params['nextUrl']);
        }
        $n = trim($n, '/');
        $name = CModel_helper::GenerateFileName($n);
        if (empty($name)) $name = 'default';
        //---
        return $name;
    }
}

/**
 * возможные строчки в настройках файла настроек
 *
 */
class XrumerSettings
{
    const PROJECT_FORUM = 'project_forum';
    const PROJECT_PROFILE = 'project_profile';
    const PROJECT_TOPICS = 'project_topics';
    const EMAIL_SERVER = 'email_server';
    const POP_SERVER = 'pop_server';
    const REDBUTTON_URL = 'redbutton_url';
    const EMAILS_FILE = 'emails_file';
    const LINKER_COUNT_SIGNATURE = 'linker_count_signature';
    const LINKER_COUNT_TEXT = 'linker_count_text';
    const LINKER_COUNT_LINKS = 'linker_count_links';
    const LINKER_NUMBER_TRAST = 'linker_number_trast';
    const LINKER_NUMBER_NORMAL = 'linker_number_normal';
    const LINKER_NUMBER_TOPICS = 'linker_number_topics';
    const EMAIL = "email";
    const EMAIL_PASSWORD = "email_password";
    const EMAIL_LOGIN = "email_login";
    const EMAIL_POP = "pop_server";
    const PATH = "path";

    /**
     *
     * список всех возможных параметров
     */
    public static function ListParams()
    {
        return array(
            'project_forum',
            'project_profile',
            'project_topics',
            'email_server',
            'pop_server',
            'redbutton_url',
            'emails_file',
            'linker_count_signature',
            'linker_count_text',
            'linker_count_links',
            'linker_number_trast',
            'linker_number_normal',
            'linker_number_topics',
        );
    }
}

?>
