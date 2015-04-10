<?php

/**
 * @package redaxo\core
 */
class rex_view
{
    private static $cssFiles = [];
    private static $jsFiles = [];
    private static $jsProperties = [];
    private static $favicon;

    /**
     * Adds a CSS file
     *
     * @param string $file
     * @param string $media
     */
    public static function addCssFile($file, $media = 'all')
    {
        self::$cssFiles[$media][] = $file;
    }

    /**
     * Returns the CSS files
     *
     * @return string[]
     */
    public static function getCssFiles()
    {
        return self::$cssFiles;
    }

    /**
     * Adds a JS file
     *
     * @param string $file
     */
    public static function addJsFile($file)
    {
        self::$jsFiles[] = $file;
    }

    /**
     * Returns the JS files
     *
     * @return string[]
     */
    public static function getJsFiles()
    {
        return self::$jsFiles;
    }

    /**
     * Sets a JS property
     *
     * @param string $key
     * @param mixed  $value
     */
    public static function setJsProperty($key, $value)
    {
        self::$jsProperties[$key] = $value;
    }

    /**
     * Returns the JS properties
     *
     * @return array
     */
    public static function getJsProperties()
    {
        return self::$jsProperties;
    }

    /**
     * Sets the favicon path
     *
     * @param string $file
     */
    public static function setFavicon($file)
    {
        self::$favicon = $file;
    }

    /**
     * Returns the favicon
     *
     * @return string
     */
    public static function getFavicon()
    {
        return self::$favicon;
    }

    /**
     * Returns an info message
     *
     * @param string $message
     * @param string $cssClass
     * @return string
     */
    public static function info($message, $cssClass = '')
    {
        $cssClassMessage = 'alert-info';
        if ($cssClass != '') {
            $cssClassMessage .= ' ' . $cssClass;
        }

        return self::message($message, $cssClassMessage);
    }

    /**
     * Returns a success message
     *
     * @param string $message
     * @param string $cssClass
     * @return string
     */
    public static function success($message, $cssClass = '')
    {
        $cssClassMessage = 'alert-success';
        if ($cssClass != '') {
            $cssClassMessage .= ' ' . $cssClass;
        }

        return self::message($message, $cssClassMessage);
    }

    /**
     * Returns an warning message
     *
     * @param string $message
     * @param string $cssClass
     * @return string
     */
    public static function warning($message, $cssClass = '')
    {
        $cssClassMessage = 'alert-warning';
        if ($cssClass != '') {
            $cssClassMessage .= ' ' . $cssClass;
        }

        return self::message($message, $cssClassMessage);
    }

    /**
     * Returns an error message
     *
     * @param string $message
     * @param string $cssClass
     * @return string
     */
    public static function error($message, $cssClass = '')
    {
        $cssClassMessage = 'alert-danger';
        if ($cssClass != '') {
            $cssClassMessage .= ' ' . $cssClass;
        }

        return self::message($message, $cssClassMessage);
    }

    /**
     * Returns a message
     *
     * @param string $message
     * @param string $cssClass
     * @return string
     */
    private static function message($message, $cssClass)
    {
        $cssClassMessage = 'alert';
        if ($cssClass != '') {
            $cssClassMessage .= ' ' . $cssClass;
        }

        $return = '<div class="' . $cssClassMessage . '">' . $message . '</div>';

        /*
        $fragment = new rex_fragment();
        $fragment->setVar('class', $cssClass);
        $fragment->setVar('message', $content, false);
        $return = $fragment->parse('message.php');
        */
        return $return;
    }

    /**
     * Returns a toolbar
     *
     * @param string $content
     * @param string $brand
     * @param string $cssClass
     * @return string
     */
    public static function toolbar($content, $brand = null, $cssClass = null)
    {
        $fragment = new rex_fragment();
        $fragment->setVar('cssClass', $cssClass);
        $fragment->setVar('brand', $brand);
        $fragment->setVar('content', $content, false);
        $return = $fragment->parse('core/toolbar.php');

        return $return;
    }

    /**
     * Returns a content block
     *
     * @param string       $key
     * @param string|array $content
     * @param string       $title
     * @param array        $params
     * @return string
     */
    public static function content($key, $content, $title = '', array $params = [])
    {
        if (!is_array($content)) {
            $content = [$content];
        }

        $fragment = new rex_fragment();
        $fragment->setVar('content', $content, false);
        $fragment->setVar('title', $title, false);
        $fragment->setVar('params', $params, false);
        return $fragment->parse('core/content/' . $key . '.php');
    }


    /**
     * Returns the formatted title
     *
     * @param string            $head
     * @param null|string|array $subtitle
     * @throws InvalidArgumentException
     * @return string
     */
    public static function title($head, $subtitle = null)
    {
        global $article_id, $category_id, $page;

        if ($subtitle !== null && !is_string($subtitle) && (!is_array($subtitle) || count($subtitle) > 0 && !reset($subtitle) instanceof rex_be_page)) {
            throw new InvalidArgumentException('Expecting $subtitle to be a string or an array of rex_be_page!');
        }

        if ($subtitle === null) {
            $subtitle = rex_be_controller::getPageObject(rex_be_controller::getCurrentPagePart(1))->getSubpages();
        }

        if (is_array($subtitle) && count($subtitle) && reset($subtitle) instanceof rex_be_page) {
            $nav = rex_be_navigation::factory();
            $nav->setHeadline('default', rex_i18n::msg('subnavigation', $head));
            foreach ($subtitle as $pageObj) {
                $nav->addPage($pageObj);
            }
            $blocks = $nav->getNavigation();
            $navigation = [];
            if (count($blocks) == 1) {
                $navigation = current($blocks);
                $navigation = $navigation['navigation'];
            }

            if (!empty($navigation)) {
                $fragment = new rex_fragment();
                $fragment->setVar('left', $navigation, false);
                $subtitle = $fragment->parse('core/navigations/content.php');
            } else {
                $subtitle = '';
            }

        } elseif (!is_string($subtitle)) {
            $subtitle = '';
        }

        $title = rex_extension::registerPoint(new rex_extension_point('PAGE_TITLE', $head, ['category_id' => $category_id, 'article_id' => $article_id, 'page' => $page]));


        $fragment = new rex_fragment();
        $fragment->setVar('heading', $title, false);
        $fragment->setVar('subtitle', $subtitle, false);
        $return = $fragment->parse('core/page/header.php');


        echo rex_extension::registerPoint(new rex_extension_point('PAGE_TITLE_SHOWN', '', [
            'category_id' => $category_id,
            'article_id' => $article_id,
            'page' => $page
        ]));

        return $return;
    }

    /**
     * Returns a clang switch
     *
     * @param rex_context $context
     * @return string
     */
    public static function clangSwitch(rex_context $context)
    {
        if (rex_clang::count() == 1) {
            return '';
        }

        $button_label =  '';
        $items  = [];
        foreach (rex_clang::getAll() as $id => $clang) {
            if (rex::getUser()->getComplexPerm('clang')->hasPerm($id)) {
                $item = [];
                $item['title'] = rex_i18n::translate($clang->getName());
                $item['href']  = $context->getUrl(['clang' => $id]);
                if ($id == $context->getParam('clang')) {
                    $item['active'] = true;
                    $button_label = rex_i18n::translate($clang->getName());
                }
                $items[] = $item;
            }
        }

        $fragment = new rex_fragment();
        $fragment->setVar('class', 'rex-language');
        $fragment->setVar('button_prefix', rex_i18n::msg('language'));
        $fragment->setVar('button_label', $button_label);
        $fragment->setVar('header', rex_i18n::msg('clang_select'));
        $fragment->setVar('items', $items, false);

        if (rex::getUser()->isAdmin()) {
            $fragment->setVar('footer', '<a href="' . rex_url::backendPage('system/lang') . '"><i class="fa fa-flag"></i> ' . rex_i18n::msg('languages_edit') . '</a>', false);
        }

        return $fragment->parse('core/dropdowns/dropdown.php');
    }
}
