const webpack = require('webpack');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const ExtractTextPlugin = require("extract-text-webpack-plugin");

module.exports = env => {
    
    /* variables */
    const ENV = env || {}
    const isProd = ENV.production;    
    const htmlPath = isProd ? __dirname + "/" : __dirname + "/dist/";    
    const cssUse = {
        dev: ['style-loader', 'css-loader'],
        prod: ExtractTextPlugin.extract({
            fallback: "style-loader",
            use: "css-loader"        
        })
    }                

    /* config */
    return {
        entry: {
            app: __dirname + '/src/app.js'
        },    

        output: {
            path: __dirname + '/dist/',
            filename: 'bundle.js'
        },

        devServer: {
            inline: true,
            port: 7777, 
            contentBase: __dirname + '/dist/', // 실행 파일들이 위치한 path
            stats: 'errors-only', // 재생성 시 에러 메세지만 보기        
            hot: true
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
                    use: isProd ? cssUse.prod : cssUse.dev
                }               
            ]        
        },

        plugins: [
            new HtmlWebpackPlugin({
                template: __dirname + '/src/template/index.html', // 가져올 템플릿 경로
                filename: htmlPath + "index.html", // 최종 생성 경로
                minify: {
                    collapseWhitespace: isProd
                }, // minify html
                hash: true // auto increase script version 
            }),
            new ExtractTextPlugin({
                filename: "app.css",
                allChunks: true,
                publicPath: __dirname + "/dist/",
                disable: !isProd 
            }),        
            new webpack.HotModuleReplacementPlugin(),
            new webpack.NamedModulesPlugin()
        ]
    }
};