<?php

namespace WpPoiMap;

class TemplateFormatter
{
    /**
     * @var string|null
     */
    private $template;

    /**
     * TemplateFormatter constructor.
     * @param string|null $template
     */
    public function __construct(?string $template)
    {
        $this->template = $template;
    }

    /**
     * @param array $params
     * @return string
     */
    public function render(array $params = []): string
    {
        return Registry::instance()['fenom']->fetch('shared/template.tpl', $params);
    }


}