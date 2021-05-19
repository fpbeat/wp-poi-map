<?php

namespace WpPoiMap\Tools;

class ObjectPost
{
    /**
     * @var string
     */
    private $language;

    /**
     * ObjectPost constructor.
     * @param string|null $language
     */
    public function __construct(?string $language = NULL)
    {
        $this->language = $language ?? pll_default_language();
    }

    /**
     * For unknown reasons translation missed for wellness-and-spa post type, let's temporary hardcode it
     *
     * @return array
     */
    private function getPostTypeLabelHack(): array
    {
        return [
            'wellness-and-spa' => pll__('Здоров\'я та краса')
        ];
    }

    /**
     * @return array[]
     */
    public function getPostTypes(): array
    {
        $types = get_post_types([
            'public' => true,

            '_builtin' => false,
        ], 'object');

        $extraLabels = $this->getPostTypeLabelHack();

        return array_map(function ($type) use ($extraLabels) {
            $label = $extraLabels[$type->name] ?? $type->label;

            return ['slug' => $type->name, 'label' => $label];
        }, $types);
    }

    /**
     * @param string $type
     * @return string
     * @throws \Exception
     */
    public function getPostCategoryTaxonomy(string $type): string
    {
        $taxonomies = get_object_taxonomies($type);

        foreach ($taxonomies as $taxonomy) {
            if (strpos($taxonomy, 'category') !== FALSE) {
                return $taxonomy;
            }
        }

        throw new \Exception('Post category taxonomy not found');
    }

    /**
     * @param string $taxonomy
     * @param string $type
     * @return array
     * @throws \Exception
     */
    public function getCategoriesId(string $taxonomy, string $type): array
    {
        $codes = [];
        foreach (get_categories(['taxonomy' => $taxonomy, 'type' => $type]) as $category) {
            $codes[] = icl_object_id($category->term_id, $category->taxonomy, false, $this->language);
        }

        if (count($codes) === 0) {
            throw new \Exception('Translated category id\'s is empty');
        }

        return array_unique($codes);
    }

    /**
     * @param string $type
     * @return array
     * @throws \Exception
     */
    public function get(string $type): array
    {
        $taxonomy = $this->getPostCategoryTaxonomy($type);

        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'lang' => $this->language,
            'include' => $this->getCategoriesId($taxonomy, $type)
        ]);

        return (!$terms instanceof \WP_Error) ? $terms : [];
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $pool = [];
        foreach ($this->getPostTypes() as $type) {
            try {
                $ids = $this->get($type['slug']);

                $categories = [];
                foreach ($ids as $id) {
                    $categories[$id->term_id] = $id->name;
                }
                $pool[] = array_merge($type, ['categories' => $categories]);
            } catch (\Exception $e) {
                // none
            }
        }

        return $pool;
    }
}