<?php

namespace WpPoiMap\Admin\Settings;

use WpPoiMap\Tools\ObjectPost;

class ObjectSettings extends AbstractSettings
{
    /**
     * @var string
     */
    const TAB_ID = 'object';

    /**
     * @inheritDoc
     */
    protected function tab(): void
    {
        $this->mapTab = $this->panel->createTab([
            'id' => self::TAB_ID,
            'name' => 'Об\'єкти на карті',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function options(): void
    {
        $objectPost = new ObjectPost();

        foreach ($objectPost->toArray() as $item) {
            $this->mapTab->createOption([
                'name' => $item['label'],
                'id' => sprintf('object-%s', $item['slug']),
                'type' => 'multicheck',
                'options' => $item['categories'],
            ]);
        }

        $this->mapTab->createOption([
            'type' => 'save'
        ]);
    }
}