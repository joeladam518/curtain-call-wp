const path = require('path');
const webpack = require('webpack');

module.exports = {
    externals: {
        "jquery": "jQuery"
    },
    resolve: {
        extensions: ['.js', '.json'],
        alias: {
            '@js': path.resolve(__dirname, 'resources/js'),
            '@sass': path.resolve(__dirname, 'resources/sass'),
        },
    },
};
