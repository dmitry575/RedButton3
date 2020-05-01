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
    $m_synonimazer_langs = 0;
    if (!empty($_REQUEST['synonimizerRu']) && $_REQUEST['synonimizerRu']) $m_synonimazer_langs |= CModel_synonimazer::SYNC_RU;
    if (!empty($_REQUEST['synonimizerEn']) && $_REQUEST['synonimizerEn']) $m_synonimazer_langs |= CModel_synonimazer::SYNC_EN;
    //--- проверим фай на наличие
    $filename = '';
    if (isset($_REQUEST['textFromList'])) $filename = 'data/texts/' . $_REQUEST['textFromList'];
    $text = !empty($_REQUEST['text']) ? $_REQUEST['text'] : (file_exists($filename) ? file_get_contents($filename) : '');
    //---
    if (empty($text)) {
        echo "No text";
        exit;
    }
    //---
    if ($m_synonimazer_langs > 0) {
        $synonimazer = new CModel_synonimazer($m_synonimazer_langs);
        $text = $synonimazer->Sync($text, isset($_REQUEST['synonimizerMin']) ? (int)$_REQUEST['synonimizerMin'] : 70, isset($_REQUEST['synonimizerMaх']) ? (int)$_REQUEST['synonimizerMaх'] : 100);
    }
    //---
    $text_new = str_replace(array("\r\n", "\r", "\n"), " ", $text);
    $text_new = str_replace("!", "!. ", $text_new);
    $text_new = str_replace("?", "?. ", $text_new);
    $text_new = str_replace(": ", ". ", $text_new);
    $text_new = str_replace("; ", ". ", $text_new);
    $textArray = explode('. ', $text_new);
    //---
    if (isset($_REQUEST['rewriteShake']) || isset($_REQUEST['rewriteChangeStruct'])) {
        //---
        $rewrite = new CModel_Rewriter($_REQUEST['shakeFrom'], $_REQUEST['shakeTo'], $_REQUEST['changestructurFrom'], $_REQUEST['changestructurTo']);
        $rewrite->Rewrite($textArray);
    }
    //--- алгоритм маркова
    if ($_REQUEST['algorithm'] == 'markov') {
        //--- нужно сохранить данные в темповую папку
        if (empty($filename)) {
            $filename = 'data/tmp/' . md5(microtime() + rand(0, 999));
            file_put_contents($filename, $text);
        }
        $text_generator_markov = new CModel_TextMarkov($filename);
        $result_text = '';
        //--- достаем тексты по алгоритму маркова
        for ($i = 0; $i < count($textArray); $i++) {
            $result_text .= $text_generator_markov->GetSentence() . ' ';
        }
        //--- отображаем результат и выходим
        echo $result_text;
        exit;
    }
    //--- карлмаркс
    if ($_REQUEST['algorithm'] == 'karlmarks') {
        $text_generator_karl = new CModel_TextKarlMarsk();
        //--- настройки
        $settings = array
        (
            //--- минимальное и максимальное количество кейвордов в тексте
            'numkeys' => rand(1, 2),
            //--- минимальное и максимальное число параграфов в тексте
            'numpar' => count($textArray),
            //--- максимальное кол-во слов в предложении (случайное число в диапазоне указанных чисел)
            'numwords' => array(35, 40)
        );
        //--- получаем текст по карлу марксу
        $result_text = $text_generator_karl->GetText($textArray, '', $settings);
        //--- отображаем результат и выходим
        echo implode('. ', $result_text);
        exit;
    }
    //--- простой алгоритм, ничего не делаем
    echo $text;
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
    <title>red.Button &mdash; get text</title>
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
        <h2>
            <?=CHome::GetTranslate('b_setting_text')?>
        </h2>

        <div class="box">
            <i><?=CHome::GetTranslate('b_text')?> <?=CHome::GetTranslate('from_list')?>
            </i> <select name="textFromList" id="textFromList"
                         style="width: 242px;">
            <?=CModel_helper::ListFiles('data/texts', '')?>
        </select>

        </div>
        <div class="box">
            <i><?=CHome::GetTranslate('b_method_generate_text')?> </i> <select
                name="algorithm" style="width: 200px;"
                title="<?=CHome::GetTranslate('b_algoritm_generate_text')?>">
            <option value="markov">
                <?=CHome::GetTranslate('b_markov')?>
            </option>
            <option value="karlmarks">
                <?=CHome::GetTranslate('b_karlmarks')?>
            </option>
            <option value="simple">
                <?=CHome::GetTranslate('b_simple')?>
            </option>
        </select>
        </div>
    </div>
    <div class="frame">
        <h2>
            <?=CHome::GetTranslate('b_synonimizer_settings')?>
        </h2>

        <div class="box">
            <input type="checkbox" name="synonimizerRu"
                   checked='checked' id="synonimizerRu"> <label
                for="synonimizerRu"><?=CHome::GetTranslate('b_synonimizer_ru')?>
        </label>
        </div>
        <div class="box">
            <input type="checkbox" name="synonimizerEn"
                   checked='checked' id="synonimizerEn"> <label
                for="synonimizerEn"><?=CHome::GetTranslate('b_synonimizer_en')?>
        </label>
        </div>
        <div class="box">
            <i><?=CHome::GetTranslate('b_synonimizer_percent')?> </i>
            <?=CHome::GetTranslate('from')?>
            <input type="text" name="synonimizerMin" value="70" size="2">
            <?=CHome::GetTranslate('to')?>
            <input type="text" name="synonimizerMax" value="100"
                   size="2"> %
        </div>
    </div>
    <div class="frame">
        <h2>
            <?=CHome::GetTranslate('b_rewrite_text')?>
        </h2>

        <div class="box">
            <input type="checkbox" name="rewriteShake" id="rewriteShake">
            <?=CHome::GetTranslate('b_rewrite_shake')?>
            <?=CHome::GetTranslate('from')?>
            <input type="text" name="shakeFrom" value="80" size="2">
            <?=CHome::GetTranslate('to')?>
            <input type="text" name="shakeTo" value="100" size="2"> %
        </div>
        <div class="box">
            <input type="checkbox" name="rewriteChangeStruct"
                   id="rewriteChangeStruct">
            <?=CHome::GetTranslate('b_rewrite_changestruct')?>
            <?=CHome::GetTranslate('from')?>
            <input type="text" name="changestructurFrom" value="80"
                   size="2">
            <?=CHome::GetTranslate('to')?>
            <input type="text" name="changestructurTo" value="100"
                   size="2"> %
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
