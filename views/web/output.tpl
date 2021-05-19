<div class="wp-poi-map-container">
    <div class="wp-poi-map__filter">
        {foreach $types as $type}
            <div>
                <input name="{$type.slug}" id="checkbox_{$type.slug}" type="checkbox" value="{$type.slug}"
                       {if in_array($type.slug, $selected)}checked{/if}/>
                <label for="checkbox_{$type.slug}">{$type.label}</label>
            </div>
        {/foreach}
    </div>

    <div class="wp-poi-map__canvas">
        <span>{t}Завантаження карти{/t}</span>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        wpPoiMap.builder('.wp-poi-map-container', {
            selectors: {
                canvas: '.wp-poi-map__canvas',
                types: '.wp-poi-map__filter input'
            },

            texts: {
                state: {
                    loading: '{t}Завантаження даних{/t}',
                    error: '{t}Помилка завантаження{/t}'
                }
            },

            token: '{$token}',
            language: '{$language}',

            data: {$data|json_encode},
            settings: {$settings|json_encode},

            ajaxPath: '{esc_url(admin_url('admin-ajax.php'))}',
            nonce: '{wp_create_nonce($token)}',

            iconsPath: '{$iconsPath}',
            pageID: {intval($pageID)}
        });
    });
</script>