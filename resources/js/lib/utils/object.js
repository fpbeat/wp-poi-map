import StringUtil from './string';
import GenericUtil from './generic';

import extend from 'extend';

export default {
    getPath(obj, path, def = null) {
        let pathArray = StringUtil.stringToPath(path);
        let current = obj;
        for (var i = 0; i < pathArray.length; i++) {
            if (!current[pathArray[i]]) {
                return def;
            }

            current = current[pathArray[i]];
        }

        return current;
    },

    pick(keys, obj) {
        return keys.reduce((a, c) => ({...a, [c]: obj[c]}), {});
    },

    merge(...args) {
        return extend(...args);
    },

    map(object, fn) {
        return Object.keys(object).reduce((result, key) => {
            result[key] = fn(object[key]);
            return result;
        }, {});
    },

    toQueryString(object, base) {
        let queryString = [];

        Object.keys(object).forEach(key => {
            let value = object[key],
                result;

            if (base) {
                key = base + '[' + key + ']';
            }

            switch (GenericUtil.getType(value)) {
                case 'object':
                    result = this.toQueryString(value, key);
                    break;
                case 'array':
                    let qs = {};
                    value.forEach((val, i) => {
                        qs[i] = val;
                    });
                    result = this.toQueryString(qs, key);
                    break;
                default:
                    result = key + '=' + encodeURIComponent(value);
            }
            if (value !== null) {
                queryString.push(result);
            }
        });

        return queryString.join('&');
    }
};
