const webpack = require('webpack');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const ExtractTextPlugin = require("extract-text-webpack-plugin");
const bootstrapEntryPoints = require("./webpack.bootstrap.config");
// const path = rquire('path');
// const glob = require('glob-all');
// const PurifyCSSPlugin = require('purifycss-webpack');

module.exports = env => {
    
    /* variables */
    const ENV = env || {}
    const isProd = ENV.production;    
    const htmlPath = isProd ? __dirname + "/" : __dirname + "/dist/";    
    const cssUse = {
        dev: ['style-loader', 'css-loader?soruceMap', 'sass-loader'],
        prod: ExtractTextPlugin.extract({
            fallback: "style-loader",
            use: ['css-loader', 'sass-loader']
        })
    }                
    const bootstrapConfig = isProd ? bootstrapEntryPoints.prod : bootstrapEntryPoints.dev;

    /* config */
    return {
        entry: {
            app: __dirname + '/src/app.js',
            bootstrap: bootstrapConfig
        },    

        output: {
            path: __dirname + '/dist/',
            filename: '[name].bundle.js'
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
                    test: /\.scss$/,
                    use: isProd ? cssUse.prod : cssUse.dev
                },                
                {
                    test: /\.(png|jpg|gif)$/,
                    use: {
                        loader: 'file-loader',
                        options: {
                            name: '[path][name].[ext]?[hash]'
                        }  
                    }                    
                },
                { 
                    test: /\.(woff2?|svg)$/, 
                    loader: 'url-loader?limit=10000&name=fonts/[name].[ext]' 
                },
                { 
                    test: /\.(ttf|eot)$/, 
                    loader: 'file-loader?name=fonts/[name].[ext]' 
                },
                { 
                    test: /bootstrap-sass[\/\\]assets[\/\\]javascripts[\/\\]/, 
                    loader: 'imports-loader?jQuery=jquery' 
                },
            ]        
        },

        plugins: [
            new HtmlWebpackPlugin({
                title: "helloworld",
                template: __dirname + '/src/template/index.html', // 가져올 템플릿 경로
                filename: htmlPath + "index.html", // 최종 생성 경로
                minify: {
                    collapseWhitespace: false //isProd
                }, // minify html
                hash: true // auto increase script version 
            }),
            new ExtractTextPlugin({
                filename: "css/[name].css",
                allChunks: true,
                publicPath: __dirname + "/dist/",
                disable: !isProd 
            }),                    
            new webpack.HotModuleReplacementPlugin(),
            new webpack.NamedModulesPlugin(),
            // new PurifyCSSPlugin({
            //     paths: glob.sync([
            //         path.join(__dirname, 'src/template/*.html'),
            //         path.join(__dirname, 'src/*.js')
            //     ]),
            //     minify: isProd
            // })
        ]
    }
};