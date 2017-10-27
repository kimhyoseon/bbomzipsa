
const HtmlWebpackPlugin = require('html-webpack-plugin');
const ExtractTextPlugin = require("extract-text-webpack-plugin");

module.exports = {
    entry: './src/index.js',

    output: {
        path: __dirname + '/dist/',
        filename: 'bundle.js'
    },

    devServer: {
        inline: true,
        port: 7777, 
        contentBase: __dirname + '/dist/', // 실행 파일들이 위치한 path
        stats: 'errors-only' // 재생성 시 에러 메세지만 보기
    },

    module: {
        rules: [
            {   
                test: /\.js$/,
                exclude: /(node_modules|bower_components)/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['env', 'react']
                    }
                }
            },
            {
                test: /\.css$/,
                use: ExtractTextPlugin.extract({
                    fallback: "style-loader",
                    use: "css-loader"
                })
            }               
        ]        
    },

    plugins: [
        new HtmlWebpackPlugin({
            template: 'src/index.html',
            filename: '../index.html',
            /*minify: {
                collapseWhitespace: true
            }, // minify html*/
            hash: true // auto increase script version 
        }),
        new ExtractTextPlugin({
            filename: "app.css"            
        }),
    ]
};