import {defineConfig} from 'rollup';
import alias from '@rollup/plugin-alias';
import {babel} from '@rollup/plugin-babel';
import commonjs from '@rollup/plugin-commonjs';
import resolve from '@rollup/plugin-node-resolve';
import typescript from '@rollup/plugin-typescript';
import replace from '@rollup/plugin-replace';
import terser from '@rollup/plugin-terser';
import postcss from 'rollup-plugin-postcss';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
import copyFontAwesome from './cp-fontawesome.ts';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename)
const outDir = path.resolve(__dirname, 'plugin/assets');
const isProduction = process.env.NODE_ENV === 'production';

export default defineConfig([
    // CastCrew admin metaboxes (React)
    {
        input: path.resolve(__dirname, 'resources/js/admin/castcrew-metaboxes.tsx'),
        output: {
            file: path.resolve(outDir, 'admin/curtain-call-wp-castcrew-metaboxes.js'),
            format: 'iife',
            sourcemap: true,
            globals: {
                '@wordpress/api-fetch': 'wp.apiFetch',
                '@wordpress/block-editor': 'wp.blockEditor',
                '@wordpress/blocks': 'wp.blocks',
                '@wordpress/components': 'wp.components',
                '@wordpress/compose': 'wp.compose',
                '@wordpress/data': 'wp.data',
                '@wordpress/data-controls': 'wp.dataControls',
                '@wordpress/edit-post': 'wp.editPost',
                '@wordpress/editor': 'wp.editor',
                '@wordpress/element': 'wp.element',
                '@wordpress/i18n': 'wp.i18n',
                '@wordpress/plugins': 'wp.plugins',
                '@wordpress/primitives': 'wp.primitives',
                'jquery': 'jQuery',
                'react': 'wp.element',
                'react-dom': 'wp.element',
            },
        },
        external: [
            "@wordpress/api-fetch",
            "@wordpress/block-editor",
            "@wordpress/blocks",
            "@wordpress/components",
            "@wordpress/compose",
            "@wordpress/data",
            "@wordpress/data-controls",
            "@wordpress/edit-post",
            "@wordpress/editor",
            "@wordpress/element",
            "@wordpress/i18n",
            "@wordpress/plugins",
            "@wordpress/primitives",
            "jquery",
            "react",
            "react-dom"
        ],
        plugins: [
            alias({
                entries: [
                    {find: '@js', replacement: path.resolve(__dirname, 'resources/js')},
                    {find: '@styles', replacement: path.resolve(__dirname, 'resources/styles')},
                    {find: '@images', replacement: path.resolve(__dirname, 'resources/images')},
                ]
            }),
            resolve({
                extensions: ['.js', '.jsx', '.ts', '.tsx', '.svg'],
            }),
            commonjs(),
            typescript({
                tsconfig: './tsconfig.web.json',
                composite: false,
            }),
            babel({
                babelHelpers: 'bundled',
                presets: ['@babel/preset-react'],
                extensions: ['.js', '.jsx', '.ts', '.tsx'],
                plugins: [
                    ['@babel/plugin-transform-react-jsx', {
                        pragma: 'wp.element.createElement',
                        pragmaFrag: 'wp.element.Fragment',
                    }]
                ],
            }),
            replace({
                'process.env.NODE_ENV': JSON.stringify(isProduction ? 'production' : 'development'),
                preventAssignment: true,
            }),
            ...(isProduction ? [terser()] : []),
        ],
    },
    // Production admin metaboxes (React)
    {
        input: path.resolve(__dirname, 'resources/js/admin/production-metaboxes.tsx'),
        output: {
            file: path.resolve(outDir, 'admin/curtain-call-wp-production-metaboxes.js'),
            format: 'iife',
            sourcemap: true,
            globals: {
                '@wordpress/api-fetch': 'wp.apiFetch',
                '@wordpress/block-editor': 'wp.blockEditor',
                '@wordpress/blocks': 'wp.blocks',
                '@wordpress/components': 'wp.components',
                '@wordpress/compose': 'wp.compose',
                '@wordpress/data': 'wp.data',
                '@wordpress/data-controls': 'wp.dataControls',
                '@wordpress/edit-post': 'wp.editPost',
                '@wordpress/editor': 'wp.editor',
                '@wordpress/element': 'wp.element',
                '@wordpress/i18n': 'wp.i18n',
                '@wordpress/plugins': 'wp.plugins',
                '@wordpress/primitives': 'wp.primitives',
                'jquery': 'jQuery',
                'react': 'wp.element',
                'react-dom': 'wp.element',
            },
        },
        external: [
            "@wordpress/api-fetch",
            "@wordpress/block-editor",
            "@wordpress/blocks",
            "@wordpress/components",
            "@wordpress/compose",
            "@wordpress/data",
            "@wordpress/data-controls",
            "@wordpress/edit-post",
            "@wordpress/editor",
            "@wordpress/element",
            "@wordpress/i18n",
            "@wordpress/plugins",
            "@wordpress/primitives",
            "jquery",
            "react",
            "react-dom"
        ],
        plugins: [
            alias({
                entries: [
                    {find: '@js', replacement: path.resolve(__dirname, 'resources/js')},
                    {find: '@styles', replacement: path.resolve(__dirname, 'resources/styles')},
                    {find: '@images', replacement: path.resolve(__dirname, 'resources/images')},
                ]
            }),
            resolve({
                extensions: ['.js', '.jsx', '.ts', '.tsx'],
            }),
            commonjs(),
            typescript({
                tsconfig: './tsconfig.web.json',
                composite: false,
            }),
            babel({
                babelHelpers: 'bundled',
                presets: ['@babel/preset-react'],
                extensions: ['.js', '.jsx', '.ts', '.tsx'],
                plugins: [
                    ['@babel/plugin-transform-react-jsx', {
                        pragma: 'wp.element.createElement',
                        pragmaFrag: 'wp.element.Fragment',
                    }]
                ],
            }),
            replace({
                'process.env.NODE_ENV': JSON.stringify(isProduction ? 'production' : 'development'),
                preventAssignment: true,
            }),
            ...(isProduction ? [terser()] : []),
        ],
    },
    // Admin sidebar
    {
        input: path.resolve(__dirname, 'resources/js/admin/sidebar.tsx'),
        output: {
            file: path.resolve(outDir, 'admin/curtain-call-wp-sidebar.js'),
            format: 'iife',
            sourcemap: true,
            globals: {
                '@wordpress/api-fetch': 'wp.apiFetch',
                '@wordpress/block-editor': 'wp.blockEditor',
                '@wordpress/blocks': 'wp.blocks',
                '@wordpress/components': 'wp.components',
                '@wordpress/compose': 'wp.compose',
                '@wordpress/data': 'wp.data',
                '@wordpress/data-controls': 'wp.dataControls',
                '@wordpress/edit-post': 'wp.editPost',
                '@wordpress/editor': 'wp.editor',
                '@wordpress/element': 'wp.element',
                '@wordpress/i18n': 'wp.i18n',
                '@wordpress/plugins': 'wp.plugins',
                '@wordpress/primitives': 'wp.primitives',
                'jquery': 'jQuery',
                'react': 'wp.element',
                'react-dom': 'wp.element',
            },
        },
        external: [
            "@wordpress/api-fetch",
            "@wordpress/block-editor",
            "@wordpress/blocks",
            "@wordpress/components",
            "@wordpress/compose",
            "@wordpress/data",
            "@wordpress/data-controls",
            "@wordpress/edit-post",
            "@wordpress/editor",
            "@wordpress/element",
            "@wordpress/i18n",
            "@wordpress/plugins",
            "@wordpress/primitives",
            "jquery",
            "react",
            "react-dom"
        ],
        plugins: [
            alias({
                entries: [
                    {find: '@js', replacement: path.resolve(__dirname, 'resources/js')},
                    {find: '@styles', replacement: path.resolve(__dirname, 'resources/styles')},
                    {find: '@images', replacement: path.resolve(__dirname, 'resources/images')},
                ]
            }),
            resolve({
                extensions: ['.js', '.jsx', '.ts', '.tsx', '.svg'],
            }),
            commonjs(),
            typescript({
                tsconfig: './tsconfig.web.json',
                composite: false,
            }),
            babel({
                babelHelpers: 'bundled',
                presets: ['@babel/preset-react'],
                extensions: ['.js', '.jsx', '.ts', '.tsx'],
                plugins: [
                    ['@babel/plugin-transform-react-jsx', {
                        pragma: 'wp.element.createElement',
                        pragmaFrag: 'wp.element.Fragment',
                    }]
                ],
            }),
            replace({
                'process.env.NODE_ENV': JSON.stringify(isProduction ? 'production' : 'development'),
                preventAssignment: true,
            }),
            ...(isProduction ? [terser()] : []),
        ],
    },
    // Admin styles
    {
        input: path.resolve(__dirname, 'resources/styles/admin/plugin.css'),
        output: {
            file: path.resolve(outDir, 'admin/curtain-call-wp-admin.css'),
        },
        plugins: [
            postcss({
                extract: true,
                minimize: isProduction,
                sourceMap: true,
            }),
        ],
    },
    // Frontend styles
    {
        input: path.resolve(__dirname, 'resources/styles/frontend/plugin.css'),
        output: {
            file: path.resolve(outDir, 'frontend/curtain-call-wp-frontend.css'),
        },
        plugins: [
            postcss({
                extract: true,
                minimize: isProduction,
                sourceMap: true,
            }),
        ],
    },
    // FontAwesome
    {
        input: path.resolve(__dirname, 'resources/styles/fontawesome/fontawesome.css'),
        output: {
            file: path.resolve(outDir, 'frontend/fontawesomefree.css'),
        },
        plugins: [
            postcss({
                extract: true,
                minimize: isProduction,
                sourceMap: true,
            }),
            copyFontAwesome({outDir}),
        ],
    },
]);
