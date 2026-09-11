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
            filename: 'dist/images/[name][ext]',
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
        'join-via-browser': './src/public/js/join-via-browser.js',
        public: './src/public/js/public.js',
        shortcode: './src/public/js/shortcode.js',
        booking: './src/public/js/booking.js',
    },
    output: {
        filename: 'dist/public/js/[name].min.js',
        path: path.resolve(__dirname, 'build'),
        clean: false,
    },
    module: {rules: commonRules},
    plugins: [
        new MiniCssExtractPlugin({
            filename: 'dist/public/css/[name].min.css',
        }),
    ],
}

// Admin / Backend Assets Configuration
const backendConfig = {
    mode: isProduction ? 'production' : 'development',
    devtool: devtoolSetting,
    entry: {
        script: './src/admin/js/script.js',
        main: './src/admin/main.js',
        editor: './src/admin/editor.js'
    },
    output: {
        filename: 'dist/admin/js/[name].min.js',
        path: path.resolve(__dirname, 'build'),
        clean: false,
    },
    module: {rules: commonRules},
    plugins: [
        new MiniCssExtractPlugin({
            filename: 'dist/admin/css/[name].min.css',
        }),
    ],
}

const modules = [wpConfig, publicConfig, backendConfig]

// Zoom WebSDK Production Configuration
if (isProduction) {
    const webSDKConfig = {
        mode: 'production',
        cache: false,
        devtool: false,
        entry: {
            'zoom-meeting': {
                import: './src/public/vendor/zoom-meeting.js',
                dependOn: 'websdk',
            },
            websdk: '@zoom/meetingsdk',
        },
        output: {
            filename: 'dist/vendor/zoom/websdk/[name].bundle.js',
            path: path.resolve(__dirname, 'build'),
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
            react: 'React',
            'react-dom': 'ReactDOM',
            redux: 'Redux',
            'redux-thunk': 'ReduxThunk',
            lodash: '_',
        },
        target: 'web',
    }

    modules.push(webSDKConfig)
}

module.exports = modules