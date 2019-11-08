const path = require('path');
const webpack = require('webpack');

module.exports = {
    externals: {
        "jquery": "jQuery"
    },
    resolve: {
        extensions: ['.js', '.json'],
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
            '>': path.resolve(__dirname, 'resources/sass'),
        },
    },
};
