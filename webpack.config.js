const path = require('path')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const defaultConfig = require('@wordpress/scripts/config/webpack.config')

const isProduction = process.env.NODE_ENV === 'production'
const devtoolSetting = isProduction ? false : 'source-map'

// Common Loader Rules
const commonRules = [
    {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        loader: 'babel-loader',
    },
    {
        test: /\.(sass|scss|css)$/,
        use: [
            MiniCssExtractPlugin.loader,
            'css-loader',
            'postcss-loader',
            'sass-loader',
        ],
    },
    {
        test: /\.(png|jpg|jpeg|gif|svg)$/i,
        type: 'asset/resource',
        generator: {
            filename: 'images/[name][ext]',
        },
    },
]

// WordPress Block Editor Configuration (Extends @wordpress/scripts default)
const wpConfig = {
    ...defaultConfig,
    entry: {
        ...defaultConfig.entry(),
        index: path.resolve(process.cwd(), 'src/block', 'index.js'),
    },
}

// Public Assets Configuration
const publicConfig = {
    mode: isProduction ? 'production' : 'development',
    devtool: devtoolSetting,
    entry: {
        shortcode: './src/public/js/shortcode.js',
        scripts: './src/public/script.js',
    },
    output: {
        filename: 'public/js/[name].min.js',
        path: path.resolve(__dirname, 'dist'),
        clean: false,
    },
    module: {rules: commonRules},
    plugins: [
        new MiniCssExtractPlugin({
            filename: 'public/css/style.min.css',
        }),
    ],
}

// Admin / Backend Assets Configuration
const backendConfig = {
    mode: isProduction ? 'production' : 'development',
    devtool: devtoolSetting,
    entry: {
        vendors: './src/admin/vendors.js',
        editor: './src/admin/editor.js',
        scripts: './src/admin/scripts.js',
    },
    output: {
        filename: 'admin/js/[name].min.js',
        path: path.resolve(__dirname, 'dist'),
        clean: false,
    },
    module: {rules: commonRules},
    plugins: [
        new MiniCssExtractPlugin({
            filename: 'admin/css/style.min.css',
        }),
    ],
}

// Zoom Meeting SDK Configuration
//
// @zoom/meetingsdk ships a UMD build that externalises react, redux and
// redux-thunk. Declaring them as webpack `externals` therefore required those
// globals to be loaded first, but the emitted files were never written to
// dist/vendor/zoom, so the bundle died with "React is not defined". Bundling
// them keeps this a single self-contained script with no load-order contract.
//
// The entry is split in two so the ~5.5MB SDK payload is only fetched when the
// visitor actually commits to joining:
//   jvb-bootstrap  - small, no SDK import, drives the join form
//   zoom-meeting   - imports the SDK, exposes window.VczapiMeeting
// The entry names below MUST match the filenames requested by
// \Codemanas\VczApi\Browser\Assets. They previously disagreed
// (websdk-router/websdk-client here, jvb-bootstrap/zoom-meeting in PHP), so
// both requests 404'd and the join page never initialised.
const webSDKConfig = {
    mode: 'production',
    cache: false,
    devtool: false,
    entry: {
        'jvb-bootstrap': './src/websdk/bootstrap.js',
        'jvb-client': './src/websdk/client.js',
    },
    output: {
        filename: 'vendor/zoom/websdk/[name].bundle.js',
        path: path.resolve(__dirname, 'dist'),
        clean: false,
        globalObject: 'self',
    },
    module: {
        rules: [
            {
                test: /\.jsx?$/,
                exclude: /node_modules/,
                loader: 'babel-loader',
            },
            {
                test: /\.css$/i,
                use: ['style-loader', 'css-loader'],
            }
        ],
    },
    resolve: {
        extensions: ['.js', '.jsx'],
    },
    optimization: {
        splitChunks: false,
        runtimeChunk: false,
    },
    performance: {
        hints: false,
    },
    target: ['web', 'es2017'],
}

const modules = [wpConfig, publicConfig, backendConfig, webSDKConfig]

module.exports = modules