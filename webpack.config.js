const fs = require('fs');
const path = require('path');
const webpack = require('webpack');

const CopyPlugin = require('copy-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require('terser-webpack-plugin');

module.exports = {
  mode: 'production',

  entry: {
    'main': './src/js/main.js',
    'styles': './src/scss/styles.scss',
    'easepick-customize': './src/scss/easepick-customize.scss'
  },

  output: {
    path: path.resolve(__dirname),
    filename: 'js/[name].min.js',
    assetModuleFilename: 'images/[hash][ext][query]'
  },

  module: {
    rules: [
      {
        test: /\.scss$/,
        exclude: /node_modules/,
        use: [
          MiniCssExtractPlugin.loader,
          'css-loader',
          'sass-loader'
        ]
      },
      {
        test: /\.(png|svg|jpg|jpeg|gif)$/i,
        type: 'asset/resource',
        generator: {
          filename: 'images/[name][ext]'
        }
      }
    ]
  },

  plugins: [
    new MiniCssExtractPlugin({
      filename: 'css/[name].min.css'
    }),
    new webpack.BannerPlugin({
      banner: fs.readFileSync('./LICENSE.txt', 'utf-8') + '\n;',
      test: /\.js$/,
      exclude: /node_modules/,
      raw: true
    }),
    new CopyPlugin({
      patterns: [
        { from: 'src/images', to: 'images' }
      ]
    }),
    {
      apply: (compiler) => {
        compiler.hooks.afterEmit.tap('ReplaceStringPlugin', (compilation) => {
          const outputPath = path.resolve(__dirname, 'js', 'main.min.js');

          if (fs.existsSync(outputPath)) {
            let bundleContent = fs.readFileSync(outputPath, 'utf-8');

            bundleContent = bundleContent.replace('n.className="previous-button unit",', 'n.className="previous-button unit",n.setAttribute("aria-label","Previous"),');
            bundleContent = bundleContent.replace('s.className="next-button unit",', 's.className="next-button unit",s.setAttribute("aria-label","Next"),');

            fs.writeFileSync(outputPath, bundleContent, 'utf-8');
          }
        });
      }
    }
  ],

  optimization: {
    minimize: true,
    minimizer: [
      new TerserPlugin({
        extractComments: false,
        terserOptions: {
          compress: {
            // List of functions that can be safely removed when their return values are not used.
            pure_funcs: ['console.log']
          }
        }
      })
    ]
  }
};