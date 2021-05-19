<?php

namespace WpPoiMap\Admin\Settings;

use WpPoiMap\Registry;

class MapSettings extends AbstractSettings
{
    /**
     * @var string
     */
    const TAB_ID = 'map';

    /**
     * @inheritDoc
     */
    protected function tab(): void
    {
        $this->mapTab = $this->panel->createTab([
            'name' => 'Загальні налаштування',
            'id'=> self::TAB_ID
        ]);
    }

    /**
     * @inheritDoc
     */
    public function options(): void
    {
        $this->mapTab->createOption([
            'name' => 'Ключ Google Maps API',
            'desc' => 'Отримати в <a href="//console.cloud.google.com/google/maps-apis/overview" target="_blank">Google Cloud Platform Console</a>',
            'id' => 'map-api-key',
            'type' => 'text'
        ]);

        $this->mapTab->createOption([
            'name' => 'Центр карти',
            'desc' => 'Шорткод map-center="широта, довгота"',
            'id' => 'map-center',
            'type' => 'text'
        ]);

        $this->mapTab->createOption([
            'name' => 'Масштаб',
            'id' => 'map-zoom',
            'desc' => 'Шорткод map-zoom="число"',
            'type' => 'number',
            'default' => 12,
            'step' => 1,
            'min' => 1,
            'max' => 20,
        ]);

        $this->mapTab->createOption([
            'name' => 'Тип карти',
            'id' => 'map-type',
            'type' => 'radio',
            'options' => [
                'roadmap' => 'Схема',
                'satellite' => 'Супутник',
                'hybrid' => 'Гибрид',
            ],
            'desc' => 'Шорткод map-type="roadmap|satellite|hybrid"',
            'default' => 'roadmap',
        ]);


        $this->mapTab->createOption([
            'name' => 'Позиціонування',
            'id' => 'map-behavior',
            'desc' => 'Шорткод map-behavior="center|fit"',
            'type' => 'radio',
            'options' => [
                'center' => 'Центр об\'єкта',
                'fit' => 'Вмістити всі об\'єкти'
            ],
            'default' => 'center',
        ]);

        $this->mapTab->createOption([
            'name' => 'Шаблони',
            'type' => 'heading',
        ]);

        $this->mapTab->createOption([
            'name' => 'Шаблон об\'єкту',
            'id' => 'map-template',
            'desc' => Registry::instance()['fenom']->fetch('admin/components/instruction.tpl'),
            'media_buttons' => FALSE,
            'type' => 'editor',
            'wpautop' => FALSE,
            'default' => Registry::instance()['fenom']->fetch('shared/template.tpl'),
            'editor_settings' => [
                'teeny' => TRUE,
                'tinymce' => FALSE,
            ]
        ]);

        $this->mapTab->createOption(array(
            'type' => 'save'
        ));
    }
}