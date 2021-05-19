import {ElementUtil, GenericUtil, ObjectUtil} from '~/lib/utils';

import Gmap from './gmap/gmap';
import GMapLoader from './gmap/gmap-loader';
import GMapStatus from './gmap/gmap-status';

import axios from "axios";

export default class extends Gmap {

    options = {
        map: {
            center: '49.27837, 23.50618',
            zoom: 10,
            type: 'roadmap'
        },

        iconPathTemplate: '{base}icons/{icon}',
        pageID: 0,

        classes: {
            loading: 'wp-poi-map__loading',
            template: 'wp-poi-map__popup'
        }
    };

    markers = [];
    pageMarker = null;

    constructor(container, options = {}) {
        super();

        GenericUtil.setOptions(this, options);
        this.bootstrap(container);

        this.load();
    }

    bootstrap(container) {
        this.container = document.querySelector(container);
        this.elements = ElementUtil.parseElements(this.container, this.options.selectors);

        for (let element of Object.values(this.elements.types)) {
            element.addEventListener('change', this.update.bind(this));
        }
    }

    getCheckedTypes() {
        let types = [];
        for (let [name, element] of Object.entries(this.elements.types)) {
            if (element.checked) {
                types.push(name);
            }
        }

        return types;
    }

    update() {
        this.infoWindow.close();

        this.toggleVisibility();

        if (this.options.settings['mapBehavior'] === 'fit') {
            this.fitAllObjects();
        }
    }

    load() {
        new GMapLoader({
            apiKey: String(this.options.settings['mapApiKey']),
            language: this.options.language,
            onLoaded: this.start.bind(this)
        });
    }

    start() {
        this.map = this.getMap();

        this.infoWindow = new google.maps.InfoWindow();

        this.infoWindow.addListener('closeclick', this.makeCurrentBounced.bind(this));
        this.infoWindow.addListener('position_changed', this.makeCurrentBounced.bind(this));

        this.status = new GMapStatus(this.elements.canvas, Object.assign({}, {
            texts: this.options.texts.state
        }));
        this.status.loading();

        this.loadData();
    }

    loadData() {
        axios({
            method: 'post',
            url: this.options.ajaxPath,

            data: ObjectUtil.toQueryString({
                action: this.options.token + '_data',
                nonce: this.options.nonce
            })
        }).then(response => {
            if (response.status === 200 && response.data) {
                return this.process(response.data);
            }

            throw new Error;
        }).catch(e => {
            console.log('Error during loading POI data. ' + e.message);

            this.status.error();
        });
    }

    process(data) {
        for (let [key, param] of Object.entries(data)) {
            const marker = this.createMarker(param);

            marker.addListener('click', this.setInfoContent.bind(this, param.template, marker));

            this.markers.push({
                key: parseInt(key, 10),
                type: param.type,
                instance: marker
            });
        }

        this.update();

        this.status.remove();

        if (this.options.settings['mapBehavior'] === 'center') {
            this.setObjectCenter();
        }
    }
}