<?php

class SupsysticTables_Tables_Module extends SupsysticTables_Core_BaseModule
{
    /**
     * {@inheritdoc}
     */
    public function onInit()
    {
        parent::onInit();

        $this->registerShortcode();
        $this->registerTwigTableRender();
        $this->registerMenuItem();
        $this->addTwigHighlighter();

        $this->cacheDirectory = $this->getConfig()->get('plugin_cache_tables');

        if ($this->isPluginPage()) {
            $this->reviewNoticeCheck();
        }

        // Widget
        add_action('widgets_init', array($this, 'registerWidget'));

        $dispatcher = $this->getEnvironment()->getDispatcher();
        $dispatcher->on('after_tables_loaded', array($this, 'onAfterLoaded'));
    }

    /**
     * Executes after module loaded.
     */
    public function onAfterLoaded()
    {
        $config = $this->getEnvironment()->getConfig();
        $config->load('@tables/language/override.php');
    }

    /**
     * {@inheritdoc}
     */
    public function afterUiLoaded(SupsysticTables_Ui_Module $ui)
    {
        parent::afterUiLoaded($ui);

        $environment = $this->getEnvironment();
        $hookName = 'admin_enqueue_scripts';
        $dynamicHookName = is_admin() ? $hookName : 'wp_enqueue_scripts';

        $version = $environment->getConfig()->get('plugin_version');
        $cachingAllowed = $environment->isProd();

        $ui->add($ui->createScript('jquery')->setHookName($dynamicHookName));
        $ui->add(
            $ui->createStyle('supsystic-tables-datatables-css')
                ->setHookName($dynamicHookName)
                ->setExternalSource('//cdn.datatables.net/1.10.11/css/jquery.dataTables.min.css')
                ->setVersion('1.10.11')
                ->setCachingAllowed(true)
        );

        $ui->add(
            $ui->createStyle('supsystic-tables-datatables-responsive-css')
                ->setHookName($dynamicHookName)
                ->setExternalSource('//cdn.datatables.net/responsive/2.0.2/css/responsive.dataTables.min.css')
                ->setVersion('2.0.2')
                ->setCachingAllowed(true)
        );


        $ui->add(
            $ui->createScript('supsystic-tables-datatables-js')
                ->setHookName($dynamicHookName)
                ->setExternalSource('//cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js')
                ->setVersion('1.10.11')
                ->setCachingAllowed(true)
                ->addDependency('jquery')
        );

        $ui->add(
            $ui->createScript('supsystic-tables-datatables-responsive-js')
                ->setHookName('wp_enqueue_scripts')
                ->setExternalSource('//cdn.datatables.net/responsive/2.0.2/js/dataTables.responsive.min.js')
                ->setVersion('2.0.2')
                ->setCachingAllowed(true)
                ->addDependency('jquery')
                ->addDependency('supsystic-tables-datatables-js')
        );

        $ui->add(
            $ui->createScript('supsystic-tables-datatables-numeral')
                ->setHookName('wp_enqueue_scripts')
                ->setModuleSource($this, 'libraries/numeral.min.js')
                ->setVersion($version)
                ->setCachingAllowed(true)
                ->addDependency('jquery')
                ->addDependency('supsystic-tables-datatables-js')
        );

		$ui->add(
			$ui->createScript('supsystic-tables-datatables-natural-sort-js')
				->setHookName('wp_enqueue_scripts')
				->setExternalSource('//cdn.datatables.net/plug-ins/1.10.11/sorting/natural.js')
				->setVersion('1.10.11')
				->setCachingAllowed(true)
				->addDependency('jquery')
				->addDependency('supsystic-tables-datatables-js')
		);

        $ui->add(
            $ui->createStyle('supsystic-tables-shortcode-css')
                ->setHookName($dynamicHookName)
                ->setModuleSource($this, 'css/tables.shortcode.css')
                ->setVersion($version)
                ->setCachingAllowed($cachingAllowed)
        );


        /* Backend scripts */
        if ($environment->isModule('tables')) {
            $ui->add(
                $ui->createScript('jquery-ui-dialog')
            );

            /* RuleJS */
            $this->loadRuleJS($ui);

            $ui->add(
                $ui->createScript('jquery-ui-autocomplete')
            );

            $ui->add(
                $ui->createScript('supsystic-tables-tables-model')
                    ->setHookName($hookName)
                    ->setModuleSource($this, 'js/tables.model.js')
                    ->setCachingAllowed($cachingAllowed)
                    ->setVersion($version)
            );

            if ($environment->isAction('index')) {
                $ui->add(
                    $ui->createScript('supsystic-tables-tables-index')
                        ->setHookName($hookName)
                        ->setModuleSource($this, 'js/tables.index.js')
                        ->setCachingAllowed($cachingAllowed)
                        ->setVersion($version)
                        ->addDependency('jquery-ui-dialog')
                );
            }

            if ($environment->isAction('view')) {

                // DataTables language selector
               // $ui->add(
               //     $ui->createScript('supsystic-tables-dt-lang-selector')
               //         ->setHookName($hookName)
               //         ->setModuleSource($this, 'js/dt.lang-selector.js')
               // );

                $ui->add(
                    $ui->createStyle('supsystic-tables-tables-editor-css')
                        ->setHookName($hookName)
                        ->setModuleSource($this, 'css/tables.editor.css')
                        ->setCachingAllowed($cachingAllowed)
                        ->setVersion($version)
                );

                /* Color Picker */
                $ui->add(
                    $ui->createStyle('supsystic-tables-colorpicker-css')
                        ->setHookName($hookName)
                        ->setModuleSource($this, 'libraries/colorpicker/colorpicker.css')
                        ->setCachingAllowed(true)
                );

                $ui->add(
                    $ui->createScript('supsystic-tables-colorpicker-js')
                        ->setHookName($hookName)
                        ->setModuleSource($this, 'libraries/colorpicker/colorpicker.js')
                        ->setCachingAllowed(true)
                );

                /* Toolbar */
                $ui->add(
                    $ui->createStyle('supsystic-tables-toolbar-css')
                        ->setHookName($hookName)
                        ->setModuleSource($this, 'libraries/toolbar/jquery.toolbars.css')
                        ->setCachingAllowed(true)
                );

                $ui->add(
                    $ui->createScript('supsystic-tables-toolbar-js')
                        ->setHookName($hookName)
                        ->setModuleSource($this, 'libraries/toolbar/jquery.toolbar.js')
                        ->setCachingAllowed(true)
                );

                /* Handsontable */
                $ui->add(
                    $ui->createStyle('supsystic-tables-handsontable-css')
                        ->setHookName($hookName)
                        ->setExternalSource('https://cdnjs.cloudflare.com/ajax/libs/handsontable/0.24.1/handsontable.full.min.css')
                        ->setCachingAllowed(true)
                        ->setVersion('0.24.1')
                );

                $ui->add(
                    $ui->createScript('supsystic-tables-handsontable-js')
                        ->setHookName($hookName)
                        ->setExternalSource('https://cdnjs.cloudflare.com/ajax/libs/handsontable/0.24.1/handsontable.full.min.js')
                        ->setCachingAllowed(true)
                        ->setVersion('0.24.1')
                );

                $ui->add(
                    $ui->createScript('supsystic-tables-editor-toolbar-js')
                        ->setHookName($hookName)
                        ->setModuleSource($this, 'js/editor/tables.editor.toolbar.js')
                        ->setCachingAllowed($cachingAllowed)
                        ->setVersion($version)
                );

                $ui->add(
                    $ui->createScript('supsystic-tables-editor-formula-js')
                        ->setHookName($hookName)
                        ->setModuleSource($this, 'js/editor/tables.editor.formula.js')
                        ->setCachingAllowed($cachingAllowed)
                        ->setVersion($version)
                        ->addDependency('jquery-ui-autocomplete')
                );

                $ui->add(
                    $ui->createStyle('supsystic-tables-tables-view')
                        ->setHookName($hookName)
                        ->setModuleSource($this, 'css/tables.view.css')
                        ->setCachingAllowed($cachingAllowed)
                        ->setVersion($version)
                );

                $ui->add(
                    $ui->createScript('supsystic-tables-tables-view')
                        ->setHookName($hookName)
                        ->setModuleSource($this, 'js/tables.view.js')
                        ->setCachingAllowed($cachingAllowed)
                        ->setVersion($version)
                );

                $ui->add(
                    $ui->createScript('supsystic-tables-ace-editor-js')
                        ->setHookName($hookName)
                        ->setExternalSource('https://cdnjs.cloudflare.com/ajax/libs/ace/1.1.9/ace.js')
                );
            }
        }

        $ui->add(
            $ui->createScript('supsystic-tables-shortcode')
                ->setHookName($dynamicHookName)
                ->setModuleSource($this, 'js/tables.shortcode.js')
                ->setVersion($version)
                ->setCachingAllowed($cachingAllowed)
                ->addDependency('jquery')
                ->addDependency('supsystic-tables-datatables-js')
        );
    }

    /**
     * Returns shortcode template name.
     * @return string
     */
    public function getShortcodeTemplate()
    {
        return '@tables/shortcode.twig';
    }

    /**
     * Renders the table
     * @param int $id
     * @return string
     */
    public function render($id)
    {
        if($this->disallowIndexing($id)) {
            return;
        }

        $cachePath = $this->cacheDirectory . DIRECTORY_SEPARATOR . $id;
        if (file_exists($cachePath) && $this->getEnvironment()->isProd()) {
            return do_shortcode(file_get_contents($cachePath));
        }

        $environment = $this->getEnvironment();
        $twig = $environment->getTwig();

        /** @var SupsysticTables_Core_Module $core */
        $core = $environment->getModule('core');
        /** @var SupsysticTables_Tables_Model_Tables $tables */
        $tables = $core->getModelsFactory()->get('tables');
        $table = $tables->getById($id);

        if (!$table) {
            return sprintf($environment->translate('The table with ID %d not exists.'), $id);
        }

        if (isset($table->meta['columnsWidth'])) {
            $columnsTotalWidth = array_sum($table->meta['columnsWidth']);
            foreach ($table->meta['columnsWidth'] as &$value) {
                $value = round($value / $columnsTotalWidth * 100, 4);
            }
        }

        $renderData = $twig->render(
            $this->getShortcodeTemplate(),
            array('table' => $table)
        );

        $renderData = preg_replace('/\s+/', ' ', trim($renderData));

        if (isset($this->cacheDirectory)) {
            file_put_contents($cachePath, $renderData);
        }

        return do_shortcode($renderData);
    }

    public function doShortcode($attributes)
    {
        $environment = $this->getEnvironment();
        $config = $environment->getConfig();

        if (!array_key_exists('id', $attributes)) {
            return sprintf($environment->translate(
                'Mandatory attribute "id" is not specified. ' .
                'Shortcode usage example: [%s id="{table_id}"]'
            ), $config->get('shortcode_name'));
        }

        /** @var SupsysticTables_Ui_Module $ui */
        $ui = $environment->getModule('ui');
        /** @var SupsysticTables_Ui_Asset[] $assets */
        $assets = array_filter($ui->getAssets(), array($this, 'filterAssets'));

        if (count($assets) > 0) {
            foreach ($assets as $asset) {
                add_action('wp_footer', array($asset, 'load'));
            }
        }

        return $this->render((int)$attributes['id']);
    }

    public function registerWidget() {
        register_widget('SupsysticTables_Widget');
    }

    /**
     * Returns only not loaded assets
     * @param \SupsysticTables_Ui_Asset $asset
     * @return bool
     */
    public function filterAssets(SupsysticTables_Ui_Asset $asset)
    {
        return !$asset->isLoaded() && 'wp_enqueue_scripts' === $asset->getHookName();
    }

    private function registerShortcode()
    {
        $config = $this->getEnvironment()->getConfig();
        $callable = array($this, 'doShortcode');

        add_shortcode(
            $config->get('shortcode_name'),
            $callable
        );
    }

    private function registerTwigTableRender()
    {
        $twig = $this->getEnvironment()->getTwig();
        $callable = array($this, 'render');


        $twig->addFunction(
            new Twig_SimpleFunction(
                'render_table',
                $callable,
                array('is_safe' => array('html'))
            )
        );
    }

    private function loadRuleJS(SupsysticTables_Ui_Module $ui)
    {
        $hookName = 'admin_enqueue_scripts';
        $dynamicHookName = is_admin() ? $hookName : 'wp_enqueue_scripts';

        if (is_admin() && !$this->getEnvironment()->isModule('tables', 'view')) {
            return;
        }

        /* External Libraries */
        $ui->add(
            $ui->createScript('supsystic-tables-rulejs-libs-js')
                ->setHookName($dynamicHookName)
                ->setModuleSource($this, 'libraries/ruleJS/ruleJS.lib.full.js')
        );

        /* RuleJS */
        $ui->add(
            $ui->createScript('supsystic-tables-rulejs-parser-js')
                ->setHookName($dynamicHookName)
                ->setModuleSource($this, 'libraries/ruleJS/parser.js')
        );

        $ui->add(
            $ui->createScript('supsystic-tables-rulejs-js')
                ->setHookName($dynamicHookName)
                ->setModuleSource($this, 'libraries/ruleJS/ruleJS.js')
        );

        /* Handsontable Module */
        $ui->add(
            $ui->createScript('supsystic-tables-rulejs-hot-js')
                ->setHookName($hookName)
                ->setModuleSource($this, 'libraries/ruleJS/handsontable.formula.js')
                ->addDependency('supsystic-tables-handsontable-js')
        );

        $ui->add(
            $ui->createStyle('supsystic-tables-rulejs-hot-css')
                ->setHookName($hookName)
                ->setModuleSource($this, 'libraries/ruleJS/handsontable.formula.css')
        );
    }

    private function addTwigHighlighter()
    {
        $twig = $this->getEnvironment()->getTwig();

        $twig->addFilter(
            new Twig_SimpleFilter(
                'highlight',
                'highlight_string',
                array('is_safe' => array('html'))
            )
        );
    }

    private function registerMenuItem()
    {
        $environment = $this->getEnvironment();
        $menu = $environment->getMenu();
        $plugin_menu = $this->getConfig()->get('plugin_menu');
        $capability = $plugin_menu['capability'];

        $item = $menu->createSubmenuItem();
        $item->setCapability($capability)
            ->setMenuSlug($menu->getMenuSlug() . '#add')
            ->setMenuTitle($environment->translate('Add table'))
            ->setModuleName('tables')
            ->setPageTitle($environment->translate('Add table'));

        $menu->addSubmenuItem('add_table', $item);

        $item = $menu->createSubmenuItem();
        $item->setCapability($capability)
           ->setMenuSlug($menu->getMenuSlug() . '&module=tables')
           ->setMenuTitle($environment->translate('Tables'))
           ->setModuleName('tables')
           ->setPageTitle($environment->translate('Tables'));

        $menu->addSubmenuItem('tables', $item);

        $menu->register();
    }

    public function disallowIndexing($id) {

        $core = $this->getEnvironment()->getModule('core');
        $tables = $core->getModelsFactory()->get('tables');
        $settings = $tables->getSettings($id);

        if (!$settings) {
            return false;
        }

        $settings = unserialize(
            htmlspecialchars_decode(
                current($settings)->settings
            )
        );
        if (!isset($settings['disallowIndexing'])) {
            return false;
        }

        $userAgent = $this->getRequest()->headers->get('USER_AGENT');
        $pattern = '/(abachobot|acoon|aesop_com_spiderman|ah-ha.com crawler|appie|arachnoidea|architextspider|atomz|baidu|bing|bot|deepindex|esismartspider|ezresult|fast-webcrawler|feed|fido|fluffy the spider|gigabot|google|googlebot|gulliver|gulper|gulper|henrythemiragorobot|http|ia_archiver|jeevesteoma|kit-fireball|linkwalker|lnspiderguy|lycos_spider|mantraagent|mediapartners|msn|nationaldirectory-superspider|nazilla|openbot|openfind piranha,shark|robozilla|scooter|scrubby|search|slurp|sogou|sohu|soso|spider|tarantula|teoma_agent1|test|uk searcher spider|validator|w3c_validator|wdg_validator|webaltbot|webcrawler|websitepulse|wget|winona|yahoo|yodao|zyborg)/i';
        return (bool) preg_match($pattern, $userAgent);
    }

    public function reviewNoticeCheck() {
        $option = $this->config('db_prefix') . 'reviewNotice';
        $notice = get_option($option);
        if (!$notice) {
            update_option($option, array(
                'time' => time() + (60 * 60 * 24 * 7),
                'shown' => false
            ));
        } elseif ($notice['shown'] === false && time() > $notice['time']) {
            add_action('admin_notices', array($this, 'showReviewNotice'));
        }
    }

    public function showReviewNotice() {
        print $this->getTwig()->render('@tables/notice/review.twig');
    }
}

require_once('Model/widget.php');
