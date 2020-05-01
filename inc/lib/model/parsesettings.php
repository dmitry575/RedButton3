<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 28.10.12
 * Time: 0:13
 */
/**
 *
 * Класс для работы с настройками
 * @author User
 *
 */
class CModel_ParseSettings
{
    //--- массив с настройками
    private $settingsArray = array();
    /**
     *
     * Папка где храняться настройки
     * @var string
     */
    const SETTINGS_PATH = 'data/parsers/settings/';

    /**
     * Инициализация
     */
    public function __construct()
    {
    }

    /**
     *
     * Установка массива настроек
     * @param array $settingsArray
     */
    public function SetSettingsArray($settingsArray)
    {
        $this->settingsArray = $settingsArray;
    }

    /**
     * Получение настроек
     * @param <string> $param Название параметра
     */
    public function Get($param, $default = NULL)
    {
        if (empty($param) || empty($this->settingsArray)) return $default;
        //--- TODO: логгировать, если нет данных в settingsArray
        if (!is_array($this->settingsArray)) $this->settingsArray = array();
        //--- возвращаем значение ключа, если оно есть в массиве
        return array_key_exists($param, $this->settingsArray)
            ? $this->settingsArray[$param]
            : $default;
    }

    /**
     * Сохранение настроек из POST-запроса
     * @param <string> $name
     * @return <bool>
     */
    public function Save($settings, $name = 'default')
    {
        $name = trim(strtolower($name));
        if (empty($name)) return false;
        //--- проверка папки
        if (!file_exists(self::SETTINGS_PATH)) mkdir(self::SETTINGS_PATH, 0777, true);
        //--- сохраняем сериализованные настройки
        $fname = CModel_helper::generate_file_name(CModel_tools::Translit($name));
        //---
        file_put_contents(self::SETTINGS_PATH . $fname . ".data.php", serialize($settings));
        CLogger::write(CLoggerType::DEBUG, 'settings save to file ' . self::SETTINGS_PATH . $fname . ".data.php, name: " . $name);
        //---
        return $fname;
    }

    /**
     * Загрузка настроек
     * @param string $name
     * @return array|mixed <array> Массив с настройками
     */
    public function Load($name = '')
    {
        if (empty($name)) $name = 'default';
        //---
        if (file_exists(self::SETTINGS_PATH . $name . ".data.php")) {
            $c = file_get_contents(self::SETTINGS_PATH . $name . ".data.php");
            $this->settingsArray = unserialize($c);
        }
    }
}

?>