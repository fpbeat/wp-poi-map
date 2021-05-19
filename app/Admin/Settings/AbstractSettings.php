<?php

namespace WpPoiMap\Admin\Settings;

abstract class AbstractSettings {

    /**
     * @var \TitanFrameworkAdminPage
     */
    protected $panel;

    /**
     * @var \TitanFrameworkAdminTab
     */
    protected $mapTab;

    /**
     * AbstractSettings constructor.
     *
     * @param \TitanFrameworkAdminPage $panel
     */
    public function __construct(\TitanFrameworkAdminPage $panel) {
        $this->panel = $panel;
    }

    /**
     * @return void
     */
    public function create(): void {
        $this->tab();
        $this->options();
    }

    /**
     * @return void
     */
    abstract protected function tab(): void;

    /**
     * @return void
     */
    abstract protected function options(): void;
}
