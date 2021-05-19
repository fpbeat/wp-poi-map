<?php

namespace WpPoiMap\Admin\Settings;

use WpPoiMap\Tools\ObjectPost;

class IconSettings extends AbstractSettings
{
    /**
     * @var string
     */
    const TAB_ID = 'icon';

    /**
     * @inheritDoc
     */
    protected function tab(): void
    {
        $this->mapTab = $this->panel->createTab([
            'id' => self::TAB_ID,
            'name' => 'Значки об\'єктів',
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
                'type' => 'heading',
            ]);

            foreach ($item['categories'] as $id => $category) {
                $this->mapTab->createOption([
                    'name' => $category,
                    'id' => sprintf('icon-%s', $id),
                    'type' => 'text',
                ]);
            }
        }


        $this->mapTab->createOption([
            'type' => 'save'
        ]);
    }
}