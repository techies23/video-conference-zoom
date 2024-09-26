const path = require('path')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const UnminifiedWebpackPlugin = require('unminified-webpack-plugin')
const defaultConfig = require('@wordpress/scripts/config/webpack.config')

const isProduction = process.env.NODE_ENV === 'production'
const rules = {
    rules: [
        // js babelization
        {
            test: /\.(js|jsx)$/,
            exclude: /node_modules/,
            loader: 'babel-loader'
        },
        // sass compilation
        {
            test: /\.(sass|scss)$/,
            use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader', 'postcss-loader']
        },
        // loader for images and icons (only required if css references image files)
        {
            test: /\.(png|jpg|gif)$/,
            type: 'asset/resource',
            generator: {
                filename: './assets/images/[name][ext]',
            }
        },
    ]
}

const plugins = (filePath) => {
    return [
        new UnminifiedWebpackPlugin(),
        // css extraction into dedicated file
        new MiniCssExtractPlugin({
            filename: filePath
        }),
    ]
}

//Public configs
const publicConfig = {
    entry: {
        'join-via-browser': './src/public/js/join-via-browser.js',
        public: './src/public/js/public.js',
        shortcode: './src/public/js/shortcode.js'
    },
    output: {
        filename: './assets/public/js/[name].min.js',
        path: path.resolve(__dirname)
    },
    module: rules,
    plugins: plugins('./assets/public/css/style.min.css'),
}

//Admin Configs
const backendConfig = {
    entry: {
        'script': './src/admin/js/script.js',
    },
    output: {
        filename: './assets/admin/js/[name].min.js',
        path: path.resolve(__dirname)
    },
    module: rules,
    plugins: plugins('./assets/admin/css/style.min.css'),
}

//Default WP configs
const wp = {
    ...defaultConfig,
    entry: {
        ...defaultConfig.entry,
        index: path.resolve(process.cwd(), 'src/block', 'index.js'),
    }
}

let modules = [wp, publicConfig, backendConfig]
if (isProduction) {
    const webSDKConfig = {
        cache: false,
        entry: {
            'zoom-meeting': {
                import: './src/public/vendor/zoom-meeting.js',
                dependOn: 'websdk',
            },
            'embed-zoom-meeting': {
                import: './src/public/vendor/embed-zoom-meeting.js',
                dependOn: 'websdk',
            },
            'websdk': '@zoom/meetingsdk'
        },
        output: {
            filename: './assets/vendor/zoom/websdk/[name].bundle.js',
            path: path.resolve(__dirname)
        },
        module: {
            rules: [
                {
                    test: /\.jsx?$/,
                    exclude: /node_modules/,
                    loader: 'babel-loader'
                },
                {
                    test: /\.css$/i,
                    use: ['style-loader', 'css-loader']
                },
                {
                    test: /\.(jpg|png|svg)$/,
                    type: 'asset'
                }
            ]
        },
        resolve: {
            extensions: ['.js', '.jsx']
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
                var: '_'
            }
        },
        target: 'web',
        mode: 'production'
    }

    modules.push(webSDKConfig)
}

module.exports = modules