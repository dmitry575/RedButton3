<?php
class CModel_tools
  {
  private static $m_translit_alpha = array('А' => 'A',
                                           'Б' => 'B',
                                           'В' => 'V',
                                           'Г' => 'G',
                                           'Д' => 'D',
                                           'Е' => 'E',
                                           'Ж' => 'ZH',
                                           'З' => 'Z',
                                           'И' => 'I',
                                           'Й' => 'Y',
                                           'К' => 'K',
                                           'Л' => 'L',
                                           'М' => 'M',
                                           'Н' => 'N',
                                           'О' => 'O',
                                           'П' => 'P',
                                           'Р' => 'R',
                                           'С' => 'S',
                                           'Т' => 'T',
                                           'У' => 'U',
                                           'Ф' => 'F',
                                           'Х' => 'H',
                                           'Ц' => 'C',
                                           'Ч' => 'CH',
                                           'Ш' => 'SH',
                                           'Щ' => 'SCH',
                                           'Ъ' => '',
                                           'Ы' => 'Y',
                                           'Ь' => '',
                                           'Э' => 'E',
                                           'Ю' => 'Y',
                                           'Я' => 'YA',
                                           'а' => 'a',
                                           'б' => 'b',
                                           'в' => 'v',
                                           'г' => 'g',
                                           'д' => 'd',
                                           'е' => 'e',
                                           'ж' => 'zh',
                                           'з' => 'z',
                                           'и' => 'i',
                                           'й' => 'y',
                                           'к' => 'k',
                                           'л' => 'l',
                                           'м' => 'm',
                                           'н' => 'n',
                                           'о' => 'o',
                                           'п' => 'p',
                                           'р' => 'r',
                                           'с' => 's',
                                           'т' => 't',
                                           'у' => 'u',
                                           'ф' => 'f',
                                           'х' => 'h',
                                           'ц' => 'c',
                                           'ч' => 'ch',
                                           'ш' => 'sh',
                                           'щ' => 'sch',
                                           'ъ' => '',
                                           'ы' => 'y',
                                           'ь' => '',
                                           'э' => 'e',
                                           'ю' => 'u',
                                           'я' => 'ya',
                                           ' ' => '-');

  /**
   * Транслитерация текста
   */
  public static function Translit($cyr_str, $is_tolower = true)
    {
    if($is_tolower) $cyr_str = mb_strtolower($cyr_str);
    //---
    $text = strtr($cyr_str, self::$m_translit_alpha);
    $text = preg_replace("/[^a-z0-9\-]*/i", '', $text);
    $text = str_replace('--', '-', $text);
    $text = str_replace('--', '-', $text);
    return trim($text, '- ');
    }

  /**
   * Генерация ника
   */
  public static function GetNick()
    {
    $lastnames  = array('abbott',
                        'adamczyk',
                        'adams',
                        'adamski',
                        'aguilar',
                        'aitken',
                        'allen',
                        'alvarez',
                        'anderson',
                        'andre',
                        'arthur',
                        'bailey',
                        'baker',
                        'barker',
                        'barnes',
                        'bauer',
                        'becker',
                        'bell',
                        'bennett',
                        'bernard',
                        'bertrand',
                        'bianchi',
                        'black',
                        'blackman',
                        'blakely',
                        'blanc',
                        'blumke',
                        'bonnet',
                        'borkowski',
                        'boyle',
                        'braun',
                        'briscoe',
                        'brooks',
                        'brown',
                        'bruce',
                        'bruneau',
                        'bruno',
                        'bryant',
                        'buchanan',
                        'burness',
                        'burns',
                        'bush',
                        'butler',
                        'caliva',
                        'cameron',
                        'campbell',
                        'carlock',
                        'carter',
                        'castillo',
                        'castro',
                        'chandler',
                        'chavarria',
                        'chavez',
                        'chevalier',
                        'chmelyk',
                        'chmielewski',
                        'christie',
                        'clark',
                        'clement',
                        'cleveland',
                        'clinton',
                        'cole',
                        'coleman',
                        'collins',
                        'colombo',
                        'conti',
                        'cook',
                        'coolidge',
                        'cooper',
                        'costa',
                        'cox',
                        'craig',
                        'crawford',
                        'cruz',
                        'cuneo',
                        'cunningham',
                        'czarnecki',
                        'czerwinski',
                        'dabrowski',
                        'daecher',
                        'david',
                        'davidson',
                        'davis',
                        'deluca',
                        'deckard',
                        'decker',
                        'delgado',
                        'diaz',
                        'dickson',
                        'docherty',
                        'donaldson',
                        'douglas',
                        'dubois',
                        'duda',
                        'dudek',
                        'dumont',
                        'duncan',
                        'dupont',
                        'durand',
                        'hahn',
                        'haley',
                        'hall',
                        'hamilton',
                        'harding',
                        'harris',
                        'harrison',
                        'hartmann',
                        'hay',
                        'hayes',
                        'henderson',
                        'henry',
                        'hernandez',
                        'herrera',
                        'herrmann',
                        'hill',
                        'hodges',
                        'hoffmann',
                        'holroyd',
                        'honey',
                        'hoover',
                        'howard',
                        'huber',
                        'huffer',
                        'hughes',
                        'hunter',
                        'kaczmarek',
                        'kaiser',
                        'kalinowski',
                        'kaminski',
                        'kelly',
                        'kemp',
                        'kennedy',
                        'kerr',
                        'king',
                        'klein',
                        'koch',
                        'koertig',
                        'kohler',
                        'kowalczyk',
                        'kowalski',
                        'kozlowski',
                        'krause',
                        'krueger',
                        'kucharski',
                        'kuhn',
                        'kwiatkowski',
                        'maciejewski',
                        'mackay',
                        'mackenzie',
                        'maclean',
                        'macleod',
                        'madison',
                        'madrid',
                        'majewski',
                        'mancini',
                        'mangini',
                        'marino',
                        'marshall',
                        'martin',
                        'martinez',
                        'masson',
                        'mathieu',
                        'mccorquodale',
                        'mccoy',
                        'mcdonald',
                        'mcgregor',
                        'mcintosh',
                        'mcintyre',
                        'mckinley',
                        'mcmillan',
                        'medina',
                        'mendez',
                        'mendoza',
                        'mercier',
                        'metz',
                        'meyer',
                        'michalski',
                        'michel',
                        'miller',
                        'milne',
                        'mitchell',
                        'mojica',
                        'moller',
                        'monroe',
                        'moore',
                        'morales',
                        'moreau',
                        'morel',
                        'moreno',
                        'moretti',
                        'morgan',
                        'morin',
                        'morris',
                        'morrison',
                        'muir',
                        'muller',
                        'munoz',
                        'munro',
                        'murphy',
                        'murray',
                        'myers',
                        'nelson',
                        'neumann',
                        'nicholas',
                        'nixon',
                        'nowak',
                        'nowakowski',
                        'nowicki',
                        'pagano',
                        'parker',
                        'patterson',
                        'pawlak',
                        'pawlowski',
                        'pena',
                        'penn',
                        'pennell',
                        'perez',
                        'perrin',
                        'perry',
                        'peters',
                        'peterson',
                        'petit',
                        'phillips',
                        'pierce',
                        'pilch',
                        'piotrowski',
                        'polk',
                        'poole',
                        'powell',
                        'price',
                        'ramirez',
                        'ramos',
                        'reagan',
                        'reed',
                        'reilly',
                        'reyes',
                        'reynolds',
                        'ricci',
                        'richard',
                        'richardson',
                        'richter',
                        'rios',
                        'ritchie',
                        'rivera',
                        'rizzo',
                        'robert',
                        'roberts',
                        'robertson',
                        'robin',
                        'robinson',
                        'rodriguez',
                        'roemer',
                        'rogers',
                        'rollins',
                        'romano',
                        'romero',
                        'roosevelt',
                        'ross',
                        'rossi',
                        'rousseau',
                        'roussel',
                        'roux',
                        'ruiz',
                        'runge',
                        'russell',
                        'russo',
                        'rutkowski',
                        'salazar',
                        'sanchez',
                        'sanders',
                        'santiago',
                        'sawicki',
                        'schafer',
                        'schaffer',
                        'schmidt',
                        'schmitt',
                        'schmitz',
                        'schneider',
                        'scholz',
                        'schroeder',
                        'schulz',
                        'schwarz',
                        'scott',
                        'shaw',
                        'shook',
                        'simmons',
                        'simon',
                        'simpson',
                        'sinclair',
                        'smith',
                        'sobczak',
                        'sokolowski',
                        'soto',
                        'staats',
                        'stanger',
                        'stevens',
                        'stewart',
                        'sullivan',
                        'sutherland',
                        'szczepanski',
                        'szymanski',
                        'taft',
                        'taylor',
                        'thomas',
                        'thompson',
                        'tomaszewski',
                        'torres',
                        'truman',
                        'turner',
                        'tyler',
                        'wagner',
                        'walczak',
                        'walker',
                        'wall',
                        'wallace',
                        'walter',
                        'ward',
                        'washington',
                        'watson',
                        'watt',
                        'weber',
                        'weis',
                        'werner',
                        'west',
                        'white',
                        'whitney',
                        'wieczorek',
                        'williams',
                        'wilson',
                        'wisniewski',
                        'wojciechowski',
                        'wolf',
                        'wood',
                        'wozniak',
                        'wright',
                        'wysocki',
                        'zajac',
                        'zakrzewski',
                        'zawadzki',
                        'zelasko',
                        'zielinski',
                        'zimmerman');
    $eng_names  = Array('jacob',
                        'michael',
                        'joshua',
                        'ethan',
                        'matthew',
                        'daniel',
                        'christopher',
                        'andrew',
                        'anthony',
                        'william',
                        'joseph',
                        'alexander',
                        'david',
                        'ryan',
                        'noah',
                        'james',
                        'nicholas',
                        'tyler',
                        'logan',
                        'john',
                        'christian',
                        'jonathan',
                        'nathan',
                        'benjamin',
                        'samuel',
                        'dylan',
                        'brandon',
                        'gabriel',
                        'elijah',
                        'aiden',
                        'angel',
                        'jose',
                        'zachary',
                        'caleb',
                        'jack',
                        'jackson',
                        'kevin',
                        'gavin',
                        'mason',
                        'isaiah',
                        'austin',
                        'evan',
                        'luke',
                        'aidan',
                        'justin',
                        'jordan',
                        'robert',
                        'isaac',
                        'landon',
                        'jayden',
                        'thomas',
                        'cameron',
                        'connor',
                        'hunter',
                        'jason',
                        'diego',
                        'aaron',
                        'owen',
                        'lucas',
                        'charles',
                        'juan',
                        'luis',
                        'adrian',
                        'adam',
                        'julian',
                        'bryan',
                        'alex',
                        'sean',
                        'nathaniel',
                        'carlos',
                        'jeremiah',
                        'brian',
                        'hayden',
                        'jesus',
                        'carter',
                        'sebastian',
                        'eric',
                        'xavier',
                        'brayden',
                        'kyle',
                        'ian',
                        'wyatt',
                        'chase',
                        'cole',
                        'dominic',
                        'tristan',
                        'carson',
                        'jaden',
                        'miquel',
                        'steven',
                        'caden',
                        'kaden',
                        'antonio',
                        'timothy',
                        'henry',
                        'alejandro',
                        'blake',
                        'liam',
                        'richard',
                        'devin',
                        'david',
                        'michael',
                        'john',
                        'james',
                        'robert',
                        'mark',
                        'william',
                        'richard',
                        'thomas',
                        'steven',
                        'charles',
                        'jeffrey',
                        'daniel',
                        'joseph',
                        'timothy',
                        'paul',
                        'kenneth',
                        'kevin',
                        'jose',
                        'brian',
                        'gary',
                        'anthony',
                        'ronald',
                        'donald',
                        'scott',
                        'emily',
                        'emma',
                        'madison',
                        'isabella',
                        'ava',
                        'abigail',
                        'olivia',
                        'hannah',
                        'sophia',
                        'samantha',
                        'elizabeth',
                        'ashley',
                        'mia',
                        'alexis',
                        'sarah',
                        'natalie',
                        'grace',
                        'chloe',
                        'alyssa',
                        'brianna',
                        'ella',
                        'taylor',
                        'anna',
                        'lauren',
                        'hailey',
                        'kayla',
                        'addison',
                        'victoria',
                        'jasmine',
                        'savannah',
                        'julia',
                        'jessica',
                        'lily',
                        'sydney',
                        'morgan',
                        'katherine',
                        'destiny',
                        'lillian',
                        'alexa',
                        'alexandra',
                        'kaitlyn',
                        'kaylee',
                        'nevaeh',
                        'brooke',
                        'makayla',
                        'allison',
                        'maria',
                        'angelina',
                        'rachel',
                        'gabriella',
                        'jennifer',
                        'avery',
                        'mackenzie',
                        'zoe',
                        'riley',
                        'sofia',
                        'maya',
                        'kimberly',
                        'andrea',
                        'megan',
                        'katelyn',
                        'gabrielle',
                        'trinity',
                        'faith',
                        'evelyn',
                        'kylie',
                        'brooklyn',
                        'audrey',
                        'leah',
                        'stephanie',
                        'madeline',
                        'sarah',
                        'jocelyn',
                        'nicole',
                        'hailey',
                        'paige',
                        'arianna',
                        'ariana',
                        'vanessa',
                        'michelle',
                        'mariah',
                        'amelia',
                        'melanie',
                        'mary',
                        'isabelle',
                        'claire',
                        'isabel',
                        'jenna',
                        'caroline',
                        'valeria',
                        'aaliyah',
                        'aubrey',
                        'jada',
                        'natalia',
                        'autumn',
                        'rebecca',
                        'jordan',
                        'gianna',
                        'jayla',
                        'layla',
                        'mary',
                        'susan',
                        'karen',
                        'maria',
                        'lisa',
                        'linda',
                        'donna',
                        'patricia',
                        'debra',
                        'deborah',
                        'cynthia',
                        'sandra',
                        'barbara',
                        'brenda',
                        'pamela',
                        'laura',
                        'nancy',
                        'theresa',
                        'laurie',
                        'sharon',
                        'cheryl',
                        'elizabeth',
                        'kathy',
                        'cindy',
                        'janet');
    $rus_names  = Array('aleksandr',
                        'sergey',
                        'andrey',
                        'dmitriy',
                        'aleksey',
                        'vladimir',
                        'igor',
                        'evgeniy',
                        'mihail',
                        'oleg',
                        'maksim',
                        'jurij',
                        'nikolaj',
                        'denis',
                        'anton',
                        'pavel',
                        'artem',
                        'roman',
                        'ilya',
                        'vadim',
                        'konstantin',
                        'daniil',
                        'ivan',
                        'viktor',
                        'valeriy',
                        'anatoliy',
                        'elena',
                        'natasha',
                        'olya',
                        'tatyana',
                        'irina',
                        'anna',
                        'julija',
                        'ekaterina',
                        'svetlana',
                        'anastasija',
                        'marina',
                        'marija',
                        'oksana',
                        'ljudmila',
                        'darya',
                        'viktorija',
                        'aleksandra',
                        'galina',
                        'evgenija',
                        'nadezhda',
                        'inna',
                        'alla',
                        'valentina',
                        'polina');
    $nicks      = Array('abbie',
                        'abe',
                        'ad',
                        'ade',
                        'addie',
                        'addy',
                        'adie',
                        'aggie',
                        'al',
                        'alec',
                        'alex',
                        'alexie',
                        'alf',
                        'alfie',
                        'alick',
                        'alison',
                        'allie',
                        'andy',
                        'angie',
                        'anita',
                        'annie',
                        'annette',
                        'babette',
                        'babs',
                        'barb',
                        'barney',
                        'bart',
                        'bartie',
                        'bastian',
                        'bea',
                        'beau',
                        'becky',
                        'bella',
                        'belle',
                        'ben',
                        'benny',
                        'bernie',
                        'bert',
                        'bertie',
                        'bessie',
                        'beth',
                        'betsy',
                        'betty',
                        'bill',
                        'billie',
                        'billy',
                        'bob',
                        'bobbie',
                        'bobby',
                        'brad',
                        'bunty',
                        'cal',
                        'carrie',
                        'casey',
                        'cathy',
                        'celine',
                        'cherry',
                        'chris',
                        'christie',
                        'christy',
                        'cindy',
                        'cissy',
                        'clint',
                        'colette',
                        'colin',
                        'connie',
                        'costin',
                        'daisy',
                        'dan',
                        'dana',
                        'danny',
                        'dante',
                        'dave',
                        'debbie',
                        'derek',
                        'della',
                        'dick',
                        'dinny',
                        'dodie',
                        'dola',
                        'dolly',
                        'dora',
                        'don',
                        'donnie',
                        'doug',
                        'drew',
                        'ed',
                        'eda',
                        'eddie',
                        'effie',
                        'elisa',
                        'eliza',
                        'ellie',
                        'elsa',
                        'elsie',
                        'ena',
                        'essie',
                        'etta',
                        'eula',
                        'fannie',
                        'fanny',
                        'fawn',
                        'flossie',
                        'francine',
                        'frankie',
                        'fred',
                        'freddie',
                        'gab',
                        'gabby',
                        'gail',
                        'gatty',
                        'gayle',
                        'gene',
                        'gia',
                        'gina',
                        'ginette',
                        'ginger',
                        'greg',
                        'gregg',
                        'greta',
                        'gretchen',
                        'gussie',
                        'gwen',
                        'hank',
                        'harry',
                        'hattie',
                        'heidi',
                        'hob',
                        'honey',
                        'ibby',
                        'ina',
                        'isa',
                        'iva',
                        'jack',
                        'jackie',
                        'jacquetta',
                        'jacqui',
                        'jake',
                        'jamie',
                        'jan',
                        'janet',
                        'janie',
                        'jay',
                        'jeanette',
                        'jeannette',
                        'jeannie',
                        'jeff',
                        'jem',
                        'jenna',
                        'jennie',
                        'jenny',
                        'jerry',
                        'jessie',
                        'jill',
                        'jim',
                        'jimmie',
                        'jimmy',
                        'jo',
                        'jody',
                        'joe',
                        'joey',
                        'johnnie',
                        'johnny',
                        'jon',
                        'josh',
                        'juanita',
                        'judy',
                        'juliet',
                        'kari',
                        'kate',
                        'kathy',
                        'katie',
                        'kay',
                        'ken',
                        'kenny',
                        'kim',
                        'kitty',
                        'krista',
                        'kristi',
                        'kristie',
                        'kristy',
                        'kurt',
                        'lana',
                        'lance',
                        'larry',
                        'laurie',
                        'lena',
                        'liam',
                        'lillie',
                        'lillian',
                        'lina',
                        'linda',
                        'lisa',
                        'lola',
                        'lonnie',
                        'loretta',
                        'lula',
                        'lynette',
                        'lynnette',
                        'mab',
                        'mace',
                        'mack',
                        'maddie',
                        'madge',
                        'magda',
                        'maggie',
                        'maidie',
                        'maisie',
                        'mamie',
                        'mandy',
                        'marge',
                        'margie',
                        'marietta',
                        'marlon',
                        'marty',
                        'matt',
                        'mattie',
                        'maureen',
                        'max',
                        'may',
                        'megan',
                        'mel',
                        'mia',
                        'mike',
                        'mindy',
                        'minnie',
                        'mollie',
                        'molly',
                        'nancy',
                        'natasha',
                        'nellie',
                        'nessie',
                        'nettie',
                        'nina',
                        'nonie',
                        'nora',
                        'ollie',
                        'paddy',
                        'pam',
                        'paulette',
                        'pat',
                        'patti,patty,patsy',
                        'peggy',
                        'penny',
                        'perry',
                        'phil',
                        'pippa',
                        'polly',
                        'posey',
                        'princess',
                        'rab',
                        'randy',
                        'ray',
                        'rick',
                        'ricky',
                        'rickey',
                        'rita',
                        'robin',
                        'robyn',
                        'ron',
                        'ronnie',
                        'rosetta',
                        'rosie',
                        'rudy',
                        'sacha',
                        'sally',
                        'sam',
                        'sammy',
                        'sandra',
                        'sandy',
                        'sheri',
                        'sherri',
                        'sherry',
                        'sissy',
                        'sonia',
                        'sonja',
                        'sonya',
                        'spike',
                        'susie',
                        'stacey',
                        'stacy',
                        'steve',
                        'sue',
                        'tammy',
                        'tanya',
                        'tasha',
                        'ted',
                        'terri',
                        'terry',
                        'tim',
                        'timmy',
                        'tina',
                        'tom',
                        'tommie',
                        'tommy',
                        'toni',
                        'tonya',
                        'tottie',
                        'tricia',
                        'trixie',
                        'ty',
                        'val',
                        'vicki',
                        'vickie',
                        'vicky',
                        'will',
                        'xander',
                        'yorick',
                        'zana');
    $arr_prefix = Array('_',
                        '-',
                        '',
                        '',
                        '_',
                        '',
                        '');
    //---
    $prefix = $arr_prefix[array_rand($arr_prefix)];
    rand(0, 1) == 0 ? $prefix .= rand(78, 93) : $prefix .= rand(1973, 1994);
    //---
    $r = rand(0, 9);
    if($r == 0) $nick = $lastnames[array_rand($lastnames)] . $prefix;
    elseif($r == 1) $nick = $eng_names[array_rand($eng_names)] . $prefix;
    else if($r == 2) $nick = $lastnames[array_rand($lastnames)] . "_" . $eng_names[array_rand($eng_names)] . $prefix;
    else if($r == 3) $nick = $eng_names[array_rand($eng_names)] . "" . $lastnames[array_rand($lastnames)];
    else if($r == 4) $nick = $eng_names[array_rand($eng_names)] . "" . $lastnames[array_rand($lastnames)] . $prefix;
    else if($r == 5) $nick = $eng_names[array_rand($eng_names)] . "_" . $lastnames[array_rand($lastnames)];
    else if($r == 6) $nick = $lastnames[array_rand($lastnames)] . "" . $eng_names[array_rand($eng_names)];
    else if($r == 7) $nick = $nicks[array_rand($nicks)] . $prefix;
    else if($r == 8) $nick = $rus_names[array_rand($rus_names)] . $prefix;
    else if($r == 9) $nick = $rus_names[array_rand($rus_names)] . "" . $lastnames[array_rand($lastnames)];
    //---
    $nick = substr($nick, 0, 20);
    unset($lastnames);
    unset($eng_names);
    unset($rus_names);
    unset($nicks);
    return $nick;
    }

  /**
   * Определение utf-8 текста
   * @param string $string
   */
  public function detectUTF8($string)
    {
    return preg_match('%(?:
        [\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
        |\xE0[\xA0-\xBF][\x80-\xBF]               # excluding overlongs
        |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
        |\xED[\x80-\x9F][\x80-\xBF]               # excluding surrogates
        |\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
        |[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
        |\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
        )+%xs', $string);
    }

  /**
   * Быстрое определение кодировки UTF-8
   * @param string $str
   */
  public static function IsUTF8($str)
    {
    $len = strlen($str);
    //---
    for($i = 0; $i < $len; $i++)
      {
      $c = ord($str[$i]);
      //---
      if($c > 128)
        {
        if(($c > 247))
          {
          return false;
          }
        elseif($c > 239)
          {
          $bytes = 4;
          }
        elseif($c > 223)
          {
          $bytes = 3;
          }
        elseif($c > 191)
          {
          $bytes = 2;
          }
        else
          {
          return false;
          }
        //---
        if(($i + $bytes) > $len)
          {
          return false;
          }
        //---
        while($bytes > 1)
          {
          $i++;
          $b = ord($str[$i]);
          //---
          if($b < 128 || $b > 191)
            {
            return false;
            }
          //---
          $bytes--;
          }
        }
      }
    return true;
    }

  /**
   * Переконверчиваем любой текст в нужный нам UTF-8 формат
   * @param string $html
   * @param string $charset
   * @return string
   */
  public static function CharsetConvert($html, $charset = 'UTF-8')
    {
    preg_match("/charset=([\w|\-]+);?/", $html, $match);
    $charset = isset($match[1]) ? strtoupper($match[1]) : $charset;
    //--- сконвертируем данные в utf-8
    if($charset != 'UTF-8') $html = mb_convert_encoding($html, 'UTF-8', $charset);
    //---
    //$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
    return self::RemoveBom($html);
    }

  /**
   * Переконверчиваем любой текст в нужный нам UTF-8 формат
   * @param string $html
   * @param string $charset
   * @return string
   */
  public static function CharsetConvertFromCharset($html, $charset = 'UTF-8')
    {
    $charset = strtoupper($charset);
    //--- сконвертируем данные в utf-8
    if($charset != 'UTF-8') $html = mb_convert_encoding($html, 'UTF-8', $charset);
    return self::RemoveBom($html);
    }

  /**
   * Parsing hex string to string
   * @param  string $str_hex
   * @return string
   */
  public static function GetFromHex($str_hex)
    {
    $length = strlen($str_hex);
    if(($length % 2)) return 0;
    //---
    $result = '';
    for($i = 0; $i < $length; $i += 2)
      {
      $result .= chr(hexdec(substr($str_hex, $i, 2)));
      }
    //---
    return $result;
    }

  /**
   * From bytes to hex
   * @param  array(byte) $bytes
   * @return string
   */
  public static function GetHexFromBytes($bytes)
    {
    //---
    $result = '';
    for($i = 0; $i < count($bytes); $i++)
      {
      $result .= sprintf("%02x", $bytes[$i]);
      }
    //---
    return $result;
    }

  /**
   * From string to hex
   * @param  string $str
   * @return string
   */
  public static function GetHexFromString($str)
    {
    //---
    $result = '';
    for($i = 0; $i < strlen($str); $i++)
      {
      $result .= sprintf("%02x", ord($str[$i]));
      }
    //---
    return $result;
    }

  /**
   * Get random string hex format
   * @param int $len - length of string
   * @return string
   */
  public static function GetRandomHex($len)
    {
    $result = '';
    //---
    for($i = 0; $i < $len; $i++) $result .= sprintf("%02x", rand(0, 254));
    //---
    return $result;
    }

  /**
   * Тримим
   * @param $val
   */
  public static function Trim(&$val)
    {
    $val = trim($val);
    }

  /**
   * Получение случайной строки
   * @param int $len
   * @return string
   */
  public static function GetRandomStrin($len)
    {
    $text = '';
    for($i = 0; $i < $len; $i++) $text .= chr(rand(97, 122));
    return $text;
    }

  /**
   * Удаляем UTF-8 BOM (Byte Order Mask) из текста
   * @param string $text
   */
  public static function RemoveBom($text)
    {
    return str_replace((chr(0xEF) . chr(0xBB) . chr(0xBF)), '', $text);
    }

  /**
   * Удаляем Unicode \u0000 из текста
   * @param string $text
   */
  public static function DeleteUnicodeSymbols($text)
    {
    //file_put_contents('ttt.txt', $text);
    return preg_replace('/[\U0000-\Uffff]/', '', $text);
    }

  /**
   * Очистка от символов для урла
   * @param string $text
   * @return string
   */
  public static function ClearUrlSymbols($text)
    {
    //--- убираем не нужные символы, двойные пробелы или просто пробелы заменяем на -
    return preg_replace('/\s+/iu', '-', trim(preg_replace("/[^\w\-]+/iu", ' ', $text)));
    }

  /**
   * Валидация ip адреса
   * @param string $ip
   * @return bool
   */
  public static function ValidateIP($ip)
    {
    return preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/i', $ip);
    }

  /**
   * Content type по расширению
   * @param $ext
   * @return string
   */
  public static function GetMime($ext)
    {
    $mime = 'application/octet-stream';
    $map  = array('pdf'  => 'application/pdf',
                  'zip'  => 'application/zip',
                  'gif'  => 'image/gif',
                  'jpg'  => 'image/jpeg',
                  'jpeg' => 'image/jpeg',
                  'png'  => 'image/png',
                  'css'  => 'text/css',
                  'html' => 'text/html',
                  'js'   => 'text/javascript',
                  'txt'  => 'text/plain',
                  'xml'  => 'text/xml',);
    if(isset($map[$ext])) return $map[$ext];
    //---
    return $mime;
    }

  /**
   * Формат CIDR в вилку айпи адресов: http://stackoverflow.com/questions/4931721/getting-list-ips-from-cidr-notation-in-php?lq=1
   * @param $cidr
   * @return array
   */
  public static function CidrToRange($cidr)
    {
    $range    = array();
    $cidr     = explode('/', $cidr);
    $range[0] = (ip2long($cidr[0])) & ((-1 << (32 - (int)$cidr[1])));
    $range[1] = (ip2long($cidr[0])) + pow(2, (32 - (int)$cidr[1])) - 1;
    return $range;
    }

  /**
   * Зачистка текста
   * @param $sentense
   * @return mixed
   */
  public static function ClearSentense($sentense)
    {
    $text = preg_replace('/[^\w]/sUi', '', $sentense);
    $text = str_replace(' ', '', $text);
    return $text;
    }

  /**
   * Генерируем случайный символ
   * @param array $matches
   *
   * @return char
   */
  static function randomsymbol($matches)
    {
    switch($matches[0])
    {
      case ';':
      case '(':
      case '\'':
      case ')':
      case '!':
        return '_';
        break;
    }
    return chr(rand(97, 122));
    }

  /**
   * Удаляем все не англиские буквы
   * @param string $filename
   *
   * @return string
   */
  public static function generate_file_name($filename)
    {
    if(preg_match('/[^0-9a-zA-Z_\.\-]/u', $filename, $out, PREG_OFFSET_CAPTURE))
      {
      return preg_replace_callback('/[^0-9a-zA-Z_\.\-]/u', "CModel_tools::randomsymbol", $filename);
      }
    //---
    $info = pathinfo($filename);
    if(empty($info['filename'])) $filename = time() . '.' . $info['extension'];
    //---
    return $filename;
    }

  /**
   * Сжатие html
   * @param $html
   * @return mixed
   */
  public static function HtmlCompress($html)
    {
    preg_match_all('!(<(?:code|pre|script).*>[^<]+</(?:code|pre|script)>)!', $html, $pre);
    $html = preg_replace('!<(?:code|pre).*>[^<]+</(?:code|pre)>!', '#pre#', $html);
    $html = preg_replace('#<!–[^\[].+–>#', '', $html);
    $html = preg_replace('/[\r\n\t]+/', ' ', $html);
    $html = preg_replace('/>[\s]+</', '><', $html);
    $html = preg_replace('/[\s]+/', ' ', $html);
    if(!empty($pre[0]))
      {
      foreach($pre[0] as $tag)
        {
        $html = preg_replace('!#pre#!', $tag, $html, 1);
        }
      }
    return $html;
    }
  }

?>