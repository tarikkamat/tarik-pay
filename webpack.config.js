const path = require('path');
const defaults = require('@wordpress/scripts/config/webpack.config');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
    ...defaults,
    externals: {
        react: 'React',
        'react-dom': 'ReactDOM',
    },
    output: {
        path: path.resolve(__dirname, 'assets/blocks/woocommerce'), // Çıkış dizini
        filename: 'blocks.js', // 'block.js' yerine 'blocks.js' olmalı
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-env', '@babel/preset-react'],
                    },
                },
            },
            {
                test: /\.css$/,
                use: [
                    MiniCssExtractPlugin.loader, // CSS'i ayrı dosyaya çıkartır
                    'css-loader',
                    'postcss-loader',
                ],
            },
        ],
    },
    plugins: [
        ...defaults.plugins,
        new MiniCssExtractPlugin({
            filename: 'index.css', // Çıkacak CSS dosyasının adı
        }),
    ],
};