<?php

namespace WpPoiMap\Layers;

use WpPoiMap\Repositories\PostMetaRepository;
use WpPoiMap\Utils\{Arr, File};

class ImageLayer
{
    /**
     * @var string
     */
    private const WP_IMAGE_SIZE = 'medium';

    /**
     * @var PostMetaRepository
     */
    private $postMetaRepository;

    /**
     * @var array
     */
    private $uploadDirectory;

    /**
     * @var array
     */
    private $images;

    /**
     * ImageLayer constructor.
     */
    public function __construct()
    {
        $this->uploadDirectory = wp_get_upload_dir();
    }

    /**
     * @param array $posts
     * @return array
     */
    public function toArray(array $posts): array
    {
        $this->bootstrap($posts);

        $pool = [];
        foreach ($posts as $post) {
            $pool[$post->ID]['image'] = $this->images[$post->ID];
        }

        return $pool;
    }

    /**
     * @param array $posts
     */
    private function bootstrap(array $posts): void
    {
        $ids = Arr::column($posts, 'ID');
        $this->postMetaRepository = new PostMetaRepository($ids);

        $this->images = $this->getImages();
    }

    /**
     * @param \stdClass $image
     * @return string|null
     */
    private function getSingleImage(\stdClass $image): ?string
    {
        $dirname = pathinfo($image->meta_value['file'], \PATHINFO_DIRNAME);

        if (is_array($image->meta_value)) {
            $size = $image->meta_value['sizes'][self::WP_IMAGE_SIZE];

            if (is_array($size)) {
                $path = trailingslashit($this->uploadDirectory['basedir']) . trailingslashit($dirname) . $size['file'];

                if (file_exists($path) && File::fileIs('image', ['mime' => $size['mime-type']])) {
                    return trailingslashit($this->uploadDirectory['baseurl']) . trailingslashit($dirname) . $size['file'];
                }
            }
        }

        return NULL;
    }

    /**
     * @return array
     */
    private function getImages(): array
    {
        $pool = [];
        foreach ($this->postMetaRepository->get() as $image) {
            $pool[$image->post_id] = $this->getSingleImage($image);
        }

        return $pool;
    }
}