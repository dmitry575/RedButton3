<?php
include_once('inc/lib/controller.php');
include_once('inc/lib/icontroller.php');
include_once('inc/lib/ipage.php');
include_once('inc/lib/logs.php');
include_once('inc/config.php');
//--- получение языка
if (isset($_REQUEST['lang'])) $LNG = $_REQUEST['lang'] == 'ru' ? 'ru' : 'en';
if (isset($_REQUEST['start'])) {
    //--- Время работы скрипта неограниченно
    set_time_limit(0);
    if (isset($_REQUEST['language_from'])) {
        $lang_from = $_REQUEST['language_from'];
    }
    //---

    if (isset($_REQUEST['language_to'])) {
        $lang_to = $_REQUEST['language_to'];
    }
    //---
    $filename = '';
    if (isset($_REQUEST['filename'])) {
        $filename = 'data/texts/' . $_REQUEST['filename'];
    }
    $translate_system = 3;
    //---
    if (isset($_REQUEST['translate_yandex']) || isset($_REQUEST['translate_gogole'])) {

        if (isset($_REQUEST['translate_gogole'])) $translate_system |= CModel_translate::TRANSLATE_GOOGLE;
        if (isset($_REQUEST['translate_yandex'])) $translate_system |= CModel_translate::TRANSLATE_YANDEX;
    }
    $translate = new CModel_translate();
    //--- Работа с большими текстами

    $text = !empty($_REQUEST['text']) ? $_REQUEST['text'] : (file_exists($filename) ? file_get_contents($filename) : '');
    $bigText = CModel_tools::RemoveBom($text);
    //--- перевод
    $text = $translate->Translate($bigText, $lang_from, $lang_to, $translate_system);
    //---
    echo str_replace('\\n', "\n", $text);
    exit;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="robots" content="noindex">
    <link rel="shortcut icon" href="/favicon.ico">
    <link href="styles/styles.css" type="text/css" rel="stylesheet">
    <title>red.Button &mdash; Translate</title>
</head>

<body>
<script type="text/javascript" src="js/main.js"></script>
<script type="text/javascript" src="js/ajax.js"></script>
<script type="text/javascript" src="js/task.js"></script>

<div class="logout">
    <?php CModel_lng::GetMainMenu(); ?>
    <a href="?logout=1" style="margin-left: 5px;"><?=$TRANSLATE[$LNG]['exit']?>
    </a>
</div>


<form action="" method="post" id="mainForm"
      enctype="multipart/form-data" target="_blank" name="send_generate">
    <h2>
        <?=CHome::GetTranslate('b_text')?>
    </h2>
    <textarea name="text" style="width: 100%; height: 450px;"></textarea>

    <div class="frame">
        <div class="box">
            <?=CTranslate::GetTranslate('b_language_from')?>
            <select name="language_from" id="language_from"
                    style="width: 150px;">
                <?php
                foreach (CTranslate::GetLanguages() as $lang) {
                    ?>
                    <option value="<?=$lang?>">
                        <?=$lang?>
                    </option>
                    <?php
                }
                ?>
            </select>

            <?=CTranslate::GetTranslate('b_language_to')?>

            <select name="language_to" id="language_to"
                    style="width: 150px;">
                <?php
                foreach (CTranslate::GetLanguages() as $lang) {
                    ?>
                    <option value="<?=$lang?>">
                        <?=$lang?>
                    </option>
                    <?php
                }
                ?>
            </select>
        </div>
        <div class="box">
            <i><?=CTranslate::GetTranslate('b_filename')?> </i> <select
                name="filename" id="filename" style="width: 200px;">
            <?=CModel_helper::ListFiles('data/texts', '')?>
        </select>
        </div>
        <div class="box">
            <input type="checkbox" name="translate_gogole"
                   checked='checked' id="translate_gogole"
                   value="<?=CModel_translate::TRANSLATE_GOOGLE?>"> <label
                for="translate_gogole"><?=CTranslate::GetTranslate('b_translate_google')?>
        </label>
        </div>
        <div class="box">
            <input type="checkbox" name="translate_yandex"
                   checked='checked' id="translate_yandex"
                   value="<?=CModel_translate::TRANSLATE_YANDEX?>"> <label
                for="translate_yandex"><?=CTranslate::GetTranslate('b_translate_yandex')?>
        </label>
        </div>
    </div>
    <div id="startBottom">
        <input type="submit"
               value="<?=CHome::GetTranslate('b_create')?>"
               class="button create" name="start">

        <div class="right">
            <a href="?module=translate" style="margin-right: 15px;"><span><?=CHome::GetTranslate('b_translate')?>
            </span> </a> <a href="?module=tasks"><span><?=CHome::GetTranslate('b_tasks')?>
            </span> </a>
        </div>

    </div>

</form>

<div
        style="border-top: 1px solid #DFDCD1; padding: 15px 0 0 0; margin: 10px 0 0 0; color: #8F8D86;">
    &hearts; red.Button <a
        href="http://support.getredbutton.com/en/download">Download last
    version</a>
</div>
</body>
</html>
