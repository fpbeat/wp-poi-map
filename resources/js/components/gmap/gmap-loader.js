import {ArrayUtil, ElementUtil, GenericUtil, StringUtil} from '~/lib/utils';

import EventEmitter from 'events';

export default class extends EventEmitter {

    options = {
        apiKey: '',
        language: 'uk',
        endpoind: '//maps.googleapis.com/maps/api/js?key={key}&language={lang}&callback={callback}',
    }

    constructor(options = {}) {
        super();

        GenericUtil.setOptions(this, options);
        this.bootstrap();
    }

    bootstrap() {
        this.load()
    }

    formatEndpoint() {
        return StringUtil.substitute(this.options.endpoind, {
            key: this.options.apiKey,
            lang: this.options.language,
            callback: this.createCallback()
        })
    }

    load() {
        const injector = document.head || document.body || document.lastChild;

        ElementUtil.createAndInject('script', {
            type: 'text/javascript',
            src: this.formatEndpoint()
        }, injector);
    }

    createCallback() {
        const funcName = 'gmap_' + StringUtil.uuid();
        window[funcName] = this.emit.bind(this, 'loaded');

        return funcName;
    }
}