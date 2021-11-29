const toml = require('toml');
const path = require('path');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
    ...defaultConfig,
    entry: {
        ...defaultConfig.entry,
        index: path.resolve(process.cwd(), 'src/block', 'index.js'),
    }
    // module: {
    //     ...defaultConfig.module,
    //     // rules: [
    //     //     ...defaultConfig.module.rules,
    //     //     {
    //     //         test: /.toml/,
    //     //         type: 'json',
    //     //         parser: {
    //     //             parse: toml.parse,
    //     //         },
    //     //     },
    //     // ],
    // },
};