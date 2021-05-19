export default {
    trim(string) {
        return String(string).replace(/^\s+|\s+$/g, '');
    },

    test(string, regexp) {
       return regexp.test(this.trim(string));
    },

    isEmpty(string) {
        return ['false', 'null', '', 'undefined', 'NaN'].includes(this.trim(string));
    },

    substitute(string, object, regexp) {
        return String(string).replace(regexp || (/\\?\{([^{}]+)\}/g), (match, name) => {
            if (match.charAt(0) === '\\') return match.slice(1);
            return (object[name] !== null) ? object[name] : '';
        });
    },
    
    lcFirst(string) {
        let first = string.charAt(0).toLowerCase();
        return first + string.substr(1, string.length - 1);
    },

    toCamelCase(string) {
        return String(string).replace(/-\D/g, (match) => match.charAt(1).toUpperCase());
    },

    sanitize(string) {
        return String(string).replace(/[^a-z0-9]/gi, '').toLowerCase();
    },

    hyphenate(string) {
        return this.lcFirst(String(string)).replace(/[A-Z]/g, match => {
            return ('-' + match.charAt(0).toLowerCase());
        });
    },

    uuid() {
        return Math.random().toString(36).substring(2);
    }
};