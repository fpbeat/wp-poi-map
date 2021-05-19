import '../styles/web.less';

import Builder from './components/builder';

export default {
    version: 'VERSION',

    builder: (container, options) => new Builder(container, options)
};