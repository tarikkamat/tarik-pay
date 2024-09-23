const path = require('path');
const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin');

module.exports = {
    entry: './src/index.js',
    output: {
        filename: 'blocks.js',
        path: path.resolve(__dirname, 'assets/blocks/woocommerce'),
    },
    module: {
        rules: [
            {
                test: /\.(js|jsx)$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-env', '@babel/preset-react']
                    }
                }
            }
        ]
    },
    resolve: {
        extensions: ['.js', '.jsx']
    },
    externals: {
        '@wordpress/element': 'wp.element',
        '@wordpress/components': 'wp.components',
        '@wordpress/html-entities': 'wp.htmlEntities'
    },
    plugins: [
        new DependencyExtractionWebpackPlugin()
    ]
};