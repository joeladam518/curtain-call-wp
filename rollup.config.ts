import { defineConfig } from 'rollup';
import { babel } from '@rollup/plugin-babel';
import commonjs from '@rollup/plugin-commonjs';
import resolve from '@rollup/plugin-node-resolve';
import typescript from '@rollup/plugin-typescript';
import replace from '@rollup/plugin-replace';
import postcss from 'rollup-plugin-postcss';
import path from 'node:path';
import fs from 'fs-extra';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename)
const outDir = path.resolve(__dirname, 'plugin/assets');

function copyFontAwesome() {
    return {
        name: 'copy-fontawesome',
        async writeBundle() {
            const src = path.resolve(__dirname, 'node_modules/@fortawesome/fontawesome-free/webfonts');
            const dest = path.resolve(outDir, 'fonts/fontawesome');

            if (fs.existsSync(src)) {
                await fs.ensureDir(dest);
                await fs.copy(src, dest);
            }
        },
    };
}

export default defineConfig([
    // Admin metaboxes
    {
        input: 'resources/js/admin/metaboxes.ts',
        output: {
            file: path.resolve(outDir, 'admin/curtain-call-wp-metaboxes.js'),
            format: 'iife',
            sourcemap: true,
            globals: {
                jquery: 'jQuery',
            },
        },
        external: ['jquery'],
        plugins: [
            resolve({
                browser: true,
            }),
            commonjs(),
            typescript({
                tsconfig: './tsconfig.web.json',
                composite: false,
            }),
            postcss({
                inject: true,
                minimize: true,
            }),
        ],
    },
    // Admin sidebar
    {
        input: 'resources/js/admin/sidebar.tsx',
        output: {
            file: path.resolve(outDir, 'admin/curtain-call-wp-sidebar.js'),
            format: 'iife',
            sourcemap: true,
            globals: {
                jquery: 'jQuery',
                'react': 'wp.element',
                'react-dom': 'wp.element',
                '@wordpress/element': 'wp.element',
                '@wordpress/blocks': 'wp.blocks',
                '@wordpress/block-editor': 'wp.blockEditor',
                '@wordpress/components': 'wp.components',
                '@wordpress/compose': 'wp.compose',
                '@wordpress/data': 'wp.data',
                '@wordpress/i18n': 'wp.i18n',
                '@wordpress/plugins': 'wp.plugins',
                '@wordpress/api-fetch': 'wp.apiFetch',
                '@wordpress/edit-post': 'wp.editPost',
                '@wordpress/editor': 'wp.editor',
            },
        },
        external: [
            'jquery',
            'react',
            'react-dom',
            '@wordpress/api-fetch',
            '@wordpress/block-editor',
            '@wordpress/blocks',
            '@wordpress/components',
            '@wordpress/compose',
            '@wordpress/data',
            '@wordpress/edit-post',
            '@wordpress/editor',
            '@wordpress/element',
            '@wordpress/i18n',
            '@wordpress/plugins',
        ],
        plugins: [
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
                'process.env.NODE_ENV': JSON.stringify('production'),
                preventAssignment: true,
            }),
        ],
    },
    // Admin styles
    {
        input: 'resources/styles/admin/plugin.css',
        output: {
            file: path.resolve(outDir, 'admin/curtain-call-wp-admin.css'),
        },
        plugins: [
            postcss({
                extract: true,
                minimize: true,
                sourceMap: true,
            }),
        ],
    },
    // Frontend styles
    {
        input: 'resources/styles/frontend/plugin.css',
        output: {
            file: path.resolve(outDir, 'frontend/curtain-call-wp-frontend.css'),
        },
        plugins: [
            postcss({
                extract: true,
                minimize: true,
                sourceMap: true,
            }),
        ],
    },
    // FontAwesome
    {
        input: 'resources/styles/fontawesome/fontawesome.css',
        output: {
            file: path.resolve(outDir, 'frontend/fontawesomefree.css'),
        },
        plugins: [
            postcss({
                extract: true,
                minimize: true,
                sourceMap: true,
            }),
            copyFontAwesome(),
        ],
    },
]);
