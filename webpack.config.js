const path = require('path')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const defaultConfig = require('@wordpress/scripts/config/webpack.config')

// Common Module Rules
const commonRules = [
    {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        loader: 'babel-loader',
    },
    {
        test: /\.(sass|scss)$/,
        use: [MiniCssExtractPlugin.loader, 'css-loader', 'postcss-loader', 'sass-loader'],
    },
    {
        test: /\.(png|jpg|gif|svg)$/,
        type: 'asset/resource',
        generator: {
            filename: 'assets/images/[name][ext]',
        },
    },
]

/**
 * Helper to generate plugin configuration per module.
 */
const getPlugins = (cssOutputPath) => [
    new MiniCssExtractPlugin({
        filename: cssOutputPath,
    }),
]

const wpConfig = {
    ...defaultConfig,
    entry: {
        ...defaultConfig.entry,
        index: path.resolve(process.cwd(), 'src/block', 'index.js'),
    },
}

const publicConfig = {
    devtool: process.env.NODE_ENV ? false : 'source-map',
    entry: {
        'join-via-browser': './src/public/js/join-via-browser.js',
        public: './src/public/js/public.js',
        shortcode: './src/public/js/shortcode.js',
        booking: './src/public/js/booking.js',
    },
    output: {
        filename: 'assets/public/js/[name].min.js',
        path: path.resolve(__dirname),
    },
    module: {rules: commonRules},
    plugins: getPlugins('assets/public/css/style.min.css'),
}

const backendConfig = {
    devtool: process.env.NODE_ENV ? false : 'source-map',
    entry: {
        script: './src/admin/js/script.js',
        main: './src/admin/main.js',
    },
    output: {
        filename: 'assets/admin/js/[name].min.js',
        path: path.resolve(__dirname),
    },
    module: {rules: commonRules},
    plugins: getPlugins('assets/admin/css/style.min.css'),
}

const modules = [wpConfig, publicConfig, backendConfig]
if (process.env.NODE_ENV === 'production') {
    const webSDKConfig = {
        cache: false,
        entry: {
            'zoom-meeting': {
                import: './src/public/vendor/zoom-meeting.js',
                dependOn: 'websdk',
            },
            websdk: '@zoom/meetingsdk',
        },
        output: {
            filename: 'assets/vendor/zoom/websdk/[name].bundle.js',
            path: path.resolve(__dirname),
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
                },
                {
                    test: /\.(jpg|png|svg)$/,
                    type: 'asset',
                },
            ],
        },
        resolve: {
            extensions: ['.js', '.jsx'],
        },
        externals: {
            'babel-polyfill': 'babel-polyfill',
            react: 'React',
            'react-dom': 'ReactDOM',
            redux: 'Redux',
            'redux-thunk': 'ReduxThunk',
            lodash: {
                commonjs: 'lodash',
                amd: 'lodash',
                root: '_',
                var: '_',
            },
        },
        target: 'web',
        mode: 'production',
    }

    modules.push(webSDKConfig)
}

module.exports = modules