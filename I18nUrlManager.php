<?php
/**
 * @link http://phe.me
 * @copyright Copyright (c) 2014 Pheme
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace pheme\i18n;

use yii\web\UrlManager;
use Yii;

/**
 * @author Aris Karageorgos <aris@phe.me>
 */
class I18nUrlManager extends UrlManager
{
    /**
     * @var array Supported languages
     */
    public $languages;
    /**
     * @var bool Whether to display the source app language in the URL
     */
    public $displaySourceLanguage = false;
    /**
     * @var string Parameter used to set the language
     */
    public $languageParam = 'lang';
    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->languages)) {
            $this->languages = [Yii::$app->language];
        }
        parent::init();
    }
    /**
     * Parses the URL and sets the language accordingly
     * @param \yii\web\Request $request
     * @return array|bool
     */
    public function parseRequest($request)
    {
        if ($this->enablePrettyUrl) {
            $pathInfo = $request->getPathInfo();
            $language = explode('/', $pathInfo)[0];
            if (in_array($language, $this->languages)) {
                $request->setPathInfo(substr_replace($pathInfo, '', 0, (strlen($language) + 1)));
                Yii::$app->language = $language;
                //redirect without default lang
                if(!$this->displaySourceLanguage && $language == Yii::$app->sourceLanguage)
                {
                    $url = $request->getUrl();
                    $request->setUrl(str_replace($pathInfo, $request->getPathInfo(), $url));
                    \Yii::$app->response->redirect($request->getUrl())->send();
                }
            }
        } else {
            $params = $request->getQueryParams();
            $language= isset($params[$this->languageParam]) ? $params[$this->languageParam] : '';
            if (in_array($language, $this->languages)) {
                Yii::$app->language = $language;
                //redirect without default lang
                if(!$this->displaySourceLanguage && $language == Yii::$app->sourceLanguage) {
                    \Yii::$app->response->redirect($this->createUrl($params))->send();
                }
            }
        }
        return parent::parseRequest($request);
    }
    /**
     * Adds language functionality to URL creation
     * @param array|string $params
     * @return string
     */
    public function createUrl($params)
    {
        $lang_url = '';
        $scriptUrl = '';
        if ($this->enablePrettyUrl && array_key_exists($this->languageParam, $params)) {
            $lang = $params[$this->languageParam];
            if (($lang !== Yii::$app->sourceLanguage || $this->displaySourceLanguage) && !empty($lang)) {
                $lang_url = $lang ;
            }
            unset($params[$this->languageParam]);
        } else {
            if (Yii::$app->language !== Yii::$app->sourceLanguage || $this->displaySourceLanguage) {
                $lang_url = Yii::$app->language;
                if (!$this->enablePrettyUrl && !isset($params[$this->languageParam])) {
                    $params[$this->languageParam] = $lang_url;
                }
            }
        }

        if(isset($params[$this->languageParam]) && $params[$this->languageParam] == Yii::$app->sourceLanguage)
        {
            unset($params[$this->languageParam]);
        }

        $url = parent::createUrl($params);

        if($this->showScriptName)
        {
            $scriptUrl = $this->getScriptUrl();
            $url = str_replace($scriptUrl, '', $url);
        }

        if($this->enablePrettyUrl) {
            if ($url == '/') {
                $url = '/' . $lang_url;
            } elseif (!empty($lang_url)) {
                $url = '/' . $lang_url . $url;
            }
        }
        return $scriptUrl.$url;
    }
}
