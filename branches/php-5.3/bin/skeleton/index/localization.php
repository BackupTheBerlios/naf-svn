// localization
$lang = filter_input(INPUT_GET, 'lang', FILTER_SANITIZE_STRING);
if (empty($lang))
{
	$lang = Naf::$settings['lang.default'];
}
if (! array_key_exists($lang, Naf::$settings['lang.list']))
{
	die("Language $lang is not yet configured");
}
$langInfo = Naf::$settings['lang.list'][$lang];
Naf::$response->langInfo = $langInfo;
Naf::$response->lang = $lang;
/* for the Content-Language to be properly set */
Naf::$response->setLanguage($lang);

Naf::urlComposer()->setPersistent(array('lang' => $lang));

// gettext setup
putenv("LANG=" . $langInfo['locale']);
putenv("LANGUAGE=" . $langInfo['locale']);
setlocale(LC_ALL, $langInfo['locale']);
bindtextdomain('messages', ROOT . 'locale');