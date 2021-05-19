import {StringUtil} from '~/lib/utils';

import EventEmitter from 'events';

export default class extends EventEmitter {

    getCenter() {
        const center = String(this.options.settings['mapCenter'] || this.options.map.center);
        const parts = center.split(',');

        if (parts.length === 2) {
            return new google.maps.LatLng(...parts.map(parseFloat));
        }

        return null;
    }

    getZoom() {
        return parseInt(this.options.settings['mapZoom'] || this.options.map.zoom, 10);
    }

    getType() {
        return new google.maps.StyledMapType([
            {
                "featureType": "poi.business",
                "stylers": [
                    {
                        "visibility": "off"
                    }
                ]
            }
        ]);

        return String(this.options.settings['mapType'] || this.options.map.type);
    }

    getMap() {
        let map = new google.maps.Map(this.elements.canvas, {
            center: this.getCenter(),
            zoom: this.getZoom(),
            mapTypeId: 'styled_map',
            controlSize: 36,
            mapTypeControl: false,
            streetViewControl: false,
            scaleControl: true
        });

        map.mapTypes.set('styled_map', this.getType());
        map.setMapTypeId('styled_map');

        return map;
    }

    createMarker(param) {
        const image = {
            url: StringUtil.substitute(this.options.iconPathTemplate, {
                base: this.options.iconsPath,
                icon: param.icon
            }),
            origin: new google.maps.Point(0, 0),
            anchor: new google.maps.Point(0, 32),
            scaledSize: new google.maps.Size(32, 32)
        }

        return new google.maps.Marker({
            position: new google.maps.LatLng(param.geo.lat, param.geo.lng),
            title: param.name,
            icon: param.icon ? image : null,
            visible: false,
            map: this.map
        });
    }


    setObjectCenter() {
        for (let marker of this.markers) {
            if (marker.key === this.options.pageID) {

                this.pageMarker = marker.instance;
                this.makeCurrentBounced();

                this.map.panTo(marker.instance.getPosition());
            }
        }
    }

    fitAllObjects() {
        let bounds = new window.google.maps.LatLngBounds();

        this.markers.forEach(param => {
            bounds.extend(param.instance.getPosition());
        });

        bounds.isEmpty() || this.map.fitBounds(bounds);
    }

    setInfoContent(template, marker) {
        marker.setAnimation(null);

        this.infoWindow.setContent(`<div class="${this.options.classes.template}">` + template + "</div>");
        this.infoWindow.open(this.map, marker);
    }

    makeCurrentBounced() {
        if (this.pageMarker !== null) {
            this.pageMarker.setAnimation(google.maps.Animation.BOUNCE);
        }
    }

    toggleVisibility() {
        const types = this.getCheckedTypes();

        this.markers.forEach(param => {
            let visible = types.includes(param.type);

            param.instance.setVisible(visible);
        });
    }
}
