const path = require('path');

module.exports = {
    externals: {
        jquery: "jQuery"
    },
    resolve: {
        extensions: ['.js', '.json'],
        alias: {
            '@js': path.resolve(__dirname, 'resources/js'),
            '@sass': path.resolve(__dirname, 'resources/sass'),
        },
    },
};
