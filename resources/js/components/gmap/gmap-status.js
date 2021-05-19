import {GenericUtil, ElementUtil} from '~/lib/utils';

export default class {

    options = {
        classes: {
            loading: 'wp-poi-map__loading',
            error: 'wp-poi-map__loading-error'
        },

        texts: {
            loading: 'Loading data',
            error: 'Error while loading'
        }
    }

    constructor(container, options) {
        GenericUtil.setOptions(this, options);

        this.loader = ElementUtil.createAndInject('DIV', {}, container);
    }

    loading() {
        ElementUtil.set(this.loader, {
            class: this.options.classes.loading,
            html: this.options.texts.loading
        });
    }

    error() {
        ElementUtil.set(this.loader, {
            class: this.options.classes.error,
            html: this.options.texts.error
        });
    }

    remove() {
        ElementUtil.destroy(this.loader);
    }
}