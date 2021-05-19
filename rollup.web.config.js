const useProduction = ['production', 'prod', 'p'].includes(process.env.stage || '');
const pkg = require("./package.json");

const path = require('path');

import resolve from '@rollup/plugin-node-resolve';
import alias from '@rollup/plugin-alias';
import replace from '@rollup/plugin-replace';
import babel from '@rollup/plugin-babel';
import commonjs from '@rollup/plugin-commonjs';
import nodePolyfills from 'rollup-plugin-node-polyfills';

import less from 'rollup-plugin-less';
import lessPluginSvg from 'less-plugin-inline-svg';
import lessPluginClean from 'less-plugin-clean-css';

import { uglify } from 'rollup-plugin-uglify';

export default {
    input: './resources/js/web.js',

    output: {
        file: './static/js/web.bundle.js',
        name: 'wpPoiMap',
        format: 'iife',
    },
    plugins: [
        resolve({
            browser: true
        }),
        commonjs({
            include: 'node_modules/**',
        }),
        alias({
            entries: [
                {
                    find: '~',
                    replacement: path.resolve(__dirname, './resources/js')
                }
            ]
        }),
        babel({
            exclude: ['node_modules/**'],
            babelHelpers: 'bundled'
        }),
        less({
            output: './static/css/web.bundle.css',
            option: {
                plugins: [
                    new lessPluginSvg({
                        base64: true
                    }),
                    useProduction ? new lessPluginClean({
                        advanced: false
                    }) : ''],
                paths: [
                    path.resolve(__dirname, 'node_modules'),
                ]
            }
        }),
        nodePolyfills(),
        replace({
            VERSION: String(pkg.version),
            'process.env.NODE_ENV': JSON.stringify('development')
        }),
        (useProduction && uglify())
    ]
};